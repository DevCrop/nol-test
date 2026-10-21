<?php

require_once "../../inc/lib/base.class.php";
require_once "../Model/PrivacyAccessModel.php";
require_once "../lib/PrivacyAccessLogger.php";

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
$keyword = trim((string) ($_POST['keyword'] ?? ''));
$action = (string) ($_POST['action'] ?? '');
$entity = (string) ($_POST['entity'] ?? '');
$filters = [
    'action' => in_array($action, PrivacyAccessLogger::ACTIONS, true) ? $action : '',
    'entity' => in_array($entity, ['inquiry', 'admin_account'], true) ? $entity : '',
    'keyword' => function_exists('mb_substr') ? mb_substr($keyword, 0, 100) : substr($keyword, 0, 100),
];
$total = PrivacyAccessModel::count((string) $NO_SITE_UNIQUE_KEY, $filters);
$pages = max(1, (int) ceil($total / $perpage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perpage;
$rows = PrivacyAccessModel::list((string) $NO_SITE_UNIQUE_KEY, $offset, $perpage, $filters);
$out = [];
foreach ($rows as $row) {
    $action = (string) ($row['action'] ?? '');
    $out[] = [
        'no' => (int) ($row['no'] ?? 0),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'actor_uid' => (string) ($row['actor_uid'] ?? ''),
        'actor_ip' => (string) ($row['actor_ip'] ?? ''),
        'action' => $action,
        'action_label' => PrivacyAccessLogger::actionLabel($action),
        'entity' => (string) ($row['entity'] ?? ''),
        'target_no' => (int) ($row['target_no'] ?? 0),
        'subject_label' => (string) ($row['subject_label'] ?? ''),
        'task' => (string) ($row['task'] ?? ''),
        'reason' => (string) ($row['reason'] ?? ''),
    ];
}

echo json_encode([
    'success' => true,
    'page' => $page,
    'pages' => $pages,
    'total' => $total,
    'perpage' => $perpage,
    'rows' => $out,
]);
