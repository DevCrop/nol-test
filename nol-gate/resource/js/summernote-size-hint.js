/**
 * Summernote 에디터: 현재 커서 위치의 블록(제목/본문)과 글자 크기(px) 표시
 * 에디터 아래 "현재: H1 · 28px" 형태로 표시
 */
(function () {
    var BLOCK_TAGS = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'DIV', 'LI', 'TD', 'TH', 'BLOCKQUOTE', 'PRE'];

    function getBlockAndSize(editableEl) {
        var sel = window.getSelection();
        if (!sel || !sel.anchorNode) return null;
        var node = sel.anchorNode;
        if (node.nodeType === 3) node = node.parentElement;
        while (node && editableEl && editableEl.contains(node)) {
            if (node.nodeType === 1 && node.tagName) {
                var tag = node.tagName.toUpperCase();
                if (BLOCK_TAGS.indexOf(tag) !== -1) {
                    return {
                        tag: tag,
                        fontSize: window.getComputedStyle(node).fontSize
                    };
                }
            }
            node = node.parentElement;
        }
        return null;
    }

    function updateAllHints() {
        $('.note-editor').each(function () {
            var $hint = $(this).find('.note-current-size');
            if (!$hint.length) return;
            var $editable = $(this).find('.note-editable');
            if (!$editable.length) return;
            var sel = window.getSelection();
            if (!sel || !sel.anchorNode || !$editable[0].contains(sel.anchorNode)) {
                $hint.text('현재: -');
                return;
            }
            var info = getBlockAndSize($editable[0]);
            if (info) {
                $hint.text('현재: ' + info.tag + ' · ' + info.fontSize);
            } else {
                $hint.text('현재: -');
            }
        });
    }

    var selectionchangeBound = false;

    function attachHint() {
        $('.note-editor').each(function () {
            var $ed = $(this);
            if ($ed.find('.note-current-size').length) return;

            var $hint = $('<div class="note-current-size">현재: -</div>');
            $ed.append($hint);

            var $editable = $ed.find('.note-editable');
            if (!$editable.length) return;

            $editable.on('keyup mouseup focus', updateAllHints);
        });
        if (!$('.note-current-size').length) return;
        if (!selectionchangeBound) {
            selectionchangeBound = true;
            document.addEventListener('selectionchange', updateAllHints);
        }
        updateAllHints();
    }

    $(function () {
        setTimeout(attachHint, 300);
        setTimeout(attachHint, 1000);
    });
})();
