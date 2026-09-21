
function doRegSave(){

	if($("#comment").val() == ""){
		alert("댓글 내용을 입력해주세요");
		$("#comment").focus();
		return;
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
		url:"./ajax/board.comment.process.php",
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



function doCommentDelete(no, board_no){

	var con = confirm("정말 삭제하시겠습니까?");
	
	var param = "";
	if(no){
		param = "no="+no+"&board_no="+board_no+"&mode=delete";
	}else{
		param = "no="+$("#no").val()+"&board_no="+$("#board_no").val()+"&mode=delete";
	}

	if(con){
	
		$.ajax({
			type:"POST",
			url:"./ajax/board.comment.process.php",
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
	
	
	}


}
