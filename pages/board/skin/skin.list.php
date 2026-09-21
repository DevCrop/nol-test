<?php
	if ($board_no == 78) {
?>
<main>
    <section class="sub-ir">
        <div class="no-container">
            <div class="no-inner">
                <div class="opt-top">
                    <h2 class="sub-section-title">
                        공지사항
                    </h2>

                    <div class="search-box">
                        <input type="search" name="searchKeyword" placeholder="검색어를 입력해주세요.">
                        <button type="button" aria-label="search" onclick="doSearch();">
                            <i class="fa-sharp fa-solid fa-magnifying-glass" style="color: #fff;" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <article class="no-article">
                    <table class="ir-list">
                        <thead>
                            <tr>
                                <th class="num">번호</th>
                                <th class="title">제목</th>
                                <th class="date">등록일</th>
                                <th class="look">조회수</th>
                            </tr>
                        </thead>

                        <tbody>
						<?

						foreach($arrResultSet as $k=>$v){ 
							$title = $v[title];
							if($v[is_secret] == "Y"){
								if($_SESSION['board_secret_confirmed_'.$v[no]] == "Y"){
									$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&category_no=$category_no";
								}else{
									$link = "./board.confirm.php?mode=view&board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&returnUrl=/pages/sub4/sub_inquiry_view.php";
								}
							}else{
								$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&category_no=$category_no";
							}

							$numZone = "";
							$numZoneName = "";

							if ($v[is_notice] == "Y") {
								$numZone = "<span class='notice'>공지</span>";
								$numZoneName = "";
							} else {
								$numZoneName = $rnumber;
							}
				?>
                            <tr onclick="location.href='<?=$link?>'">
                                <td class="num"><?=$numZoneName?><?=$numZone?></td>
                                <td class="title">
                                    <p><?=$title?></p>
                                </td>
                                <td class="date"><?=getChangeDate($v[regdate], "Y.m.d")?></td>
                                <td class="look"><?=$v['extra1'] + $v[read_cnt]?></td>
                            </tr>
							<?
							$rnumber--;
							}
						?>
                        </tbody>
                    </table>
                    <?include_once $STATIC_ROOT."/pages/board/components/pagination.php";?>
                </article>
            </div>
        </div>
    </section>
</main>

<?php
	}
?>

<?php
	if ($board_no == 79) {
?>
<main>
    <section class="sub-ir">
        <div class="no-container">
            <div class="no-inner">
                <div class="opt-top">
                    <h2 class="sub-section-title">
                        회원권 뉴스
                    </h2>

                    <div class="search-box">
                        <input type="search" name="searchKeyword" placeholder="검색어를 입력해주세요.">
                        <button type="button" aria-label="search" onclick="doSearch();">
                            <i class="fa-sharp fa-solid fa-magnifying-glass" style="color: #fff;" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <article class="no-article">
                    <table class="ir-list">
                        <thead>
                            <tr>
                                <th class="num">번호</th>
                                <th class="title">제목</th>
                                <th class="date">등록일</th>
                                <th class="look">조회수</th>
                            </tr>
                        </thead>

                        <tbody>
						<?

						foreach($arrResultSet as $k=>$v){ 
							$title = $v[title];
							if($v[is_secret] == "Y"){
								if($_SESSION['board_secret_confirmed_'.$v[no]] == "Y"){
									$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&category_no=$category_no";
								}else{
									$link = "./board.confirm.php?mode=view&board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&returnUrl=/pages/sub4/sub_inquiry_view.php";
								}
							}else{
								$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&category_no=$category_no";
							}

							$numZone = "";
							$numZoneName = "";

							if ($v[is_notice] == "Y") {
								$numZone = "<span class='notice'>공지</span>";
								$numZoneName = "";
							} else {
								$numZoneName = $rnumber;
							}
				?>
                            <tr onclick="location.href='<?=$link?>'">
                                <td class="num"><?=$numZoneName?><?=$numZone?></td>
                                <td class="title">
                                    <p><?=$title?></p>
                                </td>
                                <td class="date"><?=getChangeDate($v[regdate], "Y.m.d")?></td>
                                <td class="look"><?=$v['extra1'] + $v[read_cnt]?></td>
                            </tr>
							<?
							$rnumber--;
							}
						?>
                        </tbody>
                    </table>
                    <?include_once $STATIC_ROOT."/pages/board/components/pagination.php";?>
                </article>
            </div>
        </div>
    </section>
</main>

<?php
	}
?>