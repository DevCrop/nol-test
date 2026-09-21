<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$connect = DB::getInstance(); // Get the PDO instance
$mode = $_POST['mode'] ?? null; // Use null coalescing operator for safety

if ($mode === "getCategory") {
    $kind = $_REQUEST["kind"] ?? null;
    $big = $_REQUEST["big"] ?? null;
    $mid = $_REQUEST["mid"] ?? null;
    $sml = $_REQUEST["sml"] ?? null;

    if ($big) {
        $bigArr = explode("^", $big);
        $big = $bigArr[0];
    }

    if ($mid) {
        $midArr = explode("^", $mid);
        $mid = $midArr[0];
    }

    if ($sml) {
        $smlArr = explode("^", $sml);
        $sml = $smlArr[0];
    }

    try {
        if ($kind === "big") {
            $query = "SELECT * FROM nb_category WHERE mid=0 AND sml=0 AND itm=0 AND kind='b' ORDER BY sort_no ASC";
            $stmt = $connect->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "result" => "success",
                "msg" => "데이터 로드 성공",
                "rows" => $rows
            ]);

        } elseif ($kind === "mid") {
            $query = "SELECT * FROM nb_category WHERE big = :big AND mid > 0 AND sml = 0 AND itm = 0 ORDER BY sort_no ASC";
            $stmt = $connect->prepare($query);
            $stmt->execute([':big' => $big]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "result" => "success",
                "msg" => "데이터 로드 성공",
                "rows" => $rows
            ]);

        } elseif ($kind === "sml") {
            $query = "SELECT * FROM nb_category WHERE big = :big AND mid = :mid AND sml > 0 AND itm = 0 ORDER BY sort_no ASC";
            $stmt = $connect->prepare($query);
            $stmt->execute([':big' => $big, ':mid' => $mid]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "result" => "success",
                "msg" => "데이터 로드 성공",
                "rows" => $rows
            ]);

        } elseif ($kind === "itm") {
            $query = "SELECT * FROM nb_category WHERE big = :big AND mid = :mid AND sml = :sml AND itm > 0 ORDER BY sort_no ASC";
            $stmt = $connect->prepare($query);
            $stmt->execute([':big' => $big, ':mid' => $mid, ':sml' => $sml]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "result" => "success",
                "msg" => "데이터 로드 성공",
                "rows" => $rows
            ]);
        } else {
            echo json_encode([
                "result" => "fail",
                "msg" => "유효하지 않은 kind입니다."
            ]);
        }

    } catch (Exception $e) {
        // Log the error message for debugging
        error_log("Error in getCategory: " . $e->getMessage());

        echo json_encode([
            "result" => "fail",
            "msg" => ClientFault::message($e),
        ]);
    }
}
?>
