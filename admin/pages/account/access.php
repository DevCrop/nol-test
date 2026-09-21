<?php
require_once '../../../inc/lib/base.class.php';
\Security\AdminAccount::requireSuper();
$depthnum = 8; $pagenum = 1;
$stmt = DB::getInstance()->prepare('SELECT created_at, actor_uid, actor_ip, action, entity, subject_label, task, reason FROM nb_admin_privacy_access WHERE sitekey = ? ORDER BY no DESC LIMIT 200');
$stmt->execute(['BLUESQ']); $rows = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
include_once '../../inc/admin.title.php'; include_once '../../inc/admin.css.php'; include_once '../../inc/admin.js.php';
$e = static function ($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
</head><body class="no-account-page"><div class="no-wrap"><?php include_once '../../inc/admin.header.php'; ?><main class="no-app no-container"><?php include_once '../../inc/admin.drawer.php'; ?><section class="no-content"><div class="no-toolbar"><div class="no-toolbar-container no-flex-stack"><div class="no-page-indicator"><h1 class="no-page-title">개인정보 접속기록</h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span>개인정보 접속기록</span></li></ul></div></div></div></div><div class="no-content-container"><div class="no-card"><div class="no-card-body"><div class="no-table-responsive"><table class="no-table"><thead><tr><th>일시</th><th>열람자</th><th>IP</th><th>유형</th><th>정보주체</th><th>업무</th><th>사유</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?=$e($row['created_at'])?></td><td><?=$e($row['actor_uid'] ?: '-')?></td><td><?=$e($row['actor_ip'])?></td><td><?=$e($row['action'])?></td><td><?=$e($row['subject_label'])?></td><td><?=$e($row['task'])?></td><td><?=$e($row['reason'] ?: '-')?></td></tr><?php endforeach; ?></tbody></table><?php if (!$rows): ?><p>기록이 없습니다.</p><?php endif; ?></div></div></div></div></section></main><?php include_once '../../inc/admin.footer.php'; ?></div></body></html>
