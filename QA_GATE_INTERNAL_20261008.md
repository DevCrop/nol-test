# 놀씨어터 대학로 — gate 내부 라우팅 및 SMTP 검증 (2026-10-08)

## 적용 결과

- `https://gate.noltheater-daehakro.com/`에서 주소창에 `/nol-gate/`를 붙이지 않고 관리자 로그인 화면을 제공하도록 변경했다.
- Apache 루트 `.htaccess`의 내부 재작성과 PHP의 호스트·인증·URL 기준 경로를 함께 적용한다. PHP만으로 임의의 가상 PHP·정적 파일 경로를 처리한다고 보장하는 방식이 아니다.
- 로그인/MFA, 계정, AJAX, CSS/JS 경로는 gate 루트 기준이다. 물리 관리자 경로 직접 접근은 차단하고 공개 홈페이지·공유 리소스는 보존한다.
- 운영 `.env`의 `GATE_ROUTE_MODE=internal`을 사용한다. 10월 7일 `path` 모드의 관리자 디렉터리 302 이동은 현재 배포 기준에서 대체되었다.
- 블루스퀘어 `gate.bluesquare.kr`는 요청에 따른 가정이다. 확정 호스트가 다르면 `.env`뿐 아니라 `.htaccess`의 호스트 규칙도 함께 수정·검증해야 한다.
- 이번 변경에는 원본 SQL·증분 SQL 변경이 없다. 운영 DB 원본 덤프 재적재는 하지 않는다.

## SMTP

- 두 프로젝트의 비공개 로컬 `.env`에 공통 인증 계정 `gate@nol-theater.com`, Gmail SMTP 465/TLS 및 전달받은 앱 비밀번호를 설정했다.
- 이 프로젝트 발신 주소는 `daehakro@nol-theater.com`이다. MFA의 기존 수신 이메일 확인·인증 정책은 유지한다.
- TLS 인증서 검증, 서버 greeting, EHLO, AUTH LOGIN(235), NOOP, QUIT를 실제 서버에서 확인했다.
- 이 검사에서는 RCPT/DATA를 보내지 않았고 메일을 발송하지 않았다. 받은 메일의 실제 From 표시, 별칭 승인 상태, 배달 여부는 이번 검사로 확인되지 않는다.
- 앱 비밀번호·DB 비밀번호와 실제 `.env`는 GitHub에 올리지 않는다. 예제의 비밀번호는 빈 값이다.

## 실제 수행한 QA

- 격리된 Docker PHP 7.4 / MySQL 8.0에서 기존 전체 자동 QA: **449 PASS / 0 FAIL**, `ALL QA PASSED`.
- 동일 DocumentRoot 내부 라우팅 추가 검사: **25개 내부 라우팅 + 5개 인증·세션 + 89개 변경 경계, 모두 0 FAIL**.
- 루트 로그인 무리다이렉트, 관리자 CSS 파일 해시 일치, JS/vendor, 가상 로그인 CSRF 거부, 익명 계정 접근, 공개 호스트 관리자 차단, 비밀 파일·물리 관리자 경로 차단, Host 대소문자/포트 등을 확인했다.
- 인증·세션 및 실제 수정 경계 검사는 내부 URL 모드에서도 실행했다. QA 전용 DB/계정이며 운영 DB를 변경하지 않았다.
- 로컬 증빙: `qa/artifacts/internal-route-20261008.log`, `qa/artifacts/internal-regression-20261008.log`. 증빙 로그는 Git 배포에서 제외한다.
- SMTP 명시적 검사: `php qa/smtp-auth-check.php --live`. 자동 전체 QA에서는 실제 SMTP 접속 검사를 건너뛴다.

## 운영 확인 대기

- 가비아 도메인 추가 연결 및 gate 전용 SSL 설치가 선행되어야 한다.
- 고객이 전달한 가비아 답변은 자체 소스 구현을 제한하지 않는다는 내용이다. 실제 계정에서 루트 `.htaccess`가 실행되는지까지 이번 Docker 검사로 확인할 수는 없다.
- 연결 후 운영 호스트/IP 허용목록, HTTPS, 내부 재작성, 로그인/MFA 수신·발신 표시와 브라우저 화면을 확인해야 한다.
- 재작성 미적용 시 운영 완료로 판단하지 않고 가비아에 요청 전달·재작성 실행 여부를 확인한다. 웹서버가 읽지 않는 PHP `.env` 값만으로 Apache 호스트 규칙이 자동 생성되지는 않는다.
- 따라서 코드·자동 QA 및 SMTP 인증은 완료했지만, 운영 배포와 고객 메일 수신 QA는 대기이다.
