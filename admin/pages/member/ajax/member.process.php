<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 
	 if($mode == "save"){
		
		$campus					= xss_clean($_POST[campus]);
		$gubun						= xss_clean($_POST[gubun]);
		$grade						= xss_clean($_POST[grade]);

		$lev							= xss_clean($_POST[lev]);
		if(!$lev) $lev = 0;
		$uid							= xss_clean($_POST[uid]);
		$upwd						= xss_clean($_POST[pwd]);
		$upwd_confirm			= xss_clean($_POST[pwd_confirm]);
		$name_first				= xss_clean($_POST[name_first]);
		$name_last				= xss_clean($_POST[name_last]);
		$hp							= xss_clean($_POST[hp]);
		$email						= xss_clean($_POST[email]);
		$hp_recieve_yn			= xss_clean($_POST[hp_recieve_yn]);
		$email_recieve_yn		= xss_clean($_POST[email_recieve_yn]);
		
		$addr1						= xss_clean($_POST[addr1]);
		$child_name				= xss_clean($_POST[child_name]);

		$uname = $name_first.$name_last;


		if($upwd != $upwd_confirm){
			echo "{\"result\":\"fail\", \"msg\":\"패스워드와 확인이 일치하지 않습니다.\"}";
			exit;
		}

		$hashed = hash("sha256", $upwd);	

		$query = "select uid from nb_member
						where sitekey = '$NO_SITE_UNIQUE_KEY' and uid = '$uid' ";
		//echo $query;
		$result=mysql_query($query);
		$data=mysql_fetch_array($result);
		if($data){
			echo "{\"result\":\"fail\", \"msg\":\"이미 가입되어 있는 아이디입니다.\"}";
			exit;
		}

		
		$query = "insert into  nb_member (
						sitekey
						,lev
						,campus
						,gubun
						,grade
						,uid
						,upwd
						,uname
						,name_first
						,name_last
						,phone
						,hp
						,email
						,hp_recieve_yn
						,email_recieve_yn
						,addr1
						,regdate
						,child_name
					) 
					values
					(
						 '$NO_SITE_UNIQUE_KEY' 
						, $lev
						, $campus
						,'$gubun'
						,'$grade'
						,'$uid'
						,'$hashed'
						,'$uname'
						,'$name_first'
						,'$name_last'
						,'$phone'
						,'$hp'
						,'$email'
						,'$hp_recieve_yn'
						,'$email_recieve_yn'
						,'$addr1'
						,now()		
						,'$child_name'
		
					)";
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 가입 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}


	 }else  if($mode == "edit"){
		
		$no						= xss_clean($_POST[no]);
		$campus					= xss_clean($_POST[campus]);
		$gubun						= xss_clean($_POST[gubun]);
		$grade						= xss_clean($_POST[grade]);

		$lev							= xss_clean($_POST[lev]);
		if(!$lev) $lev = 0;
		$uid							= xss_clean($_POST[uid]);
		$upwd						= xss_clean($_POST[pwd]);
		$upwd_confirm			= xss_clean($_POST[pwd_confirm]);
		$name_first				= xss_clean($_POST[name_first]);
		$name_last				= xss_clean($_POST[name_last]);
		$hp							= xss_clean($_POST[hp]);
		$email						= xss_clean($_POST[email]);
		$hp_recieve_yn			= xss_clean($_POST[hp_recieve_yn]);
		$email_recieve_yn		= xss_clean($_POST[email_recieve_yn]);
		
		$addr1						= xss_clean($_POST[addr1]);
		$child_name				= xss_clean($_POST[child_name]);

		$uname = $name_first.$name_last;

		
		$hashed = hash("sha256", $upwd);	
	

		$query = "update nb_member set 
						lev = $lev
						,campus = $campus

						";
						if($upwd){
							$query.= ",upwd = '$hashed'";
						}

		$query.="	
						,gubun = '$gubun'
						,grade = '$grade'
						,uname = '$uname'
						,name_first = '$name_first'
						,name_last = '$name_last'
						,phone = '$phone'
						,hp = '$hp'
						,email = '$email'
						,addr1 = '$addr1'
						,child_name = '$child_name'
						 where no = $no";
		
					
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}


	 }else if($mode == "iddupcheck"){

		$uid	= $_POST[uid];

		$query = "select  uid from nb_member  
						where uid = '$uid' ";
		$result = mysql_query($query);
		$data=mysql_fetch_array($result);
		if($data){
			echo "{\"result\":\"fail\", \"msg\":\"\"}";	
		}else{
			echo "{\"result\":\"success\", \"msg\":\"\"}";
		}



	 }else if($mode == "delete"){
		
		$no		= $_POST[no];

		$query = "delete from nb_member where no = $no";
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"파일 삭제에 실패했습니다.\"}";
		}
	

	 }else if($mode == "levChange"){
	
		$lev	= $_POST[lev];
		$no	= $_POST[no];
		
		$query = "update nb_member set 
						lev = $lev
						where no = $no";
					
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
		
	
	}


?>