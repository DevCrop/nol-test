<!DOCTYPE html>
<html lang="ko">
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 8;
	$pagenum = 2;


	$no	= $_REQUEST[no];

	$query = "  select  a.no
						,a.sitekey
						,a.lev_name
						,a.is_join
						from nb_member_level a
			  where a.no = $no  ";

	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if(!$data){
		error("정보를 찾을 수 없습니다");
	}

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
			<form id="frm" name="frm" method="post" enctype="multipart/form-data">
			<input type="hidden" id="mode" name="mode" value="">
			<input type="hidden" id="no" name="no" value="<?=$data[no]?>">
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
                                            <span>회원등급 확인 및 수정</span>
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
                                <h2 class="no-card-title">회원 등급</h2>
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
                                            value="<?=$data[lev_name]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>회원가입시 적용</strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <label for="is_join" class="no-items-center">
                                            <div class="no-checkbox-form">
                                                <input type="checkbox" name="is_join" id="is_join" class="no-input--detail" value="Y">
                                                <span>
                                                    <i class="bx bxs-check-square"></i>
                                                </span>
                                            </div>

                                            <span class="no-admin-info no-mt">
                                                체크하면 이미 체크되어 있는 등급은 해제됩니다. 체크된 대상은 회원가입시 자동으로 &nbsp; <strong> <?=$data[lev_name]?> </strong> 등급으로 적용됩니다.
                                            </span>
                                        </label>
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
                                        onClick="doEditSave();"
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