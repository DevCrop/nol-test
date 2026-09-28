<?php

use Database\DB;

$conn = DB::getInstance();
$sql = "SELECT * FROM nb_siteinfo";
$result = $conn->query($sql);
$siteinfo = $result->fetch(PDO::FETCH_ASSOC);
app()->share('siteinfo', $siteinfo);

$LOCALE = $LOCALE ?? 'ko';

// HTTPS 판별(프록시 고려)
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
    (($_SERVER['SERVER_PORT'] ?? '') == 443)
);
$scheme = $isHttps ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$baseUrl = $scheme . '://' . $host;


// --- siteinfo 매핑(안전한 기본값)
$siteName        = $siteinfo['title']         ?? '';
// meta_title 필드가 없으면 title 필드를 사용, 둘 다 없으면 빈 문자열
$metaTitleBase   = $siteinfo['meta_title'] ?? ($siteinfo['title'] ?? '');
$metaDescBase    = $siteinfo['meta_description']  ?? '';
$metaKeywords    = $siteinfo['meta_keywords']     ?? '';

// 경로 기반 SEO 데이터 조회
// 우선순위: 1) nb_branch_seos (현재 URL과 일치하는 path) > 2) nb_siteinfo
$pathSeo = null;
try {
    $adminModelBasePath = isset($GLOBALS['NO_ADMIN_PATH'])
        ? $GLOBALS['NO_ADMIN_PATH']
        : (dirname(__DIR__, 2) . '/nol-gate');
    require_once $adminModelBasePath . '/Model/SeoModel.php';
    if (class_exists('SeoModel')) {
        // 쿼리스트링 제거한 경로로 조회
        $cleanUri = parse_url($uri, PHP_URL_PATH) ?: $uri;
        $pathSeo = SeoModel::findByPath($cleanUri);
    }
} catch (Exception $e) {
    // SEO 모델이 없어도 계속 진행
}

// SEO 데이터 우선순위 적용
// 1순위: nb_branch_seos의 경로별 SEO 데이터
// 2순위: nb_siteinfo의 기본 SEO 데이터
// lang/ko.php의 기본값은 사용하지 않음
$finalSeoTitle = !empty($pathSeo['meta_title'])
    ? $pathSeo['meta_title']
    : $metaTitleBase;

$finalSeoDesc = !empty($pathSeo['meta_description'])
    ? $pathSeo['meta_description']
    : $metaDescBase;

$finalSeoKeywords = !empty($pathSeo['meta_keywords'])
    ? $pathSeo['meta_keywords']
    : $metaKeywords;

// 페이지별 SEO 변수 (OG/Twitter 등에서 사용)
$pageTitle       = $finalSeoTitle;
$pageDesc        = $finalSeoDesc;
$pageKeywords    = $finalSeoKeywords;

// Canonical/URL들
$canonicalUrl    = $baseUrl . $uri;

// OG / Twitter
$ogTitle         = $siteinfo['og_title']       ?? $pageTitle;
$ogDescription   = $siteinfo['og_description'] ?? $pageDesc;
// meta_thumb 필드 사용 (없으면 기본 이미지)
$ogImage         = $siteinfo['og_image'] ?? (!empty($siteinfo['meta_thumb']) ? (BASE_PATH . '/uploads/meta/' . $siteinfo['meta_thumb']) : (BASE_PATH . '/resource/images/ogimg.jpg'));
$ogImageAbs      = (strpos($ogImage, 'http') === 0) ? $ogImage : ($baseUrl . $ogImage);
$ogLocale        = $siteinfo['og_locale']      ?? ($LOCALE === 'ko' ? 'ko_KR' : 'en_US');
$ogType          = $siteinfo['og_type']        ?? 'website';

$twitterCard     = $siteinfo['twitter_card']   ?? 'summary';
$twitterImage    = $siteinfo['twitter_image']  ?? $ogImageAbs;
$twitterTitle    = $siteinfo['twitter_title']  ?? $pageTitle;
$twitterDesc     = $siteinfo['twitter_desc']   ?? $pageDesc;

// 파비콘/썸네일
// meta_favicon_ico 필드 사용 (없으면 기본 파비콘)
$favicon         = $siteinfo['favicon'] ?? (!empty($siteinfo['meta_favicon_ico']) ? (BASE_PATH . '/uploads/meta/' . $siteinfo['meta_favicon_ico']) : (BASE_PATH . '/resource/images/favicon.png'));
$faviconAbs      = (strpos($favicon, 'http') === 0) ? $favicon : ($baseUrl . $favicon);

// 검증 태그들
$naverVerify     = $siteinfo['naver_site_verification'] ?? null;
$googleVerify    = $siteinfo['google_site_verification'] ?? []; // 문자열/배열 모두 허용
if (!is_array($googleVerify)) $googleVerify = array_filter([$googleVerify]);

// 기타 메타
$metaImageAbs    = $siteinfo['meta_image'] ?? $ogImageAbs;


?>

