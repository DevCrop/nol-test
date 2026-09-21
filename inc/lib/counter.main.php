<?php

$ToDay_Year = date("Y");
$ToDay_Month = date("m");
$ToDay_Day = date("d");

$db = DB::getInstance(); // PDO 인스턴스 가져오기

// 전체 방문자 수 조회
$stmt = $db->prepare("SELECT SUM(Visit_Num) as CDSV FROM nb_counter_data WHERE Year = ? AND Month = ?");
$stmt->execute([$ToDay_Year, $ToDay_Month]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$TotalVisit = $data['CDSV'] ?? 0;

// 오늘 방문자 수 조회
$stmt = $db->prepare("SELECT SUM(Visit_Num) as CDSV FROM nb_counter_data WHERE Year = ? AND Month = ? AND Day = ?");
$stmt->execute([$ToDay_Year, $ToDay_Month, $ToDay_Day]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$TotalVisitToday = $data['CDSV'] ?? 0;
