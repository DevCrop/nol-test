# 블루스퀘어 HTTP gate QA — 2026-10-16

## 범위와 구현
- 작업 루트: 블루스퀘어/260918. 놀 프로젝트는 수정하지 않음.
- 기존 dirty working tree를 보존. 이번 변경은 index.php에 진입 파일 require 1줄 추가, PhpGateRouter의 .env.gate 읽기 추가, 확인용 클래스/정적 HTML/설정/진입 파일 추가.
- 기준선은 작업 직전 index.php와 PhpGateRouter.php를 qa/http-preview-20261016/baseline에 복사하여 보존. Git HEAD가 아니라 현재 개발 작업본 기준.
- 공개 호스트는 기존 코드로 계속 처리. 공통 base/DB/SMTP/세션/관리자 인증 클래스/.htaccess는 변경하지 않음.
- gate 확인 화면은 DB/세션/기존 관리자 PHP 템플릿을 실행하지 않음. 정적 폼 비활성 및 CSP form-action none.
- HTTP 인증·MFA·관리자 PHP/POST/PUT/PATCH/DELETE 차단(426). SSL 설치만으로 인증 활성화되지 않음.
- ZIP에는 운영 index.php 전체를 넣지 않음. 추가 파일 5개와 index 연결 1줄 안내 제공.

## Docker 검증 (PHP 7.4.33 / MySQL 8)
- 작업 전/후 웹 컨테이너 동일 파일 루트/DB/설정. 기준선 컨테이너는 index/router 2개만 작업 직전 사본으로 오버레이.
- AllowOverride None: gate 동작에 .htaccess RewriteRule 사용하지 않음. 비밀파일/업로드 실행 차단은 별도 QA Apache 설정이며 PHP 라우터 자체가 직접 정적 요청 전체를 통제하는 것은 아님.
- 공개 회귀: 215 PASS / 0 FAIL. 페이지/API 64개 + 자산 151개.
  메인·공연 목록/상세·소개/시설·고객센터/주차·게시판·검색·대관·개인정보 문서·공개 API 및 CSS/JS/이미지/폰트/다운로드 확인.
  HTTP 상태/Location/Content-Type/본문 비교. 시각별 캐시 키 및 세션별 CSRF 난수만 비교 정규화.
- gate 검사: 24 PASS / 0 FAIL. 루트 200, 비활성 폼, no-store, Set-Cookie 없음, HEAD, 로고 바이너리 일치, 로그인/관리자 실행 차단, 경로조작/개인파일 차단, 호스트 대소문자·포트·유사 호스트, 공개 관리자 격리.
- production + 존재하지 않는 DB 호스트 + .env.gate 파일 설정 사용: 3 PASS / 0 FAIL.
  gate 200/세션 없음, 인증 PHP 426, 공개 HTTP의 HTTPS 302 유지.
- 전체 PHP 문법 검사 통과, 기존 security-unit 13개 통과, Apache Syntax OK.
- 회귀 후 최종 웹 로그 PHP Warning/Notice/Fatal/Uncaught/SQLSTATE 검색 결과 0줄.
- 진입 구조를 최종 'index 연결 1줄'로 정리한 뒤 gate 24건 및 공개 회귀 215건/문법을 재검증함.

## 기존 자료 문제 및 미확인
- 변경 전/후 동일한 404 총 42건(첨부 다운로드 7건 + 이미지 35건).
  QA DB가 참조하는 업로드 파일이 개발 사본에 없음. 운영 서버 누락이라고 단정하지 않음.
  업로드 파일이나 운영 DB를 수정/삭제하지 않음.
- 최신 실서버 사본 미제공: 운영 index.php와 개발본의 동일성은 검증하지 못함.
  따라서 개발 index 전체 덮어쓰기를 배포 방식에서 제외함.
- 브라우저 도구 초기화 실패: failed to write kernel assets / os error 3.
  데스크톱/모바일 실제 렌더링·JS 클릭·스크린샷 QA는 미완료. HTTP 응답 검증과 구분.
- HTTP 확인용이므로 실제 인증·메일·세션·CRUD·업로드 성공 흐름 검증은 이번 배포 범위가 아님.
- 실서버 PHP PATH_INFO·비밀파일 보호·인증서·운영 배포 후 기능 확인 필요.
- 운영 서버/SMTP/실DB 변경·GitHub 게시하지 않음. 신규 SQL 없음.

## 실제 서버 관찰
- 2026-10-16 시작 시 HTTP gate는 가비아 errdoc/403으로 302.
- 작업 중 재확인에서 PHP 응답 헤더 및 https://gate.bluesquare.kr/로 302로 변경.
  요청이 PHP까지 도달하는 것으로 판단. 서버 연결 관리콘솔 상태를 직접 확인한 것은 아님.
- 현재 코드 적용 전인 운영 서버에서 HTTP 확인 화면이 이미 뜬다고 주장하지 않음.
