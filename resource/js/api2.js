document.addEventListener('DOMContentLoaded', () => {
    const debounceTimeout = { timer: null };
    const workHook = document.getElementById('works-hook');
    const paginationHook = document.getElementById('pagination-pages');
    const noItemHook = document.getElementById('no-item-hook');
    const loader = document.getElementById('loader');

	let currentPage = 1;
	let totalPages = 1;
	let lastPage = 1; // 마지막 페이지를 기억


    const showLoader = () => {
        loader.style.display = 'flex';
    };

    const hideLoader = () => {
        loader.style.display = 'none';
    };

    const debounce = (func, delay) => {
        return (...args) => {
            clearTimeout(debounceTimeout.timer);
            debounceTimeout.timer = setTimeout(() => func(...args), delay);
        };
    };

    const syncFilters = (sourceSelector, targetSelector) => {
        document.querySelectorAll(sourceSelector).forEach((sourceInput) => {
            const targetInput = document.querySelector(`${targetSelector}[name="${sourceInput.name}"][value="${sourceInput.value}"]`);
            if (targetInput) {
                targetInput.checked = sourceInput.checked;
            }
        });
    };



    const placesMap = {
        0: '신한카드홀',
        1: '마스터카드홀',
        2: 'NEMO',
        3: '기타',
        4: 'SOL트래블홀',
    };
    // 공연 시작일(start_date) 기준으로 홀명 변경 시점을 적용한다.
    const getPlaceLabel = (place, startDate) => {
        const placeKey = String(place);
        const switchDates = {
            '0': '2026-03-01',
            '4': '2026-03-01',
        };

        if (!switchDates[placeKey] || !startDate) {
            return placesMap[place] ?? '장소 없음';
        }

        const performanceStartDate = new Date(startDate);
        const switchDate = new Date(switchDates[placeKey]);

        if (isNaN(performanceStartDate.getTime()) || performanceStartDate < switchDate) {
            return placesMap[place] ?? '장소 없음';
        }

        if (placeKey === '0') return '우리은행홀';
        if (placeKey === '4') return '우리WON뱅킹홀';
        return placesMap[place] ?? '장소 없음';
    };

    const genresMap = {
        0: '뮤지컬',
        1: '콘서트',
        2: '이벤트',
        3: '기타',
    };



    const filterModalEvent = (lenis) => {
        const filterBtn = document.querySelector('.no-filter-btn button');
        const filterModal = document.querySelector('.no-filter-modal');
        const filterBtnClose = document.querySelector('.no-filter-modal-close button');
        const submitBtn = document.querySelector('.no-filter-modal-submit-btn');

        if (!filterBtn || !filterModal) return;

        filterBtn.addEventListener('click', function () {
            syncFilters('input', '.no-filter-modal input');
            filterModal.classList.add('visible');
            lenis?.stop();
        });

        const closeModal = () => {
            syncFilters('.no-filter-modal input', 'input');
            filterModal.classList.remove('visible');
            lenis?.start();
        };

        filterBtnClose.addEventListener('click', closeModal);

        if (submitBtn) {
            submitBtn.addEventListener('click', closeModal);
        }
    };

    const getFilters = () => {
        const yearInput = document.querySelector('input[name="year"]:checked')?.value || '';
        const endDateFilter = document.querySelector('input[name="end_date"]:checked')?.value || 'all';
        return {
            search_term: document.getElementById('search_term')?.value || '',
            year: yearInput,
            place: document.querySelector('input[name="place"]:checked')?.value || '',
            genre: document.querySelector('input[name="genre"]:checked')?.value || '',
            end_date: endDateFilter,
            page: currentPage,
        };
    };
	
    const updateFilterUI = (filters) => {
        if (filters.search_term) document.getElementById('search_term').value = filters.search_term;
        if (filters.year) document.querySelector(`input[name="year"][value="${filters.year}"]`)?.click();
        if (filters.place) document.querySelector(`input[name="place"][value="${filters.place}"]`)?.click();
        if (filters.genre) document.querySelector(`input[name="genre"][value="${filters.genre}"]`)?.click();
        if (filters.end_date) document.querySelector(`input[name="end_date"][value="${filters.end_date}"]`)?.click();
    };
	/*
    const renderWork = (item) => {
        const element = document.createElement('li');
        element.className = 'no-skin-gallery-item';
        element.innerHTML = `
            <a href="./view.php?id=${item.id}" data-cursor-text="View" data-cursor="-project">
                <figure>
					<div class="no-skin-gallery-item__circle">
						<span>${genresMap[item.genre] || '장소 없음'}</span>
					</div>
                    <img src="/uploads/works/${item.poster_image}" alt="${item.title}">
                </figure>
                <div class="no-skin-gallery-item__txt no-pd-sm--t">
                    <div class="no-skin-gallery-item__info ">
						<div class="no-body-sm">
							${placesMap[item.place] || '장소 없음'}
						</div>
						<div class="no-body-sm">
							${item.start_date || '시작 날짜 없음'} - ${item.end_date || '종료 날짜 없음'}
						</div>
                    </div>
                    <h3 class="no-heading-md">${item.title}</h3>
                    
                </div>
            </a>
        `;
        workHook.appendChild(element);
    };*/

	const renderWork = (item) => {
    const element = document.createElement('li');
    element.className = 'no-skin-gallery-item';

    // 현재 페이지의 GET 파라미터 가져오기
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('id', item.id); // 작품 ID 추가

    element.innerHTML = `
        <a href="./view.php?${currentParams.toString()}" data-cursor-text="View" data-cursor="-project">
            <figure>
                <div class="no-skin-gallery-item__circle">
                    <span>${genresMap[item.genre] || '장르 없음'}</span>
                </div>
                <img src="/uploads/works/${item.poster_image}" alt="${item.title}">
            </figure>
            <div class="no-skin-gallery-item__txt no-pd-sm--t">
                <div class="no-skin-gallery-item__info">
                    <div class="no-body-sm">
                        ${getPlaceLabel(item.place, item.start_date) || '장소 없음'}
                    </div>
                    <div class="no-body-sm">
                        ${item.start_date || '시작 날짜 없음'} - ${item.end_date || '종료 날짜 없음'}
                    </div>
                </div>
                <h3 class="no-heading-md">${item.title}</h3>
            </div>
        </a>
    `;

    workHook.appendChild(element);
};





    const renderPagination = (current, total) => {
        currentPage = current;
        totalPages = total;

        const paginationNumHook = document.getElementById('pagination-pages');
        paginationNumHook.innerHTML = ''; 

        const maxVisiblePages = 5;
        const currentBlock = Math.ceil(current / maxVisiblePages);
        const startPage = (currentBlock - 1) * maxVisiblePages + 1;
        const endPage = Math.min(startPage + maxVisiblePages - 1, total);

        const firstPageButton = document.querySelector('.no-pagination__first');
        const prevButton = document.querySelector('.no-pagination__prev');
        const nextButton = document.querySelector('.no-pagination__next');
        const lastPageButton = document.querySelector('.no-pagination__last');

        firstPageButton.replaceWith(firstPageButton.cloneNode(true));
        prevButton.replaceWith(prevButton.cloneNode(true));
        nextButton.replaceWith(nextButton.cloneNode(true));
        lastPageButton.replaceWith(lastPageButton.cloneNode(true));

        document.querySelector('.no-pagination__first').addEventListener('click', (event) => {
            event.preventDefault();
            if (current !== 1) fetchData({ ...getFilters(), page: 1 });
        });

        document.querySelector('.no-pagination__prev').addEventListener('click', (event) => {
            event.preventDefault();
            if (current > 1) fetchData({ ...getFilters(), page: current - 1 });
        });

        for (let i = startPage; i <= endPage; i++) {
            const pageLink = document.createElement('a');
            pageLink.href = '#';
            pageLink.className = 'no-pagination__link';
            if (i === current) pageLink.classList.add('--active');
            pageLink.innerHTML = `<span>${i}</span>`;

            pageLink.addEventListener('click', (event) => {
                event.preventDefault();
                fetchData({ ...getFilters(), page: i });
            });

            paginationNumHook.appendChild(pageLink);
        }

        document.querySelector('.no-pagination__next').addEventListener('click', (event) => {
            event.preventDefault();
            if (current < total) fetchData({ ...getFilters(), page: current + 1 });
        });

        document.querySelector('.no-pagination__last').addEventListener('click', (event) => {
            event.preventDefault();
            if (current !== total) fetchData({ ...getFilters(), page: total });
        });
    };
	
	const fetchData = async (filters) => {
		window.scrollTo({ top: 0, behavior: 'smooth' });

		workHook.innerHTML = '<p>Loading...</p>';
		paginationHook.innerHTML = '';
		noItemHook.innerHTML = '';
		showLoader();

		try {
			const query = new URLSearchParams(filters).toString();
			const response = await fetch(`/api/work2.php?${query}`);
			const resData = await response.json();
			
			history.pushState(filters, '', `?${query}`); // URL 업데이트

			workHook.innerHTML = '';
			console.log(resData.totalItems);
			if (resData.data && resData.data.length > 0) {
				resData.data.forEach(renderWork);
				renderPagination(resData.pagination.currentPage, resData.pagination.totalPages);
				paginationHook.parentElement.style.display = 'flex';
			} else {
				noItemHook.innerHTML = '<p class="no-body-xl --t-center">해당 조건에 맞는 작품이 없습니다.</p>';
				paginationHook.parentElement.style.display = 'none';
			}
		} catch (error) {
			workHook.innerHTML = '<p>데이터를 가져오는 중 오류가 발생했습니다.</p>';
			console.error('API 요청 실패:', error);
			paginationHook.parentElement.style.display = 'none';
		} finally {
			hideLoader();
		}
	};



	const bindFilterEvents = () => {
		const dateButton = document.querySelector('.no-date-btn .no-form-date-text');
		const yearInputs = document.querySelectorAll('input[name="year"]');

		const updateDateText = (selectedValue) => {
			if (dateButton) {
				dateButton.textContent = selectedValue === 'all' ? '전체' : selectedValue;
			}
		};

		// 연도 필터 변경 이벤트
		yearInputs.forEach((input) => {
			input.addEventListener('change', (event) => {
				const selectedValue = event.target.value;
				updateDateText(selectedValue);
				currentPage = 1; // 페이지를 1로 초기화
				fetchData(getFilters());
			});
		});

		// 기본값 설정
		const defaultInput = document.querySelector('input[name="year"]:checked');
		if (defaultInput) {
			updateDateText(defaultInput.value);
		}

		// 검색어 필터 변경 이벤트
		document.getElementById('search_term')?.addEventListener(
			'input',
			debounce(() => {
				currentPage = 1; // 검색어 변경 시 페이지를 1로 초기화
				fetchData(getFilters());
			}, 300)
		);

		// 장소, 장르, 종료일 필터 변경 이벤트
		document.querySelectorAll('input[name="place"], input[name="genre"], input[name="end_date"]').forEach((input) => {
			input.addEventListener('change', () => {
				currentPage = 1; // 필터 변경 시 페이지를 1로 초기화
				fetchData(getFilters());
			});
		});
	};


	window.addEventListener('popstate', (event) => {
		if (event.state) {
			const filters = event.state;
			updateFilterUI(filters); // UI 업데이트
			fetchData(filters); // 데이터 가져오기
		}
	});


    const lenis = { start: () => console.log('lenis start'), stop: () => console.log('lenis stop') };

	const params = new URLSearchParams(window.location.search);
	const initialFilters = {
		search_term: params.get('search_term') || '',
		year: params.get('year') || 'all',
		place: params.get('place') || 'all',
		genre: params.get('genre') || 'all',
		end_date: params.get('end_date') || 'all',
		page: parseInt(params.get('page'), 10) || 1,
	};

    updateFilterUI(initialFilters); // Update UI based on initial filters
    fetchData(initialFilters); // Fetch data based on initial filters
    filterModalEvent(lenis);
    bindFilterEvents();
});


