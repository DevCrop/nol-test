<?php
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || APP_ENV !== 'development' || DB_USER !== 'qa_app') exit(1);
$db = DB::getInstance();
$raw = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4', 'root', (string)getenv('DB_ROOT_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$table = 'nb_qa_atomic_'.bin2hex(random_bytes(4)); $trigger = 'qa_audit_'.bin2hex(random_bytes(5));
$actor = 'qa_atomic_'.bin2hex(random_bytes(5)); $directory = sys_get_temp_dir().'/'.$actor;
function atomicFault(PDO $raw, string $trigger, string $table, bool $enable): void {
    $raw->exec('DROP TRIGGER IF EXISTS `'.$trigger.'`');
    if ($enable) $raw->exec("CREATE TRIGGER `$trigger` BEFORE INSERT ON nb_admin_audit FOR EACH ROW BEGIN IF NEW.entity='$table' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA required audit failure'; END IF; END");
}
function atomicReject(callable $operation): bool {
    try { $operation(); return false; } catch (Throwable $error) { return true; }
}
function atomicRows(PDO $raw, string $table): array { return $raw->query('SELECT * FROM `'.$table.'` ORDER BY no')->fetchAll(PDO::FETCH_ASSOC); }
try {
    $raw->exec("CREATE TABLE `$table` (no INT NOT NULL AUTO_INCREMENT PRIMARY KEY, content VARCHAR(100) NOT NULL, file VARCHAR(100) NOT NULL DEFAULT '') ENGINE=InnoDB");
    $raw->exec("INSERT INTO `$table`(content,file) VALUES('original','old.txt'),('second','')");
    $legacy = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $legacy->setAttribute(PDO::ATTR_STATEMENT_CLASS, [\Security\AuditedStatement::class, [$legacy]]);
    qa_expect((int)$legacy->query('SELECT 1')->fetchColumn() === 1, 'staged FTP classes preserve public reads on legacy PDO');
    $legacy->prepare('INSERT INTO `'.$table.'`(content) VALUES(?)')->execute(['staged public']);
    $legacyId = (int)$legacy->lastInsertId();
    qa_expect($legacyId > 2 && $raw->query('SELECT content FROM `'.$table.'` WHERE no='.$legacyId)->fetchColumn() === 'staged public', 'staged FTP classes preserve public insert and native id');
    $raw->exec('DELETE FROM `'.$table.'` WHERE no='.$legacyId);
    $_SERVER['REQUEST_METHOD']='POST'; $_SERVER['SCRIPT_NAME']='/admin/pages/board/ajax/board.process.php';
    $_SESSION['no_adm_login_no']=0; $_SESSION['no_adm_login_uid']=$actor; $_POST['no']=1;
    \Security\AuditLogger::captureRequest();
    $baseline=atomicRows($raw,$table);
    qa_expect(atomicReject(fn()=> $legacy->prepare('UPDATE `'.$table.'` SET content=? WHERE no=?')->execute(['staged admin',1])) && atomicRows($raw,$table)===$baseline, 'staged FTP administrator write waits for new connection before DML');
    atomicFault($raw,$trigger,$table,true);
    foreach ([
        'create'=>fn()=> $db->prepare('INSERT INTO `'.$table.'`(content) VALUES(?)')->execute(['new']),
        'update'=>fn()=> $db->prepare('UPDATE `'.$table.'` SET content=? WHERE no=?')->execute(['changed',1]),
        'delete'=>fn()=> $db->prepare('DELETE FROM `'.$table.'` WHERE no=?')->execute([1]),
        'batch delete'=>fn()=> $db->prepare('DELETE FROM `'.$table.'` WHERE no IN(?,?)')->execute([1,2]),
        'native exec'=>fn()=> $db->exec("UPDATE `$table` SET content='exec' WHERE no=1"),
        'native query'=>fn()=> $db->query("DELETE FROM `$table` WHERE no=1"),
        'comment prefixed update'=>fn()=> $db->prepare("/* ordinary */ UPDATE `$table` SET content=? WHERE no=?")->execute(['comment',1]),
    ] as $name=>$operation) {
        qa_expect(atomicReject($operation) && atomicRows($raw,$table)===$baseline, 'required audit failure rolls back '.$name);
        qa_expect(!$db->inTransaction(), $name.' leaves no owned transaction');
    }
    $db->beginTransaction();
    qa_expect(atomicReject(fn()=> $db->prepare('UPDATE `'.$table.'` SET content=? WHERE no=?')->execute(['nested',1])), 'nested mutation propagates required audit failure');
    qa_expect($db->inTransaction(),'nested failure preserves caller transaction');
    $db->commit();
    qa_expect(atomicRows($raw,$table)===$baseline,'catch then outer commit cannot persist unaudited change');
    mkdir($directory); file_put_contents($directory.'/old.txt','old'); file_put_contents($directory.'/new.txt','new');
    $files=\Security\UploadLifecycle::forTable($directory,$db,$table,['file']); $files->track('new.txt'); $files->retire('old.txt');
    try { $db->prepare('UPDATE `'.$table.'` SET file=? WHERE no=?')->execute(['new.txt',1]); $files->commit(); }
    catch (Throwable $error) { $files->rollback(); }
    qa_expect(is_file($directory.'/old.txt') && !is_file($directory.'/new.txt') && atomicRows($raw,$table)===$baseline,'audit fault preserves old upload and removes staged replacement');
    qa_expect((int)$raw->query("SELECT COUNT(*) FROM nb_admin_audit WHERE entity='$table'")->fetchColumn()===0,'failed mutations leave no success audit');
    atomicFault($raw,$trigger,$table,false);
    $raw->exec('ALTER TABLE `'.$table.'` ENGINE=MyISAM');
    qa_expect(atomicReject(fn()=> $db->prepare('UPDATE `'.$table.'` SET content=? WHERE no=?')->execute(['unsafe',1])) && atomicRows($raw,$table)===$baseline, 'missing transactional engine blocks BEFORE business write');
    $raw->exec('ALTER TABLE `'.$table.'` ENGINE=InnoDB');
    $insert=$db->prepare('INSERT INTO `'.$table.'`(content) VALUES(:content)');
    $insert->bindValue(':content','first'); $insert->execute(); $first=(int)$db->lastInsertId();
    qa_expect($first>2 && $raw->query('SELECT content FROM `'.$table.'` WHERE no='.$first)->fetchColumn()==='first' && $insert->rowCount()===1,'bindValue insert preserves business id and row count after audit commit');
    $insert->bindValue(':content','second insert'); $insert->execute(); $second=(int)$db->lastInsertId();
    qa_expect($second===$first+1,'reused prepared insert exposes next business id');
    $update=$db->prepare('UPDATE `'.$table.'` SET content=:content WHERE no=:no');
    $value='bound one'; $id=1; $update->bindParam(':content',$value); $update->bindParam(':no',$id,PDO::PARAM_INT); $update->execute();
    $value='bound two'; $id=2; $update->execute();
    qa_expect($raw->query('SELECT content FROM `'.$table.'` WHERE no=1')->fetchColumn()==='bound one' && $raw->query('SELECT content FROM `'.$table.'` WHERE no=2')->fetchColumn()==='bound two','bindParam reflects reference values on repeated executions');
    $before=(int)$raw->query("SELECT COUNT(*) FROM nb_admin_audit WHERE entity='$table'")->fetchColumn();
    $update->execute([':content'=>'unused',':no'=>2147483647]);
    qa_expect($update->rowCount()===0 && (int)$raw->query("SELECT COUNT(*) FROM nb_admin_audit WHERE entity='$table'")->fetchColumn()===$before,'zero affected rows do not create success audit');
    $db->beginTransaction();
    $db->prepare('UPDATE `'.$table.'` SET content=? WHERE no=?')->execute(['outer rollback',1]);
    $db->rollBack();
    qa_expect($raw->query('SELECT content FROM `'.$table.'` WHERE no=1')->fetchColumn()==='bound one' && (int)$raw->query("SELECT COUNT(*) FROM nb_admin_audit WHERE entity='$table'")->fetchColumn()===$before,'caller rollback cancels business AND required audit');
    qa_expect($db->query('SELECT content FROM `'.$table.'` WHERE no=1',PDO::FETCH_COLUMN)->fetchColumn()==='bound one','query SELECT fetch mode remains compatible');
    qa_expect($db->exec("UPDATE `$table` SET content='exec valid' WHERE no=1")===1,'exec DML successful control is audited');
    $query=$db->query("UPDATE `$table` SET content='query valid' WHERE no=2");
    qa_expect($query->rowCount()===1,'query DML successful control is audited');
    foreach (["REPLACE INTO `$table`(no,content) VALUES(1,'bad')", "UPDATE `$table` SET content='bad' WHERE no=1; DELETE FROM `$table`", "/*! UPDATE `$table` SET content='bad' */", "UPDATE `$table` a JOIN `$table` b ON a.no=b.no SET a.content='bad'"] as $sql) {
        $beforeRows=atomicRows($raw,$table); qa_expect(atomicReject(fn()=> $db->prepare($sql)->execute()) && atomicRows($raw,$table)===$beforeRows,'unsupported SQL cannot bypass required audit boundary');
    }
    $boardIds=[];
    $boardInsert=$raw->prepare("INSERT INTO nb_board(sitekey,board_no,title,contents,regdate,is_view) VALUES('BLUESQ',9,?,'QA',NOW(),'N')");
    for($i=0;$i<3;$i++){ $boardInsert->execute([$actor.'_'.$i]); $boardIds[]=(int)$raw->lastInsertId(); }
    $delete=$db->prepare("/* leading */ DELETE FROM nb_board WHERE no=:no AND title=:title /* trailing */;");
    $delete->bindValue(':no',$boardIds[0],PDO::PARAM_INT); $delete->bindValue(':title',$actor.'_0'); $delete->execute();
    $delete=$db->prepare('DELETE FROM nb_board WHERE no=:no AND title=:title');
    $boardId=$boardIds[1]; $boardTitle=$actor.'_1'; $delete->bindParam(':no',$boardId,PDO::PARAM_INT); $delete->bindParam(':title',$boardTitle); $delete->execute();
    $boardId=$boardIds[2]; $boardTitle=$actor.'_2'; $delete->execute();
    $logs=$raw->prepare("SELECT target_no,detail_json FROM nb_admin_audit WHERE actor_uid=? AND entity='nb_board' AND action='delete' ORDER BY no"); $logs->execute([$actor]); $logged=$logs->fetchAll(PDO::FETCH_ASSOC);
    qa_expect(count($logged)===3 && array_map(static fn($row)=>(int)$row['target_no'],$logged)===$boardIds && array_map(static fn($row)=>json_decode($row['detail_json'],true)['target_ids']??[],$logged)===array_map(static fn($id)=>[$id],$boardIds),'deletion snapshot preserves bindValue/bindParam references, SQL comments and exact target ids');
    $largeDetail=['affected_rows'=>1000,'target_ids'=>range(90000000,90000999)];
    $encodedLarge=json_encode($largeDetail,JSON_UNESCAPED_UNICODE);
    \Security\AuditLogger::record('delete',$table,0,'',$largeDetail,true);
    $q=$raw->prepare('SELECT detail_json FROM nb_admin_audit WHERE entity=? AND actor_uid=? ORDER BY no DESC LIMIT 1'); $q->execute([$table,$actor]);
    qa_expect(strlen($encodedLarge)>8000 && $q->fetchColumn()===$encodedLarge,'required audit preserves full 1000 high-id target detail beyond 8000 bytes');
    qa_expect(atomicReject(fn()=> \Security\AuditLogger::record('delete',$table,0,'',['safe_key'=>"\xff"],true)),'required audit rejects invalid JSON encoding');
    qa_expect(atomicReject(fn()=> \Security\AuditLogger::record('delete',$table,0,'',['safe_key'=>str_repeat('x',65536)],true)),'required audit rejects detail beyond TEXT byte capacity without truncation');
    $db->prepare("INSERT INTO nb_admin_privacy_access(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,subject_label,task,reason,created_at) VALUES('BLUESQ',0,?,'127.0.0.1','view','qa',0,'','','',NOW())")->execute([$actor]);
    $privacy=(int)$db->lastInsertId();
    qa_expect($privacy>0 && $raw->query('SELECT actor_uid FROM nb_admin_privacy_access WHERE no='.$privacy)->fetchColumn()===$actor,'explicit privacy INSERT retains its own id');
} catch (Throwable $error) { qa_expect(false,get_class($error).': '.$error->getMessage()); }
finally {
    if($db->inTransaction())$db->rollBack();
    $raw->exec('DROP TRIGGER IF EXISTS `'.$trigger.'`');
    $raw->exec('DROP TABLE IF EXISTS `'.$table.'`');
    $q=$raw->prepare('DELETE FROM nb_board WHERE title LIKE ?');$q->execute([$actor.'_%']);
    $q=$raw->prepare('DELETE FROM nb_admin_audit WHERE entity=? OR actor_uid=?');$q->execute([$table,$actor]);
    $q=$raw->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_uid=?');$q->execute([$actor]);
    foreach(['old.txt','new.txt'] as $file)if(is_file($directory.'/'.$file))unlink($directory.'/'.$file);
    if(is_dir($directory))rmdir($directory);
}
qa_done();
