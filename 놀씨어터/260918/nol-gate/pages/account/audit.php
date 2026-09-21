<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/AuditLogger.php";

$role->redirectIfCannotView();
?>

<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="audit">
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
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">대상</h3>
                                    <div class="no-admin-content">
                                        <select name="entity" id="entity">
                                            <option value="">전체</option>
                                            <?php foreach (AuditLogger::ENTITIES as $code => $label): ?>
                                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= htmlspecialchars($pageName) ?></h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th>일시</th>
                                                <th>작업자</th>
                                                <th>IP</th>
                                                <th>유형</th>
                                                <th>대상</th>
                                                <th>내용</th>
                                            </tr>
                                        </thead>
                                        <tbody id="audit-rows">
                                            <tr>
                                                <td colspan="6">불러오는 중…</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="audit-pager"></div>
                </section>
            </form>
        </main>
    </div>
    <?php include_once "../../inc/admin.footer.php"; ?>
