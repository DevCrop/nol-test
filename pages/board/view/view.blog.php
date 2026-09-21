<div class="no-view-container no-full-wrapper">
    <div class="no-view">
        <div class="no-view__info">
            <div class="no-blog__content__date">
                <span>Aug 9</span>
                <span>5 min</span>
            </div>

            <h2 class="no-view__title">
                <?=$data[title]?>
            </h2>

            <span class="no-view__updated">
                Updated: <?=getChangeDate($data[regdate], "Y-m-d")?>
            </span>
        </div>

        <div class="no-view__content">
            <?=\Security\HtmlSanitizer::clean((string) $data['contents'])?>
        </div>

    </div>

    <div class="no-view-post">
        <strong>Recent Posts</strong>
        <div class="swiper no-post-swiper">
            <? 
                $board_info = getBoardInfoByName("Blog ($lang)");
                $board_no = $board_info[0]['no'];
                $arrBoardNotice = getBoardLimit($board_no,6,"");
            ?>
            <ul class="swiper-wrapper">
                <?
                    foreach($arrBoardNotice as $k=>$v){
                        $title = iconv_substr($v[title], 0, 27, "utf-8");
                        $link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page"."&lang=$lang_html";
                        $imgSrc = "";
                        if($v[thumb_image])
                            $imgSrc = $UPLOAD_WDIR_BOARD."/".$v[thumb_image];
                        else{
                            $imgSrc = getImageTag($v[contents], "src");
                            $imgSrc = $imgSrc[0];
                        } 
                ?>
                <li class="swiper-slide">
                    <div class="no-view-post__block">
                        <div class="no-view-post__image">
                            <a href="<?=$link?>">
                                <img src="<?=$imgSrc?>" alt="<?=$title?>">
                            </a>
                        </div>
                        <div class="no-view-post__content">
                            <h3>
                                <a href="<?=$link?>">
                                    <?=$title?>
                                </a>
                            </h3>
                        </div>
                    </div>
                </li>
                <?
                    }
                ?>
                <!-- post -->
            </ul>
        </div>
        <div class="no-view-post__all">
            <a href="<?=$NO_IS_SUBDIR?><?=$pageLink_3_2?>">See All</a>
        </div>
    </div>
</div>
