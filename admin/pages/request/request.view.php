<?php include_once $_SERVER['DOCUMENT_ROOT'] . "/inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

$depthnum = 3;
$pagenum = 1;

$no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);
if (!$no) { http_response_code(400); exit('잘못된 요청입니다.'); }
$db = DB::getInstance();

try {
    // Get request data
    $stmt = $db->prepare("SELECT * FROM nb_request WHERE no = :no AND sitekey = :sitekey");
    $stmt->execute(['no' => $no, 'sitekey' => $NO_SITE_UNIQUE_KEY]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("정보를 찾을 수 없습니다");
    }
    \Security\PrivacyLogger::record('view', 'request', (int) $no, (string) ($data['manager_name'] ?? ''), '대관신청 상세 열람');

    // Fetch board list
    $stmtBoard = $db->prepare("SELECT no, title, skin, sort_no FROM nb_board_manage ORDER BY no ASC");
    $stmtBoard->execute();
    $arrBoardList = $stmtBoard->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<script>alert('" . blue_safe_error($e) . "'); history.back();</script>";
    exit;
}



include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
$plain = static function ($value): string {
    return htmlspecialchars(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
};
?>

<style>
.privacy-download-list{display:flex;flex-direction:column;gap:1.2rem}.privacy-download{display:grid;grid-template-columns:minmax(18rem,1fr) minmax(24rem,2fr) auto;gap:1rem;align-items:end;padding:1.2rem;border:1px solid #e4e7ef;border-radius:.6rem}.privacy-download label{display:flex;flex-direction:column;gap:.6rem;font-weight:600}.privacy-download input{height:4.4rem;padding:0 1.2rem;border:1px solid #d9ddea;border-radius:.4rem}.privacy-download button{height:4.4rem}.privacy-file-name{overflow-wrap:anywhere;color:#42485b}
@media(max-width:760px){.privacy-download{grid-template-columns:1fr;align-items:stretch}.privacy-download button{width:100%}}
</style>

<?php

$fileFields = ['file_1', 'file_2', 'file_3', 'file_4', 'file_5'];
$files = [];

foreach ($fileFields as $field) {
    if (!empty($data[$field])) {
        $originField = $field . '_origin';
        $files[] = [
            'field' => $field,
            'name' => $data[$field], // 저장된 서버 파일명
            'origin' => $data[$originField] ?? '', // 원본 파일명 (없을 경우 빈 문자열)
            'path' => "/uploads/board/" . $data[$field]
        ];
    }
}



?>



</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!-- Main -->
        <main class="no-app no-container" data-pii-lock>
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <!-- Contents -->
            <div id="frm">
            <input type="hidden" id="mode" name="mode" value="">
            <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no']) ?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">대관 문의</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>대관</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>대관 문의</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>
					<?php
						//show($data);
					?>
                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">신청 내용</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="performance_name">공연명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="performance_name"
                                            id="performance_name"
                                            class="no-input--detail"
                                            value="<?= $plain($data['performance_name']) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="organization_name">공연 (단체)명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="tel"
                                            name="organization_name"
                                            id="organization_name"
                                            class="no-input--detail"
                                            value="<?= $plain($data['organization_name']) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="manager_name">담당자명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="manager_name"
                                            id="manager_name"
                                            class="no-input--detail"
                                            value="<?= $plain($data['manager_name']) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
								<!-- admin-block -->
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="phone">담당자 연락처</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="phone"
                                            id="phone"
                                            class="no-input--detail"
                                            value="<?= $plain($data['phone']) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
																<!-- admin-block -->
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="email">담당자 이메일</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="email"
                                            id="email"
                                            class="no-input--detail"
                                            value="<?= $plain($data['email']) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="b_desc">등록일</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="b_desc"
                                            id="b_desc"
                                            class="no-input--detail"
                                            value="<?= htmlspecialchars(date("Y-m-d", strtotime($data['regdate']))) ?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="b_desc">문의내용</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <textarea name="" id="" cols="30" rows="10" readonly><?= $plain($data['contents']) ?></textarea>
                                    </div>
                                </div>

								<div class="no-admin-block">
									<h3 class="no-admin-title">
										<label>첨부 파일</label>
									</h3>
									<div class="no-admin-content">
										<?php if (!empty($files)): ?>
											<ul class="no-file-list privacy-download-list">
												<?php foreach ($files as $file): ?>
													<li>
												<form method="post" action="/admin/pages/request/ajax/request.download.php" class="privacy-download">
													<input type="hidden" name="no" value="<?= (int) $no ?>"><input type="hidden" name="field" value="<?=htmlspecialchars($file['field'], ENT_QUOTES, 'UTF-8')?>">
													<span class="privacy-file-name"><i class="fa-solid fa-file"></i> <?= $plain($file['origin']) ?></span>
													<label>다운로드 사유 <input name="reason" required minlength="5" maxlength="500" placeholder="업무 목적을 5자 이상 입력"></label>
													<button type="submit" class="no-btn no-btn--main">다운로드</button>
												</form>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php else: ?>
											<p>첨부된 파일이 없습니다.</p>
										<?php endif; ?>
									</div>
								</div>
                                <!-- admin-block -->

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?= htmlspecialchars($data['no']) ?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./request.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/request.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
        
    </div>
</body>
</html>
