<section class="no-sub-faq no-pd-2xl--y ">
    <div class="no-container-xl">
        <!---category-->
        <div <?=$aos_title?>>
            <?php
				include_once $STATIC_ROOT . '/pages/board/components/category.php';
			?>
        </div>

        <div class="no-pd-xl--t">
            <div class="no-skin-faq">
                <div class="no-skin-faq-container">
                    <ul class="no-skin-faq-list">
                        <?php foreach ($arrResultSet as $k => $v) :

							$title = htmlspecialchars($v['title'] ?? '', ENT_QUOTES, 'UTF-8');
							$contents = stripslashes($v['contents']);
							$contents = htmlspecialchars_decode($contents);
							
							?>

                        <li class="no-skin-faq-item " data-faq-item <?=$aos_content?>>
                            <header class="no-skin-faq-head">
                                <button type="button">
                                    <div class="no-skin-faq-item__title">
                                        <div class="no-skin-faq-item__icon">
                                            <span>Q</span>
                                        </div>
                                        <h3 class="no-body-lg --fw-semibold --t-start">
                                            <?=$title?>
                                        </h3>
                                    </div>
                                    <div class="no-skin-faq-item__arrow">
                                        <span></span>
                                        <span></span>
                                    </div>
                                </button>
                            </header>
                            <section class="no-skin-faq-body">
                                <div>
                                    <div class="no-skin-faq-item__icon ">
                                        <span>A</span>
                                    </div>
                                    <div class="no-skin-faq-body__content ">
                                        <?=\Security\HtmlSanitizer::clean((string) $contents)?>
                                    </div>
                                </div>
                            </section>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</section>
