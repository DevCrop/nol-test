<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$db = DB::getInstance();
$mode = $_POST['mode'] ?? '';

header('Content-Type: application/json');

// 업로드 경로 설정
$uploadDir = $UPLOAD_DIR_WORKS;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    $sanitizedNote = sanitize_html_fragment($_POST['note'] ?? null);
    $sanitizedContentHtml = sanitize_html_fragment($_POST['content_html'] ?? null);
    $sanitizedSeatPrices = sanitize_html_fragment($_POST['seat_prices'] ?? null);
    $sanitizedPosterLongHtml = sanitize_html_fragment($_POST['poster_long_html'] ?? null);
    $sanitizedTicketUrl = safe_url($_POST['ticket_url'] ?? null, ['http', 'https'], true) ?: null;
    // 썸네일 이미지 업로드 처리
    $thumb_image = null;
    if (isset($_FILES['thumb_image']) && $_FILES['thumb_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = imageUpload($uploadDir, $_FILES['thumb_image']);
        if (!empty($uploadResult['saved'])) {
            $thumb_image = $UPLOAD_WDIR_WORKS . '/' . $uploadResult['saved'];
        }
    }

    if ($mode === 'insert') {
        $stmt = $db->prepare("
            INSERT INTO nb_works (
                title, subtitle, venue, genre, start_date, end_date,
                running_time, age_rating, inquiry, note, content_html,
                seat_prices, poster_long_html, ticket_url, thumb_image, is_published, sort_order
            ) VALUES (
                :title, :subtitle, :venue, :genre, :start_date, :end_date,
                :running_time, :age_rating, :inquiry, :note, :content_html,
                :seat_prices, :poster_long_html, :ticket_url, :thumb_image, :is_published, :sort_order
            )
        ");
        
        $stmt->execute([
            ':title' => $_POST['title'] ?? '',
            ':subtitle' => $_POST['subtitle'] ?? null,
            ':venue' => !empty($_POST['venue']) ? (int)$_POST['venue'] : null,
            ':genre' => !empty($_POST['genre']) ? (int)$_POST['genre'] : null,
            ':start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            ':end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            ':running_time' => $_POST['running_time'] ?? null,
            ':age_rating' => $_POST['age_rating'] ?? null,
            ':inquiry' => $_POST['inquiry'] ?? null,
            ':note' => $sanitizedNote ?: null,
            ':content_html' => $sanitizedContentHtml ?: null,
            ':seat_prices' => $sanitizedSeatPrices ?: null,
            ':poster_long_html' => $sanitizedPosterLongHtml ?: null,
            ':ticket_url' => $sanitizedTicketUrl,
            ':thumb_image' => $thumb_image,
            ':is_published' => isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1,
            ':sort_order' => isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
        ]);
        
        $id = $db->lastInsertId();
        AuditLogger::record('create', 'works', $id, (string) ($_POST['title'] ?? $id));
        echo json_encode(['success' => true, 'message' => '등록되었습니다.', 'id' => $id]);
        
    } elseif ($mode === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            throw new Exception('ID가 필요합니다.');
        }
        
        // 기존 thumb_image 조회
        $oldStmt = $db->prepare("SELECT thumb_image FROM nb_works WHERE id = :id");
        $oldStmt->execute([':id' => $id]);
        $oldWork = $oldStmt->fetch(PDO::FETCH_ASSOC);
        $oldThumbImage = $oldWork['thumb_image'] ?? null;
        
        // 새 이미지가 업로드된 경우에만 업데이트
        $thumbImageValue = $thumb_image ?? $oldThumbImage;
        
        // 새 이미지가 업로드되었고 기존 이미지가 있으면 삭제
        if ($thumb_image && $oldThumbImage && $oldThumbImage !== $thumb_image) {
            $oldImagePath = $NO_PROJECT_ROOT . '/' . ltrim($oldThumbImage, '/');
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        $stmt = $db->prepare("
            UPDATE nb_works SET
                title = :title,
                subtitle = :subtitle,
                venue = :venue,
                genre = :genre,
                start_date = :start_date,
                end_date = :end_date,
                running_time = :running_time,
                age_rating = :age_rating,
                inquiry = :inquiry,
                note = :note,
                content_html = :content_html,
                seat_prices = :seat_prices,
                poster_long_html = :poster_long_html,
                ticket_url = :ticket_url,
                thumb_image = :thumb_image,
                is_published = :is_published,
                sort_order = :sort_order
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':id' => $id,
            ':title' => $_POST['title'] ?? '',
            ':subtitle' => $_POST['subtitle'] ?? null,
            ':venue' => !empty($_POST['venue']) ? (int)$_POST['venue'] : null,
            ':genre' => !empty($_POST['genre']) ? (int)$_POST['genre'] : null,
            ':start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            ':end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            ':running_time' => $_POST['running_time'] ?? null,
            ':age_rating' => $_POST['age_rating'] ?? null,
            ':inquiry' => $_POST['inquiry'] ?? null,
            ':note' => $sanitizedNote ?: null,
            ':content_html' => $sanitizedContentHtml ?: null,
            ':seat_prices' => $sanitizedSeatPrices ?: null,
            ':poster_long_html' => $sanitizedPosterLongHtml ?: null,
            ':ticket_url' => $sanitizedTicketUrl,
            ':thumb_image' => $thumbImageValue,
            ':is_published' => isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1,
            ':sort_order' => isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
        ]);
        
        AuditLogger::record('update', 'works', $id, (string) ($_POST['title'] ?? $id));
        echo json_encode(['success' => true, 'message' => '수정되었습니다.']);
        
    } elseif ($mode === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            throw new Exception('ID가 필요합니다.');
        }
        
        $stmt = $db->prepare("DELETE FROM nb_works WHERE id = :id");
        $stmt->execute([':id' => $id]);
        AuditLogger::record('delete', 'works', $id, (string) $id);
        echo json_encode(['success' => true, 'message' => '삭제되었습니다.']);
        
    } elseif ($mode === 'delete_array') {
        $rawIds = $_POST['ids'] ?? [];
        $ids = is_array($rawIds) ? $rawIds : json_decode((string)$rawIds, true);

        if (!is_array($ids) || empty($ids)) {
            throw new Exception('Invalid IDs.');
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            throw new Exception('Invalid IDs.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM nb_works WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        AuditLogger::record('delete', 'works', 0, count($ids) . '건', ['ids' => $ids]);
        echo json_encode(['success' => true, 'message' => 'Deleted selected items.']);
        
    } elseif ($mode === 'sort') {
        $id = (int)($_POST['id'] ?? 0);
        $sort_order = isset($_POST['new_no'])
            ? (int)$_POST['new_no']
            : (int)($_POST['sort_order'] ?? 0);
        
        if (!$id) {
            throw new Exception('ID가 필요합니다.');
        }
        
        $stmt = $db->prepare("UPDATE nb_works SET sort_order = :sort_order WHERE id = :id");
        $stmt->execute([':id' => $id, ':sort_order' => $sort_order]);
        AuditLogger::record('update', 'works', $id, (string) $id, ['sort_order' => $sort_order]);
        echo json_encode(['success' => true, 'message' => '순서가 변경되었습니다.']);
    } else {
        throw new Exception('잘못된 요청입니다.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => ClientFault::message($e)]);
}

