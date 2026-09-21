<!DOCTYPE html>
<html lang="ko">
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 8;
	$pagenum = 2;

	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?
        include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?
                include_once "../../inc/admin.drawer.php";
            ?>

            

            <!-- Contents -->
            <?
				$query = "select no, lev_name, is_join  from nb_member_level where sitekey = '$NO_SITE_UNIQUE_KEY' order by no asc";
				$result_2nd =mysql_query($query);
				$arrList = array(); 
				while($row = mysql_fetch_assoc($result_2nd)) { 
					$arrList[] = $row; 
				} 
			?>
			<form id="frm" name="frm" method="post" enctype="multipart/form-data">
			<input type="hidden" id="mode" name="mode" value="">
			<input type="hidden" id="dupCheck" name="dupCheck" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">회원</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>회원등급 관리</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>회원등급 등록</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>

                    <?
                        include_once "../../inc/admin.lnb.member.php";
                    ?>


                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">회원 등록</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="lev_name">등급명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="lev_name"
                                            id="lev_name"
                                            class="no-input--detail"
                                            placeholder="등급명"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                
                                

                                <div class="no-items-center center">
                                    <a
                                        href="./member.level.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--main"
                                        onClick="doRegSave();"
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
        <script type="text/javascript" src="./js/member.level.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>

    </div>
</body>
</html>