<?php


/**
 * Check if an image exists in the request, if it was successfully uploaded, and if its size is greater than 0.
 *
 * @param string $key
 * @param array|null $files
 * @return bool
 */
function hasImage(string $key, ?array $files = null): bool
{
    $files = $files ?? $_FILES;

    // Check if the file exists, has no errors, and its size is greater than 0
    if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK && $files[$key]['size'] > 0) {
        return true;
    }

    return false;
}

/**
 * Upload an image to the server.
 *
 * @param string $key
 * @param string|null $uploadDir
 * @param array|null $allowedExtensions
 * @param int $maxFileSize
 * @param array|null $files
 * @return string|null
 * @throws RuntimeException
 */

function uploadImage(string $key, string $uploadDir = null, ?array $allowedExtensions = null, int $maxFileSize = 10485760, ?array $files = null): ?string
{

    $files = $files ?? $_FILES;
    $uploadDir = $uploadDir ?? $_SERVER['DOCUMENT_ROOT'] . '/uploads';
    if (!isset($files[$key]) || $files[$key]['error'] !== UPLOAD_ERR_OK) {
		return null;
    }

    $file = $files[$key];
    try { [$fileExtension] = \Security\UploadGuard::image($file); }
    catch (Throwable $e) { return null; }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
		return null;
        //throw new RuntimeException("Failed to create upload directory: {$uploadDir}.");
    }

    $uniqueFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $destination = rtrim($uploadDir, '/') . '/' . $uniqueFileName;
	
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
		return null;
        //throw new RuntimeException("Failed to move uploaded file to destination: {$destination}.");
    }

	
    return $uniqueFileName;
}

/**
 * Check if an image exists on the server.
 *
 * @param string $fileName
 * @param string|null $folder
 * @return bool
 */
function existImage(string $fileName, string $folder = null): bool
{
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads';
    $folder = $folder ? rtrim($folder, '/') : '';
    $filePath = $baseDir . ($folder ? "/{$folder}" : '') . '/' . ltrim($fileName, '/');

    return \Security\UploadGuard::pathInside($baseDir, ltrim(($folder ? $folder . '/' : '') . $fileName, '/')) !== null;
}

/**
 * Delete an image from the server.
 *
 * @param string $fileName
 * @param string|null $folder
 * @return bool
 */
function deleteImage(string $fileName, string $folder = null): bool
{
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads';
    $folder = $folder ? rtrim($folder, '/') : '';
    $relative = ltrim(($folder ? $folder . '/' : '') . $fileName, '/');
    $filePath = \Security\UploadGuard::pathInside($baseDir, $relative);
    return $filePath !== null && unlink($filePath);
}

// 페이지 이동
function location($go_url) {
    echo "<script>document.location='$go_url';</script>";
    exit;
}

// 에러 출력
function error($msg, $go_url = "") {
    if (empty($go_url)) {
        echo "<script>alert('$msg');history.go(-1);</script>";
    } else {
        echo "<script>alert('$msg');document.location='$go_url';</script>";
    }
    exit;
}

// 에러 출력 후 닫기
function error_close($msg) {
    echo "<script>alert('$msg');self.close();</script>";
    exit;
}

// 경고창 출력
function alert($msg, $go_url = "") {
    if (empty($go_url)) {
        echo "<script>alert('$msg');history.go(-1);</script>";
    } else {
        echo "<script>alert('$msg');document.location='$go_url';</script>";
    }
}

