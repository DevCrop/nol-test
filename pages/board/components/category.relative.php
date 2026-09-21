
	<div class="no-tab  no-relative" <?=$aos_middle?>>

		<?php $relative_pages = $CUR_PAGE_LIST[1]['pages']; ?>

		<ul class="no-tab__wrap">
			<?php foreach ($relative_pages as $pi => $rel_page): 
				$rel_page_active = $rel_page['isActive'] ? 'active' : '';    
			?>
					<li class="no-tab__item">
				 <a href="<?=$rel_page['path']?>" class="<?=$rel_page_active?>"><?=$rel_page['title']?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

<!--	<section class="no-business mb80">
		<div class="no-tab ">
			<ul class="no-tab__wrap">
				<?php if(count($boardCategory) > 0) : ?>
					<?php
						foreach($boardCategory as $k=>$v) : 
						$categoryActive = $category_no == $v['no'] ? "active" : null;	
					?>
						<li class="no-tab__item">
						  <a href="javascript:void(0);" onClick="doCategoryClick(<?=$v['no']?>);" class="<?=$categoryActive?>"><?=$v['name']?></a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
	</section>-->




<?
	if($board_no == 88 || $board_no == 89) {
?>
	 <!-- tab -->
	  <div class="no-tab mb50" <?=$aos_slow?>>
		<ul class="no-tab__wrap">
		  <li class="no-tab__item no-tab__item--rel">
			<button class="active">회사개요</button>
		  </li>
		  <li class="no-tab__item no-tab__item--rel">
			<button>주요 사업 실적 현황</button>
		  </li>
		</ul>
	  </div>
<?
	}	
?>
	 
