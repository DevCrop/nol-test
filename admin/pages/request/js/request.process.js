$(document).ready(function () {
    if ($('#contents').length > 0) {
        $('#contents').summernote({
            lang: 'ko-KR',
            height: 500,
        });
    }
});


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
			url:"./ajax/request.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = "./request.list.php";
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	}
}


// manage 관리
function doManageSave(no) {
    const con = confirm("저장하시겠습니까?");
    if (!con) return;

    const param = {
        mode: "manageSave",
        no: no || $("#no").val(),
        r_view: $("input[name='r_view']:checked").val(),
        r_title: $("#r_title").val(),
        r_sdate: $("#r_sdate").val(),
        r_edate: $("#r_edate").val(),
		contents : $("#contents").val(),
    };

    if (!param.r_title || !param.r_sdate || !param.r_edate) {
        alert("모든 필드를 입력해주세요.");
        return;
    }

    $.ajax({
        type: "POST",
        url: "./ajax/request.process.php",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.result === "fail") {
                alert(data.msg);
            } else if (data.result === "success") {
                alert(data.msg);
                location.href = "./request.schedule.list.php";
            }
        },
        error: function () {
            alert("처리 중 문제가 발생하였습니다.");
        },
    });
}

// manage 관리
function doManageDelete(no){

	var con = confirm("정말 삭제하시겠습니까?");
	
	var param = "";
	if(no){
		param = "no="+no+"&mode=manageDelete";
	}else{
		param = "no="+$("#no").val()+"&mode=manageDelete";
	}

	if(con){
	
		$.ajax({
			type:"POST",
			url:"./ajax/request.process.php",
			data:param,
			cache: false,
			dataType:"html",
			success:function(data){
			
				var jsonData = $.parseJSON(data);

				if(jsonData.result == "fail"){
					alert(jsonData.msg);
				}else if(jsonData.result == "success"){
					alert(jsonData.msg);
					location.href = "./request.schedule.list.php";
				}
			},
			error:function(a,s){
				alert("처리중 문제가 발생하였습니다.");
				return;
			}
		});
	
	
	}

}

function doManageEdit(no) {
    const con = confirm("수정하시겠습니까?");
    if (!con) return;

    const param = {
        mode: "manageEdit",
        no: no || $("#no").val(),
        r_view: $("input[name='r_view']:checked").val(),
        r_title: $("#r_title").val(),
        r_sdate: $("#r_sdate").val(),
        r_edate: $("#r_edate").val(),
		contents : $("#contents").val(),
    };

    if (!param.r_title || !param.r_sdate || !param.r_edate) {
        alert("모든 필드를 입력해주세요.");
        return;
    }

    $.ajax({
        type: "POST",
        url: "./ajax/request.process.php",
        data: param,
        dataType: "json",
        success: function (data) {
            if (data.result === "fail") {
                alert(data.msg);
            } else if (data.result === "success") {
                alert(data.msg);
                location.href = "./request.schedule.list.php";
            }
        },
        error: function () {
            alert("처리 중 문제가 발생하였습니다.");
        },
    });
}
