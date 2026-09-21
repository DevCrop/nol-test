
$(document).ready(function(){
	getCategory('big');
});

function getCategory(kind){

	var big = 0;
	var mid = 0;
	var sml = 0;
	var itm = 0;

	var reqParam = "";

	if(kind == "big"){

		$("#category_big").children().remove();
		$("#category_mid").children().remove();
		$("#category_sml").children().remove();
		$("#category_itm").children().remove();

	}else if(kind == "mid") {

		$("#category_mid").children().remove();
		big = $("#category_big > option:selected").val();

	}else if(kind == "sml") {

		$("#category_sml").children().remove();
		$("#category_itm").children().remove();

		big = $("#category_big > option:selected").val();
		mid = $("#category_mid > option:selected").val();

	}else if(kind == "itm") {

		$("#category_itm").children().remove();

		big = $("#category_big > option:selected").val();
		mid = $("#category_mid > option:selected").val();
		sml = $("#category_sml > option:selected").val();

	} 

	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&mode=getCategory",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "success"){
				$.each(jsonData.rows, function(key,state){
					var disp = state.disp;
					var viewDisp = "on";
					if(disp == "N")
						viewDisp = "off";
					$("#category_"+kind).append("<option value="+state.no+"^"+state.subject+">"+state.subject+" ["+viewDisp+"]</option>");
				});
			}else if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		}
	}); 
}




function sortChange(kind, act){

	if($('#category_'+kind+' > option:selected').val()){
		var selectedDx;
		if(act == "up"){
			selectedDx = $("#category_" + kind).find("option:selected");
	        var prev = selectedDx.first().prev();
	        selectedDx.each(function() {
	            $(this).insertBefore(prev);
	        });
		}else if(act == "down"){
			 selectedDx = $("#category_" + kind).find("option:selected");
	         var next = selectedDx.last().next();
	         selectedDx.each(function() {
	            $(this).insertAfter(next);
	         });
		}
	}else{
		alert("변경할 대상을 선택해주세요");
		return;
	}
	
}

function sortChangeUpdate(kind){
	var arrData;
	var cnt=0;
	$("#category_"+kind+" option").each(function(){  
		var data = $(this).val();
		if(cnt==0)
			arrData = data;
		else
			arrData += ":"+data;
		
		cnt++;
	});

	if(cnt < 2){
		alert("카테고리가 1개 이상 필요합니다.");
		return;
	}
	
	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&data="+arrData+"&mode=setCategoryChange",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				alert(jsonData.msg);
				getCategory(kind);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		}
	}); 
	
}

function doDelete(kind){

	if($('#category_'+kind+' > option:selected').val()){

		var con = confirm("현재 카테고리에 속한 모든 데이터가 삭제됩니다.\n진행하시겠습니까?");
		if(!con)
			return;
		
		var cnt=0;
		var no;
		$("#category_"+kind+" option:selected").each(function(){  
			no = $(this).val();
			cnt++;
		});
	
		if(cnt > 1){
			alert("삭제할 대상은 1개만 선택할 수 있습니다.");
			return;
		}

		$.ajax({
			type:"POST",
			url:"./ajax/process.php",
			cache: false,
			data:"kind="+kind+"&no="+no+"&mode=setCategoryDelete",
			dataType:"html",
			success:function(data){
				var jsonData = $.parseJSON(data);
				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					getCategory(kind);
				}
			},
			error:function(a,s){
				alert("err : "+s);
			}
		}); 
		
	}else{
		alert("삭제할 대상을 선택해주세요");
		return;
	}
}

function doDisplay(kind, act){
	if($('#category_'+kind+' > option:selected').val()){
		var cnt=0;
		var no;
		$("#category_"+kind+" option:selected").each(function(){  
			no = $(this).val();
			cnt++;
		});
	
		if(cnt > 1){
			alert("변경 대상은 1개만 선택할 수 있습니다.");
			return;
		}

		$.ajax({
			type:"POST",
			url:"./ajax/process.php",
			cache: false,
			data:"kind="+kind+"&no="+no+"&act="+act+"&mode=setCategoryDisplay",
			dataType:"html",
			success:function(data){
				var jsonData = $.parseJSON(data);
				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					getCategory(kind);
				}
			},
			error:function(a,s){
				alert("err : "+s);
			}
		}); 
		
	}else{
		alert("변경할 대상을 선택해주세요");
		return;
	}	
}

