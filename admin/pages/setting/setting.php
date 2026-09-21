<?php
	include_once "../../../inc/lib/base.class.php";
	header('Location: /admin/pages/account/password.php');
	exit;


	$depthnum = 4;
	$pagenum = 1;



	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php
        include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php
                include_once "../../inc/admin.drawer.php";
            ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="post" enctype="multipart/form-data" autocomplete="off">
			<input type="hidden" id="mode" name="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">비밀번호 변경</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>설정</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>비밀번호 변경</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>


                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">비밀번호 변경</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="pwd_old">현재 비밀번호</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="password"
                                            name="pwd_old"
                                            id="pwd_old"
                                            class="no-input--detail"
                                            value=""
                                            placeholder="현재 비밀번호"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="pwd_new">신규 비밀번호</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="password"
                                            name="pwd_new"
                                            id="pwd_new"
                                            class="no-input--detail"
                                            placeholder="신규 비밀번호"
                                            value=""
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="pwd_new_confirm">신규 비밀번호 확인</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="password"
                                            name="pwd_new_confirm"
                                            id="pwd_new_confirm"
                                            class="no-input--detail"
                                            placeholder="신규 비밀번호 확인"
                                            value=""
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-items-center center">
                                    <a
                                        href="javascript:doSave();"
                                        class="no-btn no-btn--big no-btn--main"
                                    >
                                        확인
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
                </section>
            </form>
        </main>

        

        <!-- Footer -->
        <script type="text/javascript" src="./js/setting.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
        
    </div>
</body>
</html>
