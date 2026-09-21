	<!---category-->
	<div class="no-sub-category">
		<div class="no-sub-category-slider">
			<ul class="swiper-wrapper">
				  <li class="swiper-slide">
					<a href="javascript:void(0)" 
					   title="전체" 
					   onClick="location.href='./board.list.php?board_no=<?= $board_no ?? '' ?>';" 
					   class="no-btn__fill no-btn <?= empty($category_no) ? 'active' : '' ?>">
						전체
					</a>
				</li>
				<?php foreach ($boardCategory as $k => $v) : ?>
					<?php $categoryActive = ($category_no == $v['no']) ? "active" : ""; ?>
					<li class="swiper-slide">
						<a href="javascript:void(0);" 
						   onClick="doCategoryClick(<?= $v['no'] ?>);" 
						   class="<?= $categoryActive ?> no-btn no-btn__fill">
						   <?= htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8') ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>