<!DOCTYPE html>
<html lang="<?= e($LOCALE) ?>">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    // SEO 우선순위: 1) nb_branch_seos (경로별 SEO) > 2) nb_siteinfo (기본 SEO)
    // yield_section은 페이지에서 특별히 지정한 경우에만 사용 (선택적)
    $seo_title = yield_section('seo_title') ?: $finalSeoTitle;
    $seo_desc = yield_section('seo_desc') ?: $finalSeoDesc;
    $seo_keywords = yield_section('seo_keywords') ?: $finalSeoKeywords;
    ?>

    <title><?= e($seo_title) ?></title>
    <meta name="description" content="<?= e($seo_desc) ?>">
    <meta name="keywords" content="<?= e($seo_keywords) ?>">


    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="robots" content="index, follow" />
    <meta name="image" content="<?= e($metaImageAbs) ?>">

    <?php if ($naverVerify): ?>
    <meta name="naver-site-verification" content="<?= e($naverVerify) ?>" />
    <?php endif; ?>
    <?php foreach ($googleVerify as $gv): ?>
    <meta name="google-site-verification" content="<?= e($gv) ?>" />
    <?php endforeach; ?>

    <!-- Open Graph -->
    <meta property="og:locale" content="<?= e($ogLocale) ?>" />
    <meta property="og:url" content="<?= e($baseUrl) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:site_name" content="<?= e($seo_title) ?>">
    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($seo_desc) ?>">
    <meta property="og:image" content="<?= e($ogImageAbs) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="<?= e($twitterCard) ?>">
    <meta name="twitter:title" content="<?= e($seo_title) ?>">
    <meta name="twitter:description" content="<?= e($seo_desc) ?>">
    <meta name="twitter:image" content="<?= e($twitterImage) ?>">
    <meta name="naver-site-verification" content="0b943ac73847b37ff54c376259304fc21e197333" />
    <meta name="google-site-verification" content="Y9mYSI_eTkNgl3eqX04LF7AnxvZXDcBl-0tQY-71kis" />

    <!-- Favicon -->
    <?php if (!empty($siteinfo['meta_favicon_ico'])): ?>
    <link rel="icon" type="image/x-icon" href="<?= base_path('/uploads/meta/' . $siteinfo['meta_favicon_ico']) ?>">
    <?php else: ?>
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?= base_path('/resource/images/favicon/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?= base_path('/resource/images/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?= base_path('/resource/images/favicon/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= base_path('/resource/images/favicon/site.webmanifest') ?>">
    <?php endif; ?>


    <!-- Vendor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/beerslider@1.0.3/dist/BeerSlider.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/resource/vendor/swiper/swiper-bundle.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/resource/vendor/aos/aos.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/resource/dist/style.css?v=<?= time() ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/themes/base/jquery-ui.min.css">
    <?= yield_section('style', '') ?>

    <!-- Vendor JS (head 필요분) -->
    <script src="<?= BASE_PATH ?>/resource/vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js"></script>
    <script src="<?= BASE_PATH ?>/resource/vendor/swiper/swiper-bundle.js"></script>
    <script src="<?= BASE_PATH ?>/resource/vendor/marquee/jquery.marquee.min.js"></script>
    <script src="<?= BASE_PATH ?>/resource/vendor/aos/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/beerslider@1.0.3/dist/BeerSlider.min.js"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>

    <script src="<?= BASE_PATH ?>/resource/vendor/fontAwesome/fontAwesome.min.js" crossorigin="anonymous"></script>
    <script src="<?= BASE_PATH ?>/resource/vendor/lenis/lenis.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>

    <!-- Theme Init (head에서 즉시 실행 - 트랜지션 방지) -->
    <script>
    (function() {
        const savedTheme = localStorage.getItem("theme") || "dark";
        document.documentElement.setAttribute("data-theme", savedTheme);
        document.documentElement.classList.remove("is-transition");
    })();
    </script>

    <!-- App JS -->
    <script src="<?= BASE_PATH ?>/resource/dist/app.js?v=<?= time() ?>" defer></script>

    <?php
    // HEAD 위치 태그 렌더링 (location = 1)
    try {
        $tagStmt = $conn->prepare("SELECT tag_content FROM nb_site_tags WHERE location = 1 AND is_active = 1 ORDER BY id ASC");
        $tagStmt->execute();
        $headTags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($headTags as $tag) {
            echo sanitize_embed_html($tag['tag_content'] ?? '') . "\n    ";
        }
    } catch (Exception $e) {
        // 태그 조회 실패 시 무시
    }
    ?>
</head>

<body<?php
        // theater 페이지인지 확인
        $isTheaterPage = current_route_is('venue.theater');
        if ($isTheaterPage) {
            echo ' class="is-theater-page"';
        }
        ?>>
    <?php
    // BODY 위치 태그 렌더링 (location = 2)
    try {
        $tagStmt = $conn->prepare("SELECT tag_content FROM nb_site_tags WHERE location = 2 AND is_active = 1 ORDER BY id ASC");
        $tagStmt->execute();
        $bodyTags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bodyTags as $tag) {
            echo sanitize_embed_html($tag['tag_content'] ?? '') . "\n    ";
        }
    } catch (Exception $e) {
        // 태그 조회 실패 시 무시
    }
    ?>
    <div class="__root">
        <?= include_view('components.header') ?>
        <?= include_view('components.backdrop') ?>
        <?= include_view('components.drawer') ?>
        <?= include_view('components.search') ?>
        <?= include_view('components.floating-button') ?>

        <?php
        // 현재 경로 확인 (루트는 no-main, 그 외는 no-sub 클래스 추가)
        $isRoot = current_route_is('home');

        // main 클래스 조합
        $mainClass = yield_section('main_class');
        if ($isRoot) {
            // 루트 페이지: no-main 클래스 추가
            $mainClass .= $mainClass ? ' no-main' : 'no-main';
        } else {
            // 서브 페이지: no-sub 클래스 추가
            $mainClass .= $mainClass ? ' no-sub' : 'no-sub';
        }
        $mainClass = trim($mainClass);
        ?>
        <main class="<?= $mainClass ?>">
            <?= yield_section('content') ?>
        </main>
        <?= include_view('components.footer') ?>
    </div>

    <?= yield_section('portal', '') ?>
    <?= yield_section('script', '') ?>
    </body>

</html>
