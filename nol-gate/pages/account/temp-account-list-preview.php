<?php
// Static synthetic examples only; no account data, password retrieval or issuance.
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit;
}
include_once "../../../inc/lib/base.class.php";
$role->redirectIfCannotView();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
$adminAccountPreviewStyles = true;
$previewAccounts = [
    ['uid' => 'preview_admin', 'name' => '신규 담당자 (예시)', 'email' => 'new@example.com', 'temporary' => true, 'password' => 'DEMO-ONLY!482'],
    ['uid' => 'preview_staff', 'name' => '기존 담당자 (예시)', 'email' => 'staff@example.com', 'temporary' => false],
];
include_once "../../inc/admin.head.php";
?>
<body data-page="temp-account-preview">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>
        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>
            <section class="no-content">
                <div class="no-toolbar"><div class="no-toolbar-container no-flex-stack">
                    <div class="no-page-indicator"><h1 class="no-page-title">관리자 계정 관리</h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span>계정 목록 · 임시 비밀번호 UI</span></li></ul></div></div>
                    <div class="no-items-center"><a href="./temp-account-preview.php?view=new" class="no-btn no-btn--main no-btn--big">계정 등록</a></div>
                </div></div>
                <div class="no-content-container"><div class="no-card">
                    <div class="no-card-header"><h2 class="no-card-title">관리자 계정 목록</h2></div>
                    <div class="no-card-body">
                        <p class="no-admin-acl__hint">디자인 미리보기입니다. 아래 계정과 비밀번호는 모두 고정 샘플이며 실제 로그인에 사용할 수 없습니다.</p>
                        <?php if (($_GET['created'] ?? '') === '1'): ?>
                        <p class="no-admin-acl__hint" role="status"><strong>등록 완료 후 화면 예시</strong> — 목록에서 임시 비밀번호를 확인하고 로그인 정보를 담당자에게 전달합니다. 실제 계정은 생성되지 않았습니다.</p>
                        <?php endif; ?>
                        <div class="no-table-option"><span>임시 비밀번호는 기본적으로 가려집니다. 본인 비밀번호로 변경한 계정은 조회할 수 없습니다.</span><span>총 2개 · 예시</span></div>
                        <div class="no-table-responsive"><table class="no-table" aria-label="임시 비밀번호 전달 UI 미리보기">
                            <thead><tr><th scope="col">아이디</th><th scope="col">이름 / 인증 이메일</th><th scope="col">비밀번호 상태</th><th scope="col">임시 비밀번호</th><th scope="col">전달 / 관리</th></tr></thead>
                            <tbody>
                            <?php foreach ($previewAccounts as $index => $account): ?>
                                <tr data-preview-uid="<?= htmlspecialchars($account['uid'], ENT_QUOTES, 'UTF-8') ?>">
                                    <td><strong><?= htmlspecialchars($account['uid'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8') ?><p class="no-admin-acl__hint"><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></p></td>
                                    <td><span class="no-badge badge-local"><?= $account['temporary'] ? '임시 발급 · 변경 필요' : '본인 변경 완료' ?></span></td>
                                    <td>
                                        <?php if ($account['temporary']): ?>
                                        <label class="no-admin-acl__hint" for="sample-password-<?= $index ?>">임시 비밀번호 (예시)</label>
                                        <input type="password" id="sample-password-<?= $index ?>" readonly autocomplete="off" value="<?= htmlspecialchars($account['password'], ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($account['uid'], ENT_QUOTES, 'UTF-8') ?> 임시 비밀번호 예시">
                                        <button type="button" class="no-btn no-btn--sm" data-preview-toggle="sample-password-<?= $index ?>" aria-controls="sample-password-<?= $index ?>" aria-pressed="false">보기</button>
                                        <?php else: ?>
                                        <span>조회 불가</span><p class="no-admin-acl__hint">임시 비밀번호 사용 종료</p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($account['temporary']): ?>
                                        <button type="button" class="no-btn no-btn--main" data-preview-copy="sample-password-<?= $index ?>">로그인 정보 복사</button>
                                        <p class="no-admin-acl__hint">아이디 + 임시 비밀번호</p>
                                        <?php endif; ?>
                                        <a href="./temp-account-preview.php?view=edit&amp;password=<?= $account['temporary'] ? 'temp' : 'changed' ?>" class="no-btn no-btn--sm">계정 수정 UI</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table></div>
                        <p class="no-admin-acl__hint" id="sample-copy-status" role="status" aria-live="polite"></p>
                        <div id="sample-copy-fallback" hidden>
                            <label for="sample-copy-text" class="no-admin-acl__hint">전달할 로그인 정보 (디자인 예시)</label>
                            <textarea id="sample-copy-text" rows="5" readonly></textarea>
                            <p class="no-admin-acl__hint">현재 브라우저에서 자동 복사를 사용할 수 없습니다. 선택된 내용을 Ctrl+C 또는 기기의 복사 메뉴로 복사해 주세요.</p>
                        </div>
                        <p class="no-admin-acl__hint">담당자 이용 순서: 아이디·임시 비밀번호로 로그인 → MFA 이메일 인증 → 새 비밀번호로 변경 → 관리자 이용</p>
                        <a href="./password-preview.php" class="no-btn no-btn--normal">담당자의 비밀번호 변경 화면 보기</a>
                    </div>
                </div></div>
            </section>
        </main>
    </div>
    <script type="module" src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/temp-account-list-preview.js?v=20261020"></script>
    <?php include_once "../../inc/admin.footer.php"; ?>
