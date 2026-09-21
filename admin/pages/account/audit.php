<?php
require_once '../../../inc/lib/base.class.php';
\Security\AdminAccount::requireSuper();
$depthnum = 8; $pagenum = 1;
$stmt = DB::getInstance()->prepare('SELECT created_at, actor_uid, actor_ip, action, entity, target_label, detail_json FROM nb_admin_audit WHERE sitekey = ? ORDER BY no DESC LIMIT 200');
$stmt->execute(['BLUESQ']); $rows = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
include_once '../../inc/admin.title.php'; include_once '../../inc/admin.css.php'; include_once '../../inc/admin.js.php';
$e = static function ($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$actionLabel = ['create' => '생성', 'update' => '수정', 'delete' => '삭제'];
?>
</head><body class="no-account-page"><div class="no-wrap"><?php include_once '../../inc/admin.header.php'; ?><main class="no-app no-container"><?php include_once '../../inc/admin.drawer.php'; ?><section class="no-content"><div class="no-toolbar"><div class="no-toolbar-container no-flex-stack"><div class="no-page-indicator"><h1 class="no-page-title">작업 이력</h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span>작업 이력</span></li></ul></div></div></div></div><div class="no-content-container"><div class="no-card"><div class="no-card-body"><div class="no-table-responsive"><table class="no-table"><thead><tr><th>일시</th><th>작업자</th><th>IP</th><th>작업</th><th>대상</th><th>내용</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?=$e($row['created_at'])?></td><td><?=$e($row['actor_uid'] ?: '-')?></td><td><?=$e($row['actor_ip'])?></td><td><?=$e($actionLabel[$row['action']] ?? $row['action'])?></td><td><?=$e($row['entity'] . ($row['target_label'] ? ' · ' . $row['target_label'] : ''))?></td><td><?=$e($row['detail_json'] ?: '-')?></td></tr><?php endforeach; ?></tbody></table><?php if (!$rows): ?><p>기록이 없습니다.</p><?php endif; ?></div></div></div></div></section></main><?php include_once '../../inc/admin.footer.php'; ?></div></body></html>
