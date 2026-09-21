function doRegSave(){

	if($(".p_none_limit").val() == "N"){
		
		if($("#p_sdate").val() == "" || $("#p_edate").val() == ""){
			alert("노출 기간을 선택해주세요.");
			$("#b_sdate").focus();
			return;
		}
	
	}

	if($("#p_img").val() == ""){
		alert("팝업 이미지를 선택 등록해주세요");
		$("#p_img").focus();
		return;
	}

	if($("#p_title").val() == ""){
		alert("제목을 입력해주세요");
		$("#p_title").focus();
		return;
	}



	if($(".p_loc:checked").val() == "P"){
		
		if($("#p_left").val() == ""){
			alert("왼쪽 좌표 위치를 입력해주세요");
			$("#p_left").focus();
			return;
		}

		if($("#p_top").val() == ""){
			alert("상단 좌표 위치를 입력해주세요");
			$("#p_top").focus();
			return;
		}

	
	}
		

	$("#mode").val("save");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/popup.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = $.parseJSON(data);

			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				alert(jsonData.msg);
				location.href = './popup.list.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
			$("#process-loading").hide();
		}
	});




}



function doEditSave(){

	if($(".p_none_limit").val() == "N"){
		
		if($("#p_sdate").val() == "" || $("#p_edate").val() == ""){
			alert("노출 기간을 선택해주세요.");
			$("#b_sdate").focus();
			return;
		}
	
	}


	if($("#p_title").val() == ""){
		alert("제목을 입력해주세요");
		$("#p_title").focus();
		return;
	}

	if($(".p_target").val() != "_none"){
		
		if($("#p_link").val() == ""){
			alert("링크 주소를 입력해주세요");
			$("#p_link").focus();
			return;
		}
	
	}

	var loc = $('input:checkbox[name="p_loc"]').val();

	if(loc == "P"){
		
		if($("#p_left").val() == ""){
			alert("노출 위치를 입력해주세요");
			$("#p_left").focus();
			return;
		}

		if($("#p_top").val() == ""){
			alert("노출 위치를 입력해주세요");
			$("#p_top").focus();
			return;
		}
	
	}
		

	$("#mode").val("edit");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/popup.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = $.parseJSON(data);

			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				alert(jsonData.msg);
				location.href = './popup.list.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
			$("#process-loading").hide();
		}
	});




}




function doDelete(no){

	var con = confirm("정말 삭제하시겠습니까?");
	
	var param = "";
	if(no){
		param = "no="+no+"&mode=delete";
	}else{
		param = "no="+$("#no").val()+"&mode=delete";
	}

	if(con){
	
		$.ajax({
			type:"POST",
			url:"./ajax/popup.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = './popup.list.php';
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	
	}


}