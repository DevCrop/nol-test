<?php

// 입력 값 존재 여부를 확인하고 처리
if (isset($_REQUEST['pwd'])) {
    echo "SHA-256 Hash: " . hash("sha256", $_REQUEST['pwd']);
    echo "<br>";
} else {
    echo "Error: 'pwd' parameter is required.<br>";
}

if (isset($_REQUEST['base64'])) {
    echo "Base64 Encoded: " . base64_encode($_REQUEST['base64']);
} else {
    echo "Error: 'base64' parameter is required.<br>";
}

?>
