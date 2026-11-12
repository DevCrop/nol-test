var legacyAdminPending = false;
function legacyAdminAjax(options) {
    return window.BlueAdminAjax.send(options, {
        get pending() { return legacyAdminPending; },
        set pending(value) { legacyAdminPending = value; }
    });
}



function doRegSave(){
    if (legacyAdminPending) return;
	
	if($("#target").val() == ""){
		alert("데이터가 사용될 영역(위치)를 선택해주세요");
		$("#target").focus();
		return;
	}

	$("#mode").val("save");

	//에디터 적용
	submitContents();

	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	legacyAdminAjax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/setting.data.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = data;

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


function doEditSave(){
    if (legacyAdminPending) return;


	if($("#target").val() == ""){
		alert("데이터가 사용될 영역(위치)를 선택해주세요");
		$("#target").focus();
		return;
	}

	$("#mode").val("edit");

	//에디터 적용
	submitContents();

	
	// Get form
	var form = $('#frm')[0];

	// Create an FormData object 
	var data = new FormData(form);

	legacyAdminAjax({
		type: "POST",
		enctype: 'multipart/form-data',
		url:"./ajax/setting.data.process.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		success: function (data) {

			var jsonData = data;

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