function add(kind){

	if(kind == "mid"){
		$("#big").val($("#category_big > option:selected").val());
	}else if(kind == "sml"){	
		$("#big").val($("#category_big > option:selected").val());
		$("#mid").val($("#category_mid > option:selected").val());
	}else if(kind == "itm"){	
		$("#big").val($("#category_big > option:selected").val());
		$("#mid").val($("#category_mid > option:selected").val());
		$("#sml").val($("#category_sml > option:selected").val());
	}

	$("#act").val("add");
	$("#kind").val(kind);

	$(".no-modal__wrap").show();

	
}


function closeAddBox(){
	$("#categoryName").val("");
	$("#kind").val("");
	$("#big").val("");
	$(".no-modal__wrap").hide();
}


function doAddSave(){
	if($("#act").val() == "add")
		addProc();
	else if($("#act").val() == "edit")
		editProc();
}

function addProc(){

	if( $("#kind").val() == "mid"){
		if(!$('#category_big > option:selected').val()){
			alert("1단계(대분류)를 선택해주세요");
			return;
		}
	}else if( $("#kind").val() == "sml"){
		if(!$('#category_big > option:selected').val()){
			alert("1단계(대분류)를 선택해주세요");
			return;
		}

		if(!$('#category_mid > option:selected').val()){
			alert("2단계(중분류)를 선택해주세요");
			return;
		}
	}else if( $("#kind").val() == "itm"){
		if(!$('#category_big > option:selected').val()){
			alert("1단계(대분류)를 선택해주세요");
			return;
		}

		if(!$('#category_mid > option:selected').val()){
			alert("2단계(중분류)를 선택해주세요");
			return;
		}

		if(!$('#category_mid > option:selected').val()){
			alert("3단계(소분류)를 선택해주세요");
			return;
		}
	}

	if($("#categoryName").val() == ""){
		alert("카테고리명을 입력해주세요");
		$("#categoryName").focus();
		return;
	}

	var categoryName = "";
	var kind = "";
	var big = "";
	var mid = "";
	var sml = "";

	categoryName = $("#categoryName").val();
	kind = $("#kind").val();
	big = $("#big").val();
	mid = $("#mid").val();
	sml = $("#sml").val();
	
	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&categoryName="+categoryName+"&mode=setCategoryInsert",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				alert(jsonData.msg);
				getCategory(kind);
				closeAddBox();
			}
		},
		error:function(a,s){
			alert("err : "+s);
		}
	}); 
	
}

function edit(kind){

	if(!$('#category_'+kind+' > option:selected').val()){
		alert("수정하실 대상을 선택해주세요");
		return;
	}

	var cnt=0;
	var no;
	var text;
	$("#category_"+kind+" option:selected").each(function(){  
		no = $(this).val();
		if(cnt==1)return;
		cnt++;
	});

	if(cnt > 1){
		alert("수정 대상은 1개만 선택할 수 있습니다.");
		return;
	}

	var arrtxt = no.split("^");
	text = arrtxt[1];
	no = arrtxt[0];
	$("#act").val("edit");
	$("#categoryName").val(text);
	$("#no").val(no);
	$("#kind").val(kind);
	$(".no-modal__wrap").show();
	
}

function editProc(){

	if($("#categoryName").val() == ""){
		alert("카테고리명을 입력해주세요");
		$("#categoryName").focus();
		return;
	}

	var categoryName = "";
	var kind = "";
	var big = "";
	var no=0;

	categoryName = $("#categoryName").val();
	kind = $("#kind").val();
	big = $("#big").val();
	no = $("#no").val();
	
	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&no="+no+"&categoryName="+categoryName+"&mode=setCategoryUpdate",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				alert(jsonData.msg);
				getCategory(kind);
				closeAddBox();
			}
		},
		error:function(a,s){
			alert("err : "+s);
		}
	}); 
	
}
