// 공통 설정
const commonSelectMenu = (selectors) => {
    selectors.forEach(selector => $(selector).selectmenu());
};

const initDatepickers = (elements) => {
	console.log(elements);
    elements.forEach(el => $(el).datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: [
            '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'
        ],
        monthNamesShort: [
            '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'
        ],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    }));
};

const toggleDateInputs = (radioButtons, dateInputs) => {
    radioButtons.forEach(radio => {
        $(radio).change(() => {
            const isDisabled = $(radioButtons[0]).is(':checked');
            dateInputs.forEach(input => $(input).attr('disabled', isDisabled));
        });
    });
};

// 초기화 함수
const formInit = () => {
    // SelectMenu 초기화
    commonSelectMenu([
        '#board_no', '#perpage', '#category_no', '#b_loc', '#_loc', '#searchColumn',
        '#skin', '#fileattach_cnt', '#target', '#lev', 'select[name="ch_lev"]'
    ]);

    // perpage에 특수 동작 추가
    $('#perpage').selectmenu({
        change: () => $('#frm').submit()
    });

    // Datepicker 초기화
	const dateElements = [
		'#sdate', '#edate', '#b_sdate', '#b_edate', 
		'#b_sdate_view', '#b_edate_view', // ✅ 노출기간 필드 추가
		'#p_sdate', '#p_edate', '#c_date', 
		'#r_sdate', '#r_edate', '#w_sdate', '#w_edate', 
		'#start_date', '#end_date'
	];



    initDatepickers(dateElements);

	// Date input 토글
	toggleDateInputs(
		['#input3', '#input4'], 
		['#b_sdate', '#b_edate']
	);
	toggleDateInputs(
		['#input5', '#input6'], 
		['#b_sdate_view', '#b_edate_view'] // ✅ 노출기간 토글 적용
	);
	toggleDateInputs(
		['#input7', '#input8'], 
		['#w_sdate', '#w_edate']
	);
	toggleDateInputs(
		['#input9', '#input10'], 
		['#start_date', '#end_date']
	);
};

// 초기화 실행
$(document).ready(() => {
formInit();

});
