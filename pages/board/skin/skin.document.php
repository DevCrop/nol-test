<?php
$groupedCategories = [];
foreach ($arrResultSet as $item) {
    $category = $item['category_no'];
    $groupedCategories[$category]['items'][] = $item;
    $groupedCategories[$category]['name'] = $item['category_name'];
}

?>

<div class="no-pd-2xl--y">
	<section class="no-sub-docx">
		<div class="no-container-xl">
			<div class="cnt">
				<?php foreach ($groupedCategories as $categoryNo => $categoryData): ?>
					<div class="no-pd-xl--y " >
						<ul class="grid-col-4-8 no-gap-lg">
							<li <?=$aos_title?>>
								<h4 class="no-heading-lg">
									<?= htmlspecialchars($categoryData['name'], ENT_QUOTES, 'UTF-8') ?>
								</h4>
							</li>
							<li <?=$aos_content?>>
								<ul class="grid-col-2 no-gap-md md-fd-c md-ai-fs">
									<?php foreach ($categoryData['items'] as $item): ?>
										<li>
											<a href="./board.file.download.php?no=<?=$item['no']?>&fld=attach1"  
											   class="no-download-btn" 
											   download>
												<span><?=$item['title']?></span>
												<i class="fa-solid fa-file-arrow-down"></i>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
</div>