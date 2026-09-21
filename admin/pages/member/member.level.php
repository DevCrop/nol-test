<!DOCTYPE html>
<html lang="ko">
<head>
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 8;
	$pagenum = 2;
	
	$searchKeyword		= $_REQUEST['searchKeyword'];
	$searchColumn		= $_REQUEST['searchColumn'];
	$sdate					= $_REQUEST['sdate'];
	$edate					= $_REQUEST['edate'];	
	$lev						= $_REQUEST['lev'];

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
	$total			= $_POST['total']; //전체 레코드 수
	
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

	$query = " select count(*)  as cnt from nb_member_level a  $mainqry ";
	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if($data){
		$totalCnt = $data[cnt];
	}


	/* 출력되는 페이지수 */
	$Page = ceil($totalCnt / $listRowCnt);

	$query = " select
						a.no
						,a.lev_name
						,a.is_join
						from nb_member_level a
						$mainqry  order by  a.no desc

		limit $count , $listRowCnt 
	";

	//echo $query;

	$result = mysql_query($query);

	$rnumber=$totalCnt-($listCurPage-1)*$listRowCnt;
	


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
            <form  id="frm" name="frm" method="post" autocomplete='off'>	
			<input type="hidden" id="mode" name="mode" value="">
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
                                    <span>회원등급 목록</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->

                            <div class="no-items-center">
                                <a href="./member.level.add.php" class="no-btn no-btn--main no-btn--big">
                                    등록
                                </a>
                            </div>
                        </div>
                    </div>

                    <?
                        include_once "../../inc/admin.lnb.member.php";
                    ?>


                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">회원등급 관리</h2>
                            </div>

                            <div class="no-card-body">
                                
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
                                                    등급명
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    회원가입시 적용
                                                </th>
                                                <th scope="col" class="no-min-width-role no-td-center">
                                                    관리
                                                </th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?
                                                while($v = mysql_fetch_array($result)){

                                                    $isJoin = "";
                                                    if($v[is_join] == "Y")
                                                        $isJoin = "적용";
                                            ?>
                                            <tr>
                                                <td>
                                                    <span> <?=$rnumber?> </span>
                                                </td>
                                                <td>
                                                    <a href="./member.level.view.php?no=<?=$v[no]?>">
                                                    <?=$v[lev_name]?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span><?=$v[isJoin]?></span>
                                                </td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </span>
                                                        <div class="no-table-action">
                                                            
                                                            <a
                                                                href="./member.level.view.php?no=<?=$v[no]?>"
                                                                class="no-btn no-btn--sm no-btn--normal"
                                                            >
                                                                수정
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
                                    <p>등록된 등급이 없습니다.</p>
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
        <script type="text/javascript" src="./js/member.level.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>
</html>