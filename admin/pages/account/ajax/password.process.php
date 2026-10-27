<?php
require_once '../../../../inc/lib/base.class.php';
$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['new_password_confirm'] ?? '');
$json = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH']??''))==='xmlhttprequest';
$fail = function (string $message) use ($json): void {
    if($json){header('Content-Type: application/json; charset=utf-8');echo json_encode(['result'=>'fail','msg'=>$message]);exit;}
    $_SESSION['password_message'] = $message; header('Location: ' . blue_admin_url('/admin/pages/account/password.php')); exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
$row = \Security\AuthSession::currentAccount();
try {
    $db=\DB::getInstance();$db->beginTransaction();
    \AccountAccessPolicy::beginAudit('본인 비밀번호 변경',[(int)$row['no']=>$row]);
    (new \Security\TemporaryAccountService())->change((int)($_SESSION['no_adm_login_no']??0),(string)($_SESSION['no_adm_login_token']??''),$current,$new,$confirm);
    \Security\AuditLogger::record('update', 'account', (int) $row['no'], (string) $row['uid'], ['password_changed' => true]);
    $db->commit();
}catch(PDOException $e){if(isset($db)&&$db->inTransaction())$db->rollBack();$fail('비밀번호를 변경할 수 없습니다. 다시 시도하세요.');}
catch(RuntimeException $e){if(isset($db)&&$db->inTransaction())$db->rollBack();$fail($e->getMessage());}
catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();$fail('비밀번호를 변경할 수 없습니다. 다시 시도하세요.');}
\Security\AuthSession::logout();
if($json){header('Content-Type: application/json; charset=utf-8');echo json_encode(['result'=>'success','msg'=>'비밀번호가 변경되었습니다. 새 비밀번호로 다시 로그인하세요.']);exit;}
header('Location: ' . blue_admin_url('/admin/index.php')); exit;
