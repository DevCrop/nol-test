<?php


function getCampus($no){
	global $NO_SITE_UNIQUE_KEY;
	$where = "";
	if($no)
		$where = " and no = $no";
	$query = "select  no, sitekey, name, sort_no from nb_campus
					where sitekey = '$NO_SITE_UNIQUE_KEY' $where order by sort_no asc";
			
	$result=mysql_query($query);
	$r = array(); 
	while($row = mysql_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysql_free_result( $result );
	}
	return $r;

}



?>