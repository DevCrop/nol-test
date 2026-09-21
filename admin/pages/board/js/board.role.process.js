
function doEditSave(){

	
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
		url:"./ajax/board.role.process.php",
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
				location.href = './board.role.php';
			}

		},
		error: function (e) {

		 

		},
		complete : function() {
		}
	});



}
