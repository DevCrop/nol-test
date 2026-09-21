<!DOCTYPE html>
<html lang="ko">
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 8;
	$pagenum = 1;


		

	$no	= $_REQUEST[no];

	$query = "  select  
						a.no
						,a.sitekey
						,a.lev
						,a.campus
						,a.gubun
						,a.grade
						,a.uid
						,a.upwd
						,a.uname
						,a.name_first
						,a.name_last
						,a.phone
						,a.hp
						,a.email
						,a.hp_recieve_yn
						,a.email_recieve_yn
						,a.addr1
						,a.regdate
						,a.child_name
						,b.name as campus_name
						from nb_member a
						left join nb_campus b on a.campus = b.no
			  where a.no = $no  ";

	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if(!$data){
		error("정보를 찾을 수 없습니다");
	}

	$memberCampus = getCampus();

	 $query = "select no, lev_name, is_join  from nb_member_level where sitekey = '$NO_SITE_UNIQUE_KEY' order by no asc";
	$result_2nd =mysql_query($query);
	$arrList = array(); 
	while($row = mysql_fetch_assoc($result_2nd)) { 
		$arrList[] = $row; 
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

            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
			<input type="hidden" id="mode" name="mode" value="edit">
			<input type="hidden" id="returnListUrl" name="returnListUrl" value="./member.list.php">
			<input type="hidden" id="ajaxUrl" name="ajaxUrl" value="./ajax/member.process.php">
			<input type="hidden" id="useEditor" name="useEditor" value="N">
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
                                <h2 class="no-card-title">회원 </h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                            <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="lev">회원등급 선택</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="lev" id="lev" class="no_mb10 no_mt10" >
                                            <option value="">등급선택</option>
                                            <?
                                                foreach($arrList as $k=>$v){
                                            ?>
                                                <option value="<?=$v[no]?>" <? if($data[lev] == $v[no]) echo "selected";?>><?=$v[lev_name]?></option>
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
                                        <label for="campus">캠퍼스</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select
                                            name="campus"
                                            id="campus"
                                            class="no-input--detail"
											data-alert="캠퍼스를 선택해주세요" data-require="true"
                                        >
											<option value="">선택</option>
                                           <?
												$num = 0;
												foreach($memberCampus as $k=>$v){ 
												$num++;
											?>
											<option value="<?=$v['no']?>" <?=($data['campus'] == $v['no']) ? "selected" : ""?>><?=$v['name']?></option>
											<?
												}
											?>
                                        </select>
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">가입구분</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="gubun1">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="gubun"
                                                        id="gubun1"
														class="gubun"
                                                        value="재학생"
                                                        data-alert="회원 구분을 선택해주세요" data-require="true"
														<?=($data['gubun'] == "재학생") ? "checked" : ""?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >재학생</span
                                                >
                                            </label>

                                            <label for="gubun2">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="gubun"
                                                        id="gubun2"
														class="gubun"
                                                        value="학부모"
														data-alert="회원 구분을 선택해주세요" data-require="true"
														<?=($data['gubun'] == "학부모") ? "checked" : ""?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >학부모</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->


								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">학년</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
											<? 
												for($i=0;$i<13;$i++){

													$grade = "kindergarten";
													if($i>0)
														$grade = "G".$i;

											?>
                                            <label for="grade<?=$i?>">
                                                <div class="no-radio-box">
                                                    <input
                                                       type="radio"
														name="grade"
														id="grade<?=$i?>"
														class="grade"
														data-alert="학년을 선택해주세요" data-require="true"
														value="<?=$grade?>"
														<?=($data['grade'] == $grade) ? "checked" : ""?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    ><?=$grade?></span
                                                >
                                            </label>
											<?
												}
											?>
                                            
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="uname">아이디</label>
                                    </h3>
                                    <div class="no-admin-content file">
                                        <div class="admin-flex">
                                           <p class="no-textbox no-textbox--sm">
                                            <?=$data[uid]?>
                                           </p>
                                        </div>
                                        <small>아이디는 수정할 수 없습니다.</small>
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
                                        <label for="name_first">성명(성)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="name_first"
                                            id="name_first"
                                            class="no-input--detail"
                                            placeholder="성명(성)"
											data-alert="성명(성)을 입력해주세요" data-require="true"
                                            value="<?=$data[name_first]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="name_last">성명(이름)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="name_last"
                                            id="name_last"
                                            class="no-input--detail"
                                            placeholder="성명(이름)"
											data-alert="성명(이름)을 입력해주세요" data-require="true"
                                            value="<?=$data[name_last]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="child_name">자녀 이름</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="child_name"
                                            id="child_name"
                                            class="no-input--detail"
                                            placeholder="자녀 이름"
                                            value="<?=$data[child_name]?>"
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
											data-alert="휴대폰 번호를 입력해주세요" data-require="true"
                                            value="<?=$data[hp]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="email">주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="addr1"
                                            id="addr1"
                                            class="no-input--detail"
                                            placeholder="주소"
                                            value="<?=$data[addr1]?>"
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
                                            value="<?=$data[email]?>"
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
                                       onClick="doCommonRegAndSave('frm');"
                                    >
                                        저장
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

	<script>
		$('#campus').selectmenu();
	</script>
</body>
</html>