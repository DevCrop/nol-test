<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php';

function normalizeRequestNoticeHtml($html) {
	if (!is_string($html) || $html === '') {
		return $html;
	}

	$html = preg_replace('/font-family:\s*"([^"]+)";/i', "font-family: '$1';", $html);
	$html = preg_replace('/\sclass=(["\'])MsoNormal\1/i', '', $html);

	return $html;
}
?>

<?php
		$pdo = DB::getInstance();

	 try {
        $current_date = date('Y-m-d');

        $query = "SELECT no, contents, r_sdate, r_edate
				  FROM nb_request_manage
				  WHERE sitekey = :sitekey
				    AND r_view = 'Y'
				    AND :current_date BETWEEN r_sdate AND r_edate
				  ORDER BY regdate DESC, no DESC
				  LIMIT 1";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
			'sitekey' => $NO_SITE_UNIQUE_KEY,
			'current_date' => $current_date
		]);

        $requestNotice = $stmt->fetch(PDO::FETCH_ASSOC);
		$is_form_visible = $requestNotice ? true : false;
    }  
	catch (Exception $e) {
		$is_form_visible = false; // 에러 발생 시 폼 숨기기
		error_log("Error fetching date range: " . blue_safe_error($e));
	}

	// 폼 비활성화일 경우 차단
	if (!$is_form_visible) {
		echo "<script>alert('지금은 문의기간이 아닙니다.'); history.back();</script>";
		exit;
	}

?>


<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';

?>

