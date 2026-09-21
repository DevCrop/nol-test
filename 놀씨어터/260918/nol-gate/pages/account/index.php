<?php include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/Model/AccountModel.php";

$role->redirectIfCannotView();

$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage;

$db = DB::getInstance();

// 총 개수 구하기
$totalStmt = $db->query("SELECT COUNT(*) FROM nb_admin");
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// 실제 데이터 조회
$sql = "SELECT a.no, a.uid, a.uname, a.email, a.phone, a.active_status, a.role_id, r.role_name, a.created_at, a.last_login_at, a.idle_locked_at
        FROM nb_admin a
        LEFT JOIN nb_roles r ON a.role_id = r.role_id
        ORDER BY a.no DESC
        LIMIT {$count}, {$perpage}";

$stmt = $db->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$me = (int) ($_SESSION['no_adm_login_no'] ?? 0);
$superTotal = AccountModel::countByRole(1);
?>


<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="account">
    <div class="no-wrap">

        <!--=====================HEADER========================= -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!--=====================MAIN========================= -->
        <main class="no-app no-container">

            <!--=====================DRAWER========================= -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <!--=====================CONTENTS========================= -->
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">
                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?=$pageName?> 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>계정 및 권한</span></li>
                                        <li class="no-breadcrumb-item"><span><?=$pageName?> 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                            <?php if ($role->isSuper()): ?>
                            <div class="no-items-center">
                                <a href="./new.php" class="no-btn no-btn--main no-btn--big"> <?=$pageName?> 생성 </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?=$pageName?> 관리</h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-option">
                                    <ul class="no-table-check-control">
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check active "
                                                data-action="selectAll">전체선택</a></li>
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check"
                                                data-action="deselectAll">선택해제</a></li>
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check"
                                                data-action="deleteSelected">선택삭제</a></li>
                                    </ul>

                                </div>
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" id="selectAllCheckbox" />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>번호</th>
                                                <th>아이디</th>
                                                <th>이름</th>
                                                <th>이메일</th>
                                                <th>권한</th>
                                                <th>등록일</th>
                                                <th>마지막 로그인</th>
                                                <th>상태</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($rows)): ?>
                                            <?php foreach ($rows as $row): ?>
                                            <?php
                                            $rowNo = (int) $row['no'];
                                            $isSelf = $rowNo === $me;
                                            $isSuperRow = (int) ($row['role_id'] ?? 0) === 1;
                                            $lock = $isSelf ? 'self' : (($isSuperRow && $superTotal <= 1) ? 'last-super' : '');
                                            $layerName = $isSuperRow ? '최고 관리자' : '일반 관리자';
                                            $idleLocked = trim((string) ($row['idle_locked_at'] ?? '')) !== '';
                                            if ($idleLocked) {
                                                $statusLabel = '미접속잠금';
                                                $statusClass = 'no-btn--delete-outline';
                                            } elseif (($row['active_status'] ?? '') === 'Y') {
                                                $statusLabel = '활성';
                                                $statusClass = 'no-btn--notice';
                                            } else {
                                                $statusLabel = '비활성';
                                                $statusClass = 'no-btn--normal';
                                            }
                                            ?>
                                            <tr>
                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" class="no-chk"
                                                                value="<?= $rowNo ?>" <?= $lock !== '' ? 'disabled' : '' ?> />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td><?= $rowNo ?></td>
                                                <td><?= htmlspecialchars($row['uid']) ?></td>
                                                <td><?= htmlspecialchars($row['uname']) ?></td>
                                                <td><?= htmlspecialchars($row['email']) ?></td>
                                                <td><?= htmlspecialchars($layerName) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['created_at'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['last_login_at'] ?? '-')) ?></td>
                                                <td>
                                                    <span class="no-btn <?= $statusClass ?>">
                                                        <?= $statusLabel ?>
                                                    </span>

                                                </td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn"><i
                                                                class="bx bx-dots-vertical-rounded"></i></span>
                                                        <div class="no-table-action">
                                                            <a href="edit.php?no=<?= $rowNo ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">권한 및 수정</a>
                                                            <?php if ($lock === ''): ?>
                                                            <button type="button"
                                                                class="no-btn no-btn--sm no-btn--delete-outline delete-btn"
                                                                data-id="<?= $rowNo ?>">
                                                                삭제
                                                            </button>
                                                            <?php else: ?>
                                                            <button type="button"
                                                                class="no-btn no-btn--sm no-btn--delete-outline delete-btn"
                                                                data-id="<?= $rowNo ?>"
                                                                data-locked="<?= htmlspecialchars($lock, ENT_QUOTES, 'UTF-8') ?>">
                                                                삭제
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="10">등록된 계정이 없습니다.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>
                    </div>
                    <?php include_once "../../lib/admin.pagination.php"; ?>
                </section>
            </form>
        </main>
    </div>
    <?php include_once "../../inc/admin.footer.php"; ?>