
<section class="no-section-lg">
	<div class="no-container-xl">
		<h3 class="f-heading-2">허가사례</h3>

		<div class="no-content-block">
			<ul class="no-category-nav">
				<?php $isAllActive = $category_no == null ? 'active' : ''; ?>
				<li class="<?=$isAllActive?>"><a href="./board.list.php?board_no=<?=$board_no?>">전체</a></li>

				<?php foreach($boardCategory as $k => $v) : 
					$isActive = $category_no == $v['no'] ? 'active' : ''; 
				?>
				<li class="<?=$isActive?>">
					<a href="./board.list.php?page=<?=$page?>&board_no=<?=$board_no?>&category_no=<?=$v['no']?>">
					<?=$v['name']?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="no-content-base no-main-exp">
			<ul class="no-main-exp-list no-case-list">
				<?
					foreach($arrResultSet as $k=>$v){
					$title = iconv_substr($v[title], 0, 2000, "utf-8");
					$contents = strip_tags($v[contents]);
					$contents = iconv_substr($contents, 0, 500, "utf-8");
					$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page&category_no=$category_no";

					$imgSrc = "";
					if($v[thumb_image])
						$imgSrc = $UPLOAD_WDIR_BOARD."/".$v[thumb_image];
					else{
						$imgSrc = getImageTag($v[contents], "src");
						$imgSrc = $imgSrc[0];
					} 

					$target = $v['direct_url'] ? '_blank' : '_self'; 
					$link = $v['direct_url'] ? $v['direct_url'] : $link;

					$link_image = $imgSrc;

					$imgData = 'data-pswp-width="1600" data-pswp-height="1024" class="my-image"';
					
					if($v['direct_url']){
						$link_image = $v['direct_url'];
						$imgData = '';
					} 
				?>
					<li class="no-main-exp-item">
						<a href="<?=$link?>" target="<?=$target?>">
							<figure>
								<img src="<?= $imgSrc?>" alt="<?=$title?>">
							</figure>
							<div class="no-main-exp-item__content">
								<h3 class="f-heading-6"><?=$v['title']?></h3>
								<div class="no-main-exp-item__bedge">
									<dl>
										<dt>국적</dt>
										<dd><?=$v['extra1']?></dd>
									</dl>
									<dl>
										<dt>비자</dt>
										<dd><?=$v['extra2']?></dd>
									</dl>
								</div>
							</div>
						</a>
					</li>
				<?
					$rnumber--;
					}
				?>
			</ul>

			<?php include_once $STATIC_ROOT . '/inc/components/pagination.php'; ?>
		</div>
	</div>
</section>


		 