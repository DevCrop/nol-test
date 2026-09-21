<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

$id = $_GET['id'] ?? null;
$tag = null;

$page = (int)($_GET['page'] ?? $_POST['page'] ?? 1);



if ($id) {
    $stmt = $db->prepare("SELECT * FROM nb_site_tags WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $tag = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tag) {
        echo "<script>alert('존재하지 않는 태그입니다.'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
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

            <form id="frm" method="post">
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="<?= htmlspecialchars($tag['id']) ?>">
                <input type="hidden" name="page" value="<?= $_GET['page'] ?? 1 ?>">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?></h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>설정</span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title"><?= $pageName ?></h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">


                                <!-- tag_content -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="title" id="title" class="no-input--detail"
                                            value="<?= $tag['title'] ?>" placeholder="예: Google Analytics 스크립트" required
                                            maxlength="100" />
                                    </div>
                                </div>


                                <!-- location -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">위치</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php
                                            require_once __DIR__ . '/../../../inc/lib/var.php';
                                            $currentLocation = (int)($tag['location'] ?? 1);
                                            foreach ($tag_locations as $value => $label):
                                                $id = "location_$value";
                                                $checked = $currentLocation == $value ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="location" id="<?= $id ?>"
                                                        value="<?= $value ?>" <?= $checked ?>>
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
                                            required><?= htmlspecialchars($tag['tag_content']) ?></textarea>
                                        <div class="no-admin-desc" style="margin-top: 5px; color: #888;">
                                            ※ 선택한 위치에 삽입됩니다.
                                        </div>
                                    </div>
                                </div>

                                <!-- is_active -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label>사용 여부</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="activeY">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_active" id="activeY" value="1"
                                                        <?= $tag['is_active'] == 1 ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>
                                            <label for="activeN">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_active" id="activeN" value="0"
                                                        <?= $tag['is_active'] == 0 ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- buttons -->
                                <div class="no-items-center center">
                                    <a href="./external.tag.php?page=<?= $page ?>"
                                        class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="submit" class="no-btn no-btn--big no-btn--main"
                                        id="editBtn">수정</button>
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