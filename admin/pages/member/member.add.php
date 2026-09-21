<!DOCTYPE html>
<html lang="ko">
<?
include_once $_SERVER[DOCUMENT_ROOT]."/inc/lib/base.class.php";

$depthnum = 8;
$pagenum = 1;



$no	= $_REQUEST[no];




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
                                            <span>회원관리</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>회원등록</span>
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
                                        <label for="lev">회원등급 선택</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="lev" id="lev" class="no_mb10 no_mt10">
                                            <option value="">등급선택</option>
                                            <?
                                                foreach($arrList as $k=>$v){
                                            ?>
                                                <option value="<?=$v[no]?>"><?=$v[lev_name]?></option>
                                            <?
                                                }
                                            ?>
                                        </select>

                                        <small>등록될 회원의 등급을 선택해주세요. 비회원인 경우 선택하지 않으면 됩니다.</small>
                                    </div>
                                </div>
                                <!-- admin-block -->
                                

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="uid">아이디</label>
                                    </h3>
                                    <div class="no-admin-content file">
                                        <div class="admin-flex">
                                            <input
                                                type="text"
                                                name="uid"
                                                id="uid"
                                                class="no-input--detail"
                                                placeholder="아이디"
                                            />
                                            <a href="javascript:void(0);" class="no-btn no-btn--main" onClick="doDupCheck();">중복확인</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="upwd">비밀번호</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="password"
                                            name="upwd"
                                            id="upwd"
                                            class="no-input--detail"
                                            placeholder="비밀번호"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="upwd_confirm">비밀번호 확인</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="password"
                                            name="upwd_confirm"
                                            id="upwd_confirm"
                                            class="no-input--detail"
                                            placeholder="비밀번호 확인"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="uname">이름</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="uname"
                                            id="uname"
                                            class="no-input--detail"
                                            placeholder="이름"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="phone">연락처</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="phone"
                                            id="phone"
                                            class="no-input--detail"
                                            placeholder="연락처"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="hp">휴대폰번호</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="hp"
                                            id="hp"
                                            class="no-input--detail"
                                            placeholder="휴대폰번호"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="email">이메일 주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="email"
                                            id="email"
                                            class="no-input--detail"
                                            placeholder="이메일"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                

                                <div class="no-items-center center">
                                    <a
                                        href="./member.list.php"
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
        <script type="text/javascript" src="./js/member.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>

    </div>
</body>
</html>