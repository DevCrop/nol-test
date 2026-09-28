<?php

/** @var int $status */
/** @var string $message */
/** @var array|null $debug */
/** @var array<string>|null $allowed */
/** @var string $path */
/** @var string $method */

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$code    = $status ?? 500;
$msg     = $message ?? 'Something went wrong';
$is405   = ($code === 405);
$is404   = ($code === 404);
$is500   = ($code >= 500);
$homeUrl = '/';
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($code) ?> · <?= $h($msg) ?> | 놀씨어터 대학로 공연장</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Inter 대체 가능 시 -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    :root {
        --no-bg: #ffffff;
        --no-fg: #212121;
        --no-fg-muted: #9e9e9e;
        --no-border: #e0e0e0;
        --no-accent: #3549ff;
        /* 놀씨어터 primary-def */
        --no-accent-weak: #e7e9f1;
        /* primary-50 */
        --no-accent-strong: #16265a;
        /* primary-900 */
        --no-badge-bg: #e7e9f1;
        --no-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --no-bg: #121212;
            --no-fg: #ffffff;
            --no-fg-muted: #bdbdbd;
            --no-border: #2a2a2a;
            --no-accent: #7384b0;
            /* primary-300 for dark */
            --no-accent-weak: #1c1c1c;
            --no-accent-strong: #c1c9de;
            /* primary-100 for dark */
            --no-badge-bg: #1c1c1c;
            --no-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        }
    }

    * {
        box-sizing: border-box
    }

    html,
    body {
        height: 100%
    }


    .no-error {
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 16px;
    }

    .no-error__container {
        width: min(920px, 100%);
        background: var(--no-bg);
        border: 1px solid var(--no-border);
        border-radius: 20px;
        box-shadow: var(--no-shadow);
        overflow: hidden;
    }

    .no-error__header {
        display: flex;
        gap: 16px;
        padding: 28px 28px 8px;
        align-items: flex-start;
    }

    .no-error__badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: var(--no-badge-bg);
        border: 1px solid var(--no-border);
        font-weight: 600;
        letter-spacing: .2px;
        color: var(--no-accent-strong);
    }

    .no-error__badge svg {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
    }

    .no-error__code {
        font-size: 13px;
        font-weight: 700;
        color: var(--no-accent-strong);
    }

    .no-error__title {
        margin: 0;
        padding: 0 28px 8px;
        font-size: clamp(22px, 3.2vw, 32px);
        line-height: 1.2;
        font-weight: 800;
    }

    .no-error__desc {
        margin: 0;
        padding: 0 28px 18px;
        font-size: 16px;
        color: var(--no-fg-muted);
    }

    .no-error__panel {
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 20px;
        padding: 0 28px 24px;
    }

    @media (max-width: 760px) {
        .no-error__panel {
            grid-template-columns: 1fr;
        }
    }

    .no-error__card {
        border: 1px solid var(--no-border);
        border-radius: 16px;
        padding: 18px;
        background: linear-gradient(180deg, rgba(40, 64, 125, 0.08) 0%, rgba(40, 64, 125, 0.00) 100%);
    }

    .no-error__meta {
        display: grid;
        grid-template-columns: 120px 1fr;
        row-gap: 10px;
        column-gap: 12px;
        font-size: 14px;
    }

    .no-error__meta dt {
        color: var(--no-fg-muted);
    }

    .no-error__meta dd {
        margin: 0;
        word-break: break-all;
    }

    .no-error__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 28px 28px;
    }

    .no-btn {
        appearance: none;
        border: 1px solid var(--no-border);
        background: var(--no-bg);
        color: var(--no-fg);
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: transform .02s ease, background .2s ease, border-color .2s ease;
    }

    .no-btn:active {
        transform: translateY(1px);
    }

    .no-btn--primary {
        background: var(--no-accent);
        color: #fff;
        border-color: transparent;
    }

    .no-btn--ghost {
        background: transparent;
    }

    .no-btn--link {
        background: transparent;
        border-color: transparent;
        color: var(--no-accent-strong);
        text-decoration: underline;
    }

    .no-error__hint {
        padding: 0 28px 24px;
        color: var(--no-fg-muted);
        font-size: 13px;
    }

    .no-error__debug {
        padding: 0 28px 28px;
    }

    .no-error__details {
        border: 1px dashed var(--no-border);
        border-radius: 14px;
        padding: 14px 16px;
        background: rgba(0, 0, 0, 0.02);
    }

    @media (prefers-color-scheme: dark) {
        .no-error__details {
            background: rgba(255, 255, 255, 0.03);
        }
    }

    .no-error__details summary {
        cursor: pointer;
        font-weight: 700;
        color: var(--no-accent-strong);
        margin-bottom: 8px;
    }

    .no-error__pre {
        margin: 10px 0 0;
        max-height: 360px;
        overflow: auto;
        font-size: 12px;
        line-height: 1.45;
        background: #0b0f0e;
        color: #c9f2e6;
        padding: 12px 14px;
        border-radius: 10px;
    }

    .no-error__foot {
        padding: 18px 28px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-top: 1px solid var(--no-border);
        font-size: 12px;
        color: var(--no-fg-muted);
    }

    .no-error__brand {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .no-error__brand-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--no-accent);
        box-shadow: 0 0 0 4px color-mix(in srgb, var(--no-accent) 18%, transparent);
    }
    </style>
