<?php

require_once "../../inc/lib/base.class.php";
require_once "../Model/AuditModel.php";
require_once "../lib/AuditLogger.php";

$role->requireLogin();
if (!$role->isSuper()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

global $NO_SITE_UNIQUE_KEY;
$mode = (string) ($_POST['mode'] ?? '');
$perpage = 10;

if ($mode !== 'list') {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
    exit;
}

$page = max(1, (int) ($_POST['page'] ?? 1));
$entity = trim((string) ($_POST['entity'] ?? ''));
if ($entity !== '' && !isset(AuditLogger::ENTITIES[$entity])) {
    $entity = '';
}
$total = AuditModel::count((string) $NO_SITE_UNIQUE_KEY, $entity);
$pages = max(1, (int) ceil($total / $perpage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perpage;
$rows = AuditModel::list((string) $NO_SITE_UNIQUE_KEY, $offset, $perpage, $entity);
$out = [];
foreach ($rows as $row) {
    $action = (string) ($row['action'] ?? '');
    $ent = (string) ($row['entity'] ?? '');
    $out[] = [
        'no' => (int) ($row['no'] ?? 0),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'actor_uid' => (string) ($row['actor_uid'] ?? ''),
        'actor_ip' => (string) ($row['actor_ip'] ?? ''),
        'action' => $action,
        'action_label' => AuditLogger::actionLabel($action),
        'entity' => $ent,
        'entity_label' => AuditLogger::entityLabel($ent),
        'target_no' => (int) ($row['target_no'] ?? 0),
        'target_label' => (string) ($row['target_label'] ?? ''),
        'summary' => (string) ($row['summary'] ?? ''),
    ];
}

echo json_encode([
    'success' => true,
    'page' => $page,
    'pages' => $pages,
    'total' => $total,
    'rows' => $out,
    'entities' => AuditLogger::ENTITIES,
]);
