// 공통 설정
const commonSelectMenu = (selectors) => {
    selectors.forEach(selector => $(selector).selectmenu());
};

const initDatepickers = (elements) => {
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

// Bind by the form contract, never shared radio IDs (link targets reuse input5/6).
const toggleDateInputs = (groupName, dateNames) => {
    const radios = Array.from(document.querySelectorAll('input[type="radio"][name="' + groupName + '"]'));
    if (!radios.length) return;
    const scopes = new Set(radios.map(radio => radio.form || document));
    scopes.forEach(scope => {
        const group = Array.from(scope.querySelectorAll('input[type="radio"][name="' + groupName + '"]'));
        const sync = () => {
            const selected = group.find(radio => radio.checked);
            if (!selected || !['Y', 'N'].includes(selected.value)) return;
            dateNames.forEach(name => {
                scope.querySelectorAll('input[name="' + name + '"]').forEach(input => {
                    input.disabled = selected.value === 'Y';
                });
            });
        };
        group.forEach(radio => radio.addEventListener('change', sync));
        sync();
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

    // Only optional period groups present in this form control their own date fields.
    toggleDateInputs('b_none_limit', ['b_sdate', 'b_edate']);
    toggleDateInputs('b_none_view', ['b_sdate_view', 'b_edate_view']);
    toggleDateInputs('p_none_limit', ['p_sdate', 'p_edate']);
};

// 초기화 실행
$(document).ready(() => {
formInit();

});
