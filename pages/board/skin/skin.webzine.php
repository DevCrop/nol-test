

<section>
    <div class="no-full-wrapper">
        <div class="no-blog">

		
            <div class="no-blog__left">
                <div class="no-blog__search">
                    <input type="text" placeholder="Search eMoldino Content" id="searchKeyword" name="searchKeyword" value="<?=$searchKeyword?>">
                    <span class="no-blog__search__close">
                        <button type="button" id="no-search-reset">
                            <svg width="8" height="9" viewBox="0 0 8 9" xmlns="http://www.w3.org/2000/svg"><path d="M8 1.057L7.293.35 4 3.643.707.35 0 1.057 3.293 4.35 0 7.643l.707.707L4 5.057 7.293 8.35 8 7.643 4.707 4.35 8 1.057z" fill="currentcolor"></path></svg>
                        </button>
                    </span>
                    <span class="no-blog__search__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="5 5 14 14"><path fill="currentColor" d="M15.683 14.6l3.265 3.265a.2.2 0 010 .282l-.8.801a.2.2 0 01-.283 0l-3.266-3.265a5.961 5.961 0 111.084-1.084zm-4.727 1.233a4.877 4.877 0 100-9.754 4.877 4.877 0 000 9.754z"></path></svg>
                    </span>
                </div>
                
                <!-- CATE LIST -->
                <div class="no-blog__category">
                    <ul class="no-blog__category__list">
                        <li>
                            <a href="javascript:void(0)" title="전체" onClick="doCategoryClick('');" <? if($category_no =="") echo "class='active''";?>>All Posts (<?=$totalCnt?>)</a>
                        </li>
                        <? if(count($boardCategory) > 0){?>
                            <?
                                foreach($boardCategory as $k=>$v){
                            ?>
                            <li>
                                <a href="javascript:void(0)" title="<?=$v['no']?>" onClick="doCategoryClick(<?=$v['no']?>);" <? if($category_no == $v['no']) echo "class='active'";?>><?=$v['name']?> (0)</a>
                            </li>
                            <?
                                }
                            ?>
                        <? } ?>
                    </ul>
                </div>
            </div>
            <!-- block left -->
			

            <div class="no-blog__right">
                <ul class="no-blog__list">
                    <?
                        foreach($arrResultSet as $k=>$v){

                        $title = iconv_substr($v[title], 0, 500, "utf-8");
                        $contents = strip_tags($v[contents]);
                        $contents = iconv_substr($contents, 0, 500, "utf-8");
                        $link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page"."&lang=$lang_html";

                        $imgSrc = "";
                        if($v[thumb_image])
                            $imgSrc = $UPLOAD_WDIR_BOARD."/".$v[thumb_image];
                        else{
                            $imgSrc = getImageTag($v[contents], "src");
                            $imgSrc = $imgSrc[0];
                        } 
                        
                    ?>
                    <li class="no-blog__item">
                        <article class="no-blog__content">
                            <div class="no-blog__content__date">
                                <span>Aug 9</span>
                                <span>5 min</span>
                            </div>
                            <div class="no-blog__content__catg med">
                                <span>Med Tech</span>
                            </div>
                            <h3 class="no-blog__content__title">
                                <a href="<?=$link?>">
                                    <?=$title?>
                                </a>
                            </h3>
                            <p class="no-blog__content__desc">
                                <a href="<?=$link?>">
                                    <?=\Security\HtmlSanitizer::clean((string) $contents)?>
                                </a>
                            </p>

                            <!-- <div class="no-share">
                                <button type="button" class="no-share__btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19" viewBox="0 0 19 19" role="img" class="blog-post-homepage-description-fill blog-post-homepage-link-hashtag-hover-fill"><path d="M2.44398805,5.99973295 C1.62345525,5.9690612 0.980075653,5.28418875 1.00047182,4.46312144 C1.02086799,3.64205413 1.69745853,2.98998831 2.51850166,3.0001164 C3.33954478,3.01024449 3.99985313,3.67880182 4,4.50012255 C3.98424812,5.34399206 3.28763905,6.0153508 2.44398805,5.99973295 L2.44398805,5.99973295 Z M2.44398805,10.9997329 C1.62345525,10.9690612 0.980075653,10.2841888 1.00047182,9.46312144 C1.02086799,8.64205413 1.69745853,7.98998831 2.51850166,8.0001164 C3.33954478,8.01024449 3.99985313,8.67880182 4,9.50012255 C3.98424812,10.3439921 3.28763905,11.0153508 2.44398805,10.9997329 L2.44398805,10.9997329 Z M2.44398805,15.9997329 C1.62345525,15.9690612 0.980075653,15.2841888 1.00047182,14.4631214 C1.02086799,13.6420541 1.69745853,12.9899883 2.51850166,13.0001164 C3.33954478,13.0102445 3.99985313,13.6788018 4,14.5001225 C3.98424812,15.3439921 3.28763905,16.0153508 2.44398805,15.9997329 L2.44398805,15.9997329 Z"></path></svg>
                                </button>
                                <button type="button" class="no-share__content">
                                    <div>
                                        <span>
                                            <i class='bx bx-share bx-flip-horizontal' ></i>
                                        </span>
                                        <span>
                                            Share Post
                                        </span>
                                    </div>
                                </button>
                            </div> -->
                        </article>
                        
                        <div class="no-blog__image">
                            <a href="<?=$link?>">
                                <img src="<?=$imgSrc?>" alt="<?=$title?>">
                            </a>
                        </div>
                    </li>
                    <!-- post -->
                    <?
                        $rnumber--;
                        }
                    ?>
                </ul>
                
                
                <? 
                    include_once "./pagination.php";
                ?>
                
            </div>
            <!-- block right -->
        </div>
    </div>
</section>

