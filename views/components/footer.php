<?php

/** @var \Menu\Menu $menu */
$menu = app()->get('menu');
/** @var array $siteinfo */
$siteinfo = app()->get('siteinfo');
?>

<footer class="no-footer">
    <div class="no-footer__inner">
        <div class="no-container-2xl">
            <!-- 상단: 로고 + Supported by -->
            <div class="no-footer__top">
                <div class="no-footer__logo">
                    <a href="/">
                        <div class="no-theme-change">
                            <img src="<?= base_path('/resource/images/logo/logo_color.png') ?>" alt="NOL 씨어터 대학로 공연장"
                                class="light">
                            <img src="<?= base_path('/resource/images/logo/logo_white.png') ?>" alt="NOL 씨어터 대학로 공연장"
                                class="dark">
                        </div>
                    </a>
                </div>
                <div class="no-footer__sponsor">
                    <span class="no-footer__sponsor-label">Sponsor</span>
                    <a href="https://www.wooribank.com" target="_blank" rel="noopener noreferrer">
                        <img src="<?= base_path('/resource/images/logo/sponser_logo_1.png') ?>" alt="우리은행"
                            class="no-footer__sponsor-logo">
                    </a>
                </div>
            </div>

            <!-- 중간: 정보 + 네비게이션 -->
            <div class="no-footer__mid">
                <div class="no-footer__info">
                    <div class="no-footer__info-item">
                        <span class="no-footer__info-label">주소</span>
                        <span class="no-footer__info-text"><?= e($siteinfo['footer_address'] ?? '') ?></span>
                    </div>
                    <div class="no-footer__info-item">
                        <span class="no-footer__info-label">고객센터</span>
                        <span class="no-footer__info-text"><?= e($siteinfo['footer_phone'] ?? '') ?></span>
                    </div>
                    <div class="no-footer__info-item">
                        <span class="no-footer__info-label">E-mail</span>
                        <span
                            class="no-footer__info-text"><?= e($siteinfo['footer_email'] ?? $siteinfo['email'] ?? '') ?></span>
                    </div>

                </div>
                <div class="no-footer__nav">
                    <?php if ($menu): ?>
                    <?php foreach ($menu->rootItems() as $root):
                            if (!$root->isVisible()) continue;
                            $children = $root->children(true);
                        ?>
                    <div class="no-footer__nav-col">
                        <a href="<?= e($root->url()) ?>" class="no-footer__nav-title"><?= e($root->label()) ?></a>
                        <?php if (!empty($children)): ?>
                        <ul class="no-footer__nav-list">
                            <?php foreach ($children as $child): ?>
                            <li class="no-footer__nav-item">
                                <a href="<?= e($child->url()) ?>"
                                    class="no-footer__nav-link"><?= e($child->label()) ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 하단: 법적 링크 + Copyright -->
            <div class="no-footer__bottom">
                <div class="no-footer__bottom-right">
                    <div class="no-footer__legal">
                        <a href="<?= route('legal.privacy') ?>" class="no-footer__legal-link">개인정보처리방침</a>
                    </div>
                    <div class="no-footer__copy">
                        <span>Copyright © Nol Universe Co., Ltd. All rights reserved.</span>
                    </div>
                </div>
                <div class="no-footer__social">
                    <a href="https://www.instagram.com/nol.theater/" target="_blank" rel="noopener noreferrer"
                        class="no-footer__social-link" aria-label="인스타그램">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php
// FOOTER 위치 태그 렌더링 (location = 3)
try {
    $conn = DB::getInstance();
    $tagStmt = $conn->prepare("SELECT tag_content FROM nb_site_tags WHERE location = 3 AND is_active = 1 ORDER BY id ASC");
    $tagStmt->execute();
    $footerTags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($footerTags as $tag) {
        echo sanitize_embed_html($tag['tag_content'] ?? '') . "\n";
    }
} catch (Exception $e) {
    // 태그 조회 실패 시 무시
}
?>
