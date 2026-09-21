<!DOCTYPE html>
<html lang="ko">
<?
include_once $_SERVER[DOCUMENT_ROOT]."/inc/lib/base.class.php";

$depthnum = 7;
$pagenum = 1;



$no	= $_REQUEST[no];

$query = "  select
            a.no,
            a.firstname,
            a.lastname,
            a.email_address,
            a.contact_number,
            a.campus,
            a.degree,
            a.visa,
            a.teaching_subject,
            a.regdate,
            a.univ,
            a.major,
            a.school_year,
            a.filename,
			a.visa_other,
			b.name as campus_name
		  from nb_employment a
		  left join nb_campus b on a.campus = b.no
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
            <?
                $query = "select no, title, skin, sort_no  from nb_board_manage order by no asc";
                $result_2nd =mysql_query($query);
                $arrBoardList = array();
                while($row = mysql_fetch_assoc($result_2nd)) {
                    $arrBoardList[] = $row;
                }
            ?>
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
            <input type="hidden" id="mode" name="mode" value="">
            <input type="hidden" id="no" name="no" value="<?=$data[no]?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">Employment 목록</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>Employment</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>Employment 목록</span>
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
                                <h2 class="no-card-title">Employment 내용</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="firstname">First Name</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="firstname"
                                            id="firstname"
                                            class="no-input--detail"
                                            value="<?=$data[firstname]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="lastname">Last Name</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="lastname"
                                            id="lastname"
                                            class="no-input--detail"
                                            value="<?=$data[lastname]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="email_address">Email Address</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="email_address"
                                            id="email_address"
                                            class="no-input--detail"
                                            value="<?=$data[email_address]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="contact_number">Contact Number</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="contact_number"
                                            id="contact_number"
                                            class="no-input--detail"
                                            value="<?=$data[contact_number]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="campus">Campus</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="campus"
                                            id="campus"
                                            class="no-input--detail"
                                            value="<?=$data['campus_name']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="degree">Degree</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <h4><?=$data[degree]?></h4>

                                        <ul class="list">
                                            <li>
                                                <strong>University</strong>
                                                <p><?=$data[univ]?></p>
                                            </li>
                                            <li>
                                                <strong>Major</strong>
                                                <p><?=$data[major]?></p>
                                            </li>
                                            <li>
                                                <strong>School Year</strong>
                                                <p><?=$data[school_year]?></p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="visa">VISA</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <?php 
                                        $list = explode(',', $data[visa]);

                                        if(count($list)){ ?>
                                        <ul class="marker">
                                            <?php foreach($list as $key => $value): ?>
                                                <li><?=$value?></li>
                                            <? endforeach; ?>
                                        </ul>
                                        <? }?>
										
										<? if($data['visa_other']) echo "other : " .$data['visa_other']; ?>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="teaching_subject">Teaching Subject</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <?php 
                                        $list = explode(',', $data[teaching_subject]);

                                        if(count($list)){ ?>
                                        <ul class="marker">
                                            <?php foreach($list as $key => $value): ?>
                                                <li><?=$value?></li>
                                            <? endforeach; ?>
                                        </ul>
                                        <? }?>
                                    </div>
                                </div>
                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="teaching_subject">Resume</label>
                                    </h3>
                                    <div class="no-admin-content file">
                                        <? if($data[filename]){ ?>
                                             <ul class="list">
                                                <li>
                                                    <a href="/inc/lib/employment.file.download.php?no=<?=$data[no]?>" class="no-btn no-btn--main">이력서 다운로드</a>
                                                </li>
                                             </ul>
                                        <? }?>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?=$data[no]?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./employment.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
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
</body>
</html>