<?php
// 초기화
$gnbActive = array_fill(1, 10, "");
$pageActive = [
    'board' => ["", ""],
    'design' => ["", ""],
    'setting' => ["", "", ""],
    'log' => ["", "", "", ""],
	'works' => ["", "", "", ""],
    'calendar' => [""],
    'employment' => [""],
    'member' => [""],
    'admission' => [""]
];

// gnb 및 page 활성화 설정 함수
function setActive(&$gnbArray, &$pageArray, $depth, $page, $gnbIndex, $pageIndex) {
    $gnbArray[$gnbIndex] = "active";
    if (isset($pageArray[$pageIndex])) {
        $pageArray[$pageIndex][$page - 1] = "active";
    }
}

// 활성화 로직
switch ($depthnum) {
    case 1:
        setActive($gnbActive, $pageActive, $depthnum, $pagenum, 1, 'board');
        break;
    case 2:
        setActive($gnbActive, $pageActive, $depthnum, $pagenum, 2, 'design');
        break;
    case 3:
        $gnbActive[3] = "active";
        break;
    case 4:
        setActive($gnbActive, $pageActive, $depthnum, $pagenum, 4, 'setting');
        break;
    case 5:
        setActive($gnbActive, $pageActive, $depthnum, $pagenum, 5, 'log');
        break;
	case 6:
        setActive($gnbActive, $pageActive, $depthnum, $pagenum, 6, 'works');
        break;
}
?>

<aside class="no-sidebar">
    <h1 class="no-sidebar-logo">
        <a href="<?=$NO_IS_SUBDIR?>/admin/pages/board/board.list.php">
            <img src="<?=$NO_IS_SUBDIR?>/resource/images/admin/logo.png" alt="" class="no-logo--default" />
            <img src="<?=$NO_IS_SUBDIR?>/resource/images/admin/logo-sm.png" alt="" class="no-logo--sm" />
        </a>
        <div class="no-sidebar-toggle"><span><i class="bx bx-chevrons-left"></i></span></div>
    </h1>
    <div class="no-sidebar-menu">
        <nav class="no-sidebar-menu__inner">
            <ul class="no-menu-list">
                <li class="no-menu-item <?=$gnbActive[1]?>">
                    <span class="no-menu-link">
                        <span class="no-menu-icon"><i class="bx bx-list-ul"></i></span>
                        <span class="no-menu-title">게시판</span>
                        <span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
                    </span>
                    <ul class="no-menu-sub">
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/board/board.list.php" class="no-menu-link <?=$pageActive['board'][1]?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">게시글 관리</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="no-menu-item <?=$gnbActive[2]?>">
                    <span class="no-menu-link">
                        <span class="no-menu-icon"><i class="bx bx-color"></i></span>
                        <span class="no-menu-title">디자인</span>
                        <span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
                    </span>
                    <ul class="no-menu-sub">
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/design/banner.list.php" class="no-menu-link <?=$pageActive['design'][0]?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">배너 관리</span>
                            </a>
                        </li>
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/design/popup.list.php" class="no-menu-link <?=$pageActive['design'][1]?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">팝업 관리</span>
                            </a>
                        </li>
                    </ul>
                </li>
					

				<li class="no-menu-item <?=$gnbActive[5]?>">
					<a href="<?=$NO_IS_SUBDIR?>/admin/pages/opera/" class="no-menu-link <?=$gnbActive[5]?>">
						<span class="no-menu-icon"><i class='bx bx-glasses-alt'></i></span>
						<span class="no-menu-title">오페라글라스 링크</span>
					</a>
				</li>
	

				<li class="no-menu-item <?=$gnbActive[6]?>">
					<a href="<?=$NO_IS_SUBDIR?>/admin/pages/works/" class="no-menu-link <?=$gnbActive[6]?>">
						<span class="no-menu-icon"><i class='bx bxs-party'></i></span>
						<span class="no-menu-title">What's ON</span>
					</a>
				</li>



				<li class="no-menu-item <?=$gnbActive_3?>">
					  <span class="no-menu-link">
							<span class="no-menu-icon"><i class="bx bx-phone-call"></i></span>
							<span class="no-menu-title">대관</span>
							<span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
						</span>
						  <ul class="no-menu-sub">
							<li class="no-menu-item">
								<a href="<?=$NO_IS_SUBDIR?>/admin/pages/request/request.schedule.list.php" class="no-menu-link">
									<span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
									<span class="no-menu-title">대관 일정</span>
								</a>
							</li>
							<li class="no-menu-item">
								<a href="<?=$NO_IS_SUBDIR?>/admin/pages/request/request.list.php" class="no-menu-link">
									<span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
									<span class="no-menu-title">대관 문의</span>
								</a>
							</li>
						</ul>
				</li>
				

                <li class="no-menu-item <?=$gnbActive[4]?>">
                    <span class="no-menu-link">
                        <span class="no-menu-icon"><i class="bx bx-cog"></i></span>
                        <span class="no-menu-title">사이트정보관리</span>
                        <span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
                    </span>
                    <ul class="no-menu-sub">
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/account/password.php" class="no-menu-link <?=basename($_SERVER['SCRIPT_NAME']) === 'password.php' ? 'active' : ''?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">비밀번호 변경</span>
                            </a>
                        </li>
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/setting/site.config.php" class="no-menu-link <?=$pageActive['setting'][1]?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">사이트 정보관리</span>
                            </a>
                        </li>
                        <li class="no-menu-item">
                            <a href="<?=$NO_IS_SUBDIR?>/admin/pages/setting/site.data.list.php" class="no-menu-link <?=$pageActive['setting'][2]?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title">사이트 데이터관리</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="no-menu-item <?=$gnbActive[5]?>">
					<a href="https://analytics.naver.com/summary/dashboard.html" class="no-menu-link <?=$gnbActive[6]?>" target="_blank">
						<span class="no-menu-icon"><i class="bx bx-bar-chart-square"></i></span>
						<span class="no-menu-title">접속통계</span>
					</a>
				</li>
