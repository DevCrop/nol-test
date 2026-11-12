var legacyAdminPending = false;
function legacyAdminAjax(options) {
    return window.BlueAdminAjax.send(options, {
        get pending() { return legacyAdminPending; },
        set pending(value) { legacyAdminPending = value; }
    });
}

function doRegSave() {
    if (legacyAdminPending) return;
    if ($('#skin').val() == '') {
        alert('게시판 타입을 선택해주세요');
        $('#skin').focus();
        return;
    }

    if ($('#title').val() == '') {
        alert('게시판 이름을 입력해주세요');
        $('#title').focus();
        return;
    }

    $('#mode').val('save');

    //에디터 적용
    submitContents();


    // Get form
    var form = $('#frm')[0];

    // Create an FormData object
    var data = new FormData(form);

    legacyAdminAjax({
        type: 'POST',
        enctype: 'multipart/form-data',
        url: './ajax/board.manage.process.php',
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            var jsonData = data;

            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                alert(jsonData.msg);
                location.href = './board.manage.list.php';
            }
        },
        error: function (e) {},
        complete: function () {},
    });
}

function doEditSave() {
    if (legacyAdminPending) return;
    if ($('#skin').val() == '') {
        alert('게시판 타입을 선택해주세요');
        $('#skin').focus();
        return;
    }

    if ($('#title').val() == '') {
        alert('게시판 이름을 입력해주세요');
        $('#title').focus();
        return;
    }

    $('#mode').val('edit');

    //에디터 적용
    submitContents();


    // Get form
    var form = $('#frm')[0];

    // Create an FormData object
    var data = new FormData(form);

    legacyAdminAjax({
        type: 'POST',
        enctype: 'multipart/form-data',
        url: './ajax/board.manage.process.php',
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            var jsonData = data;

            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                alert(jsonData.msg);
                location.href = './board.manage.list.php';
            }
        },
        error: function (e) {},
        complete: function () {},
    });
}

function doDelete(no) {
    if (legacyAdminPending) return;
    var con = confirm('정말 삭제하시겠습니까?');

    var param = '';
    if (no) {
        param = 'no=' + no + '&mode=delete';
    } else {
        param = 'no=' + $('#no').val() + '&mode=delete';
    }

    if (con) {
        legacyAdminAjax({
            type: 'POST',
            url: './ajax/board.manage.process.php',
            data: param,
            cache: false,
            dataType: 'json',
            success: function (data) {
                var jsonData = data;

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    location.href = './board.manage.list.php';
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}

function doCategoryAdd() {
    if (legacyAdminPending) return;
    if ($('#name').val() == '') {
        alert('카테고리 이름을 입력해주세요');
        $('#name').focus();
        return;
    }

    $('#mode').val('category.add');


    // Get form
    var form = $('#frm')[0];

    // Create an FormData object
    var data = new FormData(form);

    legacyAdminAjax({
        type: 'POST',
        enctype: 'multipart/form-data',
        url: './ajax/board.manage.process.php',
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            var jsonData = data;

            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                alert(jsonData.msg);
                location.reload();
            }
        },
        error: function (e) {},
        complete: function () {},
    });
}

function doCategorySave(n) {
    if (legacyAdminPending) return;
    if ($('#name_' + n).val() == '') {
        alert('카테고리 이름을 입력해주세요');
        $('#name_' + n).focus();
        return;
    }

    if ($('#sort_no_' + n).val() == '') {
        alert('순서를 숫자로 입력해주세요');
        $('#sort_no_' + n).focus();
        return;
    }

    var name = $('#name_' + n).val();
    var sort_no = $('#sort_no_' + n).val();

    var param = { no: n, mode: 'category.save', name: name, sort_no: sort_no };

    legacyAdminAjax({
        type: 'POST',
        url: './ajax/board.manage.process.php',
        data: param,
        cache: false,
        dataType: 'json',
        success: function (data) {
            var jsonData = data;

            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                alert(jsonData.msg);
                location.reload();
            }
        },
        error: function (a, s) {
            alert('처리중 문제가 발생하였습니다.');
            return;
        },
    });
}

function doCategoryDelete(no) {
    if (legacyAdminPending) return;
    var con = confirm('정말 삭제하시겠습니까?');

    var param = '';
    if (no) {
        param = 'no=' + no + '&mode=category.delete';
    } else {
        param = 'no=' + $('#no').val() + '&mode=category.delete';
    }

    if (con) {
        legacyAdminAjax({
            type: 'POST',
            url: './ajax/board.manage.process.php',
            data: param,
            cache: false,
            dataType: 'json',
            success: function (data) {
                var jsonData = data;

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    location.reload();
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}

function changeSelectAction(elementId, $value) {
    const element = $(`#${elementId}`);

    element.selectmenu($value);
}
