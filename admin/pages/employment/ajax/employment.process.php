<?


	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 if($mode == "delete"){
	
		$no		= $_POST[no];

		$query = "delete from nb_employment where no = $no";
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"파일 삭제에 실패했습니다.\"}";
		}
	
	
	}


?>