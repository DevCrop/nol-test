
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT . '/inc/layouts/head.php'; ?>
<?php include_once $STATIC_ROOT . '/inc/layouts/header.php'; ?>
<?php include_once $STATIC_ROOT."/inc/lib/counter.inc.php";?>


<main class="no-main fm-ch no-main-ch">
   <section class="no-main-visual" id="visual">
		<div class="screen-wrap">
			<div class="screen">
				<div class="s-container-3xl no-main-visual-container">
					<div class="no-main-visual__inner">
						<h3 class="split-text of-h">你⾳乐最后的关键</h3>
						<div class="intro-text f  ai-c jc-c no-gap-16">
							<div class="blur-text ">
								<h2 class="fm-title split-text">Studio</h2>
								<span class="fm-title split-text">Studio</span>
							</div>
							<div class="img pos-r">
								<img src="<?=IMG_PATH?>/logo/logo-white.svg" alt="">
								<div class="blur-bg"></div>
							</div>
						</div>
					</div>
					<div class="no-main-visual__line">
						<span>Scroll Down</span>
						<div class="line"></div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--
    <section class="pos-r  no-main-intro" id="about">
        <div class="no-main-about">
            <div class="s-container-2xl">
                <div class="no-main-about__inner">
				<ul class="no-display-about ">
				  <li>
					<div>
					  <span class="gradient-text">Studio KEY</span>
					</div>
				  </li>
				<li>
					<div>
					  <span>是一家专业的⾳乐⼯作室</span>
					</div>
				  </li>
				  <li>
					<div>
					  <span>专注于</span>
					  <img src="<?=IMG_PATH?>/main/main-about-icon.png" alt="" class="icon">
					</div>
				  </li>
				  <li>
					<div>
					  <span class="">MIX</span>
					  <div class="image-wrap scale-up">
						<div class="image-bg"></div>
						<img src="<?=IMG_PATH?>/main/main-about-img-1.jpg" alt="" class=" image">
					  </div>
					  <span>和 MASTER</span>
					  <div class="image-wrap else-image">
						<img src="<?=IMG_PATH?>/main/main-about-img-2.jpg" alt="" class="image ">
					  </div>
					</div>
				  </li>
				
				</ul>
                </div>
            </div>
        </div>


		<div class="no-main-philosophy ">
		  <div class="s-container-2xl">
			<div class="no-main-philosophy__inner">
			  <div class="no-section-title">
				<h2 class="no-display-1 t-center ">
				他对MIX和 <br>
				MASTER的独特理念
				</h2>
			  </div>
			  <div class="cnt no-pd-80--top">
				<ul class="grid-col-4 no-gap-30">

				  <li class="box">
					<div class="img">
					  <img src="<?=IMG_PATH?>/icon/main-philosophy-icon-1.png" alt="">
					</div>
					<div class="txt no-pd-36--top">
					  <p class="no-body-1">
						MIX 和 MASTER 是将音乐的情感完整传递 给听众的过程。
					  </p>
					</div>
				  </li>

				  <li class="box">
					<div class="img">
					  <img src="<?=IMG_PATH?>/icon/main-philosophy-icon-3.png" alt="">
					</div>
					<div class="txt no-pd-36--top">
					  <p class="no-body-1">
						 MIX 和 MASTER 必须始终优先考虑音乐的 元素, 确保每一份情感都通过声音生动地表达出 来。
					  </p>
					</div>
				  </li>

				  <li class="box">
					<div class="img">
					  <img src="<?=IMG_PATH?>/icon/main-philosophy-icon-2.png" alt="">
					</div>
					<div class="txt no-pd-36--top">
					  <p class="no-body-1">
						音乐中可能没有唯一的标准答案, 但最佳的答 案是存在的。这是 MIX 和 MASTER 的过程,  最终由客户批准, 音乐发布的那一刻就是这首音 乐的决定性答案。
					  </p>
					</div>
				  </li>

				  <li class="box">
					<div class="img">
					  <img src="<?=IMG_PATH?>/icon/main-philosophy-icon-4.png" alt="">
					</div>
					<div class="txt no-pd-36--top">
					  <p class="no-body-1">
						客户的任何修改要求都没有不可解决的事 情。
					  </p>
					</div>
				  </li>
				</ul>
			  </div>
			</div>
		  </div>
		</div>

        <div class="no-gradient-primary"></div>
    </section>-->

	<section class="pos-r  no-main-intro" id="about">
		<div class="no-main-about">
			<div class="s-container-2xl">
				<div class="no-main-about__inner">
					<ul class="no-display-about ">
					  <li>
						<div>
						  <span class="gradient-text">Studio KEY</span>
						</div>
					  </li>
					<li>
						<div>
						  <span>是一家专业的⾳乐⼯作室</span>
						</div>
					  </li>
					  <li>
						<div>
						  <span>专注于</span>
						  <img src="<?=IMG_PATH?>/main/main-about-icon.png" alt="" class="icon">
						</div>
					  </li>
					  <li>
						<div>
						  <span class="">MIX</span>
						  <div class="image-wrap scale-up">
							<div class="image-bg"></div>
							<img src="<?=IMG_PATH?>/main/main-member-img-1.jpg" alt="" class=" image">
						  </div>
						  <span>MASTER</span>
						  <div class="image-wrap else-image">
							 <img src="<?=IMG_PATH?>/main/main-about-img-2.jpg" alt="" class="image ">
						  </div>
						</div>
					  </li>
					</ul>
				</div>
			</div>
		</div>

		<div class="no-main-people">
			<div class="s-container-2xl">
				<div class="no-main-people__inner">

					<!--1. 글씨가 올라가는 인터렉션--->
					<div class="title no-display-people">
						<div class="content">
							<div class="title-wrap ">
								<h2 class="">
									韩国顶级⾳频⼯程师, 
								</h2>
								   <div class="blur-text pos-r">
										<div class="fm-title ">崔珉诚.</h2>
											<span class="fm-title ">崔珉诚.</span>
										</div>
									</div>
							</div>
							<h2>
								他终于来到了 中国！
							</h2>
						</div>

					</div>

					<!--2. 이미지 2개가 올라오는 인터렉션---->
					<div class="images">
						<div class="content">
							<ul>
								<li>
									<div class="no-round-16">
										<img src="<?=IMG_PATH?>/main/main-people-img-1.jpg" alt="" class="no-round-16">
									</div>
								</li>
								<li>
									<div></div>
									<div class="no-round-16">
										<img src="<?=IMG_PATH?>/main/main-people-img-2.jpg" alt="" class="no-round-16">
									</div>
								</li>
							</ul>

							<h3 class='no-display-5'>MINKEY</h3>
							<div class="cnt no-pd-24--top">
								<div class="no-body-1">
									<p>
										崔珉诚是韩国最著名的音乐工作室的首席音频工程师, 每年他负责制作的曲目超过 300首,  <br>
										他的音乐洞察力和音频处理技术获得了高度认可。
											此外, 他还拥有与中国制作团队合作的丰富经验, 包括参与中国的偶像团队项目和影视剧 OST 的制作, <br>
										这使得他对中国的音乐偏好和情感有了深刻的理解。
										他的作品曾在韩国和中国的音乐排行榜上同时获得过第一名, 这证明了他非凡的才华和出色的音乐成就。<br>
											长期以来, 师从崔珉诚的中国混音工程师 欧姆 一直在鼓励他拓展中国市场。在这种激励下, <br>
										崔珉诚最终在中国创立了一个名为 Studio KEY 的音乐厂牌并计划以“MINKEY” 这个艺名在中国开展全新的音乐事业。
									</p>
						
								</div>
							</div>


						</div>
					</div>


				</div>
			</div>

			<figure class="no-gradient-primary"></figure>
		</div>
	</section>

	<section class="no-main-philosophy ">
		<div class="s-container-2xl">
			<div class="no-main-philosophy__inner">
				<div class="no-section-title">
					<h2 class="no-display-1 t-center ">
					他对MIX, MASTER <br>
					独特理念
					<!--
					他对MIX和 <br>
					MASTER的独特理念-->
					</h2>
				</div>
				<div class="cnt no-pd-80--top">
					<ul class="grid-col-4 no-gap-30">
						<li class="box">
							<!--
						<div class="img">
							<img src="<?=IMG_PATH?>/icon/main-philosophy-icon-1.png" alt="">
						</div>-->
							<div class="txt ">
								<h4 class="no-heading-2 ">Feel the Emotion</h4>
								<p class=" no-pd-16--top">
									MIX 和 MASTER 是将音乐的情感完整传递给听众的过程。
								</p>
							</div>
						</li>
						<li class="box">
							<!--
						<div class="img">
							<img src="<?=IMG_PATH?>/icon/main-philosophy-icon-3.png" alt="">
						</div>-->
							<div class="txt ">
								<h4 class="no-heading-2 ">Music First</h4>
								<p class=" no-pd-16--top">
									 MIX 和 MASTER 必须始终优先考虑音乐的元素,
									 确保每一份情感都通过声音生动地表达出来。
								</p>
							</div>
						</li>
						<li class="box">
							<!--
						<div class="img">
							<img src="<?=IMG_PATH?>/icon/main-philosophy-icon-2.png" alt="">
						</div>-->
							<div class="txt ">
								<h4 class="no-heading-2 ">Final Answer</h4>
								<p class=" no-pd-16--top">
									音乐中可能没有唯一的标准答案, 但最佳的答案是存在的。这是 MIX 和 MASTER 的过程, 最终由客户批准, 音乐发布的那一刻就是这首音乐的决定性答案。
								</p>
							</div>
						</li>
						<li class="box">
							<!--
						<div class="img">
							<img src="<?=IMG_PATH?>/icon/main-philosophy-icon-4.png" alt="">
						</div>-->
							<div class="txt ">
								<h4 class="no-heading-2 ">No Limits</h4>
								<p class=" no-pd-16--top">
									客户的任何修改要求都没有不可解决的事情。
								</p>
							</div>
						</li>

					</ul>
				</div>
			</div>
		</div>
		<div class="bg">
			<img src="<?=IMG_PATH?>/main/main-phi-bg.png" alt="">
		</div>
	</section>
	
	<section class="no-main-tech  t-center">
		<div class="s-container-2xl">
			<div class="no-main-tech__inner">
				<div class="no-section-title">
					<h2 class="no-display-1">Skills and Style</h2>
				</div>
					<div class="cnt no-pd-80--top no-main-tech__desc no-heading-2 fm-body ">
						<p class="">
							不论⻛格如何, Studio KEY 都能提供卓越的成果。 
						</p>
						<p class="">
							他的作品让每一个乐器和人声都变得清晰、
						</p>
						<p>
							立体、丰富, 仿佛它们就在观众面前。
						</p>
						<p class="">
							他能够发现音乐中那些你未曾察觉的微妙细节, 
						</p>
						<p class="">
							最终成为你音乐的关键, 激发听众的情感。
						</p>
				
					</div>
			</div>
		</div>
		<div class="no-main-tech__bg">
			<img src="<?=IMG_PATH?>/main/main-tech-background.jpg" alt="">
		</div>
	</section>



    <section class="no-main-service no-section-lg pos-r" id="service">
        <div class="s-container-2xl">
            <div class="no-section-title">
                <h2 class="no-display-1 t-center ">Service of KEY</h2>
            </div>
            <div class="cnt no-pd-80--top ">
				<ul class="grid-col-2 no-gap-30">
					<li class="box">
					<div class="icon ">
					  <img src="<?=IMG_PATH?>/icon/main-service-icon-4.png" alt="">
					</div>
					<div class="txt ">
					  <h4 class="no-heading-2 no-pd-40--top"> MIX</h4>
					  <p class="no-pd-16--top no-heading-5">
						和谐地结合所有录制的音轨, 完成歌曲整体声音的平衡。
					  </p>
					</div>
					</li>
					<li class="box">
					<div class="icon ">
					  <img src="<?=IMG_PATH?>/icon/main-service-icon-3.png" alt="">
					</div>
					<div class="txt ">
					  <h4 class="no-heading-2 no-pd-40--top"> MASTER</h4>
					  <p class="no-pd-16--top no-heading-5">
						将整个<span class="test">⾳</span>频优化⾄⾏业标准, 以最⼤化最终品质。
					  </p>
					</div>
					</li>
					<li class="box">
					<div class="icon ">
					  <img src="<?=IMG_PATH?>/icon/main-service-icon-1.png" alt="">
					</div>
					<div class="txt ">
					  <h4 class="no-heading-2 no-pd-40--top"> STEM MASTER</h4>
					  <p class="no-pd-16--top no-heading-5">
						利用混音后的分轨, 即按乐器组分类的音轨, 进行更精细的母带处理。
					  </p>
					</div>
					</li>
					<li class="box">
					<div class="icon ">
					  <img src="<?=IMG_PATH?>/icon/main-service-icon-2.png" alt="">
					</div>
					<div class="txt ">
					  <h4 class="no-heading-2 no-pd-40--top"> TUNE</h4>
					  <p class="no-pd-16--top no-heading-5">
						精细调整音高和节奏, 以实现自然且富有情感的表演。
					  </p>
					</div>
					</li>
					</ul>
            </div>
        </div>
        <div class="no-gradient-primary"></div>
    </section>


	<section class="no-main-team no-section-lg" id="team">
	  <div class="s-container-2xl">
		<div class="no-main-team__inner">
		  <div class="no-section-title">
			<h2 class="no-display-1 t-center ">Team of KEY</h2>
		  </div>
		  <div class="cnt no-pd-80--top">
			<ul>
			  <li class="up-content">
				<div class="txt">
				  <h3 class="no-display-3">崔珉诚</h3>
				  <p class="no-heading-2">
					韩国顶级声音工程师
				  </p>
				  <div class="no-pd-36--top">
					<button class="learn-more" type="button">
					  <span class="button-text">View More</span>
					  <i class="fa-regular fa-arrow-right-long"></i>
					</button>
				  </div>
				</div>
				<div class="img">
				  <img src="<?=IMG_PATH?>/main/main-member-img-1.jpg" alt="">
				</div>
			  </li>
			  <li class="up-content">
				<div class="img">
				  <img src="<?=IMG_PATH?>/main/main-member-img-2.png" alt="">
				</div>
				<div class="txt">
					<h3 class="no-display-3">欧姆</h3>
					  <p class="no-heading-2">
						中国混音工程师
					  </p>
				  <div class="no-pd-36--top">
					<button class="learn-more" type="button">
					  <span class="button-text">View More</span>
					  <i class="fa-regular fa-arrow-right-long"></i>
					</button>
				  </div>
				</div>
			  </li>
			  <li class="up-content">
				<div class="txt">
				  <h3 class="no-display-3">Riu</h3>
				  <p class="no-heading-2">
					音频编辑TUNE工程师
				  </p>
				
				  <div class="no-pd-36--top">
					<button class="learn-more" type="button">
					  <span class="button-text">View More</span>
					  <i class="fa-regular fa-arrow-right-long"></i>
					</button>
				  </div>
				</div>
				<div class="img">
				  <img src="<?=IMG_PATH?>/main/main-member-img-3.jpg" alt="">
				</div>
			  </li>
			</ul>
		  </div>
		</div>
	  </div>
	</section>


