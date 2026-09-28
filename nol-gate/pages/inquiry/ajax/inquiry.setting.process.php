<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
require_once dirname(__DIR__, 3) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$pdo = DB::getInstance();
$mode = $_POST['mode'];

if ($mode == "rental.setting.save") {
    $rental_is_open = isset($_POST['rental_is_open']) ? (int)$_POST['rental_is_open'] : 0;
    $rental_start_date = isset($_POST['rental_start_date']) && !empty($_POST['rental_start_date']) ? $_POST['rental_start_date'] : null;
    $rental_end_date = isset($_POST['rental_end_date']) && !empty($_POST['rental_end_date']) ? $_POST['rental_end_date'] : null;
    $rental_notice = isset($_POST['rental_notice']) ? $_POST['rental_notice'] : '';
    $rental_apply_notice = isset($_POST['rental_apply_notice']) ? $_POST['rental_apply_notice'] : '';

    // 유효성 검사
    if ($rental_is_open == 1 && $rental_start_date && $rental_end_date) {
        if (strtotime($rental_start_date) > strtotime($rental_end_date)) {
            echo json_encode([
                "result" => "fail",
                "msg" => "시작일은 종료일보다 이전이어야 합니다."
            ]);
            exit;
        }
    }

    // 닫기 상태일 때는 기간 초기화
    if ($rental_is_open == 0) {
        $rental_start_date = null;
        $rental_end_date = null;
    }

    // 기존 레코드 확인
    $stmt = $pdo->prepare("SELECT no FROM nb_siteinfo WHERE sitekey = :sitekey");
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // 업데이트 (rental_notice, rental_apply_notice 포함)
        $query = "UPDATE nb_siteinfo SET 
                    rental_is_open = :rental_is_open,
                    rental_start_date = :rental_start_date,
                    rental_end_date = :rental_end_date,
                    rental_notice = :rental_notice,
                    rental_apply_notice = :rental_apply_notice
                  WHERE sitekey = :sitekey";
        try {
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([
                ':rental_is_open' => $rental_is_open,
                ':rental_start_date' => $rental_start_date,
                ':rental_end_date' => $rental_end_date,
                ':rental_notice' => $rental_notice,
                ':rental_apply_notice' => $rental_apply_notice,
                ':sitekey' => $NO_SITE_UNIQUE_KEY
            ]);
        } catch (PDOException $e) {
            // rental_apply_notice 등 컬럼 미존재 시 컬럼 제외하고 재시도
            $query = "UPDATE nb_siteinfo SET 
                        rental_is_open = :rental_is_open,
                        rental_start_date = :rental_start_date,
                        rental_end_date = :rental_end_date,
                        rental_notice = :rental_notice
                      WHERE sitekey = :sitekey";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([
                ':rental_is_open' => $rental_is_open,
                ':rental_start_date' => $rental_start_date,
                ':rental_end_date' => $rental_end_date,
                ':rental_notice' => $rental_notice,
                ':sitekey' => $NO_SITE_UNIQUE_KEY
            ]);
        }

        echo json_encode([
            "result" => $result ? "success" : "fail",
            "msg" => $result ? "정상적으로 저장되었습니다." : "저장 중 오류가 발생했습니다."
        ]);
        if ($result) {
            AuditLogger::record('update', 'inquiry', (int) ($data['no'] ?? 0), '대관 신청 설정', [
                'rental_is_open' => $rental_is_open,
            ]);
        }
    } else {
        echo json_encode([
            "result" => "fail",
            "msg" => "사이트 정보를 찾을 수 없습니다."
        ]);
    }
}

