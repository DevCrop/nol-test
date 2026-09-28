var datepicker_default = {
	closeText : "닫기",
	prevText : "이전달",
	nextText : "다음달",
	currentText : "오늘",
	changeYear: true,
	firstDay : 0,
	isRTL : false,
	yearSuffix : '',
	viewMode: "years",
	showButtonPanel: true
}

datepicker_default.onClose = function (dateText, inst) {
var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
$(this).datepicker( "option", "defaultDate", new Date(year, month, 1) );
$(this).datepicker('setDate', new Date(year, month, 1));
}



datepicker_default.closeText = "선택";
datepicker_default.dateFormat = "yy";