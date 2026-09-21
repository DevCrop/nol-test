<!DOCTYPE html>
<html lang="ko">
<?
	$depthnum = 1;
	$pagenum = 2;

	include_once "../../../inc/lib/base.class.php";
	include_once '../../../inc/lib/core/autoload.php';
	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";

	$map = new Map(); 
	$data = $map->findAll();
	$num = count($data); 
?>
<style>


</style>
</head>
<body>
	<div class="no-wrap">
        <!-- Header -->
		<?
		include_once "../../inc/admin.header.php";
		?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?
                include_once "../../inc/admin.drawer.php";
            ?>

            <!-- Contents -->
            <form method="POST" name="frm" id="frm" autocomplete="off">
            <input type="hidden" name="mode" id="mode" value="list">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">지도 관리</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>지도 보드</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>지도 관리</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->

                            <div class="no-items-center">
                                <a href="./map.add.php" class="no-btn no-btn--main no-btn--big">지도등록 </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">지도 관리</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        
                                        <thead>
                                            <tr>
                                                <th scope="col" class="no-width-120 no-min-width-60">
                                                    번호
                                                </th>
                                                <th scope="col" class="no-min-width-120">
                                                    지도 이름
                                                </th>
                                                <th scope="col" class="no-min-width-150">주소</th>
                                                <th scope="col" class="no-min-width-150">연락처</th>
                                                <th scope="col" class="no-min-width-role no-td-center">
                                                    관리
                                                </th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
											<?php if(count($data) > 0 ) : ?>
											<?php foreach ($data as $k => $v) : ?>
                                            <tr>
                                                <td>
                                                    <span> <?=$num?> </span>
                                                </td>
                                                <td class="no-td-title">
                                                    <a href="./map.view.php?id=<?=$v->id?>">
                                                        <?=$v->title?>
                                                    </a>
                                                </td>

                                                <td>
                                                    <span><?=$v->address?></span>
                                                </td>
                                                <td>
                                                    <span> <?=$v->tel?> </span>
                                                </td>
                                                <td>
                                                    <div class="no-table-role">
                                                    <span class="no-role-btn">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </span>
                                                    <div class="no-table-action">
                                                        <a
                                                        href="./map.view.php?id=<?=$v->id?>"
                                                        class="no-btn no-btn--sm no-btn--normal"
                                                        >수정</a
                                                        >
                                                        <a
														data-id="<?=$v->id?>"
                                                        href="./map.delete.php?id=<?=$v->id?>"
                                                        class="no-btn no-btn--sm no-btn--delete-outline"
                                                        >삭제</a
                                                        >
                                                    </div>
                                                    </div>
                                                </td>
                                            </tr>
											<?php $num--; endforeach; ?>
											<?php else : ?>
											<p>등록된 내용이 없습니다.</p>
											<?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- card body -->
                        </div>
                        <!-- card -->
                    </div>
                    <!-- contents -->

                    <!-- Pagination -->
                    <? include_once "../../lib/admin.pagination.php"; ?>
                </section>
            </form>
        </main>

       
        <?
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
	<script>
		const deleteButtons = document.querySelectorAll('.no-table-action a:last-of-type');

		
		deleteButtons.forEach(btn => {
			btn.addEventListener('click', async function(e){
				e.preventDefault();
				
				if(confirm('정말로 삭제하시겠습니까?')){
					const url = './api.php?id=' + e.currentTarget.dataset.id;
					const fd = new FormData();
					fd.append('_method', 'delete');

					const response = await fetch(url, {
						method: 'POST', 
						body: fd
					});

					const result = await response.json();

					if(!result.success) {
						alert(result.message); 
						return; 
					}

					alert(result.message);
					location.reload();
				}

				
			});
		});
		
	</script>
</body>
</html>