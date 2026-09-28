<?php
?>
<div id="main-search" class="no-search" data-lenis-prevent>
    <!-- 공통 닫기 버튼 (drawer/search 공용) -->
    <div class="no-modal__close--container">
        <button type="button" class="no-modal__close" aria-label="닫기">
            <i class="fa-regular fa-xmark"></i>
        </button>
    </div>
    <div class="no-search__container no-container-2xl">
        <div class="no-search__inner">
            <div class="no-search__form-wrapper">
                <form action="/search" method="GET" class="no-search__form" id="searchForm">
                    <div class="no-search__input-wrapper">
                        <input type="text" name="q" class="no-search__input" id="searchInput" placeholder="검색어를 입력해주세요."
                            autocomplete="off" aria-label="검색어 입력">
                        <button type="submit" class="no-search__submit" aria-label="검색">
                            <i class="fa-regular fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                <!-- 검색 히스토리 -->
                <div class="no-search__history" id="searchHistory" style="display: none;">
                    <div class="no-search__history-header">
                        <span class="no-search__history-title">최근 검색어</span>
                        <button type="button" class="no-search__history-clear" id="clearAllHistory" aria-label="전체 삭제">
                            전체 삭제
                        </button>
                    </div>
                    <ul class="no-search__history-list" id="searchHistoryList">
                        <!-- 히스토리 아이템이 여기에 동적으로 추가됩니다 -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>