var legacyAdminPending = false;
function legacyAdminAjax(options) {
    return window.BlueAdminAjax.send(options, {
        get pending() { return legacyAdminPending; },
        set pending(value) { legacyAdminPending = value; }
    });
}


/*
$(document).ready(function () {
    if ($('#contents').length > 0) {
        $('#contents').summernote({
            lang: 'ko-KR',
            height: 500,
        });
    }
});*/


$(document).ready(function () {
    if ($('#contents').length > 0) {
        $('#contents').summernote({
            lang: 'ko-KR',
            height: 500,
            toolbar: [
                ['style', ['style']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontSizes: [
                '8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24',
                '26', '28', '30', '32', '34', '36', '40', '44', '48', '52',
                '56', '60', '64', '72', '80'
            ],
            callbacks: {
                onChange: function (contents, $editable) {
                    const $dom = $('<div>').html(contents);

                    $dom.find('span[style]').each((_, el) => {
                        const style = el.getAttribute('style');
                        const fontSizeMatch = style.match(/font-size:\s*(\d+)px/);

                        if (fontSizeMatch) {
                            const size = fontSizeMatch[1];
                            const parentP = $(el).closest('p');

                            if (parentP.length) {
                                parentP.removeClass((i, c) => (c.match(/fsz-\d+/g) || []).join(' ')); // 기존 제거
                                parentP.addClass(`fsz-${size}`);
                            }

                            el.style.fontSize = ''; // 인라인 제거
                        }
                    });

                    $('#contents').val($dom.html());
                }
            }
        });
    }
});





function sanitizeStyleAttributes(dom) {
    const allElements = dom.getElementsByTagName('*');
    for (let element of allElements) {
        if (element.hasAttribute('style')) {
            let styleContent = element.getAttribute('style');
            // 내부 중첩된 큰따옴표(")를 작은따옴표(')로 변환하여 오류 방지
            styleContent = styleContent.replace(/"/g, "'");
            element.setAttribute('style', styleContent);
        }
    }
}


// 게시판 타이틀 글자수 제한

const clampElements = document.querySelectorAll(".clamp");

    clampElements.forEach((el) => {
        const maxChars = 50; // 최대 글자 수
        const text = el.textContent.trim();

        if (text.length > maxChars) {
            el.textContent = text.slice(0, maxChars) + "..."; // 글자를 잘라내고 말줄임 표시
        }
    });


function removeAttributes(dom) {
    const validAttributes = [
        'accept',
        'accept-charset',
        'accesskey',
        'action',
        'align',
        'alt',
        'async',
        'autocomplete',
        'autofocus',
        'autoplay',
        'bgcolor',
        'border',
        'charset',
        'checked',
        'cite',
        'class',
        'color',
        'cols',
        'colspan',
        'content',
        'contenteditable',
        'controls',
        'coords',
        'data',
        'datetime',
        'default',
        'defer',
        'dir',
        'dirname',
        'disabled',
        'download',
        'draggable',
        'enctype',
        'for',
        'form',
        'formaction',
        'headers',
        'height',
        'hidden',
        'high',
        'href',
        'hreflang',
        'http-equiv',
        'id',
        'ismap',
        'kind',
        'label',
        'lang',
        'list',
        'loop',
        'low',
        'max',
        'maxlength',
        'media',
        'method',
        'min',
        'multiple',
        'muted',
        'name',
        'novalidate',
        'open',
        'optimum',
        'pattern',
        'placeholder',
        'poster',
        'preload',
        'readonly',
        'rel',
        'required',
        'reversed',
        'rows',
        'rowspan',
        'sandbox',
        'scope',
        'selected',
        'shape',
        'size',
        'sizes',
        'span',
        'spellcheck',
        'src',
        'srcdoc',
        'srclang',
        'srcset',
        'start',
        'step',
        'style',
        'tabindex',
        'target',
        'title',
        'type',
        'usemap',
        'value',
        'width',
        'wrap',
    ];
	return; 

    // Get all elements in the document
    const allElements = dom.getElementsByTagName('*');

    // Iterate through each element
    for (let element of allElements) {
        [...element.attributes].forEach((attr) => {
			if(attr.name.startsWith('data')){
				return; 
			}
            if (!validAttributes.includes(attr.name)) {
                element.removeAttribute(attr.name);
            }
        });

        // Remove the 'id' attribute if it exists
       /*
		if (element.hasAttribute('id')) {
            element.removeAttribute('id');
        }
		*/

        // Remove the 'style' attribute if it exists
        // if (element.hasAttribute('style')) {
        //   element.removeAttribute('style');
        // }

        // Remove the 'class' attribute if it exists
        /*
		if (element.hasAttribute('class')) {
            element.removeAttribute('class');
        }
		*/
    }
}
async function boardSave(mode) {
    if (legacyAdminPending) return;
    if ($('#board_no').val() == '') { alert('글을 등록하시려는 게시판을 선택해주세요'); $('#board_no').focus(); return; }
    if ($('#title').val() == '') { alert('제목을 입력해주세요'); $('#title').focus(); return; }
    if ($('#write_name').val() == '') { alert('작성자 이름을 입력해주세요'); $('#write_name').focus(); return; }
    legacyAdminPending = true;
    try {
        $('#mode').val(mode);
        const fd = new FormData($('#frm')[0]);
        const parentElement = document.createElement('div');
        parentElement.innerHTML = fd.get('contents') || '';
        sanitizeStyleAttributes(parentElement);
        for (const img of parentElement.querySelectorAll('img')) {
            if (!img.src.startsWith('data:image')) continue;
            const source = await fetch(img.src);
            if (!source.ok) throw new Error('이미지를 읽지 못했습니다.');
            const blob = await source.blob();
            const ext = blob.type.split('/')[1];
            const uploadFd = new FormData();
            uploadFd.append('_method', 'post');
            uploadFd.append('extension', ext);
            uploadFd.append('file', blob, 'image.' + ext);
            const uploadResponse = await fetch('./ajax/upload.php', {method:'POST', body:uploadFd});
            const result = await uploadResponse.json();
            if (!uploadResponse.ok || !result.success || !result.filename) throw new Error('이미지 업로드에 실패했습니다.');
            img.setAttribute('src', result.filename);
        }
        fd.set('contents', parentElement.outerHTML);
        fd.set('mode', mode);
        if (mode === 'edit') fd.set('category_no', $('#category_no').val());
        const response = await fetch('./ajax/board.process.php', {method:'POST', body:fd});
        const result = await response.json();
        if (!response.ok || !result || result.result !== 'success') {
            alert(result && result.msg || '저장하지 못했습니다. 다시 시도해주세요.'); return;
        }
        alert(result.msg);
        const out = new URLSearchParams();
        if (mode === 'edit') {
            const src = new URLSearchParams(window.location.search);
            ['board_no','page','sdate','edate'].forEach(key => {if (src.has(key)) out.set(key,src.get(key));});
        }
        location.href = './board.list.php' + (out.toString() ? '?' + out.toString() : '');
    } catch (error) { alert('저장 중 문제가 발생했습니다. 입력 내용을 확인한 후 다시 시도해주세요.'); }
    finally { legacyAdminPending = false; }
}
async function doRegSave() { return boardSave('save'); }
async function doEditSave() { return boardSave('edit'); }

function doDelete(no) {
    if (legacyAdminPending || !confirm('정말 삭제하시겠습니까?')) return;
    legacyAdminAjax({
        type:'POST', url:'./ajax/board.process.php',
        data:{mode:'delete',no:no || $('#no').val()},
        success:function (data) {
            alert(data.msg || '처리 결과를 확인할 수 없습니다.');
            if (data.result === 'success') location.href='./board.list.php';
        }
    });
}

function doDeleteArray() {
    if (legacyAdminPending) return;
    if ($('.no-chk').is(':checked') == false) {
        alert('대상을 선택해주세요');
        return;
    }

    var con = confirm('정말 삭제하시겠습니까?');

    if (con) {
        $('#mode').val('delete.array');

        var params = jQuery('#frm').serialize();

        // Get form
        var form = $('#frm')[0];

        // Create an FormData object
        var data = new FormData(form);

        legacyAdminAjax({
            type: 'POST',
            enctype: 'multipart/form-data',
            url: './ajax/board.process.php',
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
                    location.href = './board.list.php';
                }
            },
            error: function (e) {},
            complete: function () {},
        });
    }
}

function doGetBoardManageInfo(v) {
    doGetBoardField(v);
    doCategoryView(v);
}

(function checkCategory() {
	console.log('checkCategory()');
    $('#board_no').selectmenu({
        change: function (event, ui) {
            const { index, value } = ui.item;
            console.log(value);
            if (index === 0) return;

            doGetBoardManageInfo(value);
        },
    });
})();

function doGetBoardField(v) {
    // console.log('[BoardProcess]', 'doGetBoardField');
	console.log('doGetBoardField()');
    var param = 'board_no=' + v + '&mode=board.field';

    legacyAdminAjax({
        type: 'POST',
        url: './ajax/board.process.php',
        data: param,
        cache: false,
        dataType: 'json',
        success: function (data) {
            var jsonData = data;

            console.log(jsonData);

            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                var html = '';
                /* reset */
                $('.extra_fields').remove();
                $.each(jsonData.rows, function (key, value) {
                    console.log(value);

                    html = `
                        <div class="no-admin-block extra_fields">
                            <h3 class="no-admin-title">
                                <label for="${value.fields}">${value.name}</label>
                            </h3>
                            <div class="no-admin-content">
                                <input
                                    type="text"
                                    name="${value.fields}"
                                    id="${value.fields}"
									value="${value.value ?? ''}"
                                    class="no-input--detail"
                                    placeholder="${value.name}"
                                />
                            </div>
                        </div>
                        <!-- admin-block -->
                    `;

                    $('.no-admin-field').before(html);
                });
            }
        },
        error: function (a, s) {
            alert('처리중 문제가 발생하였습니다.');
            return;
        },
    });
}

