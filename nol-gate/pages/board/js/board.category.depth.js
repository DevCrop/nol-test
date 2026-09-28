
$(document).ready(function(){
	return; 
	var mode = $("#mode").val();
	var depth1 = $("#depth1").val();
	var depth2 = $("#depth2").val();
	var depth3 = $("#depth3").val();
	var depth4 = $("#depth4").val();

	if (mode.indexOf("edit") != -1) {
		
		getCategoryManual('big', '', depth1, 0, 0, 0);
		getCategoryManual('mid', '', depth1, depth2, 0, 0);
		getCategoryManual('sml', '', depth1, depth2, depth3, 0);
		getCategoryManual('itm', '', depth1, depth2, depth3, depth4);


	}else if (mode.indexOf("list") != -1) {
		
	
		if(depth1 == "" && depth2 == "" && depth3 == "" && depth4 == "")
			getCategory('big', '', '');
		else{
			getCategoryManual('big', '', depth1, 0, 0, 0);
			getCategoryManual('mid', '', depth1, depth2, 0, 0);
			getCategoryManual('sml', '', depth1, depth2, depth3, 0);
			getCategoryManual('itm', '', depth1, depth2, depth3, depth4);
		}

	}else{
		
		getCategory('big', '', '');

	}
});

function getCategory(kind, v, gb){

	var big = 0;
	var mid = 0;
	var sml = 0;
	var itm = 0;

	if(gb == null || gb == "")
		gb = "";

	var reqParam = "";

	if(kind == "big"){

		$("#"+gb+"category_big option").remove();

	}else if(kind == "mid") {

		$("#"+gb+"category_mid option").remove();

		big = $("#"+gb+"category_big").val();
		big = doCategoryExtraction(big);

	}else if(kind == "sml") {

		$("#"+gb+"category_sml option").remove();

		big = $("#"+gb+"category_big").val();
		mid = $("#"+gb+"category_mid").val();

		big = doCategoryExtraction(big);
		mid = doCategoryExtraction(mid);

	}else if(kind == "itm") {

		$("#"+gb+"category_itm option").remove();
		

		big = $("#"+gb+"category_big").val();
		big = doCategoryExtraction(big);

		mid = $("#"+gb+"category_mid").val();
		mid = doCategoryExtraction(mid);

		sml = $("#"+gb+"category_sml").val();
		sml = doCategoryExtraction(sml);

	} 

	$.ajax({
		type:"POST",
		url:"./ajax/category.depth.process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&mode=getCategory",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "success"){
				
				
					var title = "";
					if(kind == "big")
						title = "대분류";
					else if(kind == "mid")
						title = "중분류";
					else if(kind == "sml")
						title = "소분류";
					else if(kind == "itm")
						title = "상세분류";

					$("#"+gb+"category_"+kind).append("<option value=''>"+title+" 선택</option>");
					$.each(jsonData.rows, function(key,state){
						var disp = state.disp;
						var viewDisp = "on";
						if(disp == "Y"){
							$("#"+gb+"category_"+kind).append("<option value="+state.no+"^"+state.subject+">"+state.subject+"</option>");
						}
					});

			}else if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		},
		complete : function()
	   {
	   }
	}); 
}


function getCategoryManual(kind, gb, cbig, cmid, csml, citm){

	var big = 0;
	var mid = 0;
	var sml = 0;
	var itm = 0;

	if(gb == null || gb == "")
		gb = "";

	var reqParam = "";

	if(kind == "big"){

		$("#"+gb+"category_big option").remove();

	}else if(kind == "mid") {

		$("#"+gb+"category_mid option").remove();
		
		if(cbig > 0)
			big = cbig;
		else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}
		
		
	}else if(kind == "sml") {

		$("#"+gb+"category_sml option").remove();
		
	
		if(cbig > 0)
			big = cbig;
		else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}
		
		if(cmid > 0)
			mid = cmid;
		else{
			mid = $("#"+gb+"category_mid").val();
			mid = doCategoryExtraction(mid);
		}

	}else if(kind == "itm") {

		$("#"+gb+"category_itm option").remove();
		
		if(cbig > 0)
			big = cbig;
		else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}
		
		if(cmid > 0)
			mid = cmid;
		else{
			mid = $("#"+gb+"category_mid").val();
			mid = doCategoryExtraction(mid);
		}

		if(csml > 0)
			sml = csml;
		else{
			sml = $("#"+gb+"category_sml").val();
			sml = doCategoryExtraction(sml);
		}


	} 

	$.ajax({
		type:"POST",
		url:"./ajax/category.depth.process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&mode=getCategory",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "success"){
				
				var selno = 0;
				var title = "";
				if(kind == "big"){
					title = "대분류";
					selno = cbig;
				}else if(kind == "mid"){
					title = "중분류";
					selno = cmid;
				}else if(kind == "sml"){
					title = "소분류";
					selno = csml;
				}else if(kind == "itm"){
					title = "상세분류";
					selno = citm;
				}

				$("#"+gb+"category_"+kind).append("<option value=''>"+title+" 선택</option>");
				$.each(jsonData.rows, function(key,state){
					var disp = state.disp;
					var viewDisp = "on";
					var selected = "";
					if(state.no == selno)
						selected = "selected";


					if(disp == "Y"){
						$("#"+gb+"category_"+kind).append("<option value="+state.no+"^"+state.subject+" " + selected + ">"+state.subject+"</option>");
					}
				});

		


			}else if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		},
		complete : function()
	   {
	   }
	}); 
}



function doChangeCategory(v, kind, gb){
	getCategory(kind, v, gb);
}


function doCategoryExtraction(v){
return; 
	var value = 0;

	if(v != "" || v != null){
		
		var arr = v.split('^');
		value = arr[0];

	}

	return value;

}