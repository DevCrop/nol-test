export function summernoteInit(selector) {
  $(document).ready(function () {
    if ($(selector).length > 0) {
      $(selector).summernote({
        lang: "ko-KR",
        height: 300,
        placeholder: "내용을 입력하세요",
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
          onInit: function() {
            var self = this;
            setTimeout(function() {
              var $editable = $(self).next('.note-editor').find('.note-editable');
              if ($editable.length) convertEmptyPToDiv($editable[0]);
            }, 100);
          }
        }
      });
    }
  });
}
