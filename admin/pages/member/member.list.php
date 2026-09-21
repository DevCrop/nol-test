<!DOCTYPE html>
<html lang="ko">
<head>
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 8;
	$pagenum = 1;
	
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

	if($lev){
		$mainqry .=  " and a.lev = $lev ";
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

	$query = " select count(*)  as cnt from nb_member a  $mainqry ";
	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if($data){
		$totalCnt = $data[cnt];
	}


	/* 출력되는 페이지수 */
	$Page = ceil($totalCnt / $listRowCnt);

	$query = " select
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

            <?
                $query = "select no, lev_name, is_join  from nb_member_level where sitekey = '$NO_SITE_UNIQUE_KEY' order by no asc";
                $result_2nd =mysql_query($query);
                $arrList = array(); 
                while($row = mysql_fetch_assoc($result_2nd)) { 
                    $arrList[] = $row; 
                } 
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
                                    <span>회원 관리</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>회원 목록</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->

                            <div class="no-items-center">
                                <a href="./member.add.php" class="no-btn no-btn--main no-btn--big">
                                    회원등록
                                </a>
                            </div>
                        </div>
                    </div>

                    <?
                        include_once "../../inc/admin.lnb.member.php";
                    ?>

                    
                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">회원 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">회원등급</h3>
                                    <div class="no-admin-content">
                                        <select name="lev" id="lev" class="no_mb10 no_mt10">
                                            <option value="">등급선택</option>
											<?
												foreach($arrList as $k=>$v){
											?>
												<option value="<?=$v[no]?>" <? if($lev == $v[no]) echo "selected";?>><?=$v[lev_name]?></option>
											<?
												}
											?>
                                        </select>

                                        <small>등록될 회원의 등급을 선택해주세요. 비회원인 경우 선택하지 않으면 됩니다.</small>
                                    </div>
                                </div>
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
											<option value="a.uid" <? if($searchColumn == "a.uid") echo "selected";?>>아이디</option>
											<option value="a.uname" <? if($searchColumn == "a.uname") echo "selected";?>>이름</option>
											<option value="a.hp" <? if($searchColumn == "a.hp") echo "selected";?>>휴대폰</option>
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
                                <h2 class="no-card-title">회원 관리</h2>
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
                                                    캠퍼스
                                                </th>
												<th scope="col" class="no-min-width-120">
                                                    가입구분
                                                </th>
												<th scope="col" class="no-min-width-120">
                                                    학년
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    아이디
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    이름
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    휴대폰
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    등급
                                                </th>
                                                <th scope="col" class="no-min-width-100">
                                                    관리일
                                                </th>
                                                <th scope="col" class="no-min-width-role no-td-center">
                                                    관리
                                                </th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?
                                                while($v = mysql_fetch_array($result)){
                                            ?>
                                            <tr>
                                                <td>
                                                    <span> <?=$rnumber?> </span>
                                                </td>
                                                <td>
                                                    <a href="./member.view.php?no=<?=$v[no]?>">
                                                    <?=$v['campus_name']?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="./member.view.php?no=<?=$v[no]?>">
                                                        <?=$v['gubun']?>
                                                    </a>
                                                </td>
												<td>
                                                   <a href="./member.view.php?no=<?=$v[no]?>">
                                                        <?=$v['grade']?>
                                                    </a>
                                                </td>
												<td>
                                                    <?=$v[uid]?>
                                                </td>
												<td>
                                                    <?=$v[uname]?>
                                                </td>
												<td>
                                                    <?=$v[hp]?>
                                                </td>
                                                <td>
                                                    <select name='ch_lev' id='ch_lev' class="form-control" style='width:90%' onChange="doLevChange(this.value, <?=$v[no]?>)">
                                                        <option value="">등급없음</option>
                                                        <?
                                                            foreach($arrList as $k=>$vv){
                                                        ?>
                                                            <option value="<?=$vv[no]?>" <? if($v[lev] == $vv[no])echo "selected"?>><?=$vv[lev_name]?></option>
                                                        <?
                                                            }
                                                        ?>
                                                    </select>
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
                                                                href="./member.view.php?no=<?=$v[no]?>"
                                                                class="no-btn no-btn--sm no-btn--normal"
                                                                onClick="doRegSave();"
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
                                    <p>등록된 회원이 없습니다.</p>
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
        <script type="text/javascript" src="./js/member.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>
</html>