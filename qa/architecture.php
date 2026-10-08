<?php
require __DIR__ . '/bootstrap.php';
$transport = new class implements \Security\MailTransport {
    public $messages = [];
    public function send(string $to, array $message): void { $this->messages[] = [$to, $message]; }
};
$delivery = new \Security\MfaDelivery($transport);
$delivery->send('fixture@example.com', '123456', time() + 900);
qa_expect(count($transport->messages) === 1, 'MFA delivery uses injected transport exactly once');
qa_expect($transport->messages[0][0] === 'fixture@example.com', 'transport preserves recipient');
qa_expect(strpos($transport->messages[0][1]['text'], '123456') !== false, 'transport receives composed MFA message');
$bad = false;
try { $delivery->send('fixture@example.com', 'invalid', time()); } catch (InvalidArgumentException $e) { $bad = true; }
qa_expect($bad && count($transport->messages) === 1, 'invalid code rejected before delivery');
$broken = new class implements \Security\MailTransport {
    public function send(string $to, array $message): void { throw new RuntimeException('fixture failure'); }
};
$failed = false;
try { (new \Security\MfaDelivery($broken))->send('fixture@example.com', '123456', time()); } catch (RuntimeException $e) { $failed = true; }
qa_expect($failed, 'transport failure propagated rather than silently accepted');
qa_expect(\Security\AuthSession::currentAccount() === [], 'no authenticated rendering state before enforcement');
require_once dirname(__DIR__) . '/inc/lib/db.php';
if (APP_ENV !== 'development' || !is_file('/.dockerenv') || blue_env('DB_USER') !== 'qa_app') {
    throw new RuntimeException('isolated QA database required');
}
$db = \DB::getInstance();
$no = 0;
try {
    $uid = 'arch_' . bin2hex(random_bytes(4));
    $db->prepare("INSERT INTO nb_admin (sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at) VALUES ('BLUESQ',?,?,?,?,'Y','admin',NOW(),NOW(),NOW())")
        ->execute([$uid, password_hash('Fixture!234', PASSWORD_DEFAULT), 'QA', $uid . '@example.com']);
    $no = (int) $db->lastInsertId();
    $repository = new \Security\PdoAccountRepository($db, 'BLUESQ');
    $row = $repository->findByNo($no);
    qa_expect($row['uid'] === $uid && $repository->findByUid($uid)['no'] == $no, 'injected PDO repository retrieves exact account');
    $other = new \Security\PdoAccountRepository($db, 'OTHER_QA_SITE');
    qa_expect($other->findByNo($no) === [] && $other->findByUid($uid) === [], 'repository enforces site scope for number and identifier');
    qa_expect(\Security\AdminAccount::findByNo($no)['uid'] === $uid, 'legacy facade preserves repository result');
    \Security\AdminAccount::establish($row);
    $_SERVER['SCRIPT_NAME'] = '/admin/pages/board/board.list.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $selects = static function () use ($db): int {
        return (int) $db->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch(\PDO::FETCH_NUM)[1];
    };
    $before = $selects();
    \Security\AuthSession::enforce();
    for ($i = 0; $i < 5; $i++) $viewer = \Security\AuthSession::currentAccount();
    $after = $selects();
    qa_expect($after - $before === 1, 'authentication plus repeated rendering uses exactly one SELECT');
    qa_expect((int) $viewer['no'] === $no && $viewer['role_code'] === 'admin', 'rendering uses authenticated role snapshot');
} finally {
    if ($no) $db->prepare('DELETE FROM nb_admin WHERE no=? AND sitekey=?')->execute([$no,'BLUESQ']);
}
qa_done();
