<?php

$db = DB::getInstance(); // PDO 인스턴스 가져오기

// 과도한 접속 IP 차단
if (in_array($_SERVER["REMOTE_ADDR"], ["116.125.142.203"])) {
    exit;
}

$Counter_ON = "N";
$_COUNTER_CHECKER = parse_url($_SERVER["HTTP_REFERER"] ?? '');

// 이전 페이지가 본 사이트가 아니어야 함
if (!preg_match("/" . $_SERVER["HTTP_HOST"] . "/i", $_COUNTER_CHECKER["host"] ?? '')) {

    // nb_counter_config 테이블에서 카운터 설정 정보 가져오기
    $stmt = $db->query("SELECT * FROM nb_counter_config");
    $counter_config_row = $stmt->fetch(PDO::FETCH_ASSOC);

    // nb_counter_data 테이블에서 방문자 수 합산
    $stmt = $db->query("SELECT IFNULL(SUM(Visit_Num), 0) as Visit_Num_sum FROM nb_counter_data");
    $counter_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $Total_Num = $counter_result['Visit_Num_sum'];

    $CoIP = $_SERVER["REMOTE_ADDR"];
    $CoRoute = $_SERVER["HTTP_REFERER"] ?? '';
    $CoKinds = $_SERVER["HTTP_USER_AGENT"] ?? '';
    $CoKinds = preg_replace("/\)/i", "", $CoKinds);
    $CoKindsDivision = explode(";", $CoKinds);
    $CoKinds_Browser = $CoKindsDivision[1] ?? '';
    $CoKinds_OS = $CoKindsDivision[2] ?? '';
    $ToDay_Year = date("Y");
    $ToDay_Month = date("m");
    $ToDay_Day = date("d");
    $ToDay_Hour = date("H");
    $ToDay_Week = date("D");
    $ToDay_Time = time();

    if ($counter_config_row['Cookie_Use'] == "O") {
        $temp1 = date('Y-m-d', $_SESSION['nb_counter_Term2'] ?? 0);
        $temp2 = date('Y-m-d');

        if ($temp1 != $temp2) {
            $Counter_ON = "Y";
            $_SESSION['nb_counter_Term2'] = $ToDay_Time;
        }
    }

    if ($counter_config_row['Admin_Check_Use'] == "N" && $counter_config_row['Admin_IP'] == $CoIP) {
        $Counter_ON = "N";
    }

    // 로봇인지 체크
    $isRobot = preg_match("/googlebot|yahoo|naver/i", strtolower($CoKinds_Browser)) || preg_match("/googlebot|yahoo|naver/i", strtolower($CoKinds_OS));

    // 카운터 차단 아이피
    $count_deny_ip = ["222.122.78.16", "222.122.78.15", "118.130.232.254", "61.111.15"];
    if (in_array($CoIP, $count_deny_ip)) {
        $isRobot = true;
    }

    if ($Counter_ON == "Y" && !$isRobot) {
        $Total_Num++;

        if ($counter_config_row['Counter_Use'] == "Y") {
            if ($_POST['referer'] ?? false) {
                $CoRoute .= "&referer=" . $_POST['referer'];
            }

            // nb_counter 테이블에 방문 기록 추가
            $stmt = $db->prepare("INSERT INTO nb_counter (Connect_IP, Time, Year, Month, Day, Hour, Week, Connect_Route) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$CoIP, $ToDay_Time, $ToDay_Year, $ToDay_Month, $ToDay_Day, $ToDay_Hour, $ToDay_Week, $CoRoute]);

            // nb_counter_data 테이블에서 오늘 날짜의 데이터 개수 확인
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM nb_counter_data WHERE Year = ? AND Month = ? AND Day = ?");
            $stmt->execute([$ToDay_Year, $ToDay_Month, $ToDay_Day]);
            $data_result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data_result['cnt'] > 0) {
                $stmt = $db->prepare("UPDATE nb_counter_data SET Hour$ToDay_Hour = Hour$ToDay_Hour + 1, Visit_Num = Hour00 + Hour01 + Hour02 + Hour03 + Hour04 + Hour05 + Hour06 + Hour07 + Hour08 + Hour09 + Hour10 + Hour11 + Hour12 + Hour13 + Hour14 + Hour15 + Hour16 + Hour17 + Hour18 + Hour19 + Hour20 + Hour21 + Hour22 + Hour23 WHERE Year = ? AND Month = ? AND Day = ?");
                $stmt->execute([$ToDay_Year, $ToDay_Month, $ToDay_Day]);
            } else {
                $stmt = $db->prepare("INSERT INTO nb_counter_data (Year, Month, Day, Hour$ToDay_Hour, Week, Visit_Num) VALUES (?, ?, ?, 1, ?, 1)");
                $stmt->execute([$ToDay_Year, $ToDay_Month, $ToDay_Day, $ToDay_Week]);
            }

            // nb_counter_route 테이블에서 방문 경로 확인
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM nb_counter_route WHERE Connect_Route = ?");
            $stmt->execute([$CoRoute]);
            $route_result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($route_result['cnt'] > 0) {
                $stmt = $db->prepare("UPDATE nb_counter_route SET Time = ?, Visit_Num = Visit_Num + 1 WHERE Connect_Route = ?");
                $stmt->execute([$ToDay_Time, $CoRoute]);
            } else {
                $stmt = $db->prepare("INSERT INTO nb_counter_route (Connect_Route, Time, Visit_Num) VALUES (?, ?, 1)");
                $stmt->execute([$CoRoute, $ToDay_Time]);
            }
        }
    }

    // nb_counter_config 테이블에서 Total_Num 업데이트
    $stmt = $db->prepare("UPDATE nb_counter_config SET Total_Num = ? WHERE uid = 1");
    $stmt->execute([$Total_Num]);
}
?>
