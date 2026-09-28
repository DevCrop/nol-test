# 놀씨어터 기능 · CRUD

관리자는 PHP 페이지 + `mode` POST. REST 아님. 호출 방식은 `API_규칙.md`.

## 공개 (`PUBLIC_HOST`)

| 기능 | 읽기 | 쓰기 |
| --- | --- | --- |
| 홈, WHAT'S ON, 공연장, 대관 안내, 공지/FAQ, 약관 | GET 페이지 | 없음 |
| 대관 신청 | `/rental/apply` | `module/request.rental.php` POST |

공개 → GATE API를 부르지 않는다.

## 관리자 (`nol-gate` / GATE에서는 `/`)

화면: `pages/*/index|new|edit.php`  
쓰기: Controller 또는 `pages/*/ajax|process.php`.

JS: `resource/js/app.js` 가 `body[data-page]`로 컨트롤러. `fetcher` + `apiRoutes.js`.

| 메뉴 | 목록 | 등록 | 수정 | 삭제 | 기타 |
| --- | --- | --- | --- | --- | --- |
| 게시글 | `board.list` | `save` → `board.process.php` | `edit` | 삭제 | 업로드·댓글·카테고리 ajax |
| 메인 배너 | `banner.list` | `insert` BannerController | `update` | `delete` / `delete_array` | `sort` |
| 팝업 | `popup.list` | `insert` PopupController | `update` | 동일 | `sort` |
| FAQ | `faq/index` | `insert` FaqController | `update` | 동일 | `sort` |
| WHAT'S ON | `works/index` | `insert` `works/process.php` | `update` | 동일 | `sort` |
| 대관 신청 | `inquiry/index` | 공개에서만 생성 | 보기 | `delete` / `delete.array` | 설정, 다운로드(사유), 목록 마스킹 |
| 사이트 정보 | `setting/index` | 태그 `insert` | `update` | `delete` | `pwd.php` |
| 페이지 SEO | `setting/seo` | SeoController | `update` | 동일 | |
| 개인정보처리방침 | `privacy/index` | `privacy/process.php` | `update` | `delete` | |
| 계정 | `account/index` | `save` AccountController | `update` | `delete` / `delete_array` | 최고관리자. 미접속 잠금 해제 |
| 작업 이력 | `account/audit.php` | 없음 | 없음 | 없음 | 조회만. 성공한 쓰기 자동 기록 |
| 개인정보 접속기록 | `account/access.php` | 없음 | 없음 | 없음 | 대관 열람/다운로드. 26과 다름 |
| 접속 통계 | `log/*` | 없음 | 없음 | 없음 | 공개 트래픽. 26번 감사와 다름 |
| 로그인 | `index.php` | — | — | — | `login.process` → MFA → 세션 |

성공한 create/update/delete 는 `AuditLogger::record` (실패해도 본 작업은 진행). 본인 비번 변경(`pwd.change`)은 안 남김.

## 로그인 이후 (이미 된 것)

순서: 비활성 → 미접속 잠금 → MFA → `AuthSession::establish`(토큰·세션 재발급) → 비번 강제면 `pwd.php`.  
요청마다: 동시접속 토큰 → 비번 게이트 → ACL.

| 클래스 | 역할 |
| --- | --- |
| `AuthSession` | 세션, 동시접속, 401 시 `expire`/`denyLogin` |
| `AccountIdleLock` | 90일 미접속 잠금. 마지막 최고관리자는 자동 잠금 안 함 |
| `PasswordExpiry` / `PasswordChangeGate` | 90일 주기, 관리자 초기화 후 강제 변경 |
| `Acl` | 일반은 계정 생성 시 준 메뉴만. 계정 메뉴·감사·개인정보 접속기록은 최고만 |
| `AuditLogger` / `AuditModel` | `nb_admin_audit` |
| `PrivacyAccessLogger` / `PiiMask` / `GateAllowlist` | 열람·다운로드 사유, 목록 마스킹, GATE IP |

## 권한 가드

`$role->requireLogin()` → 없으면 401.  
`$role->requireCanModify()` / `redirectIfCannotView()`.  
레거시 ajax: `admin.check.ajax.php` 도 같은 `denyLogin`.
