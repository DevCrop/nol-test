<?php
// Actual supplied backup is restored into isolated Docker databases before this CLI-only rehearsal.
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
$db=new PDO('mysql:host=db;dbname=dbusrbluesq0211;charset=utf8mb4','root',getenv('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_STRINGIFY_FETCHES=>true]);
$pass=0;$fail=0;
function releaseCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo($ok?'PASS ':'FAIL ').$label."\n";}
function releaseHash($db,$schema,$table,$cols){$s=$db->query('SELECT '.implode(',',array_map(static function($c){return '`'.$c.'`';},$cols)).' FROM `'.$schema.'`.`'.$table.'`');$rows=[];while($row=$s->fetch(PDO::FETCH_NUM))$rows[]=hash('sha256',serialize($row));sort($rows,SORT_STRING);return[count($rows),hash('sha256',implode('',$rows))];}
function releaseTables($db,$schema){$s=$db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME');$s->execute([$schema]);return $s->fetchAll(PDO::FETCH_COLUMN);}
function releaseColumns($db,$schema,$table){$s=$db->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');$s->execute([$schema,$table]);return $s->fetchAll(PDO::FETCH_COLUMN);}
$sql=file_get_contents(dirname(__DIR__).'/sql/manual/bluesquare-production-final-20261019.sql');
$run=static function()use($db,$sql){$body=preg_replace('/^\s*--[^\r\n]*$/m','',$sql);foreach(preg_split('/;\s*(?:\r?\n|$)/',$body)as $statement)if(trim($statement)!==''){$s=$db->query(trim($statement));while($s->nextRowset()){}$s->closeCursor();}};
try{
 $run();releaseCheck(true,'final combined SQL applies on supplied production backup copy');
 foreach(releaseTables($db,'qa_original')as $table){$cols=releaseColumns($db,'qa_original',$table);releaseCheck(releaseHash($db,'qa_original',$table,$cols)===releaseHash($db,'dbusrbluesq0211',$table,$cols),'original rows and columns preserved '.$table);}
 $added=array_values(array_diff(releaseTables($db,'dbusrbluesq0211'),releaseTables($db,'qa_original')));sort($added,SORT_STRING);$expected=['blue_schema_migrations','nb_admin_audit','nb_admin_privacy_access'];
 releaseCheck($added===$expected,'only approved audit/privacy/migration tables added');
 $q=$db->query("SELECT no,uid,role_code,active_status,(email='nineonelabs@gmail.com') AS recipient_matches FROM nb_admin WHERE no=1 AND uid='tmaster' AND sitekey='BLUESQ'");$admin=$q->fetch(PDO::FETCH_ASSOC);
 releaseCheck($admin&&$admin['role_code']==='super'&&$admin['active_status']==='Y'&&$admin['recipient_matches']==='1','exact customer-approved active super MFA recipient configured');
 $before=[];foreach(releaseTables($db,'dbusrbluesq0211')as $table)$before[$table]=releaseHash($db,'dbusrbluesq0211',$table,releaseColumns($db,'dbusrbluesq0211',$table));
 $run();$after=[];foreach(releaseTables($db,'dbusrbluesq0211')as $table)$after[$table]=releaseHash($db,'dbusrbluesq0211',$table,releaseColumns($db,'dbusrbluesq0211',$table));
 releaseCheck($before===$after,'second final SQL execution preserves all account and business state');
}catch(Throwable $e){releaseCheck(false,'SQL rehearsal exception (customer values redacted)');fwrite(STDERR,get_class($e).' code '.$e->getCode()."\n");}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
