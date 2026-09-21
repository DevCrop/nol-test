<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 
	if($mode == "delete"){
		
		$no	= $_POST[no];

		$query = " select 
							photo
							,portfolio1
							,portfolio2
							,portfolio3
							,portfolio4
							from nb_admission
						where no = $no ";

		$result=mysql_query($query);
		$data=mysql_fetch_array($result);
		if(!$data){
			error("정보를 찾을 수 없습니다");
		}

		$photo			= $data[photo];
		$portfolio1		= $data[portfolio1];
		$portfolio2		= $data[portfolio2];
		$portfolio3		= $data[portfolio3];
		$portfolio4		= $data[portfolio4];


		if($photo){
			imageDelete($UPLOAD_DIR_ADMISSION."/".$photo);
		}

		if($portfolio1){
			imageDelete($UPLOAD_DIR_ADMISSION."/".$portfolio1);
		}

		if($portfolio2){
			imageDelete($UPLOAD_DIR_ADMISSION."/".$portfolio2);
		}

		if($portfolio3){
			imageDelete($UPLOAD_DIR_ADMISSION."/".$portfolio3);
		}

		if($portfolio4){
			imageDelete($UPLOAD_DIR_ADMISSION."/".$portfolio4);
		}


		$query = "delete from nb_admission where no = $no";
		$result = mysql_query($query);



		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"파일 삭제에 실패했습니다.\"}";
		}
	

	 }


?>