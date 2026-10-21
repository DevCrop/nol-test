<?php
include_once "../../../inc/lib/base.class.php";

$role->redirectIfCannotView();
$adminAuditStyles = true;
?>

<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="privacy-access">
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
                    <?php
                    $logFilterEntities = ['admin_account' => '관리자 계정', 'inquiry' => '대관신청'];
                    $logFilterActions = ['view' => '열람', 'download' => '다운로드'];
                    $logFilterTargetLabel = '접근 대상';
                    $logFilterActionLabel = '접근 유형';
                    $logFilterPlaceholder = '작업자, IP, 마스킹된 이름, 업무·사유 검색';
                    $logFilterHelp = '개인정보 열람 및 다운로드 기록을 최신순으로 확인합니다. 정보주체는 마스킹된 이름으로 표시하며, 다운로드 사유는 기록 당시 입력한 내용을 보여줍니다.';
                    include __DIR__ . '/../../inc/log.filters.php';
                    ?>
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= htmlspecialchars($pageName) ?></h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-option flex-end"><span id="privacy-access-total" role="status">조회 중</span></div>
                                <div class="no-table-responsive">
                                    <table class="no-table" aria-label="개인정보 접속기록">
                                        <thead>
                                            <tr>
                                                <th scope="col">접근 일시</th>
                                                <th scope="col">작업자 / 접속 IP</th>
                                                <th scope="col">접근 유형</th>
                                                <th scope="col">접근 대상 / 정보주체</th>
                                                <th scope="col">수행업무 / 다운로드 사유</th>
                                            </tr>
                                        </thead>
                                        <tbody id="privacy-access-rows">
                                            <tr>
                                                <td colspan="5">불러오는 중…</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <nav id="privacy-access-pager" aria-label="개인정보 접속기록 페이지"></nav>
                </section>
            </form>
        </main>
    </div>
    <?php include_once "../../inc/admin.footer.php"; ?>
