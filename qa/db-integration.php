<?php
require __DIR__ . '/bootstrap.php'; require_once dirname(__DIR__) . '/inc/lib/db.php';
$pdo = DB::getInstance(); qa_expect($pdo instanceof PDO, 'database connection');
foreach (['email','login_fail_count','login_locked_until','last_login_at','password_changed_at','password_must_change','login_token','role_code','idle_locked_at','created_at','updated_at'] as $column) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?'); $stmt->execute(['nb_admin',$column]); qa_expect((int) $stmt->fetchColumn() === 1, "nb_admin.{$column}");
}
foreach (['nb_admin_audit','nb_admin_privacy_access','blue_schema_migrations'] as $table) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $stmt->execute([$table]); qa_expect((int) $stmt->fetchColumn() === 1, "table {$table}");
}
$uid = 'qa_' . bin2hex(random_bytes(5)); $managedUid = '';
try {
    $stmt = $pdo->prepare("INSERT INTO nb_admin (sitekey,uid,upwd,uname,email,active_status,password_changed_at) VALUES ('BLUESQ',?,?,?,?, 'Y', NOW())");
    $stmt->execute([$uid,password_hash('Qa!234567890', PASSWORD_DEFAULT),'품질검사','qa@example.com']); $no = (int) $pdo->lastInsertId();
    $row = \Security\AdminAccount::findByUid($uid); qa_expect($row && \Security\AdminAccount::verifyPassword($row, 'Qa!234567890'), 'modern password verifies');
    for ($i = 0; $i < LOGIN_MAX_ATTEMPTS; $i++) { $current = \Security\AdminAccount::findByNo($no); \Security\AdminAccount::failure($current); }
    $row = \Security\AdminAccount::findByNo($no); qa_expect(!empty($row['login_locked_until']) && strtotime($row['login_locked_until']) > time(), 'fifth failure locks account');
    $pdo->prepare('UPDATE nb_admin SET login_fail_count=0,login_locked_until=NULL WHERE no=?')->execute([$no]);
    $row = \Security\AdminAccount::findByNo($no); \Security\AdminAccount::establish($row); $firstToken = (string) \Security\AdminAccount::findByNo($no)['login_token'];
    \Security\AdminAccount::establish($row); $row = \Security\AdminAccount::findByNo($no); qa_expect(strlen((string) $row['login_token']) === 64 && !hash_equals($firstToken, (string) $row['login_token']), 'new login invalidates previous token');
    $_SESSION['mfa_pending'] = ['admin_no'=>$no,'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()-1,'attempts'=>0,'sent_at'=>time(),'email'=>'qa@example.com'];
    $expired = false; try { \Security\Mfa::verify('123456'); } catch (RuntimeException $e) { $expired = strpos($e->getMessage(), '만료') !== false; }
    qa_expect($expired && empty($_SESSION['mfa_pending']), 'expired MFA is rejected and cleared');
    $_SESSION['mfa_pending'] = ['admin_no'=>$no,'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()+60,'attempts'=>MFA_MAX_ATTEMPTS-1,'sent_at'=>time(),'email'=>'qa@example.com'];
    $wrong = false; try { \Security\Mfa::verify('999999'); } catch (RuntimeException $e) { $wrong = true; }
    qa_expect($wrong && empty($_SESSION['mfa_pending']), 'MFA attempt limit clears pending challenge');
    $legacy = hash('sha256', 'Legacy!2345'); $pdo->prepare('UPDATE nb_admin SET upwd=? WHERE no=?')->execute([$legacy,$no]);
    $legacyRow = \Security\AdminAccount::findByNo($no); $legacyOk = \Security\AdminAccount::verifyPassword($legacyRow, 'Legacy!2345');
    $upgraded = (string) \Security\AdminAccount::findByNo($no)['upwd']; qa_expect($legacyOk && password_verify('Legacy!2345', $upgraded), 'legacy SHA-256 upgrades after successful login');
    $idleRow = \Security\AdminAccount::findByNo($no); $idleRow['last_login_at'] = date('Y-m-d H:i:s', strtotime('-' . (ACCOUNT_IDLE_DAYS + 1) . ' days'));
    $idleBlocked = false; try { \Security\AdminAccount::assertLoginAllowed($idleRow); } catch (RuntimeException $e) { $idleBlocked = strpos($e->getMessage(), '장기') !== false; }
    qa_expect($idleBlocked, 'long-idle account is rejected');
    \Security\AuditLogger::record('update','qa',$no,'qa'); \Security\PrivacyLogger::record('view','qa',$no,'홍길동','QA');
    qa_expect((int) $pdo->query("SELECT COUNT(*) FROM nb_admin_audit WHERE entity='qa'")->fetchColumn() > 0, 'audit log writes');
    qa_expect((int) $pdo->query("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE entity='qa'")->fetchColumn() > 0, 'privacy access log writes');

    $managedUid = 'qa_managed_' . bin2hex(random_bytes(3));
    $managedNo = \Security\AdminAccount::createManaged(['uid'=>$managedUid,'uname'=>'관리계정','email'=>$managedUid.'@example.com','active_status'=>'Y','role_code'=>'admin','password'=>'Valid!Pass123','password_confirm'=>'Valid!Pass123']);
    $managed = \Security\AdminAccount::findByNo($managedNo);
    qa_expect($managedNo > 0 && password_verify('Valid!Pass123', (string) $managed['upwd']) && (int) $managed['password_must_change'] === 1, 'managed account creation forces password change');
    $duplicateRejected = false; try { \Security\AdminAccount::createManaged(['uid'=>$managedUid,'uname'=>'중복','email'=>'other@example.com','active_status'=>'Y','role_code'=>'admin','password'=>'Valid!Pass123','password_confirm'=>'Valid!Pass123']); } catch (RuntimeException $e) { $duplicateRejected = true; }
    qa_expect($duplicateRejected, 'duplicate managed account rejected');
    $pdo->prepare('UPDATE nb_admin SET login_token=?,login_locked_until=NOW(),idle_locked_at=NOW() WHERE no=?')->execute(['old-token',$managedNo]);
    qa_expect(\Security\AdminAccount::unlock($managedNo, 1), 'managed account unlock');
    $managed = \Security\AdminAccount::findByNo($managedNo);
    qa_expect(empty($managed['login_token']) && empty($managed['login_locked_until']) && empty($managed['idle_locked_at']), 'unlock clears all account locks and session');
    qa_expect(\Security\AdminAccount::resetPassword($managedNo, 'Reset!Pass123', 'Reset!Pass123', 1), 'managed password reset');
    $managed = \Security\AdminAccount::findByNo($managedNo);
    qa_expect(password_verify('Reset!Pass123', (string) $managed['upwd']) && (int) $managed['password_must_change'] === 1 && empty($managed['login_token']), 'password reset invalidates session and forces change');
    qa_expect(\Security\AdminAccount::deleteManaged($managedNo, 1) && !\Security\AdminAccount::findByNo($managedNo), 'managed account deletion');
} finally {
    if (!empty($no)) { $pdo->exec("DELETE FROM nb_admin_audit WHERE entity='qa'"); $pdo->exec("DELETE FROM nb_admin_privacy_access WHERE entity='qa'"); $pdo->prepare('DELETE FROM nb_admin WHERE no=?')->execute([$no]); }
    if ($managedUid !== '') $pdo->prepare('DELETE FROM nb_admin WHERE uid=? AND sitekey=?')->execute([$managedUid, 'BLUESQ']);
}
qa_done();
