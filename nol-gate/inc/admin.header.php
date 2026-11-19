<?php
include_once dirname(__DIR__) . '/lib/admin.check.php';
require_once dirname(__DIR__) . '/lib/PublicSiteLink.php';
$developmentPort=parse_url('http://'.($_SERVER['HTTP_HOST']??''),PHP_URL_PORT);
$publicHome=PublicSiteLink::home(PUBLIC_HOST,env('APP_ENV','production')==='development',is_int($developmentPort)?$developmentPort:null);

$roleTitle = '계정: ' . htmlspecialchars($NO_ADM_ID ?? '') . ' / 권한: ' . htmlspecialchars($role->getName());
$sessionLifetime = defined('SESSION_LIFETIME') ? (int) SESSION_LIFETIME : 1800;
if ($role->isReadOnly()) {
    $roleTitle .= ' (조회 전용)';
}
?>

<header class="no-header" role="banner">
    <div class="no-header__inner">
        <div class="no-header__left">
            <?php if (empty($hideAdminNav)): ?>
            <div class="no-drawer-btn">
                <button type="button" aria-label="메뉴 열기" class="no-drawer-btn__trigger">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div class="no-header__right">
            <div class="no-session-timer"
                 data-session-idle
                 data-session-timeout="<?= $sessionLifetime ?>"
                 data-activity-url="<?= $NO_ADMIN_BASE ?>/ajax/session.activity.php"
                 data-logout-url="<?= $NO_ADMIN_BASE ?>/lib/login/logout.php"
                 title="마지막 활동 후 자동 로그아웃까지 남은 시간">
                <span>자동 로그아웃</span>
                <strong data-session-countdown>--:--</strong>
            </div>
            <button type="button" class="no-header-menu-btn" aria-label="사용자 메뉴"
                    aria-expanded="false" aria-controls="admin-user-menu" title="<?= $roleTitle ?>">
                <i class="bx bxs-user-pin" aria-hidden="true"></i>
            </button>
            <nav id="admin-user-menu" class="no-nav-list" aria-label="상단 메뉴">
                <ul class="no-header-actions__list" title="<?= $roleTitle ?>">
                    <?php if (empty($hideAdminNav)): ?>
                    <li>
                        <a href="<?= htmlspecialchars($publicHome, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="no-header-actions__link">
                            <i class="bx bx-home" aria-hidden="true"></i> 내홈페이지
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['no_adm_login_uid'])): ?>
                    <li>
                        <a href="<?= $NO_ADMIN_BASE ?>/lib/login/logout.php" data-admin-post class="no-header-actions__link no-header-actions__link--logout">
                            <i class="bx bx-log-out" aria-hidden="true"></i> 로그아웃
                        </a>
                    </li>
                    <li>
                        <a href="<?= $NO_ADMIN_BASE ?>/pages/setting/pwd.php" class="no-header-actions__link" title="본인 비밀번호 변경">
                            <i class="bx bx-user" aria-hidden="true"></i> 비밀번호 변경
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>