function doCategoryView(v) {
	console.log('doCategoryView()');
    var param = 'board_no=' + v + '&mode=board.category';

    legacyAdminAjax({
        type: 'POST',
        url: './ajax/board.process.php',
        data: param,
        cache: false,
        dataType: 'json',
        success: function (data) {
            var jsonData = data;
            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                if (jsonData.category_yn == 'Y') {
                    if ($('#category_no').length) {
                        $('#category_no option').remove();
                    }

                    $('#category_no').append("<option value=''>카테고리 선택</option>");
                    $.each(jsonData.rows, function (key, value) {
                        $('#category_no').append(
                            "<option value='" + value.no + "'>" + value.name + '</option>'
                        );
                    });

                    $('.no_table_category').show();
                } else {
                    if ($('#category_no').length) {
                        $('#category_no option').remove();
                    }
                    $('.no_table_category').hide();
                }
            }
        },
        error: function (a, s) {
            alert('처리중 문제가 발생하였습니다.');
            return;
        },
    });
}

function doCategoryDepthView(v) {
    var param = 'board_no=' + v + '&mode=category.depth';

    legacyAdminAjax({
        type: 'POST',
        url: './ajax/board.process.php',
        data: param,
        cache: false,
        dataType: 'json',
        success: function (data) {
            var jsonData = data;
            if (jsonData.result == 'fail') {
                alert(jsonData.msg);
            } else if (jsonData.result == 'success') {
                if (jsonData.depth_category_yn == 'Y') {
                    getCategory('big', '', '');
                    $('.no_table_category_depth').show();
                } else {
                    getCategory('big', '', '');
                    $('#category_mid option').remove();
                    $('#category_sml option').remove();
                    $('#category_itm option').remove();
                    $('.no_table_category_depth').hide();
                }
            }
        },
        error: function (a, s) {
            alert('처리중 문제가 발생하였습니다.');
            return;
        },
    });
}

function doCopy(no) {
    if (legacyAdminPending) return;
    var con = confirm('게시글을 복사하시겠습니까? 같은 게시판에 복사됩니다.');

    var param = '';
    param = 'no=' + no + '&mode=board.copy';

    if (con) {
        legacyAdminAjax({
            type: 'POST',
            url: './ajax/board.process.php',
            data: param,
            cache: false,
            dataType: 'json',
            success: function (data) {
                var jsonData = data;

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    location.href = './board.list.php?board_no=' + $('#board_no').val();
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}
