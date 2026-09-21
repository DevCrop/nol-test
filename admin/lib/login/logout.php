<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }
\Security\AuthSession::logout();
header('Location: /admin/index.php'); exit;
