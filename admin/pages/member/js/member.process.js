


function doRegSave(){
	
	if($("#uid").val() == ""){
		alert("아이디를 입력해주세요");
		$("#uid").focus();
		return;
	}

	if($("#uid").val().length < 6){
		alert("아이디는 최소 6자리 이상이어야 합니다.");
		$("#uid").focus();
		return;
	}

	if($("#dupCheck").val() == ""){
		alert("아이디 중복 체크 버튼을 클릭하여 아이디 중복 여부를 확인해주세요");
		$("#uid").focus();
		return;
	}

	if($("#dupCheck").val() == "N"){
		alert("중복되는 아이디입니다. 다른 아이디를 입력해주세요");
		$("#uid").focus();
		return;
	}
		
	if($("#upwd").val() == ""){
		alert("비밀번호를 입력해주세요");
		$("#upwd").focus();
		return;
	}

	if($("#upwd").val().length < 6){
		alert("비밀번호는 최소 6자리 이상이어야 합니다.");
		$("#upwd").focus();
		return;
	}

	if($("#upwd_confirm").val() == ""){
		alert("비밀번호 확인을 입력해주세요");
		$("#upwd_confirm").focus();
		return;
	}

	if($("#upwd").val() != $("#upwd_confirm").val()){
		alert("비밀번호와 비밀번호 확인이 서로 일치하지 않습니다.");
		$("#upwd").focus();
		return;
	}

	if($("#uname").val() == ""){
		alert("이름을 입력해주세요");
		$("#uname").focus();
		return;
	}



	if($("#phone").val() == ""){
		alert("연락처를 입력해주세요");
		$("#phone").focus();
		return;
	}

	if($("#hp").val() == ""){
		alert("휴대폰 번호를 입력해주세요");
		$("#hp").focus();
		return;
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
		url:"./ajax/member.process.php",
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
				location.href = './member.list.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
		}
	});
	

}

function doEditSave(){
	

		
	if($("#upwd").val() != ""){
		
		if($("#upwd").val().length < 6){
			alert("비밀번호는 최소 6자리 이상이어야 합니다.");
			$("#upwd").focus();
			return;
		}

		if($("#upwd_confirm").val() == ""){
			alert("비밀번호 확인을 입력해주세요");
			$("#upwd_confirm").focus();
			return;
		}

		if($("#upwd").val() != $("#upwd_confirm").val()){
			alert("비밀번호와 비밀번호 확인이 서로 일치하지 않습니다.");
			$("#upwd").focus();
			return;
		}

	}

	if($("#uname").val() == ""){
		alert("이름을 입력해주세요");
		$("#uname").focus();
		return;
	}

	if($("#phone").val() == ""){
		alert("연락처를 입력해주세요");
		$("#phone").focus();
		return;
	}

	if($("#hp").val() == ""){
		alert("휴대폰 번호를 입력해주세요");
		$("#hp").focus();
		return;
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
		url:"./ajax/member.process.php",
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
				location.href = './member.list.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
		}
	});

}


function doDupCheck(){


	
	var param = "mode=iddupcheck&uid="+$("#uid").val();
	
		if($("#uid").val() == ""){
			alert("아이디를 입력해주세요");
			$("#uid").focus();
			return;
		}

		if($("#uid").val().length < 6){
			alert("아이디는 최소 6자리 이상이어야 합니다.");
			$("#uid").focus();
			return;
		}

	
			$.ajax({
				type:"POST",
				url:"./ajax/member.process.php",
				data:param,
				cache: false,
				dataType:"html",
				success:function(data){
				
					var jsonData = $.parseJSON(data);

					$("#no-dupcheck-field").html("");

					if(jsonData.result == "fail"){
						alert('이미 사용중인 아이디입니다. 다른 아이디를 입력하세요.');
						$("#dupCheck").val("N");
					}else if(jsonData.result == "success"){
						alert('사용 가능한 아이디입니다.');
						$("#dupCheck").val("Y");
					}
				},
				error:function(a,s){
					alert("처리중 문제가 발생하였습니다.");
					return;
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
			url:"./ajax/member.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = './member.list.php';
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	
	}


}



function doLevChange(v, no){


	var con = confirm("등급을 변경하시겠습니까?");

	if(con){

		var param = "lev="+v+"&no="+no+"&mode=levChange";
		
		$.ajax({
			type:"POST",
			url:"./ajax/member.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);
				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.reload();
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	}else{
		return false;
	}


}