<?php
include_once dirname(__DIR__) . '/lib/admin.check.php';

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
            <button type="button" class="no-header-menu-btn" aria-label="메뉴 열기" aria-expanded="false">
                <i class="bx bxs-user-circle" aria-hidden="true"></i>
            </button>
            <div class="no-nav-list">
                <nav class="no-header-actions" aria-label="상단 메뉴">
                    <ul class="no-header-actions__list">
                        <li class="no-header-actions__item">
                            <div class="no-session-timer"
                                data-session-idle
                                data-session-timeout="<?= $sessionLifetime ?>"
                                data-activity-url="<?= $NO_ADMIN_BASE ?>/ajax/session.activity.php"
                                data-logout-url="<?= $NO_ADMIN_BASE ?>/lib/login/logout.php"
                                title="마지막 활동 후 자동 로그아웃까지 남은 시간">
                                <i class="bx bx-time-five" aria-hidden="true"></i>
                                <span class="no-session-timer__label">자동 로그아웃</span>
                                <strong data-session-countdown>--:--</strong>
                            </div>
                        </li>
                        <?php if (empty($hideAdminNav)): ?>
                        <li class="no-header-actions__item">
                            <a href="<?= $NO_IS_SUBDIR ?>/index.php" target="_blank" rel="noopener" class="no-header-actions__link" title="사이트 새 창에서 보기">
                                <i class="bx bx-home-alt" aria-hidden="true"></i>
                                <span class="no-header-actions__text">홈페이지</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['no_adm_login_uid'])): ?>
                        <li class="no-header-actions__item">
                            <a href="<?= $NO_ADMIN_BASE ?>/lib/login/logout.php" class="no-header-actions__link no-header-actions__link--logout" title="로그아웃">
                                <i class="bx bx-log-out" aria-hidden="true"></i>
                                <span class="no-header-actions__text">로그아웃</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="no-header-user" title="<?= $roleTitle ?>">
                    <div class="no-header-user__avatar" aria-hidden="true">
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="no-header-user__info">
                        <span class="no-header-user__name"><?= htmlspecialchars($NO_ADM_NAME ?: $NO_ADM_ID) ?></span>
                        <span class="no-header-user__meta">
                            <span class="no-header-user__role"><?= htmlspecialchars($role->getName()) ?></span>
                            <?php if ($role->isReadOnly()): ?>
                            <span class="no-header-user__badge no-header-user__badge--readonly" title="수정과 등록이 제한됩니다.">조회 전용</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <span class="no-header-user__status no-header-user__status--online" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>
</header>
