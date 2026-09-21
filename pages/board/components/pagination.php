<?php
// ✅ 변수 기본값 설정 (PHP 7.4에서 undefined 변수 방지)
$listCurPage = isset($listCurPage) ? (int)$listCurPage : 1;
$PHP_SELF = isset($PHP_SELF) ? $PHP_SELF : '';
$Page = isset($Page) ? (int)$Page : 1;
$pageBlock = isset($pageBlock) ? (int)$pageBlock : 5; // ✅ 기본값 추가

?>

<div class="no-pd-xl--t">
    <nav class="no-pagination">
        <!-- Previous Button -->
        <?php if ($listCurPage > 1): 
            $prevpage = $listCurPage - 1;
        ?>
        <a href="javascript:void(0);" class="no-pagination__arrow"
            onClick="goListMove(<?= (int)$prevpage ?>, '<?= htmlspecialchars($PHP_SELF, ENT_QUOTES, 'UTF-8') ?>');">
            <i class="fa-light fa-chevron-left"></i>
        </a>
        <?php else: ?>
        <a href="javascript:void(0);" class="no-pagination__arrow disabled">
            <i class="fa-light fa-chevron-left"></i>
        </a>
        <?php endif; ?>

        <!-- Page Numbers -->
        <div class="no-pagination__num">
            <?php 
            $startPage = max(1, $listCurPage - $pageBlock);
            $endPage = min($Page, $listCurPage + $pageBlock);

            for ($x = $startPage; $x <= $endPage; $x++): 
            ?>
            <a href="javascript:void(0);" class="no-pagination__link <?= ($x == $listCurPage) ? '--active' : '' ?>"
                onClick="goListMove(<?= (int)$x ?>, '<?= htmlspecialchars($PHP_SELF, ENT_QUOTES, 'UTF-8') ?>');">
                <span><?= (int)$x ?></span>
            </a>
            <?php endfor; ?>
        </div>

        <!-- Next Button -->
        <?php if ($listCurPage < $Page): 
            $nextpage = $listCurPage + 1;
        ?>
        <a href="javascript:void(0);" class="no-pagination__arrow"
            onClick="goListMove(<?= (int)$nextpage ?>, '<?= htmlspecialchars($PHP_SELF, ENT_QUOTES, 'UTF-8') ?>');">
            <i class="fa-light fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <a href="javascript:void(0);" class="no-pagination__arrow disabled">
            <i class="fa-light fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </nav>
</div>

<script>
function goListMove(page, url) {
    const existingForm = document.getElementById('frm');

    if (existingForm) {
        let pageInput = existingForm.querySelector('input[name="page"]');
        if (!pageInput) {
            pageInput = document.createElement('input');
            pageInput.type = 'hidden';
            pageInput.name = 'page';
            existingForm.appendChild(pageInput);
        }
        pageInput.value = page;
        existingForm.action = url;
        existingForm.submit();
        return;
    }

    const form = document.createElement('form');
    form.method = 'GET';
    form.action = url;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'page';
    input.value = page;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>