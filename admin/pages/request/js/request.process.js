var requestPending = false;
$(document).ready(function () {
    if ($('#contents').length > 0) $('#contents').summernote({ lang: 'ko-KR', height: 500 });
});
function requestSend(data, destination) {
    if (requestPending) return;
    requestPending = true;
    $('#frm').attr('aria-busy', 'true');
    $.ajax({
        type: 'POST', url: './ajax/request.process.php', data: data, dataType: 'json', cache: false,
        success: function (response) {
            alert(response.msg || '처리 결과를 확인할 수 없습니다.');
            if (response.result === 'success') location.href = destination;
        },
        error: function (xhr) {
            alert(xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message) || '처리 중 문제가 발생했습니다. 다시 시도해주세요.');
        },
        complete: function () {
            requestPending = false; $('#frm').removeAttr('aria-busy'); $('#process-loading').hide();
        }
    });
}
function doDelete(no) {
    if (requestPending || !confirm('정말 삭제하시겠습니까?')) return;
    requestSend({mode: 'delete', no: no || $('#no').val()}, './request.list.php');
}
function requestManage(mode, no) {
    if (requestPending) return;
    var data = {
        mode: mode, no: no || $('#no').val(),
        r_view: $('input[name="r_view"]:checked').val(),
        r_title: $('#r_title').val(), r_sdate: $('#r_sdate').val(), r_edate: $('#r_edate').val(),
        contents: $('#contents').val()
    };
    if (!String(data.r_title || '').trim()) { alert('제목을 입력해주세요.'); $('#r_title').focus(); return; }
    if (!data.r_sdate || !data.r_edate) { alert('대관 기간을 선택해주세요.'); $(data.r_sdate ? '#r_edate' : '#r_sdate').focus(); return; }
    if (data.r_sdate > data.r_edate) { alert('종료일은 시작일 이후로 선택해주세요.'); $('#r_edate').focus(); return; }
    if (!confirm(mode === 'manageSave' ? '저장하시겠습니까?' : '수정하시겠습니까?')) return;
    requestSend(data, './request.schedule.list.php');
}
function doManageSave(no) { requestManage('manageSave', no); }
function doManageEdit(no) { requestManage('manageEdit', no); }
function doManageDelete(no) {
    if (requestPending || !confirm('정말 삭제하시겠습니까?')) return;
    requestSend({mode: 'manageDelete', no: no || $('#no').val()}, './request.schedule.list.php');
}
