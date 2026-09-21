
<article class="no-sub-visual active" style="background-image: url('<?=$UPLOAD_WDIR_BOARD?>/<?=$data[file_attach_2]?>');">
    <div class="no-sub-visual__box">
        <div>
            <h2>					
                <?=$data[extra1]?>
            </h2>
            <p>					
                <?=$data[extra3]?>
            </p>
        </div>
    </div>
</article>
<?
    if(!empty($data['contents'])){ ?>
        <article class="no-case-section">
            <div class="no-container">
                <div class="no-case__desc">
                    <?=\Security\HtmlSanitizer::clean((string) $data['contents'])?>
                </div>
            </div>
        </article>
    <?}
?>

<article class="no-case-wrap">
    <h3 class="no-blind">case study</h3>
    <div class="no-full-wrapper">
        <div class="no-case-flex">
            <div class="no-case__image">
                <img src="<?=$UPLOAD_WDIR_BOARD?>/<?=$data[file_attach_3]?>" alt="">
            </div>
            <div class="no-case-form">
                <input type="hidden" id="category" name="category" value="Samsung Case Study">
                <h4>Request the Samsung Case Study</h4>
                
                <ul class="no-case-input">
                    <li>
                        <label for="firstname">Frist Name</label>
                        <input type="text" name="firstname" id="firstname" placeholder="e.g., Kat">
                    </li>
                    <li>
                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" placeholder="e.g., Jones">
                    </li>
                        <li>
                        <label for="company_name">Company</label>
                        <input type="text" name="company_name" id="company_name" placeholder="Company">
                    </li>
                    <li>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="e.g., example@mail.com">
                    </li>
                    <li class="no-partship-item">
                        <label for="c_captcha" class="no_contact_category">
                            <span>Captcha</span>
                        </label>
                        <div class="no_contact_item captcha_box case">
                            <div class="no_captcha_img case"><img src="/inc/lib/captcha.n.php" alt="captcha"></div>
                            <input type="text" name="c_captcha" id="c_captcha" maxlength="5" placeholder="Enter the number (without spaces) shown in the image." autocomplete="off">
                        </div>
                    </li>
                </ul>
                
                <div class="no-case-submit">
                    <button type="button" class="no-btn no-btn--sky" onClick="doRequestSubmit();">Request the Case Study</button>
                </div>
            </div>
        </div>
    </div>
</article>
