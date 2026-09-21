const debounceTimeout = {};
const loader = document.getElementById('loader'); // 로더 DOM 요소

// 로더 표시 함수
const showLoader = () => {
    loader.style.display = 'flex'; // 로더를 표시
};

// 로더 숨김 함수
const hideLoader = () => {
    loader.style.display = 'none'; // 로더를 숨김
};

// Debounce 함수
const debounce = (func, delay) => {
    return (...args) => {
        clearTimeout(debounceTimeout.timer);
        debounceTimeout.timer = setTimeout(() => func(...args), delay);
    };
};

// Get current filters from the UI
const getFilters = () => {
    const dateInput = document.querySelector('input[name="year"]:checked')?.value || '';
    return {
        search_term: document.getElementById('search_term')?.value || '',
        category_no: document.querySelector('input[name="category_no"]:checked')?.value || '',
        year: dateInput,
        extra4: document.querySelector('input[name="extra4"]:checked')?.value || '',
        extra1: document.querySelector('input[name="extra1"]:checked')?.value || '',
        page: parseInt(document.querySelector('.no-pagination__link.--active span')?.textContent || '1', 10),
    };
};

const workHook = document.getElementById('works-hook');
const paginationHook = document.getElementById('pagination-pages');
const noItemHook = document.getElementById("no-item-hook");
const prevButton = document.getElementById('pagination-prev');
const nextButton = document.getElementById('pagination-next');

let currentPage = 1;
let totalPages = 1;

// Render a single work item
const renderWork = (item) => {
    const element = document.createElement('li');
    element.className = 'no-skin-gallery-item';
    element.innerHTML = `
		  <a href="./board.view.php?board_no=12&amp;no=${item.no}&amp;searchKeyword=&amp;searchColumn=&amp;page=1 data-cursor-text="See Project" data-cursor="-project">
				<figure>
					   <img src="/uploads/board/${item.thumb_image}" alt="${item.title}">
				</figure>
				<div class="no-skin-gallery-item__txt no-pd-sm--t">
				 <span>${item.extra1}</span>
					<h3 class="no-heading-md">${item.title}</h3>
					<div class="f ai-c fd-c no-gap-xs">
					    <div class="tag">${item.w_sdate || '시작 날짜 없음'} - ${item.w_edate || '종료 날짜 없음'}</div>
						<div class="tag">${item.extra4}</div>
					</div>
				</div>
			</a>
    `;
    workHook.appendChild(element);
};

// Render pagination dynamically
const renderPagination = (current, total) => {
    currentPage = current;
    totalPages = total;

    paginationHook.innerHTML = '';

    // Disable/Enable Prev and Next buttons
    prevButton.classList.toggle('disabled', currentPage === 1);
    nextButton.classList.toggle('disabled', currentPage === totalPages);

    for (let i = 1; i <= total; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.className = 'no-pagination__link';
        if (i === current) pageLink.classList.add('--active');
        pageLink.innerHTML = `<span>${i}</span>`;

        pageLink.addEventListener('click', (event) => {
            event.preventDefault();
            fetchData({ ...getFilters(), page: i });
        });

        paginationHook.appendChild(pageLink);
    }
};
const fetchData = async (filters) => {
    // 스크롤을 상단으로 이동
    window.scrollTo({ top: 0, behavior: 'smooth' }); // 부드럽게 이동 (behavior: 'smooth')

    workHook.innerHTML = '<p>Loading...</p>';
    paginationHook.innerHTML = '';
    noItemHook.innerHTML = ''; // noItemHook 초기화
    showLoader(); // 로더 표시

    try {
        const query = new URLSearchParams(filters).toString();
        const response = await fetch(`/api/work.php?${query}`);
        const resData = await response.json();

        workHook.innerHTML = '';

        if (resData.data && resData.data.length > 0) {
            resData.data.forEach(renderWork);
            renderPagination(resData.pagination.currentPage, resData.pagination.totalPages);
            paginationHook.parentElement.style.display = 'flex'; // pagination 표시
        } else {
            // 아이템이 없을 경우 noItemHook에 메시지 표시
            noItemHook.innerHTML = `<p class="no-body-xl --t-center">진행중인 공연이 없습니다.</p>`;
            paginationHook.parentElement.style.display = 'none'; // pagination 숨김
        }

        // 선택된 연도로 버튼 텍스트 변경
        const year = filters.year || '연도별';
        const dateButton = document.querySelector('.no-form-date-text');
        if (dateButton) {
            dateButton.textContent = year;

            // 연도 버튼을 클릭했을 때 data-list 요소에서 visible 클래스 제거
            const dataList = document.querySelector('.data-list');
            if (dataList && dataList.classList.contains('visible')) {
                dataList.classList.remove('visible');
            }
        }
    } catch (error) {
        workHook.innerHTML = '<p>데이터를 가져오는 중 오류가 발생했습니다.</p>';
        console.error('API 요청 실패:', error);
        paginationHook.parentElement.style.display = 'none'; // pagination 숨김
    } finally {
        hideLoader(); // 로더 숨김
    }
};



// Handle Previous Button Click
prevButton.addEventListener('click', (event) => {
    event.preventDefault();
    if (currentPage > 1) {
        fetchData({ ...getFilters(), page: currentPage - 1 });
    }
});

// Handle Next Button Click
nextButton.addEventListener('click', (event) => {
    event.preventDefault();
    if (currentPage < totalPages) {
        fetchData({ ...getFilters(), page: currentPage + 1 });
    }
});

// Initial fetch
fetchData(getFilters());

// Bind events for search input and filters
document.getElementById('search_term')?.addEventListener(
    'input',
    debounce(() => {
        fetchData(getFilters());
    }, 300)
);

document.querySelectorAll('input[name="year"], input[name="category_no"], input[name="extra4"], input[name="extra1"]').forEach((input) => {
    input.addEventListener('change', () => {
        fetchData(getFilters());
    });
});
