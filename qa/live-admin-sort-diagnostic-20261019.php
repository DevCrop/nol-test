<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv'))exit(1);
$db=new PDO('mysql:host=db;charset=utf8mb4','root',getenv('QA_DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach(['nb_board'=>['no','sitekey,board_no'],'nb_popups'=>['id','popup_path'],'nb_banners'=>['id','banner_type'],'nb_faqs'=>['id','categories']] as $table=>$spec){
 [$key,$group]=$spec;
 $changed=$db->query('SELECT COUNT(*) FROM dbusrdaehakro0605.`'.$table.'` a JOIN qa_reference.`'.$table.'` b ON a.`'.$key.'`=b.`'.$key.'` WHERE NOT(a.sort_no <=> b.sort_no)')->fetchColumn();
 $duplicates=[];
 foreach(['qa_reference','dbusrdaehakro0605'] as $schema)$duplicates[]=(int)$db->query('SELECT COALESCE(SUM(n-1),0) FROM (SELECT COUNT(*) n FROM `'.$schema.'`.`'.$table.'` GROUP BY '.$group.',sort_no HAVING COUNT(*)>1) d')->fetchColumn();
 echo json_encode(['table'=>$table,'rows_with_changed_sort'=>(int)$changed,'duplicate_order_before'=>$duplicates[0],'duplicate_order_after'=>$duplicates[1]])."\n";
}
