<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$db = DB::getInstance();
$mode = $_POST['mode'] ?? '';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($mode === 'insert') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $apply_date = $_POST['apply_date'] ?? null;

        if (empty($title)) {
            throw new Exception('제목을 입력해주세요.');
        }

        if (empty($apply_date)) {
            throw new Exception('적용 날짜를 선택해주세요.');
        }

        $stmt = $db->prepare("
            INSERT INTO nb_privacy_policy (title, content, apply_date, created_at, updated_at)
            VALUES (:title, :content, :apply_date, NOW(), NOW())
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':apply_date' => $apply_date
        ]);
        
        $id = $db->lastInsertId();
        AuditLogger::record('create', 'privacy', $id, (string) $title);
        echo json_encode(['success' => true, 'message' => '등록되었습니다.', 'id' => $id]);
        
    } elseif ($mode === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $apply_date = $_POST['apply_date'] ?? null;
        
        if (!$id) {
            throw new Exception('ID가 필요합니다.');
        }

        if (empty($title)) {
            throw new Exception('제목을 입력해주세요.');
        }

        if (empty($apply_date)) {
            throw new Exception('적용 날짜를 선택해주세요.');
        }
        
        $stmt = $db->prepare("
            UPDATE nb_privacy_policy SET
                title = :title,
                content = :content,
                apply_date = :apply_date,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':apply_date' => $apply_date
        ]);
        
        AuditLogger::record('update', 'privacy', $id, (string) $title);
        echo json_encode(['success' => true, 'message' => '수정되었습니다.']);
        
    } elseif ($mode === 'delete') {
        // 단일 삭제
        if (isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            if (!$id) {
                throw new Exception('ID가 필요합니다.');
            }
            
            $stmt = $db->prepare("DELETE FROM nb_privacy_policy WHERE id = :id");
            $stmt->execute([':id' => $id]);
            AuditLogger::record('delete', 'privacy', $id, (string) $id);
            echo json_encode(['success' => true, 'message' => '삭제되었습니다.']);
        }
        // 다중 삭제
        elseif (isset($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
            $ids = array_filter($ids);
            
            if (empty($ids)) {
                throw new Exception('삭제할 항목을 선택해주세요.');
            }
            
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("DELETE FROM nb_privacy_policy WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            AuditLogger::record('delete', 'privacy', 0, count($ids) . '건', ['ids' => $ids]);
            echo json_encode(['success' => true, 'message' => count($ids) . '개의 항목이 삭제되었습니다.']);
        } else {
            throw new Exception('삭제할 항목을 선택해주세요.');
        }
        
    } else {
        throw new Exception('잘못된 요청입니다.');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => ClientFault::message($e)]);
}


