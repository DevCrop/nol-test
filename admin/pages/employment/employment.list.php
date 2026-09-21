<!DOCTYPE html>
<html lang="ko">
<?

	include_once "../../../inc/lib/base.class.php";

	$depthnum = 7;
	$pagenum = 1;

	$searchKeyword		= $_REQUEST['searchKeyword'];
	$searchColumn		= $_REQUEST['searchColumn'];
	$sdate						= $_REQUEST['sdate'];
	$edate						= $_REQUEST['edate'];

	$mainqry = " where a.sitekey = '$NO_SITE_UNIQUE_KEY' ";


	if($searchColumn && $searchKeyword) {
		$mainqry .=  " and  ( replace($searchColumn,' ','') like '%".trim($searchKeyword)."%' ) ";
	}

	if($sdate && $edate) {
		$mainqry .=  " and (DATE_FORMAT(a.regdate, '%Y-%m-%d') between '$sdate' and '$edate')";
	}

	$page		= $_POST['page']; //페이지 번호
	$pages		= $_POST['pages']; //총 페이지 수
	$perpage	= $_POST['perpage']; //한 페이지에 출력될 rows
	$total		= $_POST['total']; //전체 레코드 수

	/* 페이지에 출력된 페이지 블럭 1, 2, 3, 4, 등 */
	$pageBlock	= 10;

	if(!$page)
		$page = 1;

	if(!$perpage){ $perpage = 20; }

	/* 출력될 rows */
	$listRowCnt = $perpage;

	/* 현재 페이지 */
	$listCurPage = $page;


	if( !$listCurPage ) {$listCurPage = 1 ;}
	$count = $listCurPage * $listRowCnt - $listRowCnt;

	$query = " select count(*)  as cnt from nb_employment a  $mainqry ";
	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if($data){
		$totalCnt = $data[cnt];
	}


	/* 출력되는 페이지수 */
	$Page = ceil($totalCnt / $listRowCnt);

	$query = " select
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
				b.name as campus_name

                from nb_employment a
				left join nb_campus b on a.campus = b.no
                $mainqry order by  a.regdate desc
		        limit $count , $listRowCnt
	";

	// echo $query;

	$result = mysql_query($query);

	$rnumber=$totalCnt-($listCurPage-1)*$listRowCnt;

	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
<style>
    .no-table-responsive > p{
        padding-top: 4rem;
        text-align: center;
    }
</style>
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
            <form method="POST" name="frm" id="frm" autocomplete="off">
            <input type="hidden" name="mode" id="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">Employment 관리</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>Employment</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>Employment 관리</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>


                    <?
                        $query = "select no, title, skin, sort_no  from nb_board_manage where sitekey = '$NO_SITE_UNIQUE_KEY' order by no asc";
                        $result_2nd =mysql_query($query);
                        $arrBoardList = array();
                        while($row = mysql_fetch_assoc($result_2nd)) {
                            $arrBoardList[] = $row;
                        }
                    ?>
                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">Employment 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">검색 일자</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="sdate" id="sdate" value="<?=$sdate?>"/>
                                        <span></span>
                                        <input type="text" name="edate" id="edate" value="<?=$edate?>"/>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>

                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="a.title" <? if($searchColumn == "a.title") echo "selected";?>>게시물 제목</option>
                                            <option value="a.contents" <? if($searchColumn == "a.contents") echo "selected";?>>게시물 내용</option>
                                        </select>
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input
                                                    name="searchKeyword"
                                                    id="searchKeyword"
                                                    type="text"
                                                    title="검색어 입력"
                                                    placeholder="검색어를 입력해주세요."
                                                    value="<?=$searchKeyword?>"
                                                />
                                            </div>
                                            <div class="no-search-btn">
                                                <button
                                                    type="button"
                                                    title="검색"
                                                    class="no-btn no-btn--main no-btn--search"
                                                    onClick="doSearchList();"
                                                >
                                                검색
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">Employment 관리</h2>
                            </div>


                            <div class="no-card-body">
                                <div class="no-table-option flex-end">
                                    <div class="no-perpage">
                                        <select name="perpage" id="perpage" onChange="document.frm.submit();">
                                            <option value="20" <? if($perpage == "20") echo "selected";?>>20개씩</option>
                                            <option value="50" <? if($perpage == "50") echo "selected";?>>50개씩</option>
                                            <option value="100" <? if($perpage == "100") echo "selected";?>>100개씩</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수,
                                            관리로 구성된 게시글 관리표
                                        </caption>

                                        <thead>
                                            <tr>
                                                <th scope="col" class="no-width-120 no-min-width-60">
                                                    번호
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    이름
                                                </th>
                                                <th scope="col" class="no-min-width-150">
                                                    캠퍼스
                                                </th>
                                                <th scope="col" class="no-min-width-150">
                                                    연락처
                                                </th>
                                                <th scope="col" class="no-min-width-100">
                                                    등록일
                                                </th>
                                                <th scope="col" class="no-min-width-role no-td-center">
                                                    관리
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?
                                                while($v = mysql_fetch_array($result)){

                                                $isNotice = "";
                                                if($v[is_notice] == "Y") $isNotice = "<span class=\"no-btn no-btn--notice\"> 공지 </span>";
                                            ?>
                                            <tr>
                                                <td>
                                                    <span> <?=$rnumber?> </span>
                                                </td>
                                                <td>
                                                    <a href="./employment.view.php?no=<?=$v[no]?>">
                                                        <?=$v[firstname] . " " . $v[lastname]?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="./employment.view.php?no=<?=$v[no]?>">
                                                        <?=$v['campus_name']?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="./employment.view.php?no=<?=$v[no]?>">
                                                        <?=$v[contact_number]?>
                                                    </a>
                                                </td>
                                                <td>
                                                   <span><?=$v[regdate]?></span>
                                                </td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </span>
                                                        <div class="no-table-action">

                                                            <a
                                                                href="./employment.view.php?no=<?=$v[no]?>"
                                                                class="no-btn no-btn--sm no-btn--normal"
                                                                onClick="doRegSave();"
                                                            >
                                                                보기
                                                            </a>

                                                            <a
                                                            href="javascript:doDelete(<?=$v[no]?>);"
                                                            class="no-btn no-btn--sm no-btn--delete-outline"
                                                            >삭제</a
                                                            >

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?
                                                    $rnumber--;
                                                }
                                            ?>

                                        </tbody>
                                    </table>

                                    <? if(!$result){?>
                                    <!-- 글이 하나라도 등록되지 않았을 때 보여줄 div -->
                                    <p>등록된 내용이 없습니다.</p>
                                    <?}?>
                                </div>
                            </div>
                            <!-- card body -->
                        </div>
                        <!-- card -->
                    </div>
                    <!-- contents -->

                    <!-- Pagination -->
                    <? include_once "../../lib/admin.pagination.php"; ?>
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