<?php
$accountMenu = require __DIR__ . '/../config/account-menu.php';
$viewer = \Security\AdminAccount::findByNo((int) ($_SESSION['no_adm_login_no'] ?? 0));
$accountFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$accountSection = strpos((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/account/') !== false && $accountFile !== 'password.php';
if (($viewer['role_code'] ?? '') === 'super'):
?>
<li class="no-menu-item <?=$accountSection ? 'active' : ''?>">
    <span class="no-menu-link">
        <span class="no-menu-icon"><i class="fa-solid fa-user-shield"></i></span>
        <span class="no-menu-title"><?=$accountMenu['title']?></span>
        <span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
    </span>
    <ul class="no-menu-sub">
        <?php foreach ($accountMenu['subs'] as $sub): ?>
        <li class="no-menu-item"><a href="/admin/pages/account/<?=$sub['url']?>" class="no-menu-link <?=$accountSection && in_array($accountFile, $sub['active'], true) ? 'active' : ''?>" <?=$accountSection && in_array($accountFile, $sub['active'], true) ? 'aria-current="page"' : ''?>>
            <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
            <span class="no-menu-title"><?=$sub['title']?></span>
        </a></li>
        <?php endforeach; ?>
    </ul>
</li>
<?php endif; ?>

            </ul>
        </nav>
    </div>

    <div type="button" id="theme-btn">
        <input type="checkbox" id="toggle" />
        <label class="toggle" for="toggle">
            <i class="bx bxs-sun"></i>
            <i class="bx bx-moon"></i>
            <span class="ball"></span>
        </label>
    </div>
</aside>

<script defer>
    const activatedMenu = document.querySelector('.no-menu-item.active .no-menu-arrow');
    if (activatedMenu) activatedMenu.classList.add('open');
</script>
