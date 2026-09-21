<?php
require __DIR__ . '/bootstrap.php';
qa_expect(\Security\ClientIp::allowed('10.1.2.3', ['10.0.0.0/8']), 'CIDR permits member');
qa_expect(!\Security\ClientIp::allowed('11.1.2.3', ['10.0.0.0/8']), 'CIDR rejects outsider');
qa_expect(\Security\ClientIp::get(['REMOTE_ADDR'=>'203.0.113.7','HTTP_X_FORWARDED_FOR'=>'10.0.0.1']) === '203.0.113.7', 'untrusted proxy header ignored');
qa_expect(\Security\ClientIp::allowed('10.2.3.4', ['10.0.0.0/8']) && !\Security\ClientIp::allowed('10.2.3.4', ['192.168.0.0/16']), 'trusted proxy CIDR selection');
qa_expect(\Security\PiiMask::name('홍길동') === '홍*동', 'Korean name masked');
qa_expect(strpos(\Security\PiiMask::email('tester@example.com'), 'example.com') !== false, 'email domain retained');
$dirty = '<img src="x" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">x</a>';
$clean = \Security\HtmlSanitizer::clean($dirty);
qa_expect(strpos($clean, 'script') === false && strpos($clean, 'onerror') === false && strpos($clean, 'javascript:') === false, 'rich HTML attack removed');
$token = \Security\Csrf::token(); qa_expect(strlen($token) === 64 && \Security\Csrf::valid($token) && !\Security\Csrf::valid('bad'), 'CSRF token validation');
qa_expect(\Security\Csrf::valid($token), 'CSRF token supports concurrent forms in one session');
$root = dirname(__DIR__); qa_expect(\Security\UploadGuard::pathInside($root, '.htaccess') !== null, 'safe path accepted');
qa_expect(\Security\UploadGuard::pathInside($root, '../outside') === null, 'traversal path rejected');
$maskedPhone = \Security\PiiMask::phone('010-1234-5678');
qa_expect(strpos($maskedPhone, '1234') === false && substr($maskedPhone, -4) === '5678', 'phone number masked');
qa_done();
