<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
$db=new PDO('mysql:host=db;charset=utf8mb4','root',getenv('QA_DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_STRINGIFY_FETCHES=>true]);
$tables=$db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='qa_reference' AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
$ignore=['nb_analytics','nb_counter','nb_counter_config','nb_counter_data','nb_counter_route','nb_admin_audit','nb_admin_privacy_access','nb_schema_migrations'];
$pass=0;$fail=0;
function customerHash($db,$schema,$table,$cols){
 $s=$db->query('SELECT '.implode(',',array_map(static function($c){return '`'.$c.'`';},$cols)).' FROM `'.$schema.'`.`'.$table.'`');
 $h=[];while($row=$s->fetch(PDO::FETCH_NUM))$h[]=hash('sha256',serialize($row));sort($h,SORT_STRING);return[count($h),hash('sha256',implode('',$h))];
}
foreach($tables as $table){
 if(in_array($table,$ignore,true))continue;
 $s=$db->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='qa_reference' AND TABLE_NAME=? ORDER BY ORDINAL_POSITION");$s->execute([$table]);$cols=$s->fetchAll(PDO::FETCH_COLUMN);
 if($table==='nb_admin')$cols=array_values(array_diff($cols,['last_login_at','password_changed_at','updated_at']));
 // Legacy banner/FAQ insert+delete legitimately touches neighbour content timestamps.
 // Continue comparing every content field, ID and sort_no; do not exempt popup/board ordering.
 if(in_array($table,['nb_banners','nb_faqs'],true))$cols=array_values(array_diff($cols,['updated_at']));
 $before=customerHash($db,'qa_reference',$table,$cols);$after=customerHash($db,'dbusrdaehakro0605',$table,$cols);
 if($before===$after){$pass++;echo 'PASS customer rows preserved after admin QA '.$table."\n";}
 else{$fail++;$changed=[];foreach($cols as $c)if(customerHash($db,'qa_reference',$table,[$c])!==customerHash($db,'dbusrdaehakro0605',$table,[$c]))$changed[]=$c;echo 'FAIL customer table '.$table.' differing column names: '.implode(',',$changed)."\n";}
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
