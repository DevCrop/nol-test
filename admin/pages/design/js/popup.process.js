/* Keep the legacy inline handlers while sharing one request state per screen. */
var designPending = false;
function designSend(data, multipart) {
    if (designPending) return;
    designPending = true;
    $('#frm').attr('aria-busy', 'true');
    $.ajax({
        type: 'POST', url: './ajax/popup.process.php', data: data,
        dataType: 'json', cache: false,
        processData: !multipart, contentType: multipart ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
        success: function (response) {
            alert(response.msg || '처리 결과를 확인할 수 없습니다.');
            if (response.result === 'success') location.href = './popup.list.php';
        },
        error: function (xhr) {
            alert(xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message) || '처리 중 문제가 발생했습니다. 다시 시도해주세요.');
        },
        complete: function () {
            designPending = false;
            $('#frm').removeAttr('aria-busy');
            $('#process-loading').hide();
        }
    });
}
function designRequired(selector, message) {
    if (!String($(selector).val() || '').trim()) { alert(message); $(selector).focus(); return false; }
    return true;
}
function designPeriod(flag, start, end) {
    if ($('input[name="' + flag + '"]:checked').val() !== 'N') return true;
    var from = $(start).val(), to = $(end).val();
    if (!from || !to) { alert('노출 기간을 선택해주세요.'); $(from ? end : start).focus(); return false; }
    if (from > to) { alert('종료일은 시작일 이후로 선택해주세요.'); $(end).focus(); return false; }
    return true;
}
function doDelete(no) {
    if (designPending || !confirm('정말 삭제하시겠습니까?')) return;
    designSend({ mode: 'delete', no: no || $('#no').val() }, false);
}

function popupSave(mode) {
    if (designPending) return;
    if (!designPeriod('p_none_limit', '#p_sdate', '#p_edate') ||
        !designRequired('#p_title', '제목을 입력해주세요.') ||
        (mode === 'save' && !designRequired('#p_img', '팝업 이미지를 선택해주세요.'))) return;
    if ($('input[name="p_loc"]:checked').val() === 'P') {
        if (!designRequired('#p_left', '왼쪽 좌표를 입력해주세요.') ||
            !designRequired('#p_top', '상단 좌표를 입력해주세요.')) return;
        if (!/^\d+$/.test($('#p_left').val()) || !/^\d+$/.test($('#p_top').val())) {
            alert('좌표는 0 이상의 정수로 입력해주세요.'); return;
        }
    }
    $('#mode').val(mode);
    designSend(new FormData($('#frm')[0]), true);
}
$('[name=p_target]').change(function () { $('[name=p_link]').val(''); });
function doRegSave() { popupSave('save'); }
function doEditSave() { popupSave('edit'); }
