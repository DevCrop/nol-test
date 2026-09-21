<div class="no_paging_box">
	<?php if ($listCurPage > 1) {
		$prevpage = $listCurPage - 1;
	?>
		<div class="no_sub02_next">
			<a href="javascript:void(0);" title="이전" onclick="goListMove(<?= $prevpage ?>, '<?= $_SERVER['PHP_SELF'] ?>');">
				<span></span>
			</a>
		</div>
	<?php } else { ?>
		<div class="no_sub02_next">
			<a href="javascript:void(0);" title="이전">
				<span></span>
			</a>
		</div>
	<?php } ?>

	<ul>
		<?php
			for ($x = ($listCurPage - $pageBlock); $x < (($listCurPage + $pageBlock) + 1); $x++) {
				if (($x > 0) && ($x <= $Page)) {
					if ($x == $listCurPage) {
		?>
				<li class="no_num_active">
					<a href="javascript:void(0);" class="page_num no_sub_pagination_pos_focus" title="<?= $x ?>페이지"><?= $x ?></a>
				</li>
		<?php
					} else {
		?>
				<li class="no_num">
					<a href="javascript:void(0);" class="page_num" title="<?= $x ?>페이지" onclick="goListMove(<?= $x ?>, '<?= $_SERVER['PHP_SELF'] ?>');"><?= $x ?></a>
				</li>
		<?php
					} 
				} 
			} 
		?>
	</ul>

	<?php if ($listCurPage != $Page) {
		$nextpage = $listCurPage + 1;
	?>
		<div class="no_sub02_next">
			<a href="javascript:void(0);" title="다음" onclick="goListMove(<?= $nextpage ?>, '<?= $_SERVER['PHP_SELF'] ?>');">
				<span></span>
			</a>
		</div>
	<?php } else { ?>
		<div class="no_sub02_next">
			<a href="javascript:void(0);" title="다음">
				<span></span>
			</a>
		</div>
	<?php } ?>
</div>

<script>
function goListMove(start, url) {
	const frm = document.getElementById("frm");
	const input = document.createElement("input");
	input.type = "hidden";
	input.name = "page";
	input.value = start;
	frm.appendChild(input);
	frm.action = url;
	frm.submit();
}
</script>
