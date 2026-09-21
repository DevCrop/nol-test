<?php

include_once dirname(__DIR__, 2) . '/inc/lib/base.class.php';
require_once dirname(__DIR__) . '/lib/AuthSession.php';

if (empty($_SESSION['no_adm_login_uid'])) {
    AuthSession::denyLogin();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'remaining' => max(0, (defined('SESSION_LIFETIME') ? (int) SESSION_LIFETIME : 1800) - (time() - (int) ($_SESSION['no_adm_last_activity'] ?? 0))),
]);
