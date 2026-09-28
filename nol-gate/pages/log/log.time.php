<?php
include_once "../../../inc/lib/base.class.php";

$pdo = DB::getInstance();
include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>
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
            // ========== Date Processing (YYYY-MM-DD, input type="date") ==========
            $sdate = trim($_REQUEST['sdate'] ?? '');
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $sdate)) {
                $sdate = date('Y-m-d');
            }
            [$Select_Year, $Select_Month, $Select_Day] = array_map('intval', explode('-', $sdate));
            $curYMD = sprintf('%04d-%02d-%02d', $Select_Year, $Select_Month, $Select_Day);

            // ========== 총 방문자(해당 일자) ==========
            // nb_counter_data에서 집계
            $stmt = $pdo->prepare("SELECT Visit_Num FROM nb_counter_data WHERE `Year`=:y AND `Month`=:m AND `Day`=:d");
            $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
            $TotalDay = (int)($stmt->fetchColumn() ?: 0);

            // nb_analytics도 확인
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM nb_analytics WHERE `year`=:y AND `month`=:m AND `day`=:d");
            $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
            $analyticsDay = (int)$stmt->fetchColumn();
            $TotalDay += $analyticsDay;

            // nb_counter에서도 확인
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM nb_counter WHERE `Year`=:y AND `Month`=:m AND `Day`=:d");
            $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
            $counterDay = (int)$stmt->fetchColumn();
            // nb_counter_data에 없는 경우만 추가
            if ($TotalDay == 0 && $counterDay > 0) {
                $TotalDay = $counterDay;
            }

            // ========== 시간대별 집계 ==========
            // nb_counter_data에서 시간대별 집계 (Hour00 ~ Hour23)
            $hourMap = array_fill(0, 24, 0);
            $maxHourCnt = 0;
            
            if ($TotalDay > 0) {
                $stmt = $pdo->prepare("
                    SELECT Hour00, Hour01, Hour02, Hour03, Hour04, Hour05, Hour06, Hour07,
                           Hour08, Hour09, Hour10, Hour11, Hour12, Hour13, Hour14, Hour15,
                           Hour16, Hour17, Hour18, Hour19, Hour20, Hour21, Hour22, Hour23
                    FROM nb_counter_data
                    WHERE `Year`=:y AND `Month`=:m AND `Day`=:d
                ");
                $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
                $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dataRow) {
                    for ($h = 0; $h <= 23; $h++) {
                        $hourKey = sprintf('Hour%02d', $h);
                        $c = (int)($dataRow[$hourKey] ?? 0);
                        $hourMap[$h] = $c;
                        if ($c > $maxHourCnt) $maxHourCnt = $c;
                    }
                }
            }

            // nb_analytics의 time(TIME) 컬럼에서 HOUR(time)로 그룹핑
            $stmt = $pdo->prepare("
                SELECT HOUR(`time`) AS hh, COUNT(*) AS cnt
                FROM nb_analytics
                WHERE `year`=:y AND `month`=:m AND `day`=:d
                GROUP BY hh
            ");
            $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
            foreach ($stmt as $r) {
                $h = (int)$r['hh'];
                $c = (int)$r['cnt'];
                if ($h >= 0 && $h <= 23) {
                    $hourMap[$h] += $c;
                    if ($hourMap[$h] > $maxHourCnt) $maxHourCnt = $hourMap[$h];
                }
            }

            // nb_counter에서도 시간대별 집계
            $stmt = $pdo->prepare("
                SELECT `Hour`, COUNT(*) AS cnt
                FROM nb_counter
                WHERE `Year`=:y AND `Month`=:m AND `Day`=:d
                GROUP BY `Hour`
            ");
            $stmt->execute([':y' => $Select_Year, ':m' => $Select_Month, ':d' => $Select_Day]);
            foreach ($stmt as $r) {
                $h = (int)$r['Hour'];
                $c = (int)$r['cnt'];
                if ($h >= 0 && $h <= 23) {
                    // nb_counter_data에 없는 시간대만 추가
                    if ($hourMap[$h] == 0) {
                        $hourMap[$h] = $c;
                        if ($c > $maxHourCnt) $maxHourCnt = $c;
                    }
                }
            }
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
                                        <li class="no-breadcrumb-item"><span>시간별</span></li>
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
                                    <h3 class="no-admin-title">총방문자(선택일)</h3>
                                    <div class="no-admin-content">
                                        <span
                                            class="no-admin-cnt"><?= htmlspecialchars(number_format($TotalDay)) ?>명</span>
                                    </div>
                                </div>

                                <div class="no-admin-block no-w-80">
                                    <h3 class="no-admin-title">날짜선택</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <div class="no-search-wrap" style="gap:12px;display:flex;align-items:center;">
                                            <input type="date" name="sdate" id="s_date"
                                                value="<?= htmlspecialchars($curYMD) ?>">
                                            <div class="no-search-btn">
                                                <button type="submit"
                                                    class="no-btn no-btn--main no-btn--search">검색</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- /.no-card-body -->
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">시간별 접속통계 (<?= htmlspecialchars($curYMD) ?>)</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            시간, 접속수, 접속수 그래프, 접속수 퍼센트로 구성된 시간별 접속통계표
                                        </caption>
                                        <thead class="">
                                            <tr>
                                                <th scope="col" class="no-min-width-60">시간</th>
                                                <th scope="col" class="no-min-width-60">접속수</th>
                                                <th scope="col" class="no-min-width-150">접속수 그래프</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($h = 0; $h <= 23; $h++):
                                                $cnt = $hourMap[$h] ?? 0;
                                                $percentOfTotal = $TotalDay ? round(($cnt / $TotalDay) * 100, 2) : 0;
                                                $percentOfMax   = $maxHourCnt ? round(($cnt / $maxHourCnt) * 100, 2) : 0;
                                                $barWidth = max(1, $percentOfMax); // 최소 1%
                                                $isMax = ($cnt > 0 && $cnt === $maxHourCnt);
                                            ?>
                                            <tr>
                                                <td><span><?= sprintf('%02d', $h) ?>시</span></td>
                                                <td><span><?= htmlspecialchars(number_format($cnt)) ?>명</span></td>
                                                <td>
                                                    <div style="width:100%;background:#eee;height:8px;">
                                                        <div
                                                            style="width:<?= $barWidth ?>%;height:8px;<?= $isMax ? 'background:#0083e8;' : 'background:#ccc;' ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div> <!-- /.no-card-body -->
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>

</html>