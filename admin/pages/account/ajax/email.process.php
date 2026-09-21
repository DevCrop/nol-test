<?php
// 별도 MFA 이메일 수정 경로 폐기. 최고관리자의 계정 생성/수정에서 이메일을 관리한다.
require_once '../../../../inc/lib/base.class.php';
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['result'=>'fail','message'=>'이메일은 계정 관리에서 변경하세요.']);
