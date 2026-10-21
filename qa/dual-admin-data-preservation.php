<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
$db=new PDO('mysql:host=db;charset=utf8mb4','root',getenv('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_STRINGIFY_FETCHES=>true]);
$pass=0;$fail=0;
function dataHash($db,$schema,$table,$cols){$q=$db->query('SELECT '.implode(',',array_map(static function($c){return '`'.$c.'`';},$cols)).' FROM `'.$schema.'`.`'.$table.'`');$rows=[];while($row=$q->fetch(PDO::FETCH_NUM))$rows[]=hash('sha256',serialize($row));sort($rows,SORT_STRING);return[count($rows),hash('sha256',implode('',$rows))];}
$tables=$db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='qa_reference' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table){
 if(in_array($table,['nb_admin_audit','nb_admin_privacy_access','blue_schema_migrations','nb_analytics','nb_counter','nb_counter_config','nb_counter_data','nb_counter_route'],true))continue;
 $q=$db->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='qa_reference' AND TABLE_NAME=? ORDER BY ORDINAL_POSITION");$q->execute([$table]);$cols=$q->fetchAll(PDO::FETCH_COLUMN);
 if($table==='nb_admin')$cols=array_values(array_diff($cols,['last_login_at','password_changed_at','created_at','updated_at']));
 $ok=dataHash($db,'qa_reference',$table,$cols)===dataHash($db,'dbusrbluesq0211',$table,$cols);
 $ok?$pass++:$fail++;echo($ok?'PASS ':'FAIL ').'original rows preserved '.$table."\n";
 if(!$ok){$changed=[];foreach($cols as $c)if(dataHash($db,'qa_reference',$table,[$c])!==dataHash($db,'dbusrbluesq0211',$table,[$c]))$changed[]=$c;echo 'Differing column names only: '.implode(',',$changed)."\n";}
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
