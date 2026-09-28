# API 규칙

관리자 쓰기는 **GATE 같은 호스트**에서 `POST` + `mode` + 세션 쿠키다.  
`/api/...` REST는 아직 없다. 공개 페이지는 PHP 렌더. 대관 신청 POST만 `PUBLIC_HOST`.

운영 호스트·CORS 값은 `환경설정.md`.

## 지금 호출

1. 관리자 API는 GATE에만. 공개 JS는 GATE를 부르지 않는다.
2. 화면과 API가 같은 GATE면 same-origin. `Origin`이 오면 `CORS_ORIGINS` 화이트리스트만 `Allow-Origin` + `Allow-Credentials: true`. `*` 금지.
3. 쿠키: `NOLGATESESSID`, `HttpOnly`, `SameSite=Lax`, `SESSION_DOMAIN` 비움(호스트 전용).
4. JS: `nol-gate/resource/js/core/fetcher.js` — `credentials: "include"`, **FormData**. 객체 JSON body는 PHP `$_POST`가 못 읽는다.
5. URL: `apiRoutes.js` 의 `API.*` = `NO_ADMIN_BASE` + `/Controller/...php`. GATE면 `NO_ADMIN_BASE`가 `''` 이라 `/Controller/AccountController.php`.
6. 레거시 ajax(`board.process` 등)는 `result` / `msg`. 컨트롤러는 `success` / `message`. `fetcher`는 둘 다 본다.

## 상태 코드

| 코드 | 때 | 이후 |
| --- | --- | --- |
| 200 | 성공 JSON | — |
| 401 | 로그인 없음·동시접속 킥 | 세션 비움, GATE `/index.php` |
| 403 | 권한 없음, 비번 강제 변경 중 다른 API | 로그인으로 안 보냄 |
| 204 | CORS OPTIONS 허용 Origin | — |

## `mode`

다수결: `insert` `update` `delete` `delete_array` `sort`.  
예외 유지: 계정 `save`, 게시글 `edit`, 대관 `delete.array`. 새로 `save`/`edit`를 만들지 않는다.

목록 API 예: 작업 이력 `POST AuditController` `mode=list`, 개인정보 접속 `POST PrivacyAccessController` `mode=list` (최고관리자만).

## 비밀

`.env`만. 응답·감사 `detail_json`에 비밀번호·SMTP·`login_token`·`footer_ssn` 넣지 않음.

## 나중에 `/api` 로 옮길 때 (아직 하지 않음)

URL은 복수 명사, id는 경로. GET 목록, POST 등록, PATCH 수정, DELETE 삭제.  
CSRF 토큰. 화면 JS URL만 바꾸고 `mode` 분기는 재사용해도 된다.
