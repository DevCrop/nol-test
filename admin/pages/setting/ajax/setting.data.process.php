<?php
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$pdo = DB::getInstance();
	$mode = $_POST['mode'];

	if ($mode == "save") {
		$target = $_POST['target'];
		$contents = $_POST['content'];

		// Check if data already exists
		$query = "SELECT a.no FROM nb_data a WHERE a.sitekey = :sitekey AND a.target = :target";
		$stmt = $pdo->prepare($query);
		$stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY, 'target' => $target]);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($data) {
			echo json_encode(["result" => "fail", "msg" => "이미 데이터가 등록되어 있습니다."]);
		} else {
			// Insert new data
			$query = "INSERT INTO nb_data (sitekey, target, contents, regdate) VALUES (:sitekey, :target, :contents, NOW())";
			$stmt = $pdo->prepare($query);
			$result = $stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY, 'target' => $target, 'contents' => $contents]);

			if ($result) {
				echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
			} else {
				echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
			}
		}

	} else if ($mode == "edit") {
		$no = $_POST['no'];
		$target = $_POST['target'];
		$contents = $_POST['content'];

		// Update data
		$query = "UPDATE nb_data SET target = :target, contents = :contents WHERE no = :no AND sitekey = :sitekey";
		$stmt = $pdo->prepare($query);
		$result = $stmt->execute(['target' => $target, 'contents' => $contents, 'no' => $no, 'sitekey' => $NO_SITE_UNIQUE_KEY]);

		if ($result) {
			echo json_encode(["result" => "success", "msg" => "정상적으로 수정 되었습니다."]);
		} else {
			echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
		}
	} else if ($mode == 'delete') {
		$no = $_POST['no']; 
		$query = "delete from nb_data where no = :no";
		$stmt = $pdo->prepare($query); 
		$result = $stmt->execute(array('no' => $no)); 
		
		if ($result) {
			echo json_encode(["result" => "success", "msg" => "정상적으로 삭제 되었습니다."]);
		} else {
			echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
		}
	}
?>
