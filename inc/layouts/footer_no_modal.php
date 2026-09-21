<footer class="no-footer no-pd-xl--y">

    <div class="no-container-2xl">
        <div class="no-footer__inner ">
            <div class="no-footer__top no-pd-lg--b">
                <h1 class="no-footer__logo">
                    <a href="#" class="--toggle-images">
                        <img src="<?=IMG_PATH?>/logo/Logo_C_White.png" alt="" class="--dark-img">
                        <img src="<?=IMG_PATH?>/logo/Logo_C_Black.png" alt="" class="--light-img">
                        <span class="--blind">공연의 모든 것 - 블루스퀘어</span>
                    </a>
                </h1>
				  <div class="no-footer__sponsor">
                    <h5 class="no-body-lg">Sponsor</h5>
                    <ul>
						<?php if (isUpdateActive()): ?>
							<li>
								<a href="https://www.wooribank.com/" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/woori_bank_logo_dark.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/woori_bank_logo_white.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
						<?php else: ?>
							<li>
								<a href="https://www.shinhancard.com/pconts/html/main.html" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/sponsor_img_1_light.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/sponsor_img_1_dark.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
							<li>
								<a href="https://www.mastercard.co.kr/ko-kr.html" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/master_card_logo_dark.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/master_card_logo_white.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
						<?php endif; ?>
                    </ul>
                </div>

            
            </div>
            <div class="no-footer__bottom no-pd-md--t">
                <div class="no-footer__info">
                    <ul>
						<!--
						<li>
                            <b>사업자등록번호</b>
                            <p><?=$SITEINFO_FOOTER_SSN?></p>
                        </li>-->
						<li>
                            <b>주소</b>
                            <p><?=$SITEINFO_FOOTER_ADDRESS?></p>
                        </li>
						<li>
                            <b>고객센터</b>
                            <p><?=$SITEINFO_FOOTER_PHONE?></p>
                        </li>
                        <li>
                            <b>E-Mail</b>
                            <p><?=$SITEINFO_FOOTER_EMAIL?></p>
                        </li>
						<!--
                        <li>
                            <b>운영시간</b>
                            <p><?=$SITEINFO_FOOTER_FAX?></p>
                        </li>-->
                    </ul>
                </div>
				    <div class="no-footer-wrap">
						<ul class="no-footer__privacy">
							<li class="--pos-r">
								<button type="button" class="f ai-c no-gap-sm jc-sb">
									개인정보처리방침
									<i class="fa-regular fa-chevron-down"></i>
								</button>
								<ul class="no-footer__privacy-list">
									<?php if (date('Ymd') >= '20260212'): ?>
										<li>
											<a href="<?=$ROOT?>/pages/legal/legal_260212.php" class="f ai-c no-gap-sm jc-sb ">
												2026.02.12
												<i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
											</a>
										</li>
									<?php endif; ?>
									<li>
										<a href="<?=$ROOT?>/pages/legal/legal_250801.php" class="f ai-c no-gap-sm jc-sb ">
											2025.08.01
											<i class="fa-regular fa-arrow-right"></i>
										</a>
									</li>
									<li>
										<a href="<?=$ROOT?>/pages/legal/legal_250217.php" class="f ai-c no-gap-sm jc-sb ">
											2025.02.17
											<i class="fa-regular fa-arrow-right"></i>
										</a>
									</li>
									<li>
										<a href="<?=$ROOT?>/pages/legal/legal_181001.php" class="f ai-c no-gap-sm jc-sb ">
											2018.10.01
											<i class="fa-regular fa-arrow-right"></i>
										</a>
									</li>
								</ul>
							</li>
							<li>
								<a href="<?=$ROOT?>/pages/board/board.list.php?board_no=13"
									class="f al-c no-gap-sm jc-sb ai-c ">
									<p>
										기업공고
									</p>
									<i class="fa-regular fa-arrow-right"></i>
								</a>
							</li>
							<li>
								<a href=" https://www.kbei.org/y-siren" target="_blank" class="f al-c no-gap-sm jc-sb ai-c ">
									<p>
										윤리경영 / 제보
									</p>
									<i class="fa-regular fa-arrow-right"></i>
								</a>
							</li>
						</ul>
						<div class="no-footer__address ">
							<p>Copyright ⓒ NOL theater All Rights Reserved.</p>
						</div>
					</div>

            </div>
        </div>
    </div>


</footer>

<div class="no-top-btn">
    <a href="<?=$ROOT?>/pages/cs/parking.php">
        <div>
            <span>주차 안내</span>
        </div>
        <i class="fa-solid fa-car"></i>
    </a>
    <button type="button" class="active">
        <div class="arrow">
            <i class="fa-regular fa-arrow-up-long" aria-hidden="true"></i>
            <i class="fa-regular fa-arrow-up-long" aria-hidden="true"></i>
        </div>
    </button>

</div>





</body>

</html>