<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 
	 if($mode == "save"){
		

		$lev_name				= xss_clean($_POST[lev_name]);

		$query = "insert into nb_member_level(sitekey, lev_name) values ('$NO_SITE_UNIQUE_KEY', '$lev_name') ";
		//echo $query;
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}


	 }else if($mode == "edit"){
		
		$no						= xss_clean($_POST[no]);
		$lev_name				= xss_clean($_POST[lev_name]);
		$is_join					= xss_clean($_POST[is_join]);
		if(!$is_join) $is_join = "N";
		if($is_join == "Y"){
			$query = "update nb_member_level set is_join = 'N' where sitekey = '$NO_SITE_UNIQUE_KEY'"; 
			//echo $query;
			$result = mysql_query($query);
		}

		$query = "update nb_member_level set lev_name = '$lev_name' , is_join = '$is_join' where  no = $no ";
		//echo $query;
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}

	 }else if($mode == "delete"){
		
		$no		= $_POST[no];

		$query = "delete from nb_member_level where no = $no";
		$result = mysql_query($query);

		$query = "delete from nb_board_lev_manage where sitekey = '$NO_SITE_UNIQUE_KEY' and lev_no = $no";
		$result = mysql_query($query);
		

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"파일 삭제에 실패했습니다.\"}";
		}
	

	 }


?>