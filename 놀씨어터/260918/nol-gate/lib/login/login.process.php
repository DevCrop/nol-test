<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";
require_once dirname(__DIR__) . "/AccountIdleLock.php";

$pdo = DB::getInstance();

$maxLoginFailures = 5;
$loginBlockSeconds = 900;
$loginAttemptDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'nol_admin_login_attempts';
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$attemptKey = '';
$attemptFile = '';

$sendLoginError = static function (string $message): void {
    echo "<script>alert(" . json_encode($message) . "); location.href='../../index.php';</script>";
    exit;
};

$loadLoginAttempt = static function (string $path): array {
    if (!is_file($path)) {
        return ['failures' => 0, 'blocked_until' => 0];
    }

    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return ['failures' => 0, 'blocked_until' => 0];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['failures' => 0, 'blocked_until' => 0];
    }

    return [
        'failures' => (int)($data['failures'] ?? 0),
        'blocked_until' => (int)($data['blocked_until'] ?? 0),
    ];
};

$saveLoginAttempt = static function (string $dir, string $path, array $data): void {
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    @file_put_contents($path, json_encode([
        'failures' => (int)($data['failures'] ?? 0),
        'blocked_until' => (int)($data['blocked_until'] ?? 0),
    ], JSON_UNESCAPED_SLASHES), LOCK_EX);
};

$clearLoginAttempt = static function (string $path): void {
    if (is_file($path)) {
        @unlink($path);
    }
};

$uid = trim((string)($_POST['uid'] ?? ''));
$pwd = $_POST['upwd'] ?? '';
$r_captcha = $_POST['r_captcha'] ?? '';
$attemptKey = hash('sha256', strtolower($uid) . '|' . $clientIp);
$attemptFile = $loginAttemptDir . DIRECTORY_SEPARATOR . $attemptKey . '.json';

$recordLoginFailure = static function () use ($attemptFile, $loginAttemptDir, $loadLoginAttempt, $saveLoginAttempt, $maxLoginFailures, $loginBlockSeconds): void {
    $state = $loadLoginAttempt($attemptFile);
    $state['failures']++;

    if ($state['failures'] >= $maxLoginFailures) {
        $state['blocked_until'] = time() + $loginBlockSeconds;
        $state['failures'] = 0;
    }

    $saveLoginAttempt($loginAttemptDir, $attemptFile, $state);
};

$attemptState = $loadLoginAttempt($attemptFile);
if (($attemptState['blocked_until'] ?? 0) > time()) {
    $remainingMinutes = (int)ceil((($attemptState['blocked_until'] ?? 0) - time()) / 60);
    $sendLoginError("Too many login attempts. Try again in {$remainingMinutes} minute(s).");
}

if (($_SESSION['captcha_secure'] ?? '') !== $r_captcha) {
    echo "<script>alert('보안코드가 일치하지 않습니다. 정확히 입력해주세요.'); location.href='../../index.php';</script>";
    exit;
}

$sql = "
    SELECT a.no, a.uid, a.upwd, a.uname, a.active_status, a.role_id, a.email,
           a.created_at, a.last_login_at, a.idle_locked_at
    FROM nb_admin a
    WHERE a.uid = :uid AND a.sitekey = :sitekey
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'uid' => $uid,
    'sitekey' => $NO_SITE_UNIQUE_KEY,
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data || !password_verify($pwd, $data['upwd'])) {
    $recordLoginFailure();
    $sendLoginError('아이디 또는 비밀번호가 일치하지 않습니다.');
}

if (($data['active_status'] ?? '') === 'N') {
    echo "<script>alert('사용이 중지된 계정입니다.'); location.href='../../index.php';</script>";
    exit;
}

try {
    (new AccountIdleLock())->guard($data);
} catch (RuntimeException $e) {
    echo "<script>alert(" . json_encode($e->getMessage()) . "); location.href='../../index.php';</script>";
    exit;
}

$clearLoginAttempt($attemptFile);

if (Mfa::enabled()) {
    Mfa::begin($data);
    header('Location: ../../mfa.php');
    exit;
}

$roleId = $data['role_id'] ?? 3;
$roleCode = $admin_roles[$roleId]['code'] ?? 'guest';
AuthSession::loginAdmin($data, $roleCode);

require_once dirname(__DIR__) . "/PasswordChangeGate.php";
header("Location: " . (new PasswordChangeGate())->landingAfterLogin());
exit;
?>
