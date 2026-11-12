<?php
require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$uid='qa_log_visual';
$s=$db->prepare("SELECT no FROM nb_admin WHERE uid=? AND sitekey='BLUESQ'");$s->execute([$uid]);$id=(int)$s->fetchColumn();
if(($argv[1]??'')==='cleanup'){
 if($id){foreach(['nb_admin_audit','nb_admin_privacy_access']as $table)$db->prepare('DELETE FROM '.$table.' WHERE actor_no=?')->execute([$id]);$db->prepare("DELETE FROM nb_admin WHERE no=? AND uid=? AND sitekey='BLUESQ'")->execute([$id,$uid]);}
 echo "visual fixture removed\n";exit;
}
if($id)throw new RuntimeException('Fixture already exists; do not overwrite.');
$db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,menu_permissions)VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),'{}')")->execute([$uid,password_hash('QALogView9!',PASSWORD_DEFAULT),'화면검증','qa-log-visual@example.com']);$id=(int)$db->lastInsertId();
$a=$db->prepare("INSERT INTO nb_admin_audit(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,target_label,detail_json,created_at)VALUES('BLUESQ',?,?, '127.0.0.1',?,?,?,?,?,NOW())");
$p=$db->prepare("INSERT INTO nb_admin_privacy_access(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,subject_label,task,reason,created_at)VALUES('BLUESQ',?,?,'127.0.0.1',?,?,?,?,?,?,NOW())");
for($i=1;$i<=55;$i++){
 $action=['create','update','delete'][$i%3];$a->execute([$id,$uid,$action,$i%2?'nb_board':'account',9000+$i,'화면 검증용 '. $i,json_encode(['affected_rows'=>1,'target_ids'=>[9000+$i],'change_reason'=>'화면 구성 검증'],JSON_UNESCAPED_UNICODE)]);
 $action=['view','download','create','update','delete'][$i%5];$p->execute([$id,$uid,$action,'request',9000+$i,'홍*동','화면 구성 검증',$action==='download'?'자료 확인을 위한 검증':'']);
}
echo "visual fixture created; local-only synthetic records\n";
