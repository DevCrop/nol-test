<?php
require_once '../../../inc/lib/base.class.php';
\Security\AdminAccount::requireSuper();
$depthnum = 8; $pagenum = 1;
$accounts = \Security\AdminAccount::all();
$flash = (string) ($_SESSION['account_flash'] ?? '');
$flashError = !empty($_SESSION['account_flash_error']);
unset($_SESSION['account_flash'], $_SESSION['account_flash_error']);
include_once '../../inc/admin.title.php'; include_once '../../inc/admin.css.php'; include_once '../../inc/admin.js.php';
$e = static function ($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
</head><body class="no-account-page"><div class="no-wrap">
<?php include_once '../../inc/admin.header.php'; ?>
<main class="no-app no-container" data-pii-lock><?php include_once '../../inc/admin.drawer.php'; ?><section class="no-content">
<div class="no-toolbar"><div class="no-toolbar-container no-flex-stack"><div class="no-page-indicator"><h1 class="no-page-title">관리자 계정</h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span>관리자 계정</span></li></ul></div></div><div class="no-items-center"><a class="no-btn no-btn--main no-btn--big" href="/admin/pages/account/new.php">계정 생성</a></div></div></div>
<div class="no-content-container"><?php if ($flash !== ''): ?><p class="account-flash<?=$flashError ? ' error' : ''?>" role="alert"><?=$e($flash)?></p><?php endif; ?><div class="no-card"><div class="no-card-header"><h2 class="no-card-title">계정 목록</h2></div><div class="no-card-body"><div class="no-table-responsive"><table class="no-table"><thead><tr><th>아이디</th><th>이름</th><th>이메일</th><th>권한</th><th>상태</th><th>마지막 로그인</th><th>관리</th></tr></thead><tbody>
<?php foreach ($accounts as $row): $locked = (!empty($row['login_locked_until']) && strtotime($row['login_locked_until']) > time()) || !empty($row['idle_locked_at']); ?><tr><td><?=$e($row['uid'])?></td><td><?=$e(\Security\PiiMask::name((string) $row['uname']))?></td><td><?=$e(\Security\PiiMask::email((string) $row['email']))?></td><td><?=$row['role_code'] === 'super' ? '최고 관리자' : '일반 관리자'?></td><td><span class="account-state<?=$locked ? ' locked' : ''?>"><?=$row['active_status'] !== 'Y' ? '비활성' : ($locked ? '잠김' : '정상')?></span></td><td><?=$e($row['last_login_at'] ?: '-')?></td><td><div class="account-actions"><a class="no-btn no-btn--sm no-btn--normal" href="/admin/pages/account/edit.php?no=<?=(int) $row['no']?>">수정</a><?php if ((int) $row['no'] !== (int) ($_SESSION['no_adm_login_no'] ?? 0)): ?><?php if ($locked): ?><form class="account-inline" method="post" action="/admin/pages/account/process.php"><input type="hidden" name="mode" value="unlock"><input type="hidden" name="no" value="<?=(int) $row['no']?>"><button class="no-btn no-btn--sm no-btn--normal" type="submit">잠금 해제</button></form><?php endif; ?><form class="account-inline" method="post" action="/admin/pages/account/process.php" onsubmit="return confirm('이 계정을 삭제하시겠습니까?')"><input type="hidden" name="mode" value="delete"><input type="hidden" name="no" value="<?=(int) $row['no']?>"><button class="no-btn no-btn--sm no-btn--delete-outline" type="submit">삭제</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
</tbody></table><?php if (!$accounts): ?><p>등록된 계정이 없습니다.</p><?php endif; ?></div></div></div></div>
</section></main><?php include_once '../../inc/admin.footer.php'; ?></div></body></html>