<!-- contents -->
<main class="no-sub no-pd-2xl--t">
	
	<div class="loader-container" id="loader" data-lenis-prevent="">
		<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
			<path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
			s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
			c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path>
			<path fill="#000" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0
			C22.32,8.481,24.301,9.057,26.013,10.047z">
				<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="1.4s" repeatCount="indefinite"></animateTransform>
			</path>
		</svg>
	</div>

	<?php $noticeContents = normalizeRequestNoticeHtml($requestNotice['contents'] ?? ''); ?>
	<section class="no-sub-inquiry-section no-mg-lg--t">
		<div class="no-container-lg">
			<div class="--box notice">
				<?=$noticeContents?>
			</div>
		</div>
	</section>

	<form action="" method="POST" id ="frm" enctype="multipart/form-data">
		<input type="hidden" name="_csrf" value="<?=htmlspecialchars(\Security\Csrf::token(), ENT_QUOTES, 'UTF-8')?>">
		<section class="no-sub-inquiry no-pd-xl--t no-pd-2xl--b">
			<div class="no-container-lg">
				<div class="no-form-container">
					<div class="no-form-row">
						<div class="no-form-group">
							 <label for="performance_name" class="no-form-label">
								<span class="no-form-text">
									공연명
								</span>
								<div class="no-form-control">
									<input type="text" name="performance_name" id="performance_name"
										placeholder="공연명을 입력해주세요">
								</div>
							</label>
						</div>
						<div class="no-form-group">
							 <label for="organization_name" class="no-form-label">
								<span class="no-form-text">
									단체 (공연제작사)명
								</span>
								<div class="no-form-control">
									<input type="text" name="organization_name" id="organization_name"
										placeholder="단체 혹은 공연제작사의 이름을 입력해주세요">
								</div>
							</label>
						</div>
					</div>
					<div class="no-form-row">
						<div class="no-form-group">
							 <label for="manager_name" class="no-form-label">
								<span class="no-form-text">
									담당자명
								</span>
								<div class="no-form-control">
									<input type="text" name="manager_name" id="manager_name" placeholder="담당자명을 입력해주세요">
								</div>
							</label>
						</div>
						<div class="no-form-group">
							<label for="phone" class="no-form-label">
								<span class="no-form-text">
									담담자 연락처
								</span>
								<div class="no-form-control">
									<input type="text" name="phone" id="phone" placeholder="담당자 연락처를 입력해주세요">
								</div>
							</label>
						</div>
					</div>
					<div class="no-form-group">
						  <label for="email" class="no-form-label">
							<span class="no-form-text">
								담담자 이메일
							</span>
							<div class="no-form-control">
								<input type="email" name="email" id="email" placeholder="담당자 이메일를 입력해주세요">
							</div>
						</label>
					</div>
					<div class="no-form-group">
					  <label for="contents" class="no-form-label">
							<span class="no-form-text">
								내용
							</span>
							<div class="no-form-control">
								<textarea name="contents" id="contents" placeholder="전하고자하는 내용을 입력해주세요"></textarea>
							</div>
						</label>
					</div>
					<!--
					<div class="no-form-group">
						<label for="file_attach" class="no-form-label f ai-c jc-sb">
							<span class="no-form-text  no-pd-sm--b">
								첨부파일
							</span>
							<div class="no-form-file">
								<div class="fake-field-file">Attach file</div>
								<div aria-label="Attach file" class="btn">파일첨부
									<input type="file" name="file_attach" id="file_attach" class="field-file" />
								</div>
							</div>
						</label>
					</div>-->
					<div class="no-form-row">
						<div class="no-form-group">
							<p class="no-form-text no-pd-sm--b">
								스팸방지문자
							</p>
							<div class="no-form-captcha">
								<div class="no-form-captcha__box">
									<div class="f no-gap-xs ai-c">
										<div class="no-form-captcha__img">
											<img src="/inc/lib/captcha.n.php" alt="captcha">
										</div>
										<div class="no-form-captcha__reload no-radius-md">
											<button type="button">
												<i class="fa-solid fa-rotate-right"></i>
											</button>
										</div>
									</div>

									<input type="text" name="r_captcha" id="r_captcha" maxlength="5"
										placeholder="스팸방지 5자리를 입력해 주세요." autocomplete="off">
								</div>
							</div>
						</div>

						<div class="no-form-group">
							<p class="no-form-text no-pd-sm--b">
								개인정보 수집 및 이용 안내
							</p>
							<div class="no-form-checkbox">
								<label for="check " class="--w-100">
									<input type="checkbox" name="check" id="check">
									<span>
										<button type="button" class="no-modal-privacy-btn --w-100 --t-start" data-modal-id="privacy">자세히 보기</button>
										<i class="fa-regular fa-plus"></i>
										<!--
										<div>
											<div class="no-form-checkbox__box">
												<i class="fa-regular fa-check" aria-hidden="true"></i>
											</div>
										</div>-->
									</span>

								</label>
								
							</div>
						</div>
					</div>
					<!--
					<div class="no-form-group">
						<label for="file_attach" class="no-form-label">
							<div class="no-form-file">
								<i class="fa-solid fa-folder-arrow-up"></i>
								<div class="fake-field-file">파일업로드하기</div>
								<input type="file" id="file_attach" class="field-file" multiple />
							</div>
							<div class="no-form-feedback">
								<i class="fa-solid fa-circle-info"></i>
								<span>파일당 10mb 이하 · 첨부 파일은 최대 10개</span>
							</div>
						</label>
						<div class="--file-info">
							<ul></ul>
						</div>
					</div>-->


					<div>
						<p class="no-form-text no-pd-sm--b">
							파일첨부
						</p>
						<div class="f fd-c no-gap-sm">
							<!-- 첫 번째 파일 첨부 -->
							<div class="no-form-group">
								<label for="file_1" class="no-form-label">
									<div class="no-form-file no-file-control">
										<!-- 파일 입력 텍스트 필드 -->
										<input type="text" class="no-fake-file" id="file_1_txt" placeholder="파일을 선택해주세요." readonly disabled>
										<!-- 파일 업로드 박스 -->
										<div class="no-file-box">
											<input type="file" id="file_1" name="file_1" onchange="document.getElementById('file_1_txt').value = this.value">
											<button type="button" class="no-btn no-btn--main">파일찾기</button>
										</div>
									</div>
								</label>
							</div>

							<!-- 두 번째 파일 첨부 -->
							<div class="no-form-group">
								<label for="file_2" class="no-form-label">
									<div class="no-form-file no-file-control">
										<!-- 파일 입력 텍스트 필드 -->
										<input type="text" class="no-fake-file" id="file_2_txt" placeholder="파일을 선택해주세요." readonly disabled>
										<!-- 파일 업로드 박스 -->
										<div class="no-file-box">
											<input type="file" id="file_2" name="file_2" onchange="document.getElementById('file_2_txt').value = this.value">
											<button type="button" class="no-btn no-btn--main">파일찾기</button>
										</div>
									</div>
								</label>
							</div>

							<!-- 세 번째 파일 첨부 -->
							<div class="no-form-group">
								<label for="file_3" class="no-form-label">
									<div class="no-form-file no-file-control">
										<!-- 파일 입력 텍스트 필드 -->
										<input type="text" class="no-fake-file" id="file_3_txt" placeholder="파일을 선택해주세요." readonly disabled>
										<!-- 파일 업로드 박스 -->
										<div class="no-file-box">
											<input type="file" id="file_3" name="file_3" onchange="document.getElementById('file_3_txt').value = this.value">
											<button type="button" class="no-btn no-btn--main">파일찾기</button>
										</div>
									</div>
								</label>
							</div>

							<div class="no-form-group">
								<label for="file_4" class="no-form-label">
									<div class="no-form-file no-file-control">
										<!-- 파일 입력 텍스트 필드 -->
										<input type="text" class="no-fake-file" id="file_4_txt" placeholder="파일을 선택해주세요." readonly disabled>
										<!-- 파일 업로드 박스 -->
										<div class="no-file-box">
											<input type="file" id="file_4" name="file_4" onchange="document.getElementById('file_4_txt').value = this.value">
											<button type="button" class="no-btn no-btn--main">파일찾기</button>
										</div>
									</div>
								</label>
							</div>

							<div class="no-form-group">
								<label for="file_5" class="no-form-label">
									<div class="no-form-file no-file-control">
										<!-- 파일 입력 텍스트 필드 -->
										<input type="text" class="no-fake-file" id="file_5_txt" placeholder="파일을 선택해주세요." readonly disabled>
										<!-- 파일 업로드 박스 -->
										<div class="no-file-box">
											<input type="file" id="file_5" name="file_5" onchange="document.getElementById('file_5_txt').value = this.value">
											<button type="button" class="no-btn no-btn--main">파일찾기</button>
										</div>
									</div>
								</label>
							</div>

						</div>

						<!-- 파일 업로드 정보 -->
						<div class="no-form-feedback">
							<i class="fa-solid fa-circle-info"></i>
							<span>파일당 20mb 이하 · 첨부 파일은 최대 5개까지 가능합니다.</span>
						</div>
						<!-- 파일 업로드 정보 -->
						<div class="no-form-feedback --no-mg-none">
							<i class="fa-solid fa-circle-info"></i>
							<span>영상 및 음원 등 고용량 파일은 클라우드 링크 등으로 첨부 바랍니다.</span>
						</div>
						<div class="no-form-feedback --no-mg-none">
							<i class="fa-solid fa-circle-info"></i>
							<span>zip, xls, xlsx, pdf, ppt, pptx, word, doc, docx, hwp 확장자만 가능합니다.</span>
						</div>
					</div>
				</div>
				<div class="no-form-action --full no-pd-xl--t">
					<button type="button" class="submit-btn no-btn no-btn__fill--primary --form-radius">전송하기</button>
				</div>
			</div>
		</section>
	</form>

