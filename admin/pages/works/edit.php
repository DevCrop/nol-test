<!DOCTYPE html>
<html lang="ko">
<?php

header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 과거 날짜로 설정하여 캐시 무효화
header("Pragma: no-cache"); // HTTP 1.0


include_once "../../../inc/lib/base.class.php";

try {
    $db = DB::getInstance(); // PDO 인스턴스
} catch (Exception $e) {
    echo "데이터베이스 연결 오류: " . blue_safe_error($e);
    exit;
}

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'] ?? null; 
if (!$id) {
    echo "<script>
        alert('정보를 찾을 수 없습니다.');
        history.back();
    </script>";
    exit;
}

$data = DB::query("select * from nb_works where id = {$id}");

$depthnum = 6;
$pagenum = 1;



include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";


?>


</head>

<body>

    <div class="no-wrap">
        <!-- Header -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
				<input type="hidden" name="_method" value="update">
				<input type="hidden" name="id" value="<?=$id?>">

                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">공연 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>What's ON</span></li>
                                        <li class="no-breadcrumb-item"><span>공연 게시글 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">공연 등록</h2>
                            </div>
                            
                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                  
                                <div class="no-admin-block no-admin-pos no-admin-field">
									<h3 class="no-admin-title"><label for="title">제목</label></h3>
									<div class="no-admin-content">
										<input type="text" name="title" id="title" class="no-input--detail" placeholder="제목을 입력해주세요." value="<?= escape($data[0]['title'] ?? '') ?>" />
									</div>
								</div>

								<div class="no-admin-block no-admin-pos no-admin-field">
									<h3 class="no-admin-title"><label for="subtitle">소제목</label></h3>
									<div class="no-admin-content">
										<input type="text" name="subtitle" id="subtitle" class="no-input--detail" placeholder="소제목을 입력해주세요." value="<?= escape($data[0]['subtitle'] ?? '') ?>" />
									</div>
								</div>
								
						
								<div class="no-admin-block no-admin-pos no-admin-field">
									<h3 class="no-admin-title"><label for="created_at">생성 날짜</label></h3>
									<div class="no-admin-content">
										<input type="text" name="created_at" id="created_at" class="no-input--detail" placeholder="소제목을 입력해주세요." value="<?= escape($data[0]['created_at'] ?? '') ?>" />
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title">
										<strong>장르</strong>
									</h3>
									<div class="no-admin-content">
										<div class="no-radio-form">
											<?php foreach ($genres as $key => $value): ?>
												<label for="genre_<?= strtolower($value) ?>">
													<div class="no-radio-box">
														<input type="radio" name="genre" id="genre_<?= strtolower($value) ?>" value="<?= $key ?>" <?= isset($data[0]['genre']) && $data[0]['genre'] == $key ? 'checked' : '' ?>>
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text"><?= $value ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
								</div>

								<div class="no-admin-block extra_fields">
									<h3 class="no-admin-title">
										<strong>공연 기간</strong>
									</h3>
									<div class="no-admin-content no-admin-date no-flex-form">
										<input type="text" name="start_date" id="start_date" value="<?= escape($data[0]['start_date'] ?? '') ?>">
										<span></span>
										<input type="text" name="end_date" id="end_date" value="<?= escape($data[0]['end_date'] ?? '') ?>"  >
									</div>
								</div>

								<div class="no-admin-block no-admin-textarea">
									<h3 class="no-admin-title"><label for="show_time">공연 시간 정보</label></h3>
									<div class="no-admin-content">
										<textarea name="show_time" id="show_time" placeholder="공연 시간정보를 입력해주세요." ><?= escape($data[0]['show_time'] ?? '') ?></textarea>
									</div>
								</div>



								<div class="no-admin-block extra_fields">
									<h3 class="no-admin-title">
										<strong>공연 장소</strong>
									</h3>
									<div class="no-admin-content">
										<?php
										$adminPlaceOptions = [
											0 => getPlaceDisplayName(0, $data[0]['start_date'] ?? null),
											4 => getPlaceDisplayName(4, $data[0]['start_date'] ?? null),
											2 => 'NEMO',
											3 => '기타',
										];
										?>
										<div class="no-radio-form">
											<?php foreach ($adminPlaceOptions as $key => $label): ?>
												<label for="place_<?= $key ?>">
													<div class="no-radio-box">
														<input 
															type="radio" 
															name="place" 
															id="place_<?= $key ?>" 
															value="<?= $key ?>" 
															<?= isset($data[0]['place']) && (string)$data[0]['place'] === (string)$key ? 'checked' : '' ?>
														>
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
								<!--
								<div class="no-admin-block extra_fields">
									<h3 class="no-admin-title">
										<strong>배너 선택 여부</strong>
									</h3>
									<div class="no-admin-content">
										<div class="no-radio-form">
											<label for="is_featured_yes">
												<div class="no-radio-box">
													<input type="radio" name="is_featured" id="is_featured_yes" value="1" <?= isset($data[0]['is_featured']) && $data[0]['is_featured'] == 1 ? 'checked' : '' ?>>
													<span><i class="bx bx-radio-circle-marked"></i></span>
												</div>
												<span class="no-radio-text">배너 선택</span>
											</label>

											<label for="is_featured_no">
												<div class="no-radio-box">
													<input type="radio" name="is_featured" id="is_featured_no" value="0" <?= isset($data[0]['is_featured']) && $data[0]['is_featured'] == 0 ? 'checked' : '' ?>>
													<span><i class="bx bx-radio-circle-marked"></i></span>
												</div>
												<span class="no-radio-text">배너 미선택</span>
											</label>
										</div>
									</div>
								</div>-->

								<!-- <div class="no-admin-block extra_fields">
									<h3 class="no-admin-title">
										<strong>생성일</strong>
									</h3>
									<div class="no-admin-content">
										<input type="text" readonly value="<?= escape($data[0]['created_at'] ?? '') ?>" class="no-input--detail">
									</div>
								</div> -->

								<div class="no-admin-block no-admin-textarea">
									<h3 class="no-admin-title"><label for="notes">비고</label></h3>
									<div class="no-admin-content">
										<textarea name="notes" id="notes"><?= escape($data[0]['notes'] ?? '') ?></textarea>
									</div>
								</div>

								<div class="no-admin-block no-admin-textarea">
									<h3 class="no-admin-title"><label for="contact">문의</label></h3>
									<div class="no-admin-content">
										<textarea name="contact" id="contact"><?= escape($data[0]['contact'] ?? '') ?></textarea>
									</div>
								</div>

								<div class="no-admin-block ">
									<h3 class="no-admin-title"><label for="running_time">러닝 타임</label></h3>
									<div class="no-admin-content">
										<input type="text" name="running_time" id="running_time" class="no-input--detail" placeholder="러닝 타임을 입력해주세요.." value="<?= escape($data[0]['running_time'] ?? '') ?>" />
									</div>
								</div>

								<div class="no-admin-block ">
									<h3 class="no-admin-title"><label for="viewing_age">관람 연령</label></h3>
									<div class="no-admin-content">
										<input type="text" name="viewing_age" id="viewing_age" class="no-input--detail" placeholder="관람 연령을 입력해주세요.." value="<?= escape($data[0]['viewing_age'] ?? '') ?>" />
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="opera_glass_link">오페라글라스 링크</label></h3>
									<div class="no-admin-content">
										<input type="text" name="opera_glass_link" id="opera_glass_link" placeholder="오페라글라스 링크" class="no-input--detail" value="<?= escape($data[0]['opera_glass_link'] ?? '') ?>" />
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="ticket_link">티켓예매 링크</label></h3>
									<div class="no-admin-content">
										<input type="text" name="ticket_link" id="ticket_link" placeholder="티켓 링크" class="no-input--detail" value="<?= escape($data[0]['ticket_link'] ?? '') ?>" />
									</div>
								</div>

								<!--
								<div class="no-admin-block">
									<h3 class="no-admin-title">좌석 별 가격</h3>
									<input type="hidden" name="price" value='<?=$data[0]['price']?>'>
									<div id="price-hook" class="no-admin-content" style="gap: 16px;"></div>
								</div>-->

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="price">좌석 별 가격</label></h3>
									<div class="no-admin-content">
										<textarea name="price"  id="price"><?=$data[0]['price']?></textarea>
									</div>
								</div>

								<!--

                                <div class="no-admin-block">
									<h3 class="no-admin-title"><label for="thumbnail_image_txt">메인 상단 비쥬얼 (데스크탑)</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="thumbnail_image_txt" placeholder="파일을 선택해주세요." readonly />
											<div class="no-file-box">
												<input type="file" name="thumbnail_image" id="thumbnail_image" onchange="document.getElementById('thumbnail_image_txt').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 메인 페이지 상단 비쥬얼 이미지입니다.</span>
									<?php if($data[0]['thumbnail_image']): ?>
									<img src="/uploads/works/<?=$data[0]['thumbnail_image']?>" alt="" width="120">
									<?php endif; ?>
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="tablet_image_txt">메인 상단 비쥬얼 (태블릿)</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="tablet_image_txt" placeholder="파일을 선택해주세요." readonly />
											<div class="no-file-box">
												<input type="file" name="tablet_image" id="tablet_image" onchange="document.getElementById('tablet_image_txt').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 메인 페이지 상단 비쥬얼 이미지입니다.</span>
									<?php if($data[0]['tablet_image']): ?>
									<img src="/uploads/works/<?=$data[0]['tablet_image']?>" alt="" width="120">
									<?php endif; ?>
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="mobile_poster_image_txt">메인 상단 비쥬얼 (모바일)</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="mobile_poster_image_txt" value="" placeholder="파일을 선택해주세요." readonly />
											<div class="no-file-box">
												<input type="file" name="mobile_poster_image" id="mobile_poster_image" onchange="document.getElementById('mobile_poster_image_txt').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 모바일 환경일 때 나오는 메인 페이지 상단 비쥬얼 이미지입니다.</span>

										<?php if($data[0]['mobile_poster_image']): ?>
										<img src="/uploads/works/<?=$data[0]['mobile_poster_image']?>" alt="" width="120">
										<?php endif; ?>
									</div>
								</div>
								-->
								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="poster_image_txt">포스터 이미지</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="poster_image_txt" value="" placeholder="파일을 선택해주세요." readonly />
											<div class="no-file-box">
												<input type="file" name="poster_image" id="poster_image" onchange="document.getElementById('poster_image_txt').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>

										<?php if($data[0]['poster_image']): ?>
										<img src="/uploads/works/<?=$data[0]['poster_image']?>" alt="" width="120">
										<?php endif; ?>
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="detail_page_top_image_txt">상세페이지 상단 이미지</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="detail_page_top_image_txt" value="" placeholder="파일을 선택해주세요." readonly />
											<div class="no-file-box">
												<input type="file" name="detail_page_top_image" id="detail_page_top_image" onchange="document.getElementById('detail_page_top_image_txt').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 상세 페이지 상단 이미지입니다. 업로드를 안할 시, 일반 상단 텍스트만 표시됩니다.</span>

										<?php if($data[0]['detail_page_top_image']): ?>
										<img src="/uploads/works/<?=$data[0]['detail_page_top_image']?>" alt="" width="120">
										<?php endif; ?>
									</div>
								</div>


                                <!-- 내용 -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="contents">내용</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-admin-check">
                                            <textarea name="contents" id="contents"><?=$data[0]['contents']?></textarea>
                                        </div>
                                    </div>
                                </div>	

								<?php 
									// 기존 URL에서 id 값 제거
									$queryParams = $_GET;
									unset($queryParams['id']);
									$backUrl = "./index.php?" . http_build_query($queryParams);
								?>

                                <div class="no-items-center center">
                                    <a href="<?= $backUrl ?>" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="submit" class="no-btn no-btn--big no-btn--main" id="save-btn">저장</button>
									<button type="button" class="no-btn no-btn--big no-btn--delete-outline" id="delete-btn" data-id="<?=$id?>">삭제</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

		<script src="./js/works.process.js?v=<?=time()?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
        
    </div>
</body>
</html>
