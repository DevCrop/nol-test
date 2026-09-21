<!DOCTYPE html>
<html lang="ko">
<?
	include_once $_SERVER[DOCUMENT_ROOT]."/inc/lib/base.class.php";
	
	$depthnum = 6;
	$pagenum = 1;
	
	
	$no			= $_REQUEST[no];


	$query = "  select  a.no,
								a.campus,
								a.cdate,
								a.title,
								a.contents,
								a.regdate
					from nb_academic_calendar a
					where a.no = $no  ";
	//echo $query;exit;
	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if(!$data){
		error("정보를 찾을 수 없습니다");
	}

	$memberCampus = getCampus();

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
             <input type="hidden" id="mode" name="mode" value="edit">
			<input type="hidden" id="returnListUrl" name="returnListUrl" value="./calendar.list.php">
			<input type="hidden" id="ajaxUrl" name="ajaxUrl" value="./ajax/process.php">
			<input type="hidden" id="useEditor" name="useEditor" value="N">
            <input type="hidden" id="no" name="no" value="<?=$data[no]?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">Calendar 목록</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>Calendar</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>Calendar 목록</span>
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
                                <h2 class="no-card-title">Calendar 내용</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="campus">캠퍼스</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select
                                            type="text"
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
											<option value="<?=$v['no']?>" <?=($data['campus'] == $v['no']) ? "selected" : "" ?>><?=$v['name']?></option>
											<?
												}
											?>
                                        </select>
                                    </div>
                                </div>
                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="c_date">날짜</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="date"
                                            name="cdate"
                                            id="c_date"
                                            class="no-input--detail"
                                            value="<?=$data[cdate]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="email_address">제목</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="title"
                                            id="title"
                                            class="no-input--detail"
                                            value="<?=$data[title]?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="content" >내용</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-admin-check">
                                            <textarea
                                                style="height: 200px;"
                                                name="contents"
                                                id="contents"
                                            ><?=$data['contents']?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doCommonDelete(<?=$data[no]?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./calendar.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--main"
                                          onClick="doCommonRegAndSave('frm');"
                                    >
                                        수정
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
        <script type="text/javascript" src="./js/employment.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>

    </div>

    <script>
        $('#campus').selectmenu();

        $('#c_date').click(function(e){
        })
		

		const contents = document.getElementById('contents');
		contents.value.trim();
    </script>
</body>
</html>