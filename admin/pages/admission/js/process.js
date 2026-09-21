
function doDelete(no) {
    var con = confirm('정말 삭제하시겠습니까?');

    var param = '';
    if (no) {
        param = 'no=' + no + '&mode=delete';
    } else {
        param = 'no=' + $('#no').val() + '&mode=delete';
    }

    if (con) {
        $.ajax({
            type: 'POST',
            url: './ajax/process.php',
            data: param,
            cache: false,
            dataType: 'html',
            success: function (data) {
                var jsonData = $.parseJSON(data);

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    location.href = './admission.list.php';
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}
