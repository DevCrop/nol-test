var legacyAdminPending = false;
function legacyAdminAjax(options) {
    return window.BlueAdminAjax.send(options, {
        get pending() { return legacyAdminPending; },
        set pending(value) { legacyAdminPending = value; }
    });
}


function doDelete(no){
    if (legacyAdminPending) return;

	var con = confirm("정말 삭제하시겠습니까?");
	
	var param = "";
	if(no){
		param = "no="+no+"&mode=delete";
	}else{
		param = "no="+$("#no").val()+"&mode=delete";
	}

	if(con){
	
		legacyAdminAjax({
			type:"POST",
			url:"./ajax/opera.process.php",
			data:param,
			cache: false,
			dataType: 'json',
			success:function(data){
			
				var jsonData = data;

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = "./index.php";
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	}
}


function doManageSave() {
    if (legacyAdminPending) return;
    const con = confirm("저장하시겠습니까?");
    if (!con) return;

    // Collect data from the form
    const param = {
        mode: "save",
        state: $("input[name='state']:checked").val(),
        title: $("#title").val(),
        link: $("#link").val()
    };

    // Validate required fields
    if (!param.title || !param.link) {
        alert("타이틀과 링크를 입력해주세요.");
        return;
    }

    // AJAX request to save data
    legacyAdminAjax({
        type: "POST",
        url: "./ajax/opera.process.php",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.result === "fail") {
                alert(data.msg);
            } else if (data.result === "success") {
                alert(data.msg);
                location.href = "./index.php"; // Redirect to list page
            }
        },
        error: function () {
            alert("처리 중 문제가 발생하였습니다.");
        }
    });
}


// manage 관리
function doManageDelete(no){
    if (legacyAdminPending) return;

	var con = confirm("정말 삭제하시겠습니까?");
	
	var param = "";
	if(no){
		param = "no="+no+"&mode=delete";
	}else{
		param = "no="+$("#no").val()+"&mode=delete";
	}

	if(con){
	
		legacyAdminAjax({
			type:"POST",
			url: "./ajax/opera.process.php",
			data:param,
			cache: false,
			dataType: 'json',
			success:function(data){
				var jsonData = data;

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = "./index.php";
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	
	}

}

function doManageEdit() {
    if (legacyAdminPending) return;
    const con = confirm("수정하시겠습니까?");
    if (!con) return;

    const param = {
        mode: "edit", // 반드시 "edit"으로 설정
        id: $("#id").val(), // 수정할 항목의 ID
        state: $("input[name='state']:checked").val(),
        title: $("#title").val(),
        link: $("#link").val()
    };

    // Validate required fields
    if (!param.id || !param.title || !param.link) {
        alert("ID, 타이틀, 링크를 입력해주세요.");
        return;
    }

    legacyAdminAjax({
        type: "POST",
        url: "./ajax/opera.process.php",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.result === "fail") {
                alert(data.msg);
            } else if (data.result === "success") {
                alert(data.msg);
                location.href = "./index.php";
            }
        },
        error: function () {
            alert("처리 중 문제가 발생하였습니다.");
        }
    });
}
