<?php
// Isolated fixtures only: no customer rows or settings are changed.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) exit(1);
ob_start();
$root = dirname(__DIR__);
require $root.'/config/env.php';
require $root.'/inc/lib/session.boot.php';
if (env('APP_ENV') !== 'development') exit(1);
require $root.'/src/Database/DB.php';
require $root.'/inc/lib/db.php';
foreach (['HOST','NAME','USER','PASS','PORT'] as $key) if (!defined('DB_'.$key)) define('DB_'.$key, env('DB_'.$key));
define('DB_CHARSET', 'utf8mb4');
require $root.'/nol-gate/lib/Acl.php';
require $root.'/nol-gate/lib/upload.guard.php';
$db = DB::getInstance(); $pass=0; $fail=0; $admin=0; $sessions=[]; $files=[]; $dirs=[];
$uid='qa_boundary_'.bin2hex(random_bytes(5));
function boundaryCheck($ok, $label) { global $pass,$fail; $ok?$pass++:$fail++; echo ($ok?'PASS ':'FAIL ').$label."\n"; }
function boundaryHttp($path, $sid, array $data, ?array $file=null, ?string $csrf=null): array {
    $data['_csrf'] = $csrf ?? $GLOBALS['qaCsrf'];
    $boundary='qa'.bin2hex(random_bytes(12)); $body='';
    foreach($data as $k=>$v) $body.="--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
    if($file) $body.="--$boundary\r\nContent-Disposition: form-data; name=\"".($file[2]??'file')."\"; filename=\"".$file[0]."\"\r\nContent-Type: image/png\r\n\r\n".$file[1]."\r\n";
    $body.="--$boundary--\r\n";
    $qaHost=(string)env('GATE_HOST','gate.local');
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Host: ".$qaHost."\r\nCookie: ".session_name()."=$sid\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: multipart/form-data; boundary=$boundary\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $response=file_get_contents('http://127.0.0.1'.$path,false,$ctx); preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($response,true),(string)$response];
}
try {
    foreach(['board.copy'=>'create','category.add'=>'create','category.save'=>'update','category.delete'=>'delete','setting.config.save'=>'update','rental.setting.save'=>'update'] as $mode=>$expected) {
        $_SERVER['SCRIPT_NAME']='/pages/board/ajax/board.process.php'; $_POST=['mode'=>$mode];
        boundaryCheck(Acl::resolve()===['board',$expected], 'ACL mode '.$mode);
    }
    foreach(['board','works'] as $menu) {
        $_SERVER['SCRIPT_NAME']='/pages/'.$menu.'/ajax/upload.php'; $_POST=['mode'=>'save','_method'=>'delete'];
        boundaryCheck(Acl::resolve()===[$menu,'delete'], $menu.' method overrides decoy mode');
        $_POST=['mode'=>'delete','_method'=>'post'];
        boundaryCheck(Acl::resolve()===[$menu,'upload'], $menu.' upload action from actual parser');
        boundaryCheck((new Acl(1,false,[$menu=>['update'=>true]]))->allows($menu,'upload'), $menu.' update-only can attach');
        boundaryCheck(!(new Acl(1,false,[$menu=>['delete'=>true]]))->allows($menu,'upload'), $menu.' delete-only cannot attach');
    }
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,phone,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES('NOLTHE',?,?,? ,?,'','Y',2,NOW(),NOW(),NOW(),?)")
       ->execute([$uid,password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT),'QA',$uid.'@example.com',$token=bin2hex(random_bytes(32))]);
    $admin=(int)$db->lastInsertId();
    if(session_status()===PHP_SESSION_ACTIVE) session_write_close();
    session_id(''); session_start();
    $GLOBALS['qaCsrf'] = bin2hex(random_bytes(32));
    $_SESSION=['_csrf'=>$GLOBALS['qaCsrf'],'no_adm_login_no'=>$admin,'no_adm_login_uid'=>$uid,'no_adm_login_uname'=>'QA','no_adm_login_role_id'=>2,'no_adm_login_role'=>'manager','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];
    $sid=session_id(); $sessions[]=$sid; session_write_close();
    if(posix_geteuid()===0) chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII=');
    foreach(['board','works'] as $menu) {
        $path='/pages/'.$menu.'/ajax/upload.php';
        AclModel::replaceAll($admin,[$menu=>['create'=>true]]);
        [$status]=boundaryHttp($path,$sid,['_method'=>'delete','mode'=>'save','link'=>'uploads/'.$menu.'/missing']);
        boundaryCheck($status===403,$menu.' create-only delete denied via HTTP');
        AclModel::replaceAll($admin,[$menu=>['update'=>true]]);
        foreach(['',bin2hex(random_bytes(32))] as $invalidCsrf) {
            [$status]=boundaryHttp($path,$sid,['_method'=>'post','extension'=>'png'],['valid.png',$png],$invalidCsrf);
            boundaryCheck($status===403,$menu.' missing or foreign CSRF token denied');
        }
        [$status,$json]=boundaryHttp($path,$sid,['_method'=>'post','extension'=>'png'],['fake.png','<?php echo "not an image";']);
        boundaryCheck($status===200 && empty($json['success']),$menu.' MIME spoof denied');
        [$status,$json]=boundaryHttp($path,$sid,['_method'=>'post','extension'=>'png'],['file.php.png',$png]);
        boundaryCheck($status===200 && empty($json['success']),$menu.' double extension denied');
        [$status,$json]=boundaryHttp($path,$sid,['_method'=>'post','mode'=>'delete','extension'=>'png'],['valid.png',$png]);
        $stored=$json['filename']??'';
        boundaryCheck($status===200 && !empty($json['success']) && $stored!=='',$menu.' update-only valid image upload');
        if($stored) $files[]=$root.'/'.ltrim($stored,'/');
        $sibling=$root.'/uploads/'.$menu.'-'.$uid; mkdir($sibling,0777); $dirs[]=$sibling;
        $victim=$sibling.'/fixture.txt'; file_put_contents($victim,'synthetic'); $files[]=$victim;
        AclModel::replaceAll($admin,[$menu=>['delete'=>true]]);
        [$status,$json]=boundaryHttp($path,$sid,['_method'=>'delete','link'=>'uploads/'.$menu.'/../'.$menu.'-'.$uid.'/fixture.txt']);
        boundaryCheck($status===200 && empty($json['success']) && is_file($victim),$menu.' sibling-prefix traversal denied');
        [$status,$json]=boundaryHttp($path,$sid,['_method'=>'delete','link'=>$stored]);
        boundaryCheck($status===200 && !empty($json['success']) && !is_file($root.'/'.ltrim($stored,'/')),$menu.' valid image deletion');
        $q=$db->prepare('SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=? AND actor_uid=? AND entity=?'); $q->execute([$admin,$uid,$menu]);
        boundaryCheck((int)$q->fetchColumn()===2,$menu.' exactly one create and one delete audit; failures absent');
    }
    if (env('DB_USER') === 'qa_app') {
        // These endpoints adjust sort order; run only against the isolated dump.
        AclModel::replaceAll($admin, array_fill_keys(['board','works','siteinfo','design','faq','inquiry'], ['view'=>true,'create'=>true,'update'=>true,'delete'=>true]));
        $cases = [
            'works'=>['/pages/works/process.php','nb_works','title',['title'=>$uid,'is_published'=>0],null],
            'privacy'=>['/pages/privacy/process.php','nb_privacy_policy','title',['title'=>$uid,'content'=>'QA','apply_date'=>'2026-09-28'],null],
            'setting'=>['/Controller/SettingController.php','nb_site_tags','title',['title'=>$uid,'tag_content'=>'QA','location'=>1],null],
            'seo'=>['/Controller/SeoController.php','nb_branch_seos','path',['path'=>'/'.$uid,'page_title'=>'QA','meta_title'=>'QA','meta_description'=>'QA','meta_keywords'=>'QA'],null],
            'faq'=>['/Controller/FaqController.php','nb_faqs','question',['categories'=>1,'question'=>$uid,'answer'=>'QA','sort_no'=>1],null],
            'popup'=>['/Controller/PopupController.php','nb_popups','title',['title'=>$uid,'popup_type'=>1,'popup_path'=>'/'.$uid,'sort_no'=>1],['valid.png',$png,'popup_image']],
            'banner'=>['/Controller/BannerController.php','nb_banners','title',['title'=>$uid,'banner_type'=>1,'sort_no'=>1],['valid.png',$png,'banner_image']],
        ];
        foreach ($cases as $entity => [$path,$table,$key,$data,$upload]) {
            [$status,$json] = boundaryHttp($path,$sid,['mode'=>'insert']+$data,$upload);
            $q=$db->prepare("SELECT id FROM $table WHERE $key=? ORDER BY id DESC LIMIT 1"); $q->execute([$data[$key]]); $id=(int)$q->fetchColumn();
            boundaryCheck($status===200 && !empty($json['success']) && $id>0,$entity.' actual HTTP create');
            if (!$id) continue;
            [$status,$json]=boundaryHttp($path,$sid,['mode'=>'update','id'=>$id]+$data);
            boundaryCheck($status===200 && !empty($json['success']),$entity.' same-value update remains valid');
            if ($upload) {
                $q=$db->prepare("SELECT ".$upload[2]." FROM $table WHERE id=?"); $q->execute([$id]); $oldFile=$q->fetchColumn();
                $oldPath=$root.'/uploads/'.$entity.'s/'.$oldFile; $files[]=$oldPath;
                [$status,$json]=boundaryHttp($path,$sid,['mode'=>'update','id'=>$id]+$data,['fake.png','not an image',$upload[2]]);
                boundaryCheck(empty($json['success']) && is_file($oldPath),$entity.' invalid replacement preserves existing file');
            }
            $q=$db->prepare('SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=? AND actor_uid=?');$q->execute([$admin,$uid]);$before=(int)$q->fetchColumn();
            foreach(['update','delete'] as $mode) {
                boundaryHttp($path,$sid,['mode'=>$mode,'id'=>2147483647]+$data);
            }
            $q->execute([$admin,$uid]);
            boundaryCheck((int)$q->fetchColumn()===$before,$entity.' nonexistent update/delete create no audit');
            if ($entity==='privacy') {
                [$status,$json]=boundaryHttp($path,$sid,['mode'=>'delete','id'=>$id]);
            } else {
                [$status,$json]=boundaryHttp($path,$sid,['mode'=>'delete_array','ids'=>json_encode([$id,$id,2147483647])]);
            }
            boundaryCheck($status===200 && !empty($json['success']),$entity.' delete succeeds');
            $q=$db->prepare("SELECT COUNT(*) FROM $table WHERE id=?");$q->execute([$id]);
            boundaryCheck((int)$q->fetchColumn()===0,$entity.' deleted row absent');
            if($entity!=='privacy') {
                $q=$db->prepare("SELECT detail_json FROM nb_admin_audit WHERE actor_no=? AND actor_uid=? AND entity=? AND action='delete' ORDER BY no DESC LIMIT 1");$q->execute([$admin,$uid,$entity]);
                boundaryCheck((json_decode($q->fetchColumn(),true)['ids']??null)===[$id],$entity.' bulk audit only real unique ID');
            }
        }
        $boardNo=(int)$db->query('SELECT no FROM nb_board_manage ORDER BY no LIMIT 1')->fetchColumn();
        $path='/pages/board/ajax/board.process.php';
        [$status,$json]=boundaryHttp($path,$sid,['mode'=>'save','board_no'=>$boardNo,'title'=>$uid,'contents'=>'<p>QA fixture</p>','write_name'=>'QA']);
        $q=$db->prepare('SELECT no FROM nb_board WHERE title=? ORDER BY no DESC LIMIT 1');$q->execute([$uid]);$postId=(int)$q->fetchColumn();
        boundaryCheck($status===200 && ($json['result']??'')==='success' && $postId>0,'board actual HTTP create');
        if($postId) {
            [$status,$json]=boundaryHttp('/pages/board/ajax/board.comment.process.php',$sid,['mode'=>'save','no'=>$postId,'board_no'=>$boardNo,'comment'=>'QA']);
            $q=$db->prepare('SELECT no FROM nb_board_comment WHERE parent_no=? ORDER BY no DESC LIMIT 1');$q->execute([$postId]);$commentId=(int)$q->fetchColumn();
            boundaryCheck(($json['result']??'')==='success' && $commentId>0,'board comment create');
            [$status,$json]=boundaryHttp('/pages/board/ajax/board.comment.process.php',$sid,['mode'=>'delete','no'=>$commentId,'board_no'=>2147483647]);
            $q=$db->prepare('SELECT comment_cnt FROM nb_board WHERE no=?');$q->execute([$postId]);
            boundaryCheck(($json['result']??'')==='success' && (int)$q->fetchColumn()===0,'comment delete uses stored parent rather than forged parent');
            [$status,$json]=boundaryHttp($path,$sid,['mode'=>'board.copy','no'=>$postId]);
            $q=$db->prepare('SELECT no FROM nb_board WHERE title=? ORDER BY no');$q->execute([$uid]);$postIds=$q->fetchAll(PDO::FETCH_COLUMN);
            boundaryCheck(($json['result']??'')==='success' && count($postIds)===2,'board copy creates exactly one row');
            foreach($postIds as $post) boundaryHttp($path,$sid,['mode'=>'delete','no'=>$post]);
            $q=$db->prepare('SELECT COUNT(*) FROM nb_board WHERE title=?');$q->execute([$uid]);
            boundaryCheck((int)$q->fetchColumn()===0,'board actual HTTP delete');
        }
        foreach(['login.process.php','mfa.email.php','mfa.process.php','mfa.resend.php','logout.php'] as $endpoint) {
            $ctx=stream_context_create(['http'=>['header'=>'Host: gate.local','ignore_errors'=>true,'follow_location'=>0]]);
            file_get_contents('http://127.0.0.1/lib/login/'.$endpoint,false,$ctx);
            boundaryCheck(strpos($http_response_header[0]??'','405')!==false,'GET cannot mutate '.$endpoint);
        }
        $db->prepare("INSERT INTO nb_request(sitekey,name,email,phone,performance_name,file_1,org_file_1,is_confirmed) VALUES('NOLTHE',?,?,?,?,?,?,0)")->execute(['검증담당자',$uid.'@example.com','01012345678',$uid,$uid.'.txt','qa.txt']);
        $request=(int)$db->lastInsertId();
        $private=$root.'/uploads/request/'.$uid.'.txt';file_put_contents($private,'QA synthetic private attachment');$files[]=$private;
        $ctx=stream_context_create(['http'=>['header'=>"Host: gate.local\r\nX-Requested-With: XMLHttpRequest",'ignore_errors'=>true,'follow_location'=>0]]);
        file_get_contents('http://127.0.0.1/pages/inquiry/view.php?no='.$request,false,$ctx);
        $q=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE target_no=? AND entity='inquiry' AND actor_no=0");$q->execute([$request]);
        boundaryCheck(strpos($http_response_header[0]??'','401')!==false && (int)$q->fetchColumn()===0,'anonymous inquiry denied before subject query and privacy audit');
        $response=boundaryHttp('/pages/inquiry/download.php',$sid,['no'=>$request,'slot'=>1,'reason'=>'QA verification']);
        boundaryCheck($response[0]===403,'non-super private attachment denied');
        $db->prepare('UPDATE nb_admin SET role_id=1 WHERE no=?')->execute([$admin]);
        session_id($sid);session_start();$_SESSION['no_adm_login_role_id']=1;$_SESSION['no_adm_login_role']='superadmin';session_write_close();
        $response=boundaryHttp('/pages/inquiry/download.php',$sid,['no'=>$request,'slot'=>1,'reason'=>'QA verification']);
        boundaryCheck($response[0]===200 && $response[2]==='QA synthetic private attachment','super private download returns exact bytes');
        $q=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE target_no=? AND actor_no=? AND action='download' AND reason='QA verification'");$q->execute([$request,$admin]);
        boundaryCheck((int)$q->fetchColumn()===1,'NOL private download logs subject and reason');
        $db->prepare('DELETE FROM nb_request WHERE no=? AND performance_name=?')->execute([$request,$uid]);
        $db->prepare("DELETE FROM nb_admin_privacy_access WHERE target_no=? AND entity='inquiry'")->execute([$request]);
    } else {
        echo "SKIP sort-changing CRUD matrix requires isolated qa_app database\n";
    }
} catch(Throwable $e) { boundaryCheck(false,$e->getMessage()); }
finally {
    foreach($files as $file) if(is_file($file)) unlink($file);
    foreach($dirs as $dir) if(is_dir($dir)) rmdir($dir);
    if($admin) {
        $db->prepare('DELETE FROM nb_admin_acl WHERE admin_no=?')->execute([$admin]);
        $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=? AND actor_uid=?')->execute([$admin,$uid]);
        $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);
    }
    foreach($sessions as $sid) { $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid; if(is_file($file)) unlink($file); }
}
echo "pass=$pass fail=$fail\n"; exit($fail?1:0);
