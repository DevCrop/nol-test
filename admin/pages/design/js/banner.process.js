




function doRegSave(){


	if($("#b_loc").val() == ""){
		alert("배너 노출 위치를 선택해주세요");
		$("#b_loc").focus();
		return;
	}

	if($("#b_title").val() == ""){
		alert("배너 제목을 입력해주세요");
		$("#b_title").focus();
		return;
	}
		
	if($("#b_img").val() == ""){
		alert("배너 이미지를 선택 등록해주세요");
		$("#b_img").focus();
		return;
	}

	if($("#b_none_limit").val() == "N"){
		
		if($("#b_sdate").val() == "" || $("#b_edate").val() == ""){
			alert("노출 기간을 선택해주세요.");
			$("#b_sdate").focus();
			return;
		}
	
	}

	$("#mode").val("save");

	//에디터 적용
    submitContents();

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/banner.process.php",
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
				location.href = './banner.list.php';
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
	
	if($("#b_loc").val() == ""){
		alert("배너 노출 위치를 선택해주세요");
		$("#b_loc").focus();
		return;
	}

	if($("#b_title").val() == ""){
		alert("배너 제목을 입력해주세요");
		$("#b_title").focus();
		return;
	}
		/*
	if($("#b_img").val() == ""){
		alert("배너 이미지를 선택 등록해주세요");
		$("#b_img").focus();
		return;
	}*/

	if($("#b_none_limit").val() == "N"){
		
		if($("#b_sdate").val() == "" || $("#b_edate").val() == ""){
			alert("노출 기간을 선택해주세요.");
			$("#b_sdate").focus();
			return;
		}
	
	}

	$("#mode").val("edit");

	//에디터 적용
    submitContents();

	var params = jQuery("#frm").serialize();
	
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/banner.process.php",
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
				location.href = './banner.list.php';
			}

		},
		error: function (e) {


		},
		complete : function() {
			
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
			url:"./ajax/banner.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = './banner.list.php';
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	
	}


}