</head>

<body>
    <main class="no-error" role="main">
        <section class="no-error__container" aria-labelledby="error-title">
            <header class="no-error__header">
                <div class="no-error__badge" aria-label="놀씨어터 대학로 공연장 안내">
                    <!-- 잎사귀 + 방패(클린/케어 느낌) 아이콘 -->
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2c3 2 6 2.5 9 3v5.5c0 4.9-3.2 9.3-9 11-5.8-1.7-9-6.1-9-11V5c3-.5 6-1 9-3Z"
                            stroke="currentColor" stroke-width="1.2" />
                        <path d="M15.5 8.5c-3.2-.6-5.7 .9-7.5 4 2.9.5 5.2-.6 7.5-4Z" fill="currentColor"
                            opacity=".15" />
                        <path d="M8 13c1.8-3.1 4.3-4.6 7.5-4M8 13c1.7.3 3.5.1 5.5-.8" stroke="currentColor"
                            stroke-width="1.2" stroke-linecap="round" />
                    </svg>
                    <span class="no-error__code"><?= $h($code) ?></span>
                </div>
            </header>

            <h1 id="error-title" class="no-error__title">
                <?php if ($is404): ?>
                요청하신 페이지를 찾을 수 없어요.
                <?php elseif ($is405): ?>
                요청하신 방식은 지원하지 않아요.
                <?php elseif ($is500): ?>
                잠시 문제가 발생했어요.
                <?php else: ?>
                알 수 없는 오류가 발생했어요.
                <?php endif; ?>
            </h1>

            <p class="no-error__desc">
                <?= $h($msg) ?> · <strong><?= $h($method) ?></strong> <code><?= $h($path) ?></code>
                <?php if ($is405 && !empty($allowed)): ?>
                — 허용 메서드: <?= $h(implode(', ', array_map('strtoupper', (array)$allowed))) ?>
                <?php endif; ?>
            </p>

            <div class="no-error__panel">
                <div class="no-error__card">
                    <dl class="no-error__meta">
                        <dt>상태 코드</dt>
                        <dd><?= $h($code) ?></dd>
                        <dt>요청 경로</dt>
                        <dd><code><?= $h($path) ?></code></dd>
                        <dt>요청 방식</dt>
                        <dd><?= $h($method) ?></dd>
                        <dt>안내</dt>
                        <dd>
                            <?php if ($is404): ?>
                            주소가 정확한지 확인해주세요. 아래 메뉴로 이동하실 수 있어요.
                            <?php elseif ($is405): ?>
                            해당 경로는 다른 방식으로만 접근 가능합니다.
                            <?php elseif ($is500): ?>
                            불편을 드려 죄송합니다. 잠시 후 다시 시도해주세요.
                            <?php else: ?>
                            문제가 계속되면 문의 부탁드립니다.
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>

                <div class="no-error__card">
                    <div style="font-weight:700; margin-bottom:10px;">빠른 이동</div>
                    <div class="no-error__actions">
                        <a class="no-btn no-btn--primary" href="<?= $h($homeUrl) ?>">홈으로</a>
                        <button class="no-btn no-btn--ghost" onclick="history.back()">이전 페이지</button>
                        <button class="no-btn no-btn--ghost" onclick="location.reload()">새로고침</button>
                        <a class="no-btn no-btn--link" href="/contact">문의하기</a>
                    </div>
                    <p class="no-error__hint">문제가 반복되면, 발생 시각과 함께 스크린샷을 첨부해주시면 더 빠르게 도와드릴 수 있어요.</p>
                </div>
            </div>

            <?php if (!empty($debug)): ?>
            <div class="no-error__debug">
                <details class="no-error__details">
                    <summary>개발자 정보 (<?= $h($debug['file'] ?? ''); ?>:<?= $h($debug['line'] ?? ''); ?>)</summary>
                    <?php if (!empty($debug['message'])): ?>
                    <div><strong>Message:</strong> <?= $h($debug['message']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($debug['trace']) && is_array($debug['trace'])): ?>
                    <pre class="no-error__pre"><?= $h(implode("\n", $debug['trace'])) ?></pre>
                    <?php endif; ?>
                </details>
            </div>
            <?php endif; ?>

            <footer class="no-error__foot">
                <span>요청: <code><?= $h($method) ?></code> <code><?= $h($path) ?></code></span>
                <span class="no-error__brand"><span class="no-error__brand-dot"></span> 놀씨어터 대학로 공연장</span>
            </footer>
        </section>
    </main>
</body>

</html>