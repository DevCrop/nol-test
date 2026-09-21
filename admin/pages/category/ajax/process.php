<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST[mode];

	 if($mode == "getCategory"){
		
		$kind	= $_REQUEST["kind"];
		$big	= $_REQUEST["big"];
		$mid	= $_REQUEST["mid"];
		$sml	= $_REQUEST["sml"];

		if($big){
			$bigArr = explode("^", $big);
			$big = $bigArr[0];
		}

		if($mid){
			$midArr = explode("^", $mid);
			$mid = $midArr[0];
		}

		if($sml){
			$smlArr = explode("^", $sml);
			$sml = $smlArr[0];
		}

		if($kind == "big"){
		
			$query = " select * from nb_category where mid=0 and sml=0 and itm=0 and kind='b' order by sort_no asc";

			$result=mysql_query($query);
			//echo $query;

			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";


		}else if($kind == "mid"){
			

			$query = " select * from nb_category where big= $big and mid>0 and sml = 0 and itm = 0 order by sort_no asc";

			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}else if($kind == "sml"){
			

			$query = " select * from nb_category where big= $big and mid = $mid and sml > 0 and itm = 0 order by sort_no asc";

			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}else if($kind == "itm"){
			

			$query = " select * from nb_category where big= $big and mid = $mid and sml = $sml and itm > 0 order by sort_no asc";

			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}

	 }else if($mode == "setCategoryInsert"){
		

		$big					= $_REQUEST['big'];
		$mid					= $_REQUEST['mid'];
		$sml					= $_REQUEST['sml'];
		$kind					= $_REQUEST['kind'];
		$categoryName	= $_REQUEST['categoryName'];
		
		$query = "select ifnull((max(no)+1),1) as maxcnt from nb_category 
						where sitekey = '$NO_SITE_UNIQUE_KEY' ";
		//echo $query;
		$result=mysql_query($query);
		$data=mysql_fetch_array($result);
		if(!$data){
			error("정보를 찾을 수 없습니다");
		}

		$maxcnt = $data[maxcnt];


		if($kind == "big"){

			$query = "select ifnull((max(sort_no)+1),1) as sort_no from nb_category 
						where sitekey = '$NO_SITE_UNIQUE_KEY' and kind = 'b'";
			$result=mysql_query($query);
			$data=mysql_fetch_array($result);
			if(!$data){
				error("정보를 찾을 수 없습니다");
			}

			$sort_no = $data[sort_no];

			$query = "insert into  nb_category (no, sitekey, big, mid, sml, itm, kind, subject, sort_no, regdate
						) 
						values
						(
							$maxcnt
							, '$NO_SITE_UNIQUE_KEY'
							, $maxcnt
							, 0
							, 0
							, 0
							,'b'
							,'$categoryName'
							, $sort_no
							, now())";
			
			$result = mysql_query($query);
		

		}else if($kind == "mid"){

			$bigArr = explode("^", $big);
			$big = $bigArr[0];

			$query = "select ifnull((max(sort_no)+1),1) as sort_no from nb_category 
						where sitekey = '$NO_SITE_UNIQUE_KEY' and big = $big and kind = 'm'";
			$result=mysql_query($query);
			$data=mysql_fetch_array($result);
			if(!$data){
				error("정보를 찾을 수 없습니다");
			}

			$sort_no = $data[sort_no];

		
			$query = "insert into  nb_category (no, sitekey, big, mid, sml, itm, kind, subject, sort_no, regdate
						) 
						values
						(
							$maxcnt
							, '$NO_SITE_UNIQUE_KEY'
							, $big
							, $maxcnt
							, 0
							, 0
							,'m'
							,'$categoryName'
							, $sort_no
							, now())";
			
			$result = mysql_query($query);


		}else if($kind == "sml"){

			$bigArr = explode("^", $big);
			$big = $bigArr[0];

			$midArr = explode("^", $mid);
			$mid = $midArr[0];

			$query = "select ifnull((max(sort_no)+1),1) as sort_no from nb_category 
						where sitekey = '$NO_SITE_UNIQUE_KEY' and big = $big and mid = $mid and kind = 's'";
			$result=mysql_query($query);
			$data=mysql_fetch_array($result);
			if(!$data){
				error("정보를 찾을 수 없습니다");
			}

			$sort_no = $data[sort_no];

		
			$query = "insert into  nb_category (no, sitekey, big, mid, sml, itm, kind, subject, sort_no, regdate
						) 
						values
						(
							$maxcnt
							, '$NO_SITE_UNIQUE_KEY'
							, $big
							, $mid
							, $maxcnt
							, 0
							,'s'
							,'$categoryName'
							, $sort_no
							, now())";
			
			$result = mysql_query($query);


		}else if($kind == "itm"){

			$bigArr = explode("^", $big);
			$big = $bigArr[0];

			$midArr = explode("^", $mid);
			$mid = $midArr[0];

			$smlArr = explode("^", $sml);
			$sml = $smlArr[0];

			$query = "select ifnull((max(sort_no)+1),1) as sort_no from nb_category 
						where sitekey = '$NO_SITE_UNIQUE_KEY' and big = $big and mid = $mid and sml = $sml and kind = 'i'";
			$result=mysql_query($query);
			$data=mysql_fetch_array($result);
			if(!$data){
				error("정보를 찾을 수 없습니다");
			}

			$sort_no = $data[sort_no];

		
			$query = "insert into  nb_category (no, sitekey, big, mid, sml, itm, kind, subject, sort_no, regdate
						) 
						values
						(
							$maxcnt
							, '$NO_SITE_UNIQUE_KEY'
							, $big
							, $mid
							, $sml
							, $maxcnt
							,'i'
							,'$categoryName'
							, $sort_no
							, now())";
			
			$result = mysql_query($query);


		}

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 등록되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}


	 
	 }else if($mode == "setCategoryUpdate"){
	 
	 	$no					= $_REQUEST[no];
		$big					= $_REQUEST[big];
		$kind					= $_REQUEST[kind];
		$categoryName	= $_REQUEST[categoryName];

		$query = "update nb_category set subject = '$categoryName' where no = $no";
		$result = mysql_query($query);
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
	 
	 }else if($mode == "setCategoryDelete"){
		
		$no					= $_REQUEST[no];
		$kind					= $_REQUEST[kind];
		
		$noArr = explode("^", $no);

	
		if($kind == "big"){
			$query = "delete from nb_category where no = $noArr[0]";
			$result = mysql_query($query);

			$query = "delete from nb_category where big = $noArr[0]";
			$result = mysql_query($query);

		}else if($kind == "mid"){

			$query = "delete from nb_category where no = $noArr[0]";
			$result = mysql_query($query);

			$query = "delete from nb_category where mid = $noArr[0]";
			$result = mysql_query($query);
			
		}else if($kind == "sml"){

			$query = "delete from nb_category where no = $noArr[0]";
			$result = mysql_query($query);

			$query = "delete from nb_category where sml = $noArr[0]";
			$result = mysql_query($query);
			
		}else if($kind == "itm"){

			$query = "delete from nb_category where no = $noArr[0]";
			$result = mysql_query($query);

			
		}
		
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
	 
	 }else if($mode == "setCategoryDisplay"){
	 
		$no					= $_REQUEST[no];
		$kind					= $_REQUEST[kind];
		$act					= $_REQUEST[act];

		$display_yn = "N";

		if($act == "show")
			$display_yn = "Y";

		$noArr = explode("^", $no);

		$query = "update nb_category set disp = '$display_yn' where no = $noArr[0]";
		$result = mysql_query($query);
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 변경 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
	 
	 }else if($mode == "setCategoryChange"){
	 
		$data					= $_REQUEST[data];
		$kind					= $_REQUEST[kind];
	 
		if (strpos($data,":") !== false){
			
			$noArr = explode(":", $data);
			$i=1;

			foreach($noArr as $no){
		
				$arrDat = explode("^", $no);
				$query = "update nb_category set sort_no = $i where no = $arrDat[0]";
		
				$result = mysql_query($query);

				$i++;
			}


		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";exit;
		}

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 변경 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
	 
	 }

?>