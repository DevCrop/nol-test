<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/AuditLogger.php";

$role->redirectIfCannotView();
$adminAuditStyles = true;
?>

<?php include_once "../../inc/admin.head.php"; ?>

<body class="no-admin-log-page" data-page="audit">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>
        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">
                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= htmlspecialchars($pageName) ?></h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>계정 및 권한</span></li>
                                        <li class="no-breadcrumb-item"><span><?= htmlspecialchars($pageName) ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="no-content-container">
                        <?php
                        $logFilterEntities = AuditLogger::ENTITIES;
                        $logFilterActions = ['create' => '생성', 'update' => '수정', 'delete' => '삭제'];
                        $logFilterPlaceholder = '작업자, 작업 내용 또는 IP 검색';
                        $logFilterHelp = '관리자의 성공한 생성·수정·삭제 작업을 최신순으로 확인합니다. 비밀번호와 인증번호 등 민감정보는 기록하지 않습니다.';
                        include __DIR__ . '/../../inc/log.filters.php';
                        ?>
                        <div class="no-card admin-log-results" data-pii-log-results>
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= htmlspecialchars($pageName) ?></h2>
                                <p class="admin-log-help"><?= htmlspecialchars($logFilterHelp, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="no-card-body">
                                <p class="admin-log-total" id="audit-total" role="status">조회 중</p>
                                <div class="no-table-responsive">
                                    <table class="no-table admin-log-table" aria-label="관리자 작업이력">
                                        <thead>
                                            <tr>
                                                <th scope="col">작업 일시</th>
                                                <th scope="col">작업자 / 접속 IP</th>
                                                <th scope="col">작업 유형</th>
                                                <th scope="col">작업 대상</th>
                                                <th scope="col">작업 내용</th>
                                            </tr>
                                        </thead>
                                        <tbody id="audit-rows">
                                            <tr>
                                                <td colspan="5" class="admin-log-empty">불러오는 중…</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <nav class="admin-log-pager" id="audit-pager" aria-label="작업이력 페이지"></nav>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>
    </div>
    <?php include_once "../../inc/admin.footer.php"; ?>
