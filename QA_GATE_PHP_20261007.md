# 동일 호스팅 PHP gate 라우팅

## 최종 재확인

- 컨테이너 재시작 후 라우팅 20 PASS / 0 FAIL, 인증·세션 18 PASS / 0 FAIL, Apache Syntax OK.
- 기존 전체 회귀 292 PASS 결과는 코드 변경이 없어 재사용. 이번에는 공유 루트 재시작 경계를 추가 확인.
- QA 컨테이너 정지, 볼륨 보존. 실서버 반영 및 운영 SSL·IP·SMTP QA는 별도 대기.

- 가비아 추가 도메인 연결과 해당 호스트용 SSL 설치가 선행되어야 합니다.
- 같은 프로젝트 루트에 연결합니다. gate 전용 DocumentRoot 변경은 필요하지 않습니다.
- 놀은 GATE_ROUTE_MODE=path일 때 gate / → /nol-gate/로 PHP에서 이동합니다.
- 블루는 기존 PHP Gate를 재사용하여 gate / 및 /index.php → /admin/으로 이동합니다.
- 주소의 관리자 디렉터리 접두어는 유지합니다. 공개 호스트의 관리자 접근은 404입니다.
- 운영 GATE_HOST와 허용 IP는 확정된 고객 값만 입력합니다. 블루 QA 호스트는 운영값이 아닙니다.
- 세션, MFA, 권한, CSRF 정책은 그대로 유지하며 DB 스키마 변경은 없습니다.
- qa/compose.php-gate.yml은 실제 SMTP를 사용하지 않는 격리 QA 설정입니다.
- 실서버의 Host 전달, HTTPS 인증서, 실제 IP/메일 발송은 Docker로 검증할 수 없습니다.

## 검증 결과

- PHP 7.4 + MySQL 8.0 격리 Docker에서 기존 전체 QA 292 PASS / 0 FAIL.
- 원본 덤프를 신규 적재한 공유 루트 QA DB에 release up 적용 후 라우팅 20 PASS / 0 FAIL.
- 같은 gate 호스트로 실제 HTTP 인증·세션 검사 18 PASS / 0 FAIL.
- Apache 설정 Syntax OK. 공유 루트 QA 로그에서 PHP Warning, Notice, Fatal 및 재작성 루프 미검출.
- 결과 로그: qa/artifacts/gate-php-regression-20261007.log, qa/artifacts/gate-php-routing-20261007.log.
- QA SMTP는 비활성화했으며 고객 계정·원본 SQL은 변경하지 않았습니다.
- 신규 DB 변경이나 서버 업로드·GitHub 커밋은 하지 않았습니다.

## 운영 적용

1. 가비아에서 gate 도메인을 기존 호스팅 계정에 추가 연결하고 해당 gate 호스트의 SSL을 설치합니다.
2. 공개 홈페이지와 관리자가 같은 프로젝트 루트를 사용합니다.
3. APP_ENV=production, GATE_ENFORCE=true, SESSION_SECURE=true를 유지합니다.
4. GATE_HOST에 확정 서브도메인, GATE_ALLOW_IPS에 운영자의 공인 IP/CIDR을 입력합니다.
5. 놀은 .env에 GATE_ROUTE_MODE=path, ADMIN_DIR=nol-gate를 적용합니다. 블루는 기존 /admin 경로를 유지합니다.
6. gate / 접속 이후 주소가 같은 gate 호스트의 관리자 디렉터리로 이동하는지 확인합니다.
7. 운영 적용 전 기존 파일을 백업하고 로그인·MFA·권한·업로드·다운로드·공개 사이트 회귀를 확인합니다.

참고: PHP 라우팅은 도메인별 내부 재작성 없이 동작합니다. 기존 .htaccess의 비밀파일/업로드 차단 규칙까지 제거하는 방식은 아닙니다.
