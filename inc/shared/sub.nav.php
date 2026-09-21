<section class="no-sub-nav">
    <div class="no-container-xl">
        <div class="no-sub-nav-slider swiper">
            <div class="swiper-wrapper">
                <?php foreach ($CUR_PAGE_ITEMS as $pi => $cur_page): 

					$cur_page_active = $cur_page['isActive'] ? 'active' : '';    
				?>
                <div class="swiper-slide">
                    <a href="<?=$cur_page['path']?>" class="<?=$cur_page_active?>"><?=$cur_page['title']?></a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="no-sub-nav-slider-line"></div>
            <div class="no-sub-nav__btn">
                <button type="button" class="swiper-button-prev sub-nav-btn sub-nav-prev"></button>
                <button type="button" class="swiper-button-next sub-nav-btn sub-nav-next"></button>
            </div>
        </div>
    </div>
</section>