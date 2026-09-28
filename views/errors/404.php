<?php
$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$homeUrl = '/';
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>404 Not Found | 놀씨어터 대학로 공연장</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --no-bg: #ffffff;
      --no-fg: #212121;
      --no-muted: #9e9e9e;
      --no-border: #e0e0e0;
      --no-accent: #3549ff;
      --no-accent-light: #e7e9f1;
      --no-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --no-bg: #121212;
        --no-fg: #ffffff;
        --no-muted: #bdbdbd;
        --no-border: #2a2a2a;
        --no-accent: #7384b0;
        --no-accent-light: #1c1c1c;
        --no-shadow: 0 10px 30px rgba(0, 0, 0, .45);
      }
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      background: linear-gradient(180deg, var(--no-accent-light) 0%, var(--no-bg) 60%);
      color: var(--no-fg);
      font-family: "Inter", "Noto Sans KR", sans-serif;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 16px;
    }

    .no-404 {
      max-width: 640px;
      background: var(--no-bg);
      border: 1px solid var(--no-border);
      border-radius: 20px;
      box-shadow: var(--no-shadow);
      text-align: center;
      padding: 60px 40px 50px;
    }

    .no-404__icon {
      width: 72px;
      height: 72px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background: var(--no-accent-light);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--no-accent);
    }

    .no-404__icon svg {
      width: 36px;
      height: 36px;
    }

    .no-404__title {
      font-size: clamp(26px, 3.5vw, 34px);
      font-weight: 800;
      margin: 0 0 12px;
    }

    .no-404__desc {
      font-size: 15px;
      color: var(--no-muted);
      margin: 0 0 36px;
      line-height: 1.6;
    }

    .no-btn {
      appearance: none;
      border: none;
      border-radius: 12px;
      padding: 14px 24px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: .2s ease;
    }

    .no-btn--primary {
      background: var(--no-accent);
      color: #fff;
    }

    .no-btn--primary:hover {
      background: var(--no-accent);
      filter: brightness(1.1);
    }

    .no-btn--ghost {
      border: 1px solid var(--no-border);
      background: transparent;
      color: var(--no-fg);
    }

    .no-btn--ghost:hover {
      background: var(--no-accent-light);
    }

    .no-404__actions {
      display: flex;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .no-404__foot {
      margin-top: 40px;
      font-size: 12px;
      color: var(--no-muted);
    }

    .no-404__brand {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 700;
      letter-spacing: .3px;
    }

    .no-404__brand-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--no-accent);
      box-shadow: 0 0 0 4px color-mix(in srgb, var(--no-accent) 18%, transparent);
    }
  </style>
</head>

<body>
  <main class="no-404">
    <div class="no-404__icon" aria-hidden="true">
      <!-- 리프 모양 아이콘 -->
      <svg viewBox="0 0 24 24" fill="none">
        <path d="M12 2c3 2 6 2.5 9 3v5.5c0 4.9-3.2 9.3-9 11-5.8-1.7-9-6.1-9-11V5c3-.5 6-1 9-3Z"
          stroke="currentColor" stroke-width="1.2" />
        <path d="M15.5 8.5c-3.2-.6-5.7 .9-7.5 4 2.9.5 5.2-.6 7.5-4Z" fill="currentColor" opacity=".15" />
        <path d="M8 13c1.8-3.1 4.3-4.6 7.5-4M8 13c1.7.3 3.5.1 5.5-.8" stroke="currentColor" stroke-width="1.2"
          stroke-linecap="round" />
      </svg>
    </div>

    <h1 class="no-404__title">페이지를 찾을 수 없습니다</h1>
    <p class="no-404__desc">
      요청하신 페이지가 존재하지 않거나 이동되었어요.<br>
      주소를 다시 확인하시거나 아래 버튼을 이용해 주세요.
    </p>

    <div class="no-404__actions">
      <a href="<?= $h($homeUrl) ?>" class="no-btn no-btn--primary">홈으로 돌아가기</a>
      <button type="button" class="no-btn no-btn--ghost" onclick="history.back()">이전 페이지</button>
    </div>

    <p class="no-404__foot">
      <span class="no-404__brand"><span class="no-404__brand-dot"></span> 놀씨어터 대학로 공연장</span> · <?= date('Y') ?>
    </p>
  </main>
</body>

</html>