function getBanner($loc, $limit = 1, $return_type = 'html') {
    global $NO_SITE_UNIQUE_KEY;
    
    // Initialize an empty array to store the results
    $r = array();

    // Define the query with placeholders for parameterized statements
    $query = "
        SELECT * 
        FROM nb_banner 
        WHERE sitekey = :sitekey 
          AND b_loc = :loc 
          AND b_view = 'Y' 
          AND ((b_sdate <= CURDATE() AND b_edate >= CURDATE()) OR b_none_limit = 'Y') 
        ORDER BY b_idx ASC, no ASC 
        LIMIT :limit
    ";

    // Get the PDO instance
    $db = DB::getInstance();

    // Prepare the statement
    $stmt = $db->prepare($query);

    // Bind parameters with appropriate data types
    $stmt->bindParam(':sitekey', $NO_SITE_UNIQUE_KEY, PDO::PARAM_STR);
    $stmt->bindParam(':loc', $loc, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    // Fetch all results into an associative array
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results array
    return $r;
}


function getFeaturedWorks($limit = 10) {
    // Initialize an empty array to store results
    $r = array();

    // Define the query
    $query = "
        SELECT * 
        FROM nb_works 
        WHERE is_featured = 1
        ORDER BY start_date ASC 
        LIMIT :limit
    ";

    // Get the PDO instance
    $db = DB::getInstance();

    // Prepare the statement
    $stmt = $db->prepare($query);

    // Bind the limit parameter
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch all results as an associative array
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results array
    return $r;
}



// 경고창만 출력
function alertonly($msg) {
    echo "<script>alert('$msg');</script>";
}

// 완료 메시지 출력
function complete($com_msg, $go_url = "") {
    if (empty($go_url)) {
        echo "<script>window.setTimeout('history.go(-1)', 600);</script>";
    } else {
        echo "<script>window.setTimeout('document.location=\"$go_url\";', 600);</script>";
    }

    echo "<body><table width=100% height=100%><tr><td align=center><font size=2>$com_msg</font></td></tr></table></body>";
}

// 체크박스 체크확인 리턴
function chkPrint($val, $target) {
    return $val == $target ? "checked" : "";
}

function selectedPrint($val, $target) {
    return $val == $target ? "selected" : "";
}

// 문자열 끊기 (길이가 초과되면 '...'로 표시)
function cut_str($msg, $cut_size) {
    return mb_strimwidth($msg, 0, $cut_size, '...');
}

// 인젝션 방지 치환
function safeStr($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 난수 생성
function getRndCode($len) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
    $pass = '';
    while ($len--) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

// xss_clean (XSS 방지 필터링)
function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// D-DAY 계산
function getDday($target) {
    $end_date = strtotime($target);
    return floor(($end_date - strtotime(date('Y-m-d'))) / 86400);
}

// 시간 경과 계산
function time_ago($date) {
    if (empty($date)) return "No date provided";
    $periods = ["초", "분", "시간", "일", "주", "개월", "년", "10년"];
    $lengths = [60, 60, 24, 7, 4.35, 12, 10];
    $now = time();
    $unix_date = strtotime($date);
    if (!$unix_date) return "Bad date";
    
    $tense = $now > $unix_date ? "전" : "후";
    $difference = abs($now - $unix_date);

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);

    return "$difference{$periods[$j]} $tense";
}

// 파일 확장자 체크
function file_check($filename, $file_str = "php|htm|html|inc|shtm|ztx|dot|cgi|pl|exe") {
    $fext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $file_str = strtolower($file_str);
    if (preg_match("/\b$fext\b/", $file_str)) {
        error("해당 파일은 업로드할 수 없는 형식입니다.");
    }
}


// 특수문자 제거
function doRemoveSpecial($str) {
    return preg_replace("/[ #&+\-%@=\/\\\:;,'\"\^`~_|!\?\*$#<>()\[\]\{\}]/i", "", $str);
}

// URL에서 링크 추출
function makeLinks($str, $target) {
    return preg_replace_callback('/(http|https):\/\/[^\s]+/i', function($matches) use ($target) {
        $url = $matches[0];
        return "<a href=\"$url\" $target>$url</a>";
    }, $str);
}

// 이미지 업로드

function imageUpload($path, $upfile, $origin_file = '', $return_origin = false, $allowed_ext = null) {
    $max_file_size = 20971520;
    if (empty($upfile) || !isset($upfile['name']) || $upfile['error'] === UPLOAD_ERR_NO_FILE) {
        return ['origin' => '', 'saved' => ''];
    }
    try {
        [$ext] = \Security\UploadGuard::attachment($upfile, $max_file_size);
        if (is_array($allowed_ext) && !in_array($ext, array_map('strtolower', $allowed_ext), true)) throw new RuntimeException('허용되지 않는 파일 형식입니다.');
        $file_saved = bin2hex(random_bytes(20)) . ".{$ext}";
        $file_origin = basename((string) $upfile['name']);
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException('파일 업로드 경로를 생성하지 못했습니다.');
        }
        if (!move_uploaded_file($upfile['tmp_name'], $path . DIRECTORY_SEPARATOR . $file_saved)) throw new RuntimeException('파일 업로드에 실패했습니다.');
        if ($origin_file) {
            $old = \Security\UploadGuard::pathInside($path, basename((string) $origin_file));
            if ($old !== null) unlink($old);
        }
        return $return_origin ? ['origin' => $file_origin, 'saved' => $file_saved] : ['origin' => '', 'saved' => $file_saved];
    } catch (Throwable $e) {
        $message = $e instanceof RuntimeException ? $e->getMessage() : blue_safe_error($e);
        echo json_encode(["result" => "fail", "msg" => $message]);
        exit;
    }
}

function imageDelete(string $path): bool
{
    $root = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/uploads');
    $candidate = realpath($path);
    if ($root === false || $candidate === false || strpos($candidate, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($candidate)) return false;
    return unlink($candidate);
}



// 페이지 리스트 출력
function print_pagelist($page, $list_amount, $page_count, $param, $page_type = "") {
    global $code, $catcode, $orderby, $skin_dir, $ptype;
    $skin_dir = $skin_dir ?: "/admin/manage";
    $param = $param ? "&$param" : "";

    $spage = floor(($page - 1) / $list_amount) * $list_amount + 1;
    $epage = min($spage + $list_amount - 1, $page_count);
    $ppage = max($spage - $list_amount, 1);
    $npage = min($epage + 1, $page_count);
    $page_name = $page_type === "C" ? "cpage" : "page";

    if ($epage > 0) {
        echo "<div class='pagination'>";
        echo "<a href='?ptype=$ptype&$page_name=1$param'>◀◀</a>";
        echo "<a href='?ptype=$ptype&$page_name=$ppage$param'>◀</a>";
        for ($i = $spage; $i <= $epage; $i++) {
            echo $i === $page ? "<strong>$i</strong>" : "<a href='?ptype=$ptype&$page_name=$i$param'>$i</a>";
        }
        echo "<a href='?ptype=$ptype&$page_name=$npage$param'>▶</a>";
        echo "<a href='?ptype=$ptype&$page_name=$page_count$param'>▶▶</a>";
        echo "</div>";
    }
}

// 메일 발송 함수
function sendMailer($_email, $_from, $_sitename, $_title, $_content, $cc = []) {
    $headers = "Content-Type: text/html; charset=utf-8\r\n";
    if (!preg_match("/@daum.net|@hamail.net/i", $_email)) {
        $headers .= "From: =?UTF-8?B?" . base64_encode($_sitename) . "?= <$_from>\r\n";
    }
    $headers .= "Return-Path: $_from\r\n";
    if ($cc) $headers .= "Cc: " . implode(", ", $cc) . "\r\n";

    $_title = '=?UTF-8?B?' . base64_encode($_title) . '?=';
    return mail($_email, $_title, $_content, $headers, "-f $_from");
}
