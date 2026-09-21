<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}


include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

</head>

<body data-page="setting">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="save">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 등록</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 등록</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">외부 스크립트 태그 등록</h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- tag_content -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title" placeholder="제목을 입력해주세요.">
                                        <div class="no-admin-desc" style="margin-top: 5px; color: #888;">
                                        </div>
                                    </div>
                                </div>
                                <!-- location -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">위치</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php
                                            require_once __DIR__ . '/../../../inc/lib/var.php';
                                            foreach ($tag_locations as $value => $label):
                                                $id = "location_$value";
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="location" id="<?= $id ?>"
                                                        value="<?= $value ?>" <?= $value == 1 ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- tag_content -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="tag_content">스크립트 태그</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="tag_content" id="tag_content" class="no-textarea--detail"
                                            placeholder="<script>...</script> 또는 <meta> 태그 등 입력" rows="8"
                                            required></textarea>
                                        <div class="no-admin-desc" style="margin-top: 5px; color: #888;">
                                            ※ 선택한 위치에 삽입됩니다. Google Analytics, 채팅 위젯 등을 입력하세요.
                                        </div>
                                    </div>
                                </div>


                                <!-- buttons -->
                                <div class="no-items-center center">
                                    <a href="./external.tag.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="submit" class="no-btn no-btn--big no-btn--main"
                                        id="submitBtn">저장</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>

        </main>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

</body>

</html>