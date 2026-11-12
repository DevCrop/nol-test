<?php
/** CLI-only: prepare private, additive account SQL; never applies it to a DB. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/inc/lib/security.bootstrap.php';
spl_autoload_register(static function(string $class):void{$p=dirname(__DIR__).'/inc/lib/classes/'.str_replace('\\','/',$class).'.php';if(is_file($p))require_once $p;});
require dirname(__DIR__).'/inc/lib/db.php';
umask(0077);
try{
    if($argc!==4)throw new RuntimeException('Usage: php deploy/prepare-admin-accounts.php REQUEST.json PRIVATE_OUTPUT_DIR ACTOR_UID');
    $manifest=json_decode((string)file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
    $output=realpath($argv[2]);$root=realpath(dirname(__DIR__));
    if(!$output||!is_dir($output))throw new RuntimeException('Prepare the private output directory first.');
    $normalized=str_replace('\\','/',$output);$webRoot=str_replace('\\','/',$root);
    if(strpos($normalized.'/', $webRoot.'/')===0 && strpos($normalized.'/', $webRoot.'/deploy/private-handoff/')!==0)throw new RuntimeException('Output must be outside the web root or in excluded deploy/private-handoff.');
    $db=DB::getInstance();
    if(strcasecmp((string)$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin'")->fetchColumn(),'InnoDB')!==0)throw new RuntimeException('Administrator table must be transactional.');
    $db->query('SELECT menu_permissions,authority_valid_from,authority_valid_until,temporary_password_expires_at FROM nb_admin LIMIT 0');
    $actor=\Security\AdminAccount::findByUid($argv[3]);
    if(!$actor||$actor['role_code']!=='super'||$actor['active_status']!=='Y')throw new RuntimeException('An active existing super administrator is required as operator.');
    $requests=$manifest['accounts']??null;
    if(!is_array($requests)||!$requests||count($requests)>100)throw new RuntimeException('Invalid account request list.');
    $reason=AccountAccessPolicy::reason(['change_reason'=>$manifest['change_reason']??null]);
    $q=static fn($v)=>$v===null?'NULL':$db->quote((string)$v);
    $sql=["-- Private account setup. Apply required schema first. Stop and ROLLBACK on any error.","-- No existing password, role or profile is overwritten. Execute the whole file.","SET NAMES utf8mb4;","START TRANSACTION;","SET @setup_actor=(SELECT no FROM nb_admin WHERE sitekey='BLUESQ' AND uid=".$q($argv[3])." AND role_code='super' AND active_status='Y' LIMIT 1);"];
    $guard=static function(string $condition)use(&$sql):void{
        $sql[]="SET @setup_guard=IF(".$condition.", 'SELECT 1', 'SELECT * FROM __ACCOUNT_SETUP_ABORT__');";
        $sql[]='PREPARE setup_check FROM @setup_guard;';$sql[]='EXECUTE setup_check;';$sql[]='DEALLOCATE PREPARE setup_check;';
    };
    $guard("@setup_actor IS NOT NULL AND (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin')='InnoDB'");
    $credentials=[];$seenIds=[];$seenEmails=[];$ttl=max(300,min(604800,(int)blue_env('TEMP_PASSWORD_TTL_SECONDS',86400)));
    foreach($requests as $input){
        if(!is_array($input))throw new RuntimeException('Invalid account entry.');
        $uid=trim((string)($input['uid']??''));if(!preg_match('/^[A-Za-z0-9_]{4,20}$/D',$uid))throw new RuntimeException('Invalid account identifier.');
        $name=\Security\AccountValidator::name((string)($input['uname']??''));$email=\Security\AccountValidator::email((string)($input['email']??''));
        if(isset($seenIds[strtolower($uid)])||isset($seenEmails[$email]))throw new RuntimeException('Duplicate request identifier or email.');
        $seenIds[strtolower($uid)]=true;$seenEmails[$email]=true;
        if(($input['role_code']??'admin')!=='admin')throw new RuntimeException('This request exporter provisions regular administrators only.');
        $grants=\Security\AdminAcl::normalize((array)($input['permissions']??[]));$encoded=json_encode($grants,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $period=AccountAccessPolicy::period($input);$old=\Security\AdminAccount::findByUid($uid);
        if($old){
            if($old['uname']!==$name||strtolower($old['email'])!==$email||$old['role_code']!=='admin'||$old['active_status']!=='Y'||\Security\AdminAcl::grants($old)!==$grants||($old['authority_valid_from']??null)!==$period['authority_valid_from']||($old['authority_valid_until']??null)!==$period['authority_valid_until'])throw new RuntimeException('Existing requested account conflicts; review it without overwriting.');
            continue;
        }
        $st=$db->prepare("SELECT COUNT(*) FROM nb_admin WHERE sitekey='BLUESQ' AND LOWER(TRIM(email))=?");$st->execute([$email]);if((int)$st->fetchColumn()>0)throw new RuntimeException('Requested email is already registered.');
        do{$plain=\Security\TemporaryPassword::generate();}while(stripos($plain,$uid)!==false);
        \Security\AccountValidator::password($plain,$plain,$uid);$hash=password_hash($plain,PASSWORD_DEFAULT);
        $where="sitekey='BLUESQ' AND LOWER(TRIM(uid))=LOWER(".$q($uid).")";
        $same=$where.' AND uname='.$q($name).' AND email='.$q($email)." AND role_code='admin' AND active_status='Y' AND menu_permissions=".$q($encoded).' AND authority_valid_from <=> '.$q($period['authority_valid_from']).' AND authority_valid_until <=> '.$q($period['authority_valid_until']);
        $guard("((SELECT COUNT(*) FROM nb_admin WHERE ".$where.")=0 OR (SELECT COUNT(*) FROM nb_admin WHERE ".$same.")=1) AND (SELECT COUNT(*) FROM nb_admin WHERE sitekey='BLUESQ' AND LOWER(TRIM(email))=LOWER(".$q($email).") AND LOWER(TRIM(uid))<>LOWER(".$q($uid)."))=0");
        $sql[]="INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,login_fail_count,password_changed_at,password_must_change,temporary_password_expires_at,created_at,updated_at,authority_valid_from,authority_valid_until,menu_permissions) SELECT 'BLUESQ',".implode(',',[$q($uid),$q($hash),$q($name),$q($email)]).",'Y','admin',0,NOW(),1,DATE_ADD(NOW(),INTERVAL ".$ttl." SECOND),NOW(),NOW(),".implode(',',[$q($period['authority_valid_from']),$q($period['authority_valid_until']),$q($encoded)])." WHERE NOT EXISTS(SELECT 1 FROM nb_admin WHERE ".$where.");";
        $sql[]='SET @setup_inserted=ROW_COUNT();';
        $detail=['change_reason'=>$reason,'authority_before'=>[],'authority_after'=>['account_no'=>0,'account_identifier'=>$uid,'owner_display_name'=>$name,'role'=>'admin','permissions'=>$grants,'active_status'=>'Y','authority_valid_from'=>$period['authority_valid_from'],'authority_valid_until'=>$period['authority_valid_until']]];
        [$prefix,$suffix]=explode('"account_no":0',json_encode($detail,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),2);
        $detailSql='CONCAT('.$q($prefix.'"account_no":').',CAST(a.no AS CHAR),'.$q($suffix).')';
        $sql[]="INSERT INTO nb_admin_audit(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,target_label,detail_json,created_at) SELECT 'BLUESQ',@setup_actor,".$q($argv[3]).",'','create','account',a.no,".$q(\Security\PiiMask::identifier($uid)).','.$detailSql.",NOW() FROM nb_admin a WHERE a.".$where." AND @setup_inserted=1;";
        $credentials[]=['uid'=>$uid,'temporary_password'=>$plain,'expires_after_sql_seconds'=>$ttl];
    }
    $sql[]='COMMIT;';$stem='accounts-'.date('Ymd-His').'-'.bin2hex(random_bytes(3));
    foreach([$stem.'.sql'=>implode("\n",$sql)."\n",$stem.'-credentials.json'=>json_encode(['private'=>true,'activation'=>'Temporary deadline starts when SQL is applied; reissue expired credentials.','accounts'=>$credentials],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)] as $file=>$data){
        $handle=fopen($output.DIRECTORY_SEPARATOR.$file,'x');if(!$handle)throw new RuntimeException('Cannot create private output.');fwrite($handle,$data);fclose($handle);chmod($output.DIRECTORY_SEPARATOR.$file,0600);
    }
    echo json_encode(['prepared_accounts'=>count($credentials),'sql_file'=>$stem.'.sql','private_credentials_file'=>$stem.'-credentials.json'],JSON_UNESCAPED_UNICODE)."\n";
}catch(Throwable $e){fwrite(STDERR,'Account preparation failed: '.get_class($e)."; no database mutations performed.\n");exit(1);}
