

function doSave(){


	if($("#pwd_old").val() == ""){
		alert("현재 사용중인 비밀번호를 입력해주세요");
		$("#pwd_old").focus();
		return;
	}

	if($("#pwd_new").val() == ""){
		alert("변경하시려는 신규 비밀번호를 입력해주세요");
		$("#pwd_new").focus();
		return;
	}

	if($("#pwd_new_confirm").val() == ""){
		alert("변경하시려는 신규 비밀번호를 한 번 더 동일하게 입력해주세요");
		$("#pwd_new_confirm").focus();
		return;
	}

	if($("#pwd_new").val() != $("#pwd_new_confirm").val()){
		alert("변경하시려는 신규 비밀번호와 비밀번호 확인이 서로 일치하지 않습니다.");
		$("#pwd_new_confirm").focus();
		return;
	}


	$("#mode").val("pwd.change");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/setting.process.php",
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
				location.href = '/admin/';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
			
		}
	});




}


function doDeleteData(no){
	if(!confirm('정말로 삭제하시겠습니까?')){
		return; 
	}

	const data = new URLSearchParams({
		no: no,
		mode: 'delete'
	});

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/setting.data.process.php",
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
				location.href = './site.data.list.php';
			}

		},
		error: function (e) {
			
		},
		complete : function() {
		}
	});

}


/* settting config */

function doSettingSave(){
	

	if($("#title").val() == ""){
		alert("사이트 제목을 입력해주세요");
		$("#title").focus();
		return;
	}

	if($("#logo_top_filename").val() == ""){
		if($("#logo_top").val() == ""){
			alert("상단 로고 파일을 선택해주세요");
			$("#logo_top").focus();
			return;
		}
	}
	
	if($("#logo_footer_filename").val() == ""){
		if($("#logo_footer").val() == ""){
			alert("하단 푸터 로고 파일을 선택해주세요");
			$("#logo_footer").focus();
			return;
		}
	}


	if($("#footer_name").val() == ""){
		alert("하단 푸터 사이트명을 입력해주세요");
		$("#footer_name").focus();
		return;
	}

	$("#mode").val("setting.config.save");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/setting.process.php",
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
				location.href = './site.config.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
			
		}
	});

}