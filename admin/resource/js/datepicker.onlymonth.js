var datepicker_default = {
	closeText : "닫기",
	prevText : "이전달",
	nextText : "다음달",
	currentText : "오늘",
	changeMonth: true,
	changeYear: true,
	monthNames : [ "1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월" ],
	monthNamesShort : [ "1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월" ],
	dayNames : [ "일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일" ],
	dayNamesShort : [ "일", "월", "화", "수", "목", "금", "토" ],
	dayNamesMin : [ "일", "월", "화", "수", "목", "금", "토" ],
	weekHeader : "주",
	firstDay : 0,
	isRTL : false,
	showMonthAfterYear : true,
	yearSuffix : '',
	showButtonPanel: true
}

datepicker_default.onClose = function (dateText, inst) {
var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
$(this).datepicker( "option", "defaultDate", new Date(year, month, 1) );
$(this).datepicker('setDate', new Date(year, month, 1));
}

datepicker_default.beforeShow = function () {
var selectDate = $("#sdate").val().split("-");
var year = Number(selectDate[0]);
var month = Number(selectDate[1]) - 1;
$(this).datepicker( "option", "defaultDate", new Date(year, month, 1) );
}

datepicker_default.closeText = "선택";
datepicker_default.dateFormat = "yy-mm";