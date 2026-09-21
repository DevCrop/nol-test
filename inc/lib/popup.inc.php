<?php

// 쿼리 실행 및 결과 가져오기
$query = "SELECT * FROM nb_popup 
          WHERE sitekey = '$NO_SITE_UNIQUE_KEY' 
          AND p_loc = 'P' 
          AND p_view = 'Y' 
          AND ((p_sdate <= CURDATE() AND p_edate >= CURDATE()) OR p_none_limit = 'Y') 
          ORDER BY p_idx ASC, no DESC";

$result4th = mysql_query($query);
$arrPopup = array(); 
$array_nums = array();

while ($row = mysql_fetch_assoc($result4th)) { 
    $arrPopup[] = $row; 

	if ($_COOKIE["AuthPopupClose_" . $row['no']] !== "Y") {
		$array_nums[] = $row['no'];
	}
}

$popup_nums = implode(',', $array_nums);

?>

<?php if (!empty($popup_nums)): ?>
<div class="no-popup">
<div class="no-popup-overlay"></div>

    <!-- POPUP BEGIN -->
    <div class="no-popup__inner" data-nums="<?php echo $popup_nums; ?>">
        <div class="popup-swiper swiper">
            <ul class="swiper-wrapper">
                <?php foreach ($arrPopup as $k => $v): ?>
                <?php if ($_COOKIE["AuthPopupClose_" . $v['no']] !== "Y"):
                    $_img = $UPLOAD_WDIR_POPUP . "/" . $v['p_img'];
                    $close_url = './inc/lib/popup.close.php?_mode=popup_close&uid=' . $popup_nums;
                ?>
                <li class="swiper-slide">
                    <div class="no-popup__image">
                        <a href="<?php echo $v['p_link']; ?>" class="no-popup__zone" target="<?php echo $v['p_target']; ?>">
                            <picture>
                                <source media="(min-width:0px)" srcset="<?php echo $_img; ?>">
                                <img src="<?php echo $_img; ?>" alt="Flowers">
                            </picture>
                        </a>
                    </div>
                    <div class="no-popup__content">
                        <?php if ($v['p_title']): ?>
                        <h4 class="no-popup__title"><?php echo $v['p_title']; ?></h4>
                        <?php endif; ?>
                        <?php if ($v['p_link']): ?>
                        <div class="no-popup__pos">
                            <a href="<?php echo $v['p_link']; ?>" target="<?php echo $v['p_target']; ?>">바로가기</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="popup-pager swiper-pagination"></div>
        </div>

        <!-- Control -->
        <div class="no-popup-ctrl">
            <div class="no-popup-ctrl__off">
                <a href="javascript:void(0)">
                    <label for="agree_check_<?php echo $v['no']; ?>">
                        <input type="checkbox" id="agree_check_<?php echo $v['no']; ?>" onclick="close_popup('<?php echo $popup_nums; ?>');">
                        <span>오늘 하루 보지 않기</span>
                    </label>
                </a>
            </div>
            <div class="no-popup-ctrl__close">
                <a href="javascript:popup_display('.no-popup')" title="닫기">닫기</a>
            </div>
        </div>
    </div>
    <!-- POPUP END -->

</div>
<?php endif; ?>

<script>
document.querySelector('.no-popup-overlay').addEventListener('click', function(){
    document.querySelector('.no-popup').style.display = 'none'; 
});

function popup_display(divname) {
    document.querySelector(divname).style.display = 'none'; 
}

function close_popup(nums) {
    common_frame.location.href = `./inc/lib/popup.close.php?_mode=popup_close&uid=${nums}`;
}

const popup = new Swiper('.popup-swiper', {
    loop: true,
    autoHeight: true, 
    pagination: {
        type: 'bullets',
        clickable: true,
        el: '.popup-pager',
    },
});
</script>
