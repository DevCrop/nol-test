<section class="no-sub-list no-pd-2xl--y" >

    <div class="no-container-xl"  >
        <div class="f ai-c jc-sb f-w no-gap-lg" <?=$aos_title?>>
            <!---category-->
            <?php include_once $STATIC_ROOT . '/pages/board/components/category.php'; ?>
            <!---search---->
            <div class="--mobile-full">
                <div class="no-form-search__type_A --mobile-full">
                    <input type="text" name="searchKeyword" id="searchKeyword" placeholder="검색어를 입력해주세요.">
                    <button type="button" class="no-form-search__type_A-icon" aria-label="search" onclick="doSearch();">
                        <i class="fa-light fa-magnifying-glass" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="no-pd-xl--t" <?=$aos_content?>>
            <div class="no-skin-list">
                <div class="--table">
                    <table class="no-skin-list-table">
                        <colgroup>
                            <col style="width: 9%;">
                            <col style="width: 9%">
                            <col style="width: 60%;">
                            <col style="width: 10%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <thead>
                            <tr class="no-body-lg --fw-semibold">
                                <th class="head">번호</th>
                                <th class="head">카테고리</th>
                                <th class="--tal head">제목</th>
                                <th class="head">조회수</th>
                                <th class="head">날짜</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($arrResultSet) && is_array($arrResultSet)) : ?>
                                <?php 
                                $counter = count($arrResultSet); // 데이터 개수로 카운터 초기화
                                foreach ($arrResultSet as $k => $v) :
                                    $title = htmlspecialchars($v['title'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $contents = htmlspecialchars_decode(stripslashes($v['contents'] ?? ''), ENT_QUOTES | ENT_HTML5);
                                    $link = "./board.view.php?board_no=" . ($board_no ?? '') . "&no=" . ($v['no'] ?? '') .
                                        "&searchKeyword=" . base64_encode($searchKeyword ?? '') .
                                        "&searchColumn=" . base64_encode($searchColumn ?? '') . "&page=" . ($page ?? '');
                                    $formattedDate = !empty($v['regdate']) ? date('Y.m.d', strtotime($v['regdate'])) : '';
                                ?>
                                <tr>
                                    <td class="body --mobile-full no-notice-num">
                                        <span class=""><?= $counter ?></span> <!-- 역순 출력 -->
                                    </td>
                                    <td class="body">
                                        <span class="category"><?= htmlspecialchars($v['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td class="--t-start body link">
                                        <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" class="no-clr-text-title no-body-md --fw-semibold">
                                            <strong><?= htmlspecialchars_decode($title, ENT_QUOTES | ENT_HTML5) ?></strong>
                                        </a>
                                        <span class="" data-label="등록일"><?= $formattedDate ?></span>
                                    </td>
                                    <td class="body">
                                        <span class="" data-label="조회수"><?= (int)($v['read_cnt'] ?? 0) ?></span>
                                    </td>
                                    <td class="body time">
                                        <span class="" data-label="등록일"><?= $formattedDate ?></span>
                                    </td>
                                </tr>
                                <?php 
                                $counter--; // 카운터 감소
                                endforeach; 
                                ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="body --text-center no-data-message">
                                        공지사항이 없습니다.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once $STATIC_ROOT . '/pages/board/components/pagination.php'; ?>
</section>
