function doLoginAdmin() {
  if ($("#uid").val() == "") {
    alert("아이디를 입력해주세요");
    $("#uid").focus();
    return;
  }

  if ($("#pwd").val() == "") {
    alert("패스워드를 입력해주세요");
    $("#pwd").focus();
    return;
  }

  if ($("#r_captcha").val() == "") {
    alert("보안코드 6자리를 대소문자 및 숫자를 포함하여 정확히 입력해주세요");
    $("#r_captcha").focus();
    return;
  }

  $("#login_form").submit();
}

function doOpenDiv(id) {
  $("#" + id).show();
}

function doHideDiv(cls) {
  $("." + cls).hide();
  console.log(cls);
}

function doSearchList() {
  $("#frm").submit();
}

function doAttrChange(_class, el, value) {
  $("." + _class).attr(el, value);
  $("." + _class).val("");
}

function doFileGetName(elm, _class) {
  var fn = $(elm).val();
  var filename = fn.match(/[^\\/]*$/)[0]; // remove C:\fakename
  $("." + _class).text(filename);
}

function doResetInput(id) {
  $("#" + id).val("");
}

/* 
체크박스 체크 언체크 
_class : 대상 class
action : check / uncheck
*/
function doCheckUnCheck(_class, action) {
  if (action == "check") {
    $("." + _class).prop("checked", true);
  } else if (action == "uncheck") {
    $("." + _class).prop("checked", false);
  }
}

/* 
체크박스 체크 언체크 (체크박스 하나로 처리)
e : this 파라메터
_class : 대상 class
*/
function doCheckUnCheckOne(e, _class) {
  const _checkbox = $(e).attr("id");
  const isChecked = () => {
    return $("#" + _checkbox).is(":checked");
  };

  console.log(isChecked());

  if (isChecked()) {
    $("." + _class).prop("checked", true);
  } else {
    $("." + _class).prop("checked", false);
  }
}

/* Summernote: 빈 <p> → <div> 자동 변환 */
$(document).on('keyup', '.note-editable', function(e) {
  if (e.keyCode !== 13) return;
  var sel = window.getSelection();
  if (!sel || !sel.rangeCount) return;
  var node = sel.anchorNode;
  while (node && node.nodeType !== 1) node = node.parentNode;
  if (!node || node.nodeName !== 'P') return;
  var text = node.innerHTML.replace(/<br\s*\/?>/gi, '').replace(/&nbsp;/gi, '').trim();
  if (text !== '') return;
  var div = document.createElement('div');
  div.innerHTML = '<br>';
  node.parentNode.replaceChild(div, node);
  var range = document.createRange();
  range.setStart(div, 0);
  range.collapse(true);
  sel.removeAllRanges();
  sel.addRange(range);
});

function convertEmptyPToDiv(editable) {
  if (!editable) return;
  var children = editable.children;
  for (var i = children.length - 1; i >= 0; i--) {
    var el = children[i];
    if (el.nodeName !== 'P') continue;
    var text = el.innerHTML.replace(/<br\s*\/?>/gi, '').replace(/&nbsp;/gi, '').trim();
    if (text !== '') continue;
    var div = document.createElement('div');
    div.innerHTML = '<br>';
    el.parentNode.replaceChild(div, el);
  }
}

if (typeof jQuery !== "undefined") {
  jQuery(document).ajaxError(function (_e, xhr) {
    if (xhr && xhr.status === 401) {
      window.location.replace((window.NO_ADMIN_BASE || "") + "/index.php");
    }
  });
}
