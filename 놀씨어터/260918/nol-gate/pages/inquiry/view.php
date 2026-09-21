<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/PrivacyAccessLogger.php";
require_once dirname(__DIR__, 2) . "/lib/PiiMask.php";

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    echo "<script>alert('처리할 수 없습니다.'); history.back();</script>";
    exit;
}

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
if ($no <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$stmt = $db->prepare("SELECT * FROM nb_request WHERE no = :no");
$stmt->execute([':no' => $no]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "<script>alert('해당 대관 신청을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

PrivacyAccessLogger::record(
    'view',
    'inquiry',
    (int) $no,
    (string) ($data['name'] ?? ''),
    '대관 신청 열람'
);

$pageName = "대관 신청 상세";

$venueLabelMap = [
    'woori-card' => '우리카드홀',
    'woori-securities' => '우리투자증권홀',
];

$venue = trim((string)($data['venue'] ?? ''));
$venue = $venueLabelMap[$venue] ?? $venue;

$performanceName = trim((string)($data['performance_name'] ?? ''));
$content = '';
$attachedFiles = [];

if (!empty($data['contents'])) {
    $rawContents = trim((string)$data['contents']);

    // 우선 한국어 섹션 헤더 기반으로 내용만 추출하고, 실패 시 원문 표시
    if (preg_match('/(?:내용|대관\s*신청\s*내용)\s*\n(.*)$/us', $rawContents, $matches)) {
        $content = trim((string)$matches[1]);
    } else {
        $content = $rawContents;
    }

    // 과거 데이터 호환: 공연명이 별도 컬럼에 없으면 contents에서 추출 시도
    if ($performanceName === '' && preg_match('/(?:공연명|공연\s*사업명)\s*\n(.*?)(?:\n\s*\n|$)/us', $rawContents, $matches)) {
        $performanceName = trim((string)$matches[1]);
    }

    // 과거 데이터 호환: venue가 별도 컬럼에 없으면 contents에서 추출 시도
    if ($venue === '' && preg_match('/(?:대관구분|대관\s*구분)\s*\n(.*?)(?:\n\s*\n|$)/us', $rawContents, $matches)) {
        $venue = trim((string)$matches[1]);
    }
}

// 첨부파일 추출 (file_1 ~ file_5)
for ($i = 1; $i <= 5; $i++) {
    $fileField = 'file_' . $i;
    $orgFileField = 'org_file_' . $i;

    if (!empty($data[$fileField])) {
        $attachedFiles[] = [
            'server' => (string)$data[$fileField],
            'original' => !empty($data[$orgFileField]) ? (string)$data[$orgFileField] : (string)$data[$fileField],
            'slot' => $i,
        ];
    }
}

// 과거 데이터 호환: file 필드가 없으면 contents의 첨부파일 블록에서 추출
if (empty($attachedFiles) && !empty($data['contents'])) {
    if (preg_match('/(?:첨부파일|첨부\s*파일)\s*\n(.*?)(?:\n\s*\n|$)/us', (string)$data['contents'], $matches)) {
        $fileList = trim((string)$matches[1]);
        if ($fileList !== '') {
            $fileNames = preg_split('/\r\n|\r|\n/', $fileList);
            foreach ($fileNames as $fileName) {
                $fileName = trim((string)$fileName);
                if ($fileName !== '') {
                    $attachedFiles[] = [
                        'server' => $fileName,
                        'original' => $fileName,
                        'slot' => 0,
                    ];
                }
            }
        }
    }
}
?>

<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="inquiry-view">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <section class="no-content">
                <div class="no-toolbar">
                    <div class="no-toolbar-container no-flex-stack">
                        <div class="no-page-indicator">
                            <h1 class="no-page-title"><?= $pageName ?></h1>
                        </div>
                    </div>
                </div>

                <div class="no-toolbar-container">
                    <div class="no-card">
                        <div class="no-card-header no-card-header--detail">
                            <h2 class="no-card-title"><?= $pageName ?></h2>
                        </div>

                        <div class="no-card-body no-admin-column no-admin-column--detail">

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">대관구분</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars($venue) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">단체(공연 단체명)</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars((string)($data['company'] ?? '')) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">담당자 연락처</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars((string)($data['phone'] ?? '')) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">공연명</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars($performanceName) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">담당자명</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars((string)($data['name'] ?? '')) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">담당자 이메일</h3>
                                <div class="no-admin-content">
                                    <input type="text" value="<?= htmlspecialchars((string)($data['email'] ?? '')) ?>" readonly>
                                </div>
                            </div>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">내용</h3>
                                <div class="no-admin-content">
                                    <textarea id="content" rows="5" readonly><?= htmlspecialchars($content) ?></textarea>
                                </div>
                            </div>

                            <?php if (!empty($attachedFiles)): ?>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title">첨부파일</h3>
                                <div class="no-admin-content">
                                    <ul class="no-inquiry-file-list">
                                        <?php foreach ($attachedFiles as $fileInfo): ?>
                                        <?php
                                            $serverFile = (string)$fileInfo['server'];
                                            $originalFile = (string)$fileInfo['original'];
                                            $safeServerFile = basename($serverFile);
                                            $filePath = '/uploads/request/' . $safeServerFile;
                                            $fileExists = is_file($NO_PROJECT_ROOT . $filePath);

                                            $downloadUrl = './download.php?no=' . (int)$no;
                                            if (!empty($fileInfo['slot'])) {
                                                $downloadUrl .= '&slot=' . (int)$fileInfo['slot'];
                                            } else {
                                                $downloadUrl .= '&file=' . urlencode($safeServerFile);
                                            }

                                            $fileExt = strtolower(pathinfo($originalFile, PATHINFO_EXTENSION));
                                        ?>
                                        <li class="no-inquiry-file-item">
                                            <div class="no-inquiry-file-item__icon">
                                                <?php
                                                    $iconClass = 'fa-regular fa-file';
                                                    if (in_array($fileExt, ['pdf'], true)) {
                                                        $iconClass = 'fa-regular fa-file-pdf';
                                                    } elseif (in_array($fileExt, ['doc', 'docx'], true)) {
                                                        $iconClass = 'fa-regular fa-file-word';
                                                    } elseif (in_array($fileExt, ['xls', 'xlsx'], true)) {
                                                        $iconClass = 'fa-regular fa-file-excel';
                                                    } elseif (in_array($fileExt, ['ppt', 'pptx'], true)) {
                                                        $iconClass = 'fa-regular fa-file-powerpoint';
                                                    } elseif (in_array($fileExt, ['zip', 'rar'], true)) {
                                                        $iconClass = 'fa-regular fa-file-zipper';
                                                    }
                                                ?>
                                                <i class="<?= $iconClass ?>"></i>
                                            </div>
                                            <div class="no-inquiry-file-item__info">
                                                <span class="no-inquiry-file-item__name"><?= htmlspecialchars($originalFile) ?></span>
                                                <?php if ($fileExists): ?>
                                                <span class="no-inquiry-file-item__size">파일 존재</span>
                                                <?php else: ?>
                                                <span class="no-inquiry-file-item__size no-inquiry-file-item__size--error">파일 없음</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($fileExists): ?>
                                            <a href="<?= htmlspecialchars($downloadUrl) ?>"
                                                class="no-inquiry-file-item__download"
                                                data-pii-download
                                                data-no="<?= (int)$no ?>"
                                                data-slot="<?= (int)($fileInfo['slot'] ?? 0) ?>"
                                                data-file="<?= htmlspecialchars($safeServerFile, ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fa-regular fa-download"></i>
                                                <span>다운로드</span>
                                            </a>
                                            <?php else: ?>
                                            <span class="no-inquiry-file-item__download no-inquiry-file-item__download--disabled">
                                                <i class="fa-regular fa-xmark"></i>
                                                <span>파일 없음</span>
                                            </span>
                                            <?php endif; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="no-items-center center">
                        <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                    </div>
                </div>
            </section>
        </main>
        <form id="pii-download-form" method="POST" action="./download.php" hidden>
            <input type="hidden" name="no" value="">
            <input type="hidden" name="slot" value="">
            <input type="hidden" name="file" value="">
            <input type="hidden" name="reason" value="">
        </form>
        <div id="pii-reason-modal" class="no-pii-modal" hidden>
            <div class="no-card no-pii-modal__card">
                <div class="no-card-header">
                    <h2 class="no-card-title">다운로드 사유</h2>
                </div>
                <div class="no-card-body">
                    <p class="no-pii-modal__hint">개인정보 파일이 포함될 수 있습니다. 사유를 남긴 뒤 내려받으세요.</p>
                    <textarea id="pii-reason" class="no-input--detail" rows="3" maxlength="500" placeholder="예: 대관 검토, 담당자 확인"></textarea>
                    <div class="no-items-center center" style="margin-top:16px;gap:8px;">
                        <button type="button" class="no-btn no-btn--normal" data-pii-cancel>취소</button>
                        <button type="button" class="no-btn no-btn--main" data-pii-confirm>다운로드</button>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

    <style>
    body.no-pii-lock,
    body.no-pii-lock input,
    body.no-pii-lock textarea {
        -webkit-user-select: none;
        user-select: none;
    }
    .no-pii-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(17, 24, 39, 0.45);
    }
    .no-pii-modal[hidden] {
        display: none;
    }
    .no-pii-modal__card {
        width: min(480px, calc(100% - 32px));
    }
    .no-pii-modal__hint {
        margin: 0 0 12px;
        color: #6b7280;
        font-size: 14px;
    }
    .no-inquiry-file-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .no-inquiry-file-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: var(--main-background, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 12px;
        transition: all 0.2s ease;
        position: relative;
    }

    .no-inquiry-file-item:hover {
        border-color: var(--primary-color, #2a6b55);
        box-shadow: 0 2px 8px rgba(42, 107, 85, 0.1);
        transform: translateY(-1px);
    }

    .no-inquiry-file-item__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 10px;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .no-inquiry-file-item:hover .no-inquiry-file-item__icon {
        background: linear-gradient(135deg, var(--primary-color, #2a6b55) 0%, #1f4d3d 100%);
        transform: scale(1.05);
    }

    .no-inquiry-file-item__icon i {
        font-size: 24px;
        color: var(--body-color, #374151);
        transition: color 0.2s ease;
    }

    .no-inquiry-file-item:hover .no-inquiry-file-item__icon i {
        color: #fff;
    }

    .no-inquiry-file-item__info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .no-inquiry-file-item__name {
        font-size: 15px;
        font-weight: 500;
        color: var(--title-color, #111827);
        word-break: break-all;
        line-height: 1.4;
    }

    .no-inquiry-file-item__size {
        font-size: 13px;
        color: var(--body-color, #6b7280);
    }

    .no-inquiry-file-item__size--error {
        color: #ef4444;
    }

    .no-inquiry-file-item__download {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: var(--primary-color, #2a6b55);
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .no-inquiry-file-item__download:hover {
        background: #1f4d3d;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(42, 107, 85, 0.3);
    }

    .no-inquiry-file-item__download:active {
        transform: translateY(0);
    }

    .no-inquiry-file-item__download i {
        font-size: 14px;
    }

    .no-inquiry-file-item__download--disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
        pointer-events: none;
    }

    .no-inquiry-file-item__download--disabled:hover {
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 768px) {
        .no-inquiry-file-item {
            flex-wrap: wrap;
            padding: 12px 16px;
        }

        .no-inquiry-file-item__icon {
            width: 40px;
            height: 40px;
        }

        .no-inquiry-file-item__icon i {
            font-size: 20px;
        }

        .no-inquiry-file-item__download {
            width: 100%;
            justify-content: center;
            padding: 12px;
        }

        .no-inquiry-file-item__name {
            font-size: 14px;
        }

        .no-inquiry-file-item__size {
            font-size: 12px;
        }
    }
    </style>
</body>

</html>
