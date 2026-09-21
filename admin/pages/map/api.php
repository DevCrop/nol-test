<?php

include_once "../../../inc/lib/base.class.php";
include_once '../../../inc/lib/core/autoload.php';

$map = new Map(); 

$data = array(
	'success' => false, 
	'message' => '처리중 문제가 발생했습니다.'
);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	 $method = isset($_POST['_method']) ? $_POST['_method'] : '';
	 


	  if($method === 'patch'){
		$is_success = $map->update($_GET['id'], $_POST);
		if($is_success){
		  $data['success'] = true; 
		  $data['message'] = '성공적으로 수정되었습니다.';

		} else {
		  $data['success'] = false; 
		  $data['message'] = '수정에 실패했습니다.';
		}
		

	  } else if($method === 'delete'){
		$is_success = $map->delete($_GET['id']);
		if($is_success){
		  $data['success'] = true; 
		  $data['message'] = '성공적으로 삭제되었습니다.';

		} else {
		  $data['success'] = false; 
		  $data['message'] = '삭제에 실패했습니다.';
		}


	  } else {
		$is_success = $map->insert($_POST);

			if($is_success){
			  $data['success'] = true; 
			  $data['message'] = '성공적으로 등록되었습니다.';

			} else {
			  $data['success'] = false; 
			  $data['message'] = '등록에 실패했습니다.';
			}
	  }

	  
}


echo json_encode($data); 