<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

    $pdo = DB::getInstance();
    $depthnum = 4;
    $pagenum = 3;

    $no = $_REQUEST['no'];

    // Query to retrieve data
    $query = "SELECT a.no, a.sitekey, a.target, a.contents, a.regdate FROM nb_data a WHERE a.no = :no";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':no', $no, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        error("정보를 찾을 수 없습니다");
    }

    include_once "../../inc/admin.title.php";
    include_once "../../inc/admin.css.php";
    include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="">
                <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no']) ?>">
                
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">사이트 데이터관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>설정</span></li>
                                        <li class="no-breadcrumb-item"><span>사이트 데이터관리</span></li>
                                        <li class="no-breadcrumb-item"><span>데이터 확인 및 수정</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">사이트 데이터 수정</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                
                                <!-- Data Usage Area -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="target">데이터 사용영역</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="target" id="target">
                                            <option value="">선택</option>
                                            <?php foreach ($siteDataTarget as $key => $val) : ?>
                                                <option value="<?= htmlspecialchars($key) ?>" <?= ($data['target'] == $key) ? "selected" : "" ?>>
                                                    <?= htmlspecialchars($val) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Data Content -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="content">데이터 내용</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-admin-check">
                                            <textarea name="content" id="content" class="SEditor"><?= htmlspecialchars($data['contents']) ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="no-items-center center">
                                    <a href="./site.data.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doEditSave();">수정</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/setting.data.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
