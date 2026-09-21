<!-- BEGIN :: W-LIST -->
<div class="no_w_list" data-aos="fade-up" data-aos-duration="1000">
	<? if($role_info[0]['role_write'] == "Y") { ?>
	<a href="./board.write.php?board_no=<?=$board_no?>&searchKeyword=<?=base64_encode($searchKeyword)?>&searchColumn=<?=base64_encode($searchColumn)?>&page=<?=$page?>"  title="글쓰기" class="no_sub_write_btn">
		<strong class="icon-pencil">
		</strong>
		글쓰기
	</a>
	<? } ?>

	<div class="no_w_list_box">
		<ul class="no_w_list_con">
			<li class="no_w_list_header">
				<span class="no_w_num no_w_empha">번호</span>
				<span class="no_w_tit no_w_tit_align no_w_empha">제목</span>
				<span class="no_w_writer no_w_empha">작성자</span>
				<span class="no_w_date no_w_empha">등록일</span>
				<span class="no_w_hits no_w_empha">답변 여부</span>
			</li>
			<?
				foreach($arrResultSet as $k=>$v){

				$title = $v[title];

				if($v[is_secret] == "Y"){
					if($_SESSION['board_secret_confirmed_'.$v[no]] == "Y"){
						$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page";
					}else{
						$link = "./board.confirm.php?mode=view&board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&returnUrl=/pages/sub4/sub_inquiry_view.php";
					}
				}else{
					$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page";
				}

				$numZone = "";
				if($v[is_notice] == "Y"){
					$numZone = "<div class=\"no_w_annon\"><img src=\"/resource/images/icon/notice_icon.png\" alt=\"공지 아이콘\" width=\"40\" height=\"40\"/></div>";
				}else{
					$numZone = $rnumber;
				}
				
				$new = "";
				if(time() - strtotime($v[regdate])< (60*60*24*2)) {	
					$new = "<img src=\"/resource/images/icon/no_new_icon.png\"/>";
				}	
			
				$commentCnt = "";
				if($v[comment_cnt])
					$commentCnt = "<span class=\"no_w_list_sign\">(".$v[comment_cnt].")</span>";

				$isAdminComment = "답변대기";
				$isAdminCommentActive = "";
				if($v[is_admin_comment_yn] == "Y"){
					$isAdminComment = "답변완료";
					$isAdminCommentActive = "no_hits_answer";
				}

			?>
			<li>
				<span class="no_w_num">
					<?=$numZone?>
				</span>
				<span class="no_w_width">

					<!-- LOOP -->
					<a href="<?=$link?>"><?=$title?></a>
					<?=$commentCnt?>
					<?=$new?>
				
				</span>
				<span class="no_w_writer"><?=$v[write_name]?></span>
				<span class="no_w_date"><?=getChangeDate($v[regdate], "Y.m.d")?></span>
				<span class="no_w_hits"><strong class="<?=$isAdminCommentActive?>"><?=$isAdminComment?></strong></span>
			</li>
			<?
				$rnumber--;
			}
		?>
	</ul>
	</div>
</div>
<!-- END :: W-LIST -->
