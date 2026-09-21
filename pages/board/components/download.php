<?php if ($data['file_attach_1'] || $data['file_attach_2'] || $data['file_attach_3'] || $data['file_attach_4'] || $data['file_attach_5']): ?>
<div class="no-download no-pd-md--t">
    <div class="no-download__wrap">
		<span>첨부 파일</span>
        <ul>
            <?php if ($data['file_attach_1']): ?>
            <li>
                <a  href="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/board.file.download.php?no=<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>&fld=attach1">
                    <i class="fa-solid fa-file-arrow-down"></i> <?= htmlspecialchars($data['file_attach_origin_1'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($data['file_attach_2']): ?>
            <li>
                <a href="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/board.file.download.php?no=<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>&fld=attach2">
                   <i class="fa-solid fa-file-arrow-down"></i>  <?= htmlspecialchars($data['file_attach_origin_2'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($data['file_attach_3']): ?>
            <li>
                <a href="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/board.file.download.php?no=<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>&fld=attach3">
                    <i class="fa-solid fa-file-arrow-down"></i>  <?= htmlspecialchars($data['file_attach_origin_3'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($data['file_attach_4']): ?>
            <li>
                <a href="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/board.file.download.php?no=<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>&fld=attach4">
                    <i class="fa-solid fa-file-arrow-down"></i>  <?= htmlspecialchars($data['file_attach_origin_4'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($data['file_attach_5']): ?>
            <li>
                <a href="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/board.file.download.php?no=<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>&fld=attach5">
                    <i class="fa-solid fa-file-arrow-down"></i>  <?= htmlspecialchars($data['file_attach_origin_5'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
