<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'expiresIn' => max(0, SESSION_LIFETIME - (time() - (int) ($_SESSION['no_adm_last_activity'] ?? 0)))]);
