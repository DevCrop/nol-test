<?php
// Local Docker fixtures only. Does not migrate tables or alter customer accounts.
if(PHP_SAPI!=='cli'||getenv('APP_ENV')!=='development'||getenv('DB_USER')!=='qa_app'||!is_file('/.dockerenv'))exit(1);
function txConnection(): PDO {
    return new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}
$db=txConnection();$other=txConnection();$uid='qa_tx_'.bin2hex(random_bytes(6));
$site=getenv('DB_NAME')==='dbusrbluesq0211'?'BLUESQ':'NOLTHE';$id=0;$fail=0;
function txCheck(bool $ok,string $label):void {global $fail;echo ($ok?'PASS ':'FAIL ').$label.PHP_EOL;if(!$ok)$fail++;}
try {
    $engine=$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin'")->fetchColumn();
    txCheck($engine==='InnoDB','administrator table supports transactions');
    if($engine!=='InnoDB')exit(1);
    $db->beginTransaction();
    $insert=$db->prepare('INSERT INTO nb_admin(sitekey,uid,upwd,uname,active_status,created_at) VALUES(?,?,?,?,?,NOW())');
    $insert->execute([$site,$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA','N']);
    $id=(int)$db->lastInsertId();$db->rollBack();
    $count=$db->prepare('SELECT COUNT(*) FROM nb_admin WHERE no=? AND uid=?');$count->execute([$id,$uid]);
    txCheck((int)$count->fetchColumn()===0,'rolled back insert leaves no account');
    $insert->execute([$site,$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA','N']);
    $id=(int)$db->lastInsertId();
    $before=$db->query('SELECT * FROM nb_admin WHERE no='.(int)$id)->fetch(PDO::FETCH_ASSOC);
    try {
        $db->beginTransaction();
        $db->prepare('UPDATE nb_admin SET uname=? WHERE no=?')->execute(['MUST ROLLBACK',$id]);
        $db->exec("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA injected failure'");
    }catch(PDOException $e){if($db->inTransaction())$db->rollBack();}
    $after=$db->query('SELECT * FROM nb_admin WHERE no='.(int)$id)->fetch(PDO::FETCH_ASSOC);
    txCheck($before===$after,'failure after update preserves all account values');
    $db->beginTransaction();$db->query('SELECT no FROM nb_admin WHERE no='.(int)$id.' FOR UPDATE')->fetchColumn();
    $other->exec('SET SESSION innodb_lock_wait_timeout=1');$other->beginTransaction();$blocked=false;
    try{$other->query('SELECT no FROM nb_admin WHERE no='.(int)$id.' FOR UPDATE')->fetchColumn();}
    catch(PDOException $e){$blocked=($e->errorInfo[1]??0)===1205;}
    finally{if($other->inTransaction())$other->rollBack();}
    txCheck($blocked,'concurrent credential transition cannot bypass row lock');
    $db->rollBack();$other->beginTransaction();
    $unlocked=$other->query('SELECT no FROM nb_admin WHERE no='.(int)$id.' FOR UPDATE')->fetchColumn();
    txCheck((int)$unlocked===$id,'row lock released after rollback');$other->rollBack();
}finally{
    if($db->inTransaction())$db->rollBack();
    if($other->inTransaction())$other->rollBack();
    $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=? AND sitekey=?')->execute([$id,$uid,$site]);
}
echo $fail?"TRANSACTION QA FAILED\n":"TRANSACTION QA PASSED\n";exit($fail?1:0);
