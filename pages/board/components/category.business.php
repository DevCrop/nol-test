<div class="no-components" <?=$aos_middle?>>
	<section class="no-business">
		<div class="no-tab ">
			<ul class="no-tab__wrap">
				<li class="no-tab__item no-tab__item--business">
					<a href="javascript:void(0)" title="전체" onClick="location.href='./board.list.php?board_no=<?=$board_no?>';" <? if($category_no =="") echo "class='active''";?>>전체</a>
				</li>
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
	</section>

	<div class="no-search__box">
		 <input type="search" placeholder="Search"  name="searchKeyword" id="searchKeyword" title="search" value="<?=$searchKeyword?>">
		  <button type="button" aria-label="search" onClick="doSearch();">
			<i class="bx bx-search"></i>
		  </button>
	</div>
</div>

