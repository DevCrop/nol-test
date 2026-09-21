

function doPasswordConfirm(mode){
	if($("#pwd").val() == ""){
		alert("비밀번호를 입력해주세요");
		$("#pwd").focus();
		return;
	}
	
	$("#mode").val("board.password.confirm");
	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		url:"./ajax/board.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = $.parseJSON(data);

			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				if(mode == "edit")
					$("#frm").attr("action","./board.edit.php");
				else if(mode == "view")
					$("#frm").attr("action","./board.view.php");
				$("#frm").submit();
			}

		},
		error: function (e) {
		},
		complete : function() {
		}
	});
}



function doBoardSubmit(isSecret){
	
	if($("#title").val() == ""){
		alert("제목을 입력해주세요");
		$("#title").focus();
		return;
	}

	if($("#write_name").val() == ""){
		alert("이름을 입력해주세요");
		$("#write_name").focus();
		return;
	}

	if(isSecret){
		if($("#secret_pwd").val() == ""){
			alert("비밀번호를 입력해주세요");
			$("#secret_pwd").focus();
			return;
		}
	}

	if($("#contents").val() == ""){
		alert("내용을 입력해주세요");
		$("#contents").focus();
		return;
	}

	if($("#r_captcha").val() == ""){
		alert("보안코드를 입력해주세요");
		$("#r_captcha").focus();
		return;
	}
	
	$("#mode").val("board.write");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		url:"./ajax/board.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = $.parseJSON(data);

			if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}else if(jsonData.result == "success"){
				location.href = './board.list.php?board_no='+$("#board_no").val();
			}

		},
		error: function (e) {
		},
		complete : function() {
		}
	});


}



function doBoardEditSubmit(){
	
	if($("#title").val() == ""){
		alert("제목을 입력해주세요");
		$("#title").focus();
		return;
	}

	if($("#write_name").val() == ""){
		alert("이름을 입력해주세요");
		$("#write_name").focus();
		return;
	}

	if($("#contents").val() == ""){
		alert("내용을 입력해주세요");
		$("#contents").focus();
		return;
	}

	if($("#r_captcha").val() == ""){
		alert("보안코드를 입력해주세요");
		$("#r_captcha").focus();
		return;
	}
	
	$("#mode").val("board.edit");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		url:"./ajax/board.process.php",
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
				location.href = './board.view.php?no='+$("#no").val()+'&board_no='+$("#board_no").val();
			}

		},
		error: function (e) {
		},
		complete : function() {
		}
	});

}

function doBoardEditUserConfirm(){
	$("#frm").attr("action", "./board.confirm.php");
	$("#mode").val("edit");
	$("#frm").submit();
}

function doBoardEditUser(){
	$("#frm").attr("action", "./board.edit.php");
	$("#frm").submit();
}

function doBoardDeleteUser(){
	

	var c = confirm("정말 삭제하시겠습니까?");
	if(c){

		$("#mode").val("board.delete.user");

		var params = jQuery("#frm").serialize();
		
		// Get form
		var form = $('#frm')[0];

		// Create an FormData object 
		var data = new FormData(form);

		$.ajax({
			type: "POST",
			url:"./ajax/board.process.php",
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
					location.href = './board.list.php?board_no='+$("#board_no").val();
				}

			},
			error: function (e) {
			},
			complete : function() {
			}
		});

	}

}




function doCommentSave(lev){
	
	if(lev == 0){
		
		if($("#write_name").val() == ""){
			alert("이름을 입력해주세요");
			$("#write_name").focus();
			return;
		}

		if($("#pwd").val() == ""){
			alert("비밀번호를 입력해주세요");
			$("#pwd").focus();
			return;
		}
	
	}


	if($("#comment_contents").val() == ""){
		alert("댓글 내용을 입력해주세요");
		$("#comment_contents").focus();
		return;
	}


	if($("#r_captcha").val() == ""){
		alert("보안 코드를 입력해주세요");
		$("#r_captcha").focus();
		return;
	}

	$("#mode").val("comment.save");

	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		url:"./ajax/board.process.php",
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
				location.reload();
			}

		},
		error: function (e) {
		},
		complete : function() {
		}
	});


}


function doCommentDelete(no){
	

	var c = confirm("정말 삭제하시겠습니까?");
	if(c){

		$("#mode").val("comment.delete");
		$("#comment_no").val(no);
		var params = jQuery("#frm").serialize();
		
		// Get form
		var form = $('#frm')[0];

		// Create an FormData object 
		var data = new FormData(form);

		$.ajax({
			type: "POST",
			url:"./ajax/board.process.php",
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
					location.reload();
				}

			},
			error: function (e) {
			},
			complete : function() {
			}
		});

	}

}


function doCommentDeleteSecret(no){
	
	$("#comment_no").val(no);
	$("#frm").attr("action", "../board.comment.confirm.php");
	$("#frm").submit();


}



function doCommentPasswordConfirm(){

	if($("#pwd").val() == ""){
		alert("비밀번호를 입력해주세요");
		$("#pwd").focus();
		return;
	}
	
	$("#mode").val("comment.password.delete.confirm");
	var params = jQuery("#frm").serialize();
	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	$.ajax({
		type: "POST",
		url:"./ajax/board.process.php",
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
				location.href = './board.view.php?no='+$("#no").val()+'&board_no='+$("#board_no").val();
			}

		},
		error: function (e) {
		},
		complete : function() {
		}
	});
}



function doCategoryChange(v){
	$("#frm").submit();
}


function doCategoryClick(v){
	$("#category_no").val(v);
	$("#frm").submit();
}

function doSearch(){
	
	if($("#searchColumn").val() == ""){
		alert("컬럼을 선택해주세요");
		return;
	}

	$("#frm").submit();

}