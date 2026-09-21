<?php

// 데이터베이스 연결 (연결 정보를 적절히 설정하세요)

/*
$mysql = $connect; 

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// 인젝션 방어 - 재귀적으로 stripslashes를 적용하는 함수
function stripslashes_deep($var) {
    return is_array($var) ? array_map('stripslashes_deep', $var) : stripslashes($var);
}

// mysqli_real_escape_string을 재귀적으로 적용하는 함수
function escape_input_deep($var, $mysqli) {
    return is_array($var) ? array_map(function($v) use ($mysqli) {
        return escape_input_deep($v, $mysqli);
    }, $var) : $mysqli->real_escape_string($var);
}

// POST와 GET 데이터에서 매직 쿼트를 제거하고, 실질적인 인젝션 방어를 적용
$_POST = is_array($_POST) ? array_map(function($v) use ($mysqli) {
    return escape_input_deep($v, $mysqli);
}, stripslashes_deep($_POST)) : stripslashes_deep($_POST);

$_GET = is_array($_GET) ? array_map(function($v) use ($mysqli) {
    return escape_input_deep($v, $mysqli);
}, stripslashes_deep($_GET)) : stripslashes_deep($_GET);
*/
?>
