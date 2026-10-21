# PHP gate 라우터 QA — 2026-10-12

## 현재 방식

- `GATE_ROUTE_MODE=php`, `GATE_HOST`에 프로젝트 관리자 호스트 지정.
- gate의 `/` 요청은 루트 `index.php`에서 관리자 진입 파일을 내부 include한다. `/nol-gate` 또는 `/admin`으로 리다이렉트하지 않는다.
- 이후 링크·폼·AJAX·정적 자산은 `/index.php/pages/...`, `/index.php/lib/...`, `/index.php/resource/...` 형식이다. 깔끔한 `/pages/...` URL까지 제공하는 rewrite 방식은 아니다.
- 허용 경로와 realpath 경계 검사, 인증 진입점, CSRF·세션 정책을 유지한다. 응답 JSON·바이너리는 URL 치환하지 않는다.
- .htaccess의 **gate 라우팅**은 제거했다. 공개 사이트의 기존 라우팅 및 비밀파일·업로드 보안 규칙은 삭제하지 않았다.

## 실제 실행

- PHP 7.4 / MySQL 8.0, 별도 QA DB와 임시 계정 사용. 기존 운영·개발 DB 및 SMTP 비밀값 변경 없음. 메일 미발송.
- `qa/compose.clean.yml` + `qa/compose.php-router.yml` 사용. 공용 DocumentRoot, `AllowOverride None`, rewrite·Alias·FallbackResource·AcceptPathInfo 강제 설정 없이 검증.
- 명령: 컨테이너에서 `php qa/run-php-router.php`. 모든 하위 검사 종료 0, `PHP ROUTER QA PASSED`.
- 라우터/실제 인증 HTTP 60건, CRUD·업로드·다운로드·권한 91건, 세션·개인정보·MFA 템플릿 추가 42건: 합계 193 PASS / 0 FAIL. PHP 356개 문법 검사 통과.
- 로그인 POST, CAPTCHA/CSRF, 세션 ID 재발급, 동시 로그인 이전 세션 무효화, 30분 유휴 만료, 활동 연장과 단순 폴링 구분, 로그아웃, 익명 401 확인.
- 렌더링된 CSS/JS/로고/CAPTCHA 실제 HTTP 요청, 업로드 이미지 원본 바이트 일치, 비공개 첨부 사유·권한·감사 기록 확인.
- 경로 조작·비공개 파일·폐기 vendor 예제 차단, 공개 홈페이지 200 확인. 직접 `/.env`, `/260918.sql`, `/inc/lib/PhpGateRouter.php`는 QA 호스트 보안 설정으로 403 확인.

## 배포 조건과 미확인 사항

- 가비아의 서브도메인 **호스팅 연결 등록**이 먼저다. PHP 이전 단계의 가비아 403을 이 코드로 우회할 수 없다.
- gate와 공개 도메인이 같은 프로젝트 루트를 가리켜야 한다. PHP PATH_INFO (`/index.php/...`) 지원은 Docker의 기본 설정에서 검증했으며, 가비아 실서버 지원 여부는 배포 후 별도 확인해야 한다.
- PHP 라우터는 직접 정적 파일 요청을 통제할 수 없다. 비밀파일 차단·업로드 PHP 실행 금지 등 기존 보안 규칙/호스팅 통제를 유지해야 한다. QA Apache의 보호 설정이 가비아에도 자동 적용되는 것은 아니다.
- 운영 HTTPS 인증서·허용 IP는 기존 production 정책대로 필수. HTTP 허용은 development 기준이고 운영 HTTPS/IP 통제를 낮추지 않았다.
- 이번 검증은 HTTP 자동 QA이며 최신 실제 브라우저 화면/클릭 검증, 실서버 배포, 실제 MFA 메일 재발송, 전체 과거 QA 스크립트 재실행 또는 GitHub 게시를 완료했다는 의미는 아니다.
- DB 스키마 변경 없음. 새 SQL 마이그레이션 필요 없음. 앞선 10월 8일 internal 방식 문서는 과거 기록이다.