</main>


<script>

	document.addEventListener("DOMContentLoaded", function () {
		const loader = document.getElementById("loader");
		const body = document.querySelector("body");

		// ✅ Lenis 전역 인스턴스 생성 및 raf 실행 (이미 선언된 경우 생략 가능)
		if (typeof lenis === "undefined") {
			var lenis = new Lenis();
			
			function raf(time) {
				lenis.raf(time);
				requestAnimationFrame(raf);
			}
			requestAnimationFrame(raf);
		}

		// 캡차 새로고침 버튼 클릭 시
		$(".no-form-captcha__reload button").click(function () {
			$(".no-form-captcha__img img").attr("src", "/inc/lib/captcha.n.php?" + new Date().getTime());
		});

		function doRequest() {
			// 입력 필드 검증
			if ($("#performance_name").val() == "") {
				alert("공연명을 입력해주세요.");
				$("#performance_name").focus();
				return;
			}
			if ($("#organization_name").val() == "") {
				alert("단체 (공연제작사) 명을 입력하세요.");
				$("#organization_name").focus();
				return;
			}
			if ($("#manager_name").val() == "") {
				alert("담당자명을 입력하세요.");
				$("#manager_name").focus();
				return;
			}
			if ($("#phone").val() == "") {
				alert("담당자 연락처를 입력하세요.");
				$("#phone").focus();
				return;
			}
			if ($("#email").val() == "") {
				alert("담당자 이메일을 입력하세요.");
				$("#email").focus();
				return;
			}
			if ($("#contents").val() == "") {
				alert("내용을 입력해주세요.");
				$("#contents").focus();
				return;
			}
			if ($("#r_captcha").val() == "") {
				alert("보안문자를 입력해주세요.");
				$("#r_captcha").focus();
				return;
			}

			// ✅ Lenis 스크롤 멈추기
			if (typeof lenis !== "undefined") {
				lenis.stop();
				console.log("Lenis 스크롤 중지됨 ✅");
			}

			// 로딩 표시
			loader.classList.add("active");
			$(".submit-btn").prop("disabled", true);

			// FormData 객체 생성
			var formData = new FormData(document.getElementById("frm"));

			$.ajax({
				type: "POST",
				url: "/module/ajax/request.process.php",
				data: formData,
				processData: false,
				contentType: false,
				cache: false,
				success: function (data) {
					var jsonData = JSON.parse(data);

					// ✅ Lenis 스크롤 다시 활성화
					if (typeof lenis !== "undefined") {
						lenis.start();
						console.log("Lenis 스크롤 다시 활성화됨 ✅");
					}

					// 로딩 숨기기
					loader.classList.remove("active");
					$(".submit-btn").prop("disabled", false);

					if (jsonData.result === "fail") {
						alert(jsonData.msg);
						$(".no-form-captcha__img img").attr("src", "/inc/lib/captcha.php?" + new Date().getTime());
						$("#r_captcha").val(""); // 입력 필드 초기화
					} else if (jsonData.result === "success") {
						alert(jsonData.msg);
						location.reload();
					}
				},
				error: function () {
					// ✅ Lenis 스크롤 다시 활성화
					if (typeof lenis !== "undefined") {
						lenis.start();
						console.log("Lenis 스크롤 다시 활성화됨 ✅");
					}

					// 로딩 숨기기
					loader.classList.remove("active");
					$(".submit-btn").prop("disabled", false);
					
					alert("오류가 발생했습니다. 다시 시도해주세요.\n지속적인 오류 발생시 담당자에게 문의해주시기 바랍니다.");
				}
			});
		}

		// 전송 버튼 클릭 시 doRequest 실행
		$(".submit-btn").click(function () {
			doRequest();
		});
	});




</script>




<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>
