<?php if($logData['total']>0): $page=$logData['page'];$pages=$logData['pages'];$begin=max(1,$page-2);$finish=min($pages,$begin+4);$begin=max(1,$finish-4); ?>
<nav class="admin-log-pager" aria-label="<?=$e($logTitle)?> 페이지">
    <?php if($page>1):?><a class="no-btn no-btn--normal" href="<?=$e(\Security\AdminLogView::pageUrl($logPath,$f,1))?>" aria-label="첫 페이지">처음</a><a class="no-btn no-btn--normal" href="<?=$e(\Security\AdminLogView::pageUrl($logPath,$f,$page-1))?>" aria-label="이전 페이지">이전</a><?php endif;?>
    <?php for($i=$begin;$i<=$finish;$i++):?><?php if($i===$page):?><span class="admin-log-page-current" aria-current="page"><?=$i?></span><?php else:?><a class="no-btn no-btn--normal" href="<?=$e(\Security\AdminLogView::pageUrl($logPath,$f,$i))?>" aria-label="<?=$i?> 페이지"><?=$i?></a><?php endif;?><?php endfor;?>
    <?php if($page<$pages):?><a class="no-btn no-btn--normal" href="<?=$e(\Security\AdminLogView::pageUrl($logPath,$f,$page+1))?>" aria-label="다음 페이지">다음</a><a class="no-btn no-btn--normal" href="<?=$e(\Security\AdminLogView::pageUrl($logPath,$f,$pages))?>" aria-label="마지막 페이지">마지막</a><?php endif;?>
</nav>
<?php endif;?>
