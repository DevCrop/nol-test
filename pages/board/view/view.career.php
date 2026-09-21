<div class="no-view-container no-career-inner">
    <div class="no-career__info">
        <h3><?=$data[title]?></h3>
        <span><?=$data[extra2]?></span>
        <span><?=$data[extra4]?></span>
    </div>
    
    <div class="no-career__content">
        <?=\Security\HtmlSanitizer::clean((string) $data['contents'])?>
    </div>
    
    <div class="no-career-btn-pos">
        <a href="mailto:recruit@emoldino.com" class="no-btn no-btn--sky">Apply for this position</a>
    </div>
</div>

