<?php
	$CUR_PAGE_ITEMS		= $CUR_PAGE_LIST[0]['pages'];


	$cur_gnb = $CUR_PAGE_LIST[0]['title'];
	$cur_lnb = $CUR_PAGE_LIST[1]['title'];
	$cur_lnb_sub = $CUR_PAGE_LIST[1]['en-title'];
?>

<section class="no-sub-visual ">
    <div class="no-container-xl">
        <!--BreadCrumb---->
        <div class="no-sub-breadcrumb">
            <nav aria-label="Breadcrumb">
                <ul>
                    <li>
                        <i class="fa-solid fa-house"></i>
                    </li>
                    <li>
                        <i class="fa-light fa-chevron-right"></i>
                    </li>
                    <li>
                        <?=$cur_gnb?>
                    </li>
                    <li>
                        <i class="fa-light fa-chevron-right"></i>
                    </li>
                    <li>
                        <?=$cur_lnb?>
                    </li>
                </ul>
            </nav>
        </div>

    </div>
    <div class="no-sub-visual-txt">
        <div class="no-sub-visual-txt-h2">
            <h2 class="no-heading-xl ">
                <?=$cur_lnb?>
            </h2>
        </div>
        <div class="no-sub-visual-txt-p">
            <p class="no-display-xl"><?=$cur_lnb_sub?></p>
        </div>
    </div>
</section>