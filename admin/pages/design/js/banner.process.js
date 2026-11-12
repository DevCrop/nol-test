/* Keep the legacy inline handlers while sharing one request state per screen. */
var designPending = false;
function designSend(data, multipart) {
    if (designPending) return;
    designPending = true;
    $('#frm').attr('aria-busy', 'true');
    $.ajax({
        type: 'POST', url: './ajax/banner.process.php', data: data,
        dataType: 'json', cache: false,
        processData: !multipart, contentType: multipart ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
        success: function (response) {
            alert(response.msg || '처리 결과를 확인할 수 없습니다.');
            if (response.result === 'success') location.href = './banner.list.php';
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

function bannerSave(mode) {
    if (designPending) return;
    if (!designRequired('#b_loc', '배너 노출 위치를 선택해주세요.') ||
        !designRequired('#b_title', '배너 제목을 입력해주세요.') ||
        (mode === 'save' && !designRequired('#b_img', '배너 이미지를 선택해주세요.')) ||
        !designPeriod('b_none_limit', '#b_sdate', '#b_edate') ||
        !designPeriod('b_none_view', '#b_sdate_view', '#b_edate_view')) return;
    $('#mode').val(mode);
    if (typeof submitContents === 'function') submitContents();
    designSend(new FormData($('#frm')[0]), true);
}
function doRegSave() { bannerSave('save'); }
function doEditSave() { bannerSave('edit'); }

