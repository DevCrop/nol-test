<?php
require __DIR__ . '/bootstrap.php';
$root = dirname(__DIR__);
$files = [
    'db' => file_get_contents($root . '/inc/lib/db.php'),
    'login' => file_get_contents($root . '/admin/lib/login/login.process.php'),
    'board' => file_get_contents($root . '/admin/pages/board/board.list.php'),
    'upload' => file_get_contents($root . '/admin/pages/board/ajax/upload.php'),
    'works' => file_get_contents($root . '/admin/pages/works/ajax/works.process.php'),
    'request' => file_get_contents($root . '/module/ajax/request.process.php'),
    'htaccess' => file_get_contents($root . '/.htaccess'),
];
qa_expect(strpos($files['db'], "blue_env('DB_PASS'") !== false && strpos($files['db'], "define('DB_PASS', '") === false, 'database secret removed from source');
qa_expect(strpos($files['login'], 'topmaster') === false && strpos($files['login'], 'password_hash') === false, 'hardcoded administrator bypass removed');
qa_expect(strpos($files['board'], '$searchColumns') !== false && strpos($files['board'], 'REPLACE($searchColumn') !== false, 'search column allowlist precedes dynamic identifier');
qa_expect(strpos($files['upload'], 'UploadGuard::image') !== false && strpos($files['upload'], 'pathInside') !== false, 'upload and delete share security guard');
qa_expect(strpos($files['works'], '/admin/lib/admin.check.ajax.php') !== false && strpos($files['works'], '/inc/llib/') === false, 'works AJAX authentication include fixed');
qa_expect(strpos($files['request'], "blue_env('SMTP_PASS'") !== false && strpos($files['request'], 'request.process.backup.php') === false, 'mail secret and backup route removed');
qa_expect(strpos($files['htaccess'], 'uploads/board/file_') !== false && !file_exists($root . '/index.test.php') && !file_exists($root . '/module/ajax/request.process.backup.php'), 'private uploads and public leftovers blocked');
qa_expect(strpos(file_get_contents($root . '/admin/pages/request/ajax/request.excel.php'), 'PrivacyLogger::record') !== false && strpos(file_get_contents($root . '/admin/pages/request/ajax/request.excel.php'), 'prepare(') !== false, 'bulk privacy download is audited and parameterized');
qa_expect(strpos(file_get_contents($root . '/admin/lib/login/logout.php'), 'REQUEST_METHOD') !== false, 'logout is POST only');
qa_expect(strpos(file_get_contents($root . '/inc/lib/classes/Security/Runtime.php'), 'trustedProxy') !== false, 'forwarded HTTPS requires a trusted proxy');
qa_expect(strpos(file_get_contents($root . '/admin/pages/account/edit.php'), 'name="email"') !== false && strpos(file_get_contents($root . '/admin/pages/account/password.php'), 'name="email"') === false, 'account email is managed once, not a separate MFA setting');
qa_expect(strpos(file_get_contents($root . '/inc/lib/classes/Security/UploadGuard.php'), 'finfo(FILEINFO_MIME_TYPE)') !== false && strpos(file_get_contents($root . '/inc/lib/classes/Security/UploadGuard.php'), 'is_uploaded_file') !== false, 'uploads require real HTTP upload and MIME inspection');
qa_expect(strpos(file_get_contents($root . '/admin/resource/js/security.js'), 'beginLoading') !== false && is_file($root . '/admin/resource/css/security.css'), 'global network loading UI is installed');
qa_expect(strpos(file_get_contents($root . '/inc/lib/captcha.admin.n.php'), 'security.bootstrap.php') !== false, 'CAPTCHA shares hardened session configuration');
qa_expect(is_file($root . '/admin/pages/account/index.php') && is_file($root . '/admin/pages/account/edit.php') && is_file($root . '/admin/pages/account/audit.php') && strpos(file_get_contents($root . '/admin/pages/account/process.php'), 'requireSuper') !== false, 'administrator lifecycle UI requires super role');
qa_expect(strpos(file_get_contents($root . '/admin/pages/setting/ajax/setting.process.php'), 'hash("sha256", $pwd') === false && strpos(file_get_contents($root . '/admin/pages/setting/setting.php'), '/admin/pages/account/password.php') !== false, 'legacy password change route retired');
qa_expect(strpos(file_get_contents($root . '/inc/lib/classes/Security/AdminAccount.php'), 'password_must_change = 1') !== false && strpos(file_get_contents($root . '/inc/lib/classes/Security/AdminAccount.php'), 'login_token = NULL') !== false, 'admin password reset forces change and invalidates sessions');
qa_done();
