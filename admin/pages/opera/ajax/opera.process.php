<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$pdo = DB::getInstance();
$mode = (string) ($_POST['mode'] ?? '');
header('Content-Type: application/json; charset=utf-8');

function operaInput(): array {
    $state = filter_var($_POST['state'] ?? null, FILTER_VALIDATE_INT);
    $title = trim((string) ($_POST['title'] ?? ''));
    $link = trim((string) ($_POST['link'] ?? ''));
    $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));
    if (!in_array($state, [0,1], true) || $title === '' || strlen($title) > 255 || ($link !== '' && (!filter_var($link, FILTER_VALIDATE_URL) || !in_array($scheme, ['http','https'], true)))) {
        throw new InvalidArgumentException('invalid input');
    }
    return [$state, $title, $link];
}

if ($mode == 'save') {
    try {
        [$state, $title, $link] = operaInput();

        // INSERT query
        $query = "
            INSERT INTO nb_opera (state, title, link) 
            VALUES (:state, :title, :link)
        ";

        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            'state' => $state,
            'title' => $title,
            'link' => $link,
        ]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 저장되었습니다."])
            : json_encode(["result" => "fail", "msg" => "저장에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('opera save failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}

if ($mode == 'edit') {
    try {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        [$state, $title, $link] = operaInput();

        if (!$id) {
            echo json_encode(["result" => "fail", "msg" => "수정할 항목의 ID가 없습니다."]);
            exit;
        }

        // UPDATE query
        $query = "
            UPDATE nb_opera
            SET 
                state = :state,
                title = :title,
                link = :link
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            'id' => $id,
            'state' => $state,
            'title' => $title,
            'link' => $link,
        ]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 수정되었습니다."])
            : json_encode(["result" => "fail", "msg" => "수정에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('opera edit failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}

if ($mode == 'delete') {
    try {
        $no = filter_var($_POST['no'] ?? null, FILTER_VALIDATE_INT);

        if (!$no) {
            echo json_encode(["result" => "fail", "msg" => "삭제할 항목이 없습니다."]);
            exit;
        }

        $query = "DELETE FROM nb_opera WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute(['id' => $no]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."])
            : json_encode(["result" => "fail", "msg" => "삭제에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('opera delete failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}
?>
