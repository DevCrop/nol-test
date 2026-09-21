<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

	$pdo = DB::getInstance();
	$depthnum = 5;
	$pagenum = 4;

	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
<script type="text/javascript" src="<?= htmlspecialchars($NO_IS_SUBDIR) ?>/admin/resource/js/datepicker.onlyYear.js?v=<?= htmlspecialchars($STATIC_FRONT_JS_MODIFY_DATE) ?>"></script>
</head>

<body>
	<div class="no-wrap">
        <!-- Header -->
		<?php include_once "../../inc/admin.header.php"; ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <?php
            $ToDay_Year = date("Y");

            // Get total visitors
            $stmt = $pdo->query("SELECT IFNULL(SUM(Visit_Num), 0) AS CDSV FROM nb_counter_data");
            $Total = $stmt->fetchColumn() ?? 0;
            ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="post" autocomplete="off">
			    <input type="hidden" id="mode" name="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">접속통계</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>접속통계</span></li>
                                        <li class="no-breadcrumb-item"><span>년도별</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block no-w-20">
                                    <h3 class="no-admin-title">총방문자</h3>
                                    <div class="no-admin-content">
                                        <span class="no-admin-cnt"><?= htmlspecialchars(number_format($Total)) ?>명</span>
                                    </div>
                                </div>    

                                <div class="no-admin-block no-w-80">
                                    <h3 class="no-admin-title">날짜선택</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <div class="no-search-wrap">
                                            <input type="text" name="sdate" id="sdate" value="<?= htmlspecialchars($ToDay_Year) ?>" autocomplete="off">
                                            <div class="no-search-btn">
                                                <button type="submit" class="no-btn no-btn--main no-btn--search">검색</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">년도별 접속통계</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            연도, 접속수, 접속수 그래프, 접속수 퍼센트로 구성된 시간별 접속통계표
                                        </caption>
                                        
                                        <thead class="no-blind">
                                            <tr>
                                                <th scope="col" class="no-min-width-60">연도</th>
                                                <th scope="col" class="no-min-width-60">접속수</th>
                                                <th scope="col" class="no-min-width-150">접속수 그래프</th>
                                                <th scope="col" class="no-min-width-60">접속수 퍼센트</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                            // Fetch max visit count by year
                                            $stmt = $pdo->query("SELECT IFNULL(SUM(Visit_Num), 0) AS maxVisit FROM nb_counter_data GROUP BY Year ORDER BY Year DESC LIMIT 1");
                                            $max = $stmt->fetchColumn() ?? 0;

                                            // Get list of years with visit data
                                            $stmt = $pdo->query("SELECT DISTINCT Year FROM nb_counter_data ORDER BY Year ASC");
                                            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                            foreach ($years as $year) {
                                                $stmt = $pdo->prepare("SELECT IFNULL(SUM(Visit_Num), 0) AS SV FROM nb_counter_data WHERE Year = :year");
                                                $stmt->execute(['year' => $year]);
                                                $Year_Num = $stmt->fetchColumn() ?? 0;

                                                $Percent = $Total ? round(100 * $Year_Num / $Total, 2) : 0;
                                                $Percent_Width = $Percent ? $Percent : 1;
                                                $Back_Color = ($max == $Year_Num && $max > 0) ? "style='background-color:#0083e8;'" : "style='background-color:#CCCCCC;'";
                                            ?>
                                            <tr>
                                                <td><span><?= htmlspecialchars($year) ?>년</span></td>
                                                <td><span><?= htmlspecialchars(number_format($Year_Num)) ?>명</span></td>
                                                <td>
                                                    <table width='<?= htmlspecialchars($Percent_Width) ?>%' cellspacing='0' cellpadding='0' height='8'>
                                                        <tr><td <?= $Back_Color ?>>&nbsp;</td></tr>
                                                    </table>
                                                </td>
                                                <td><span><?= htmlspecialchars(number_format($Percent, 2)) ?>%</span></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/setting.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

    <!-- jQuery Datepicker Configuration for Year Only -->
    <script>
		$(function() { 
		  $('#sdate').datepicker({
			yearRange: "c-10:c",
			changeMonth: false,
			changeYear: true,
			showButtonPanel: true,
			closeText: '선택',
			currentText: '오늘',
			onClose: function(dateText, inst) {
			  var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
			  $(this).val($.datepicker.formatDate('yy', new Date(year, 1, 1)));
			}
		  }).focus(function () {
			$(".ui-datepicker-month, .ui-datepicker-calendar, .ui-datepicker-current, .ui-datepicker-prev, .ui-datepicker-next").hide();
			$("#ui-datepicker-div").position({
			  my: "left top",
			  at: "left bottom",
			  of: $(this)
			});
		  }).attr("readonly", false);
		});
	</script>
	<style>
		table.ui-datepicker-calendar { display: none; }
		table.ui-datepicker-months { display: none; }
	</style>
</body>
</html>
