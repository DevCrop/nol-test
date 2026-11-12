<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$pdo = DB::getInstance();
header('Content-Type: application/json; charset=utf-8');
$mode = (string) ($_POST['mode'] ?? '');

function validateRequestSchedule(string $title,string $start,string $end): void {
    if(trim($title)==='')throw new RuntimeException('일정 제목을 입력하세요.');
    foreach([$start,$end]as $raw){$date=DateTimeImmutable::createFromFormat('!Y-m-d',$raw);if(!$date||$date->format('Y-m-d')!==$raw)throw new RuntimeException('노출 날짜를 YYYY-MM-DD 형식으로 입력하세요.');}
    if($end<$start)throw new RuntimeException('종료일은 시작일보다 빠를 수 없습니다.');
}

function normalizeRequestNoticeHtml($html) {
    if (!is_string($html) || $html === '') {
        return $html;
    }

    // Fix invalid nested double quotes from pasted rich text, e.g. font-family: "Helvetica Neue";
    $html = preg_replace('/font-family:\s*"([^"]+)";/i', "font-family: '$1';", $html);
    $html = preg_replace('/\sclass=(["\'])MsoNormal\1/i', '', $html);
    $html = preg_replace_callback('/\sstyle=(["\'])(.*?)\1/i', function ($matches) {
        $style = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $declarations = array_filter(array_map('trim', explode(';', $style)));
        $allowed = [];

        foreach ($declarations as $declaration) {
            if (preg_match('/^font-size\s*:\s*[^;]+$/i', $declaration)) {
                $allowed[] = $declaration;
            }
        }

        if (empty($allowed)) {
            return '';
        }

        return ' style="' . htmlspecialchars(implode('; ', $allowed) . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"';
    }, $html);

    return \Security\HtmlSanitizer::clean($html);
}


if ($mode == "delete") {
    try {
        $no = filter_var($_POST['no'] ?? null, FILTER_VALIDATE_INT);

        if (!$no) {
            echo json_encode(["result" => "fail", "msg" => "삭제할 항목이 없습니다."]);
            exit;
        }

        (new \Security\RentalRequestService($pdo, $NO_SITE_UNIQUE_KEY))->delete((int) $no);
        echo json_encode(['result' => 'success', 'msg' => '정상적으로 삭제되었습니다.']);
    } catch (Exception $e) {
        error_log('request delete failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}


// manage 관리
if ($mode == 'manageSave') {
    try {
        if (!empty($_POST['no'])) throw new RuntimeException('새 일정 등록에는 기존 번호를 지정할 수 없습니다.');
        $no = null;
        $r_view = in_array($_POST['r_view'] ?? '', ['Y','N'], true) ? $_POST['r_view'] : 'N';
        $r_title = trim((string) ($_POST['r_title'] ?? ''));
        $r_sdate = (string) ($_POST['r_sdate'] ?? '');
        $r_edate = (string) ($_POST['r_edate'] ?? '');
		$contents = normalizeRequestNoticeHtml((string) ($_POST['contents'] ?? ''));

      $query = "INSERT INTO nb_request_manage (sitekey, no, r_view, r_title, r_sdate, r_edate, contents) 
          VALUES (:sitekey, :no, :r_view, :r_title, :r_sdate, :r_edate, :contents)
          ";

	validateRequestSchedule($r_title,$r_sdate,$r_edate);
	$stmt = $pdo->prepare($query);

	$result = $stmt->execute([
		'sitekey' => $NO_SITE_UNIQUE_KEY, // $NO_SITE_UNIQUE_KEY는 적절한 값으로 설정
		'no' => $no,
		'r_view' => $r_view,
		'r_title' => $r_title,
		'r_sdate' => $r_sdate,
		'r_edate' => $r_edate,
		'contents' => $contents,
	]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."])
            : json_encode(["result" => "fail", "msg" => "저장에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('request manage save failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}


// manage 관리
if ($mode == "manageDelete") {
    try {
        $no = filter_var($_POST['no'] ?? null, FILTER_VALIDATE_INT);

        if (!$no) {
            echo json_encode(["result" => "fail", "msg" => "삭제할 항목이 없습니다."]);
            exit;
        }

        $query = "DELETE FROM nb_request_manage WHERE no = :no AND sitekey = :sitekey";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute(['no' => $no, 'sitekey' => $NO_SITE_UNIQUE_KEY]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."])
            : json_encode(["result" => "fail", "msg" => "삭제에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('request manage delete failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}


// manage 관리
if ($mode == 'manageEdit') {
    try {
        $no = filter_var($_POST['no'] ?? null, FILTER_VALIDATE_INT);
        if(!$no||$no<1)throw new RuntimeException('수정할 일정을 선택하세요.');
        $exists=$pdo->prepare('SELECT no FROM nb_request_manage WHERE no=? AND sitekey=?');$exists->execute([$no,$NO_SITE_UNIQUE_KEY]);
        if(!$exists->fetchColumn())throw new RuntimeException('일정 정보를 찾을 수 없습니다.');
        $r_view = in_array($_POST['r_view'] ?? '', ['Y','N'], true) ? $_POST['r_view'] : 'N';
        $r_title = trim((string) ($_POST['r_title'] ?? ''));
        $r_sdate = (string) ($_POST['r_sdate'] ?? '');
        $r_edate = (string) ($_POST['r_edate'] ?? '');
		$contents = normalizeRequestNoticeHtml((string) ($_POST['contents'] ?? ''));



        // UPDATE 쿼리
        validateRequestSchedule($r_title,$r_sdate,$r_edate);
        $query = "UPDATE nb_request_manage 
                  SET r_view = :r_view, 
                      r_title = :r_title, 
                      r_sdate = :r_sdate, 
                      r_edate = :r_edate,
					  contents = :contents
				  WHERE no = :no AND sitekey = :sitekey";

        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            'no' => $no,
            'sitekey' => $NO_SITE_UNIQUE_KEY,
            'r_view' => $r_view,
            'r_title' => $r_title,
            'r_sdate' => $r_sdate,
            'r_edate' => $r_edate,
			'contents' => $contents
        ]);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 수정되었습니다."])
            : json_encode(["result" => "fail", "msg" => "수정에 실패했습니다."]);
    } catch (Exception $e) {
        error_log('request manage edit failed: ' . blue_safe_error($e)); echo json_encode(["result" => "fail", "msg" => "요청을 처리할 수 없습니다."]);
    }
}





?>
