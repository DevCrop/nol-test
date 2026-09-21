<?php
include_once "../../../inc/lib/base.class.php";

$id  = $_GET['id'] ?? null;
$seo = null;

$page = (int)($_GET['page'] ?? $_POST['page'] ?? 1);

try {
    $db = DB::getInstance();

    $router = app()->router();
    $buildRouteDisplayName = static function (string $routeName, string $uri): string {
        return $routeName !== '' ? $routeName . ' (' . $uri . ')' : $uri;
    };
    $buildRouteGroupLabel = static function (string $groupName): string {
        return $groupName !== '' ? strtoupper($groupName) : 'ROUTE';
    };

    // routes/web.php 파일을 include하여 라우트 등록
    $routesFile = dirname(__DIR__, 3) . '/routes/web.php';
    if (file_exists($routesFile)) {
        // 라우트 등록을 위해 파일 include (라우터는 이미 초기화되어 있음)
        include_once $routesFile;
    }

    // 이름이 있는 라우트 목록 가져오기
    $namedRoutes = $router->getNamedRoutes();
    $routeOptions = [];

    foreach ($namedRoutes as $name => $route) {
        $uri = $route->getUri();
        $method = $route->getMethod();

        // GET 메서드만 표시 (POST 등은 제외)
        // 인증(auth) 관련 라우트는 제외
        if ($method === 'GET' && strpos($name, 'auth.') !== 0) {
            // 한국어 표시명 생성
            $displayName = $buildRouteDisplayName($name, $uri);

            // 그룹명 추출 (첫 번째 점 이전)
            $parts = explode('.', $name);
            $groupName = $parts[0] ?? '';
            $groupLabel = $buildRouteGroupLabel($groupName);

            // 라우트 이름과 URI를 함께 저장
            $routeOptions[] = [
                'name' => $name,
                'uri' => $uri,
                'display' => $displayName,
                'group' => $groupName,
                'groupLabel' => $groupLabel
            ];
        }
    }

    // 그룹별로 정렬 (그룹명 -> 표시명)
    usort($routeOptions, function ($a, $b) {
        $groupCompare = strcmp($a['groupLabel'], $b['groupLabel']);
        if ($groupCompare !== 0) {
            return $groupCompare;
        }
        return strcmp($a['display'], $b['display']);
    });

    // SEO 정보
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM nb_branch_seos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $seo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seo) {
            echo "잘못된 접근입니다. 해당 SEO 정보가 존재하지 않습니다.";
            exit;
        }
    }
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

<script>
// PHP에서 준비한 라우트 목록을 JavaScript에 전달
window.routeOptions = <?= json_encode($routeOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>


</head>

<body data-page="seo">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="<?= (int)($seo['id'] ?? 0) ?>">
                <input type="hidden" name="page" value="<?= $_GET['page'] ?? 1 ?>">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 수정</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 수정</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title"><?= $pageName ?> 수정</h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- 경로 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="path">경로</label></h3>
                                    <div class="no-admin-content">
                                        <select name="path" id="path"
                                            data-current="<?= htmlspecialchars($seo['path'] ?? '') ?>" required>
                                            <option value="">페이지 경로 선택</option>
                                            <?php
                                            $currentGroup = '';
                                            foreach ($routeOptions as $route):
                                                // 그룹이 변경되면 optgroup 시작/종료
                                                if ($currentGroup !== $route['groupLabel']):
                                                    if ($currentGroup !== ''):
                                                        echo '</optgroup>';
                                                    endif;
                                                    $currentGroup = $route['groupLabel'];
                                                    echo '<optgroup label="' . htmlspecialchars($route['groupLabel']) . '">';
                                                endif;
                                            ?>
                                            <option value="<?= htmlspecialchars($route['uri']) ?>"
                                                <?= ($seo['path'] ?? '') === $route['uri'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($route['display']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                            <?php if ($currentGroup !== ''): ?>
                                            </optgroup>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 페이지 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="page_title">페이지 제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="page_title" name="page_title"
                                            value="<?= htmlspecialchars($seo['page_title'] ?? '') ?>"
                                            placeholder="예: 여성암 검사 페이지" required>
                                    </div>
                                </div>


                                <!-- Meta Title -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="meta_title">메타 타이틀</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="meta_title" name="meta_title"
                                            value="<?= htmlspecialchars($seo['meta_title'] ?? '') ?>"
                                            placeholder="검색결과 제목">
                                    </div>
                                </div>

                                <!-- Meta Description -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="meta_description">메타 설명글</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <textarea name="meta_description" id="meta_description"
                                            class="no-textarea--detail" rows="4"
                                            placeholder="검색결과 요약 설명 입력"><?= htmlspecialchars($seo['meta_description'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Meta Keywords -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="meta_keywords">메타 키워드</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="meta_keywords" name="meta_keywords"
                                            value="<?= htmlspecialchars($seo['meta_keywords'] ?? '') ?>"
                                            placeholder="예: 여성암, 자궁경부암, 건강검진">
                                    </div>
                                </div>

                                <!-- 버튼 -->
                                <div class="no-items-center center">
                                    <a href="./seo.php?page=<?= $page ?>"
                                        class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <?php if (!$role->isReadOnly()): ?>
                                    <button type="submit" class="no-btn no-btn--big no-btn--main"
                                        id="editBtn">수정</button>
                                    <?php endif; ?>
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
