<?php

$root = $_SERVER['DOCUMENT_ROOT'];

include_once $root . "/inc/lib/base.class.php";
include_once $root . "/admin/lib/admin.check.ajax.php";

$uploadDir = $root.'/uploads/works';

$allowedFields = [
    'title',
    'genre',
    'start_date',
    'end_date',
    'show_time',
    'place',
    'is_featured',
    'notes',
    'contents',
    'price',
    'running_time',
    'viewing_age',
    'contact',
    'opera_glass_link',
    'ticket_link',
    'subtitle',
    'thumbnail_image',
    'tablet_image',
    'poster_image',
    'detail_page_top_image',
    'mobile_poster_image',
    'created_at',
];

function setupDefaultValue($data) {
    foreach ($data as $k => $v) {
        if (is_null($v) || $v === '') {
            $data[$k] = null;
        } elseif (in_array($v, ['on', '1', 'yes', 'true', true], true)) {
            $data[$k] = 1;
        } elseif (in_array($v, ['off', '0', 'no', 'false', false], true)) {
            $data[$k] = 0;
        }
    }
    return $data;
}

$requiredFields = [
    'title' => '제목',
    'genre' => '장르',
    'start_date' => '공연 시작일',
    'end_date' => '공연 종료일',
];

function validate($requiredFields) {
    foreach ($requiredFields as $field => $label) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            header('Content-Type: application/json');
            ob_clean(); // Prevent unwanted output
            echo json_encode([
                'success' => false,
                'message' => "{$label}란을 입력해주세요.",
            ]);
            exit;
        }
    }
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$method = isset($_REQUEST['_method']) ? strtoupper($_REQUEST['_method']) : $_SERVER['REQUEST_METHOD'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false, 'message'=>'상태 변경은 POST 요청만 허용됩니다.']);
    exit;
}

$data = [
    'success' => false,
    'data' => null,
    'message' => '',
];

if (!in_array($method, ['GET', 'POST', 'UPDATE', 'DELETE', 'BATCH_DELETE'])) {
    $data['message'] = '잘못된 요청입니다.';
    ob_clean();
    echo json_encode($data);
    exit;
}

if ($method === 'GET') {
    if ($id) {
        $sql = "SELECT * FROM nb_works WHERE id = :id";
        $result = DB::query($sql, ['id' => $id]);
        if ($result) {
            $data['success'] = true;
            $data['data'] = $result;
        } else {
            $data['message'] = "No data found for ID $id.";
        }
    } else {
        $sql = "SELECT * FROM nb_works";
        $result = DB::query($sql);
        $data['success'] = true;
        $data['data'] = $result;
    }
}

if ($method === 'POST') {
    validate($requiredFields);

    $fields = array_intersect_key($_POST, array_flip($allowedFields));
    $fields['start_date'] = date("Y-m-d", strtotime($fields['start_date']));
    $fields['end_date'] = date("Y-m-d", strtotime($fields['end_date']));

    foreach (['thumbnail_image', 'tablet_image', 'detail_page_top_image', 'poster_image', 'mobile_poster_image'] as $imgField) {
        $fields[$imgField] = uploadImage($imgField, $uploadDir);
    }

    $fields = setupDefaultValue($fields);

    $keys = implode(", ", array_keys($fields));
    $placeholders = ":" . implode(", :", array_keys($fields));
    $sql = "INSERT INTO nb_works ($keys) VALUES ($placeholders)";
    $success = DB::query($sql, $fields);
    
    $data['success'] = $success;
    $data['message'] = $success ? "성공적으로 등록되었습니다." : "등록에 실패했습니다.";
}

if ($method === 'UPDATE') {
    if ($id) {
        validate($requiredFields);

        $sql = "SELECT * FROM nb_works WHERE id = :id";
        $dbData = DB::query($sql, ['id' => $id]);

        if ($dbData) {
            $dbData = $dbData[0];
            $fields = array_intersect_key($_POST, array_flip($allowedFields));
            $fields['start_date'] = date("Y-m-d", strtotime($fields['start_date']));
            $fields['end_date'] = date("Y-m-d", strtotime($fields['end_date']));

            foreach (['thumbnail_image', 'tablet_image', 'detail_page_top_image', 'poster_image', 'mobile_poster_image'] as $imgField) {
                $fields[$imgField] = hasImage($imgField) ? uploadImage($imgField, $uploadDir) : $dbData[$imgField];
            }

            $fields['created_at'] = isset($_POST['created_at']) && strtotime($_POST['created_at'])
                ? $_POST['created_at']
                : $dbData['created_at'];

            $fields['updated_at'] = date("Y-m-d H:i:s");

            $setClause = implode(", ", array_map(function ($field) {
                return "$field = :$field";
            }, array_keys($fields)));

            $fields['id'] = $id;
            $sql = "UPDATE nb_works SET $setClause WHERE id = :id";
            $success = DB::query($sql, $fields);

            $data['success'] = $success;
            $data['message'] = $success ? "업데이트에 성공했습니다." : "업데이트에 실패했습니다.";
        }
    }
}

if ($method === 'BATCH_DELETE') {
    $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];
    $ids = array_filter($ids, function ($id) {
        return is_numeric($id);
    });

    if (empty($ids)) {
        $data['message'] = "유효하지 않은 ID가 포함되어 있습니다.";
        ob_clean();
        echo json_encode($data);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM nb_works WHERE id IN ($placeholders)";
    $success = DB::query($sql, $ids);

    $data['success'] = $success;
    $data['message'] = $success ? "선택된 항목이 성공적으로 삭제되었습니다." : "삭제에 실패했습니다.";
}

if ($method === 'DELETE') {
    if ($id) {
        $sql = "DELETE FROM nb_works WHERE id = :id";
        $success = DB::query($sql, ['id' => $id]);

        $data['success'] = $success;
        $data['message'] = $success ? "정상적으로 삭제되었습니다." : "삭제에 실패했습니다.";
    }
}

header('Content-Type: application/json');
ob_clean();
echo json_encode($data);
exit;
