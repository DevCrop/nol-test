<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 if($mode == "save"){
		
	
		$campus			= xss_clean($_POST[campus]);
		$cdate				= xss_clean($_POST[cdate]);
		$title					= xss_clean($_POST[title]);
		$contents			= $_POST[contents];
		
		$query = "insert into  nb_academic_calendar (sitekey, campus, cdate, title, contents, regdate ) 
					values
					(
						'$NO_SITE_UNIQUE_KEY'
						,$campus
						,'$cdate'
						,'$title'
						,'$contents'
						,now()
					)
						";
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 등록되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}


	}else if($mode == "edit"){

		$no					= xss_clean($_POST[no]);
		$campus			= xss_clean($_POST[campus]);
		$cdate				= xss_clean($_POST[cdate]);
		$title					= xss_clean($_POST[title]);
		$contents			= $_POST[contents];
		
		$query = "update nb_academic_calendar set 
						campus = $campus
						,cdate = '$cdate'
						,title = '$title'
						,contents = '$contents'
						where no = $no";
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
			
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}

	}else if($mode == "delete"){
	
		$no = $_POST[no];

		$query = "delete from nb_academic_calendar where no = $no";
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"파일 삭제에 실패했습니다.\"}";
		}
	
	
	}