<?php
	$board_info = getBoardInfoByName("플레이리스트 - 중국어");
	$board_no = $board_info[0]['no'];
	$arrLenders = getBoardLimit($board_no, 100, "");
	$board_category = getBoardCategory($board_no);
?>

<section class="no-main-playlist no-section-lg" id="sample">
    <div class="s-container-2xl">
        <div class="no-main-playlist__inner">
            <div class="no-section-title t-center">
                <h2 class="t-center no-display-1 ">Sample of KEY</h2>
				<p class="no-heading-4 no-pd-16--top">
					若需要其他⻛格的样本, 请随时联系我们。
				</p>
            </div>
            <div class="cnt no-pd-80--top">
                <div class="no-category-swiper swiper">
                    <ul class="swiper-wrapper">
                        <?php foreach ($board_category as $category): ?>
                            <li class="swiper-slide">
                                <button type="button" class="no-btn category-btn" data-category="<?= $category['no'] ?>">
                                    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="no-main-playlist__wrap no-pd-64--top">
                    <?php foreach ($board_category as $category): ?>
                        <div class="category-content" data-category="<?= $category['no'] ?>" style="display: none;">
                            <ul class="grid-col-4 no-gap-30 playlist">
                                <?php foreach ($arrLenders as $k => $v): ?>
                                    <?php if ($v['category_no'] == $category['no']): ?>
                                        <?php
                                            $title = iconv_substr($v['title'], 0, 150, "utf-8");
                                            $link = "/pages/board/board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=" . base64_encode($searchKeyword) . "&searchColumn=" . base64_encode($searchColumn) . "&page=$page";
                                            $new = "";
                                            if (time() - strtotime($v['regdate']) < (60 * 60 * 24 * 2)) {    
                                                $new = "<img src=\"../../resource/images/no_new_icon.png\"/>";
                                            }    
                                            $imgSrc = $v['thumb_image'] ? $UPLOAD_WDIR_BOARD . "/" . $v['thumb_image'] : 'default_image.png';
                                        ?>
                                        <li >
                                            <div class="no-main-play__audio">
                                                <div class="no-main-playlist__info">
                                                    <figure>
                                                        <img src="<?=$imgSrc?>" alt="<?=$title?>">
                                                    </figure>
                                                    <div class="info no-pd-20--top">
                                                        <div class="txt">
                                                            <h2 class="no-heading-2"><?=$title?></h2>
                                                            <p class="no-heading-4 no-pd-8--top"><?=$v['extra1']?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="no-main-playlist__background">
                                                    <div class="blur"></div>
                                                    <img src="<?=$imgSrc?>" alt="">
                                                </div>
                                   
                                                <audio class="player" crossorigin playsinline>
                                                   <source src="/uploads/board/<?= $v['file_attach_1'] ?>" type="audio/mp3">
                                                </audio>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>


	

    <section class="no-main-fee no-section-lg" id="pricing">
        <div class="s-container-2xl">
            <div class="no-section-title">
                <h2 class="no-display-1 t-center ">Pricing of KEY</h2>
            </div>
            <div class="cnt no-pd-80--top up-content">
                <div class="no-main-fee__inner">
                    <table class="no-main-fee__table">
                        <thead>
                            <tr class="no-heading-4">
                                <th class=""></th>
                                <th class="">
									<div>
										<img src="<?=IMG_PATH?>/icon/fee-icon-img-1.png" alt="">
										
                                    崔珉诚                                
									</div>
								</th>
                                <th class="">
									<div>
										<img src="<?=IMG_PATH?>/icon/fee-icon-img-2.png" alt="">
									欧姆	
									</div>
                                </th>
                                <th class="">
									<div>
										<img src="<?=IMG_PATH?>/icon/fee-icon-img-3.png" alt="">
										
									Riu
									</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="no-heading-5">
                            <tr>
                                <td class="">
                                    Package <br>
									(TUNE+MIX+STEM MASTER)
                                </td>
                                <td class="">
									<div>
										 6,000
										 <span> RMB</span>
									</div>
								</td>
                                <td class="">
									<div>
										-
									</div>
								</td>
                                <td class="">
									<div>
										-
									</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="">
                                    MIX
                                </td>
                                <td class="">
									<div>
										 5,000 
										  <span> RMB</span>
									</div>
								</td>
                                <td class="">
									<div>
										 3,000 
										  <span> RMB</span>
									</div>
                                </td>
                                <td class="">
									<div>
										
									</div>
								</td>
                            </tr>
                            <tr>
                                <td class="">
									MASTER
								</td>
                                <td class="">
									<div>
										 1.000 
										  <span> RMB</span>
									</div>
								</td>
                                <td class="">
									<div>
										-
									</div>
                                </td>
                                <td class="">
									<div>
										-
									</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="">
								STEM MASTER <br>
								</td>
                                <td class="">
									<div>
										 2,000 
										<span> RMB</span>
									</div>
                                </td>
                                <td class="">
									<div>
										-
									</div>
                                </td>
                                <td class="">
									<div>
										-
									</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="">
									TUNE <br>
								</td>
                                <td class="">
									<div>
										-
									</div>
								</td>
                                <td class="">
									<div>
										-
									</div>
								</td>
                                <td class="">
									<div>
										1,200 
										<span> RMB</span>
									</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="no-notice no-pd-40--top">
                    <div class="title">
                        <i class="fa-regular fa-circle-info"></i>
                        <p class="no-heading-5">Notice</p>
                    </div>
                    <div class="no-notice__inner no-pd-24--top">
                        <ul class="no-body-1">
							<li>
								<p>
									曲目轨道数量和修改次数没有限制。
								</p>
							</li>
							<li>
								<p>
									向欧姆请求MIX时, TUNE将免费提供。
								</p>
							</li>
							<li>
								<p>
									如果客户选择欧姆的MIX服务时, 并且需要资深工程师崔珉诚进行MASTER, 崔珉诚将在MIX过程中⼀起聆听并提供正确的反馈, 同时参与整个MIX的发展。<br>
									换句话说, 最终成果得到了质量保证。
								</p>
							</li>
						</ul>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="no-main-marquee fm-title">
        <div class="no-main-marquee__container" wb-data="marquee" duration="50">
            <div class="no-main-marquee__content">

				<span>TUNE</span>
				<img src="<?=IMG_PATH?>/icon/marquee-icon-3.png" alt="">
				<div>
                    <img src="<?=IMG_PATH?>/logo/logo-white.svg" alt="">
                </div>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-4.png" alt="">

				<span>MIX</span>
				<img src="<?=IMG_PATH?>/icon/marquee-icon-2.png" alt="">
				<div>
                    <img src="<?=IMG_PATH?>/logo/logo-white.svg" alt="">
                </div>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-1.png" alt="">

				<span>MASTER</span>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-4.png" alt="">
				<div>
                    <img src="<?=IMG_PATH?>/logo/logo-white.svg" alt="">
                </div>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-3.png" alt="">
            
				 <span>STEM MASTER</span>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-1.png" alt="">
				<div>
                    <img src="<?=IMG_PATH?>/logo/logo-white.svg" alt="">
                </div>
                <img src="<?=IMG_PATH?>/icon/marquee-icon-2.png" alt="">
                
            </div>
        </div>
    </section>


    <section class="no-main-contact  no-section-md" id="contact">
        <div class="s-container-2xl">
            <div class="no-section-title">
                <h2 class="no-display-1 t-center">Contact of KEY</h2>
            </div>
            <div class="cnt no-pd-80--top">
                <div class="box up-content">
                    <ul>
                        <li>
                            <div class="icon">
                                <i class="fa-brands fa-weixin"></i>
                                <p>
                                    WeChat
                                </p>
                            </div>
                            <div class="txt">
                                <p>StudioKEY</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <i class="fa-solid fa-envelope"></i>
                                <p>
                                    Email
                                </p>
                            </div>
                            <div class="txt">
                                <p>mail@studiokey.cn</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="no-main-contact__bg">
            <img src="<?=IMG_PATH?>/main/main-contact-background.png" alt="">
        </div>
    </section>
</main>


<?php include_once $STATIC_ROOT . '/inc/layouts/footer.ch.php'; ?>




