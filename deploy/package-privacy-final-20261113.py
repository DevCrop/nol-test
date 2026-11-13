"""v4 incremental deployment and clean customer source from immutable v3."""
from pathlib import Path
import hashlib
import json
import zipfile

ROOT = Path(__file__).resolve().parents[1]
PARENT = ROOT/'deploy/private-handoff/bluesquare-cleanup-20261113-v3'
OUT = ROOT/'deploy/private-handoff/bluesquare-privacy-final-20261113-v4'
SOURCE = '03_고객전달_소스_비밀값DB데이터제외.zip'
FILES = [
    'inc/lib/classes/Security/BoardPrivacyService.php',
    'admin/pages/board/ajax/board.process.php',
    'admin/pages/board/ajax/board.comment.process.php',
    'admin/pages/board/board.view.php',
    'admin/pages/board/board.comment.view.php',
    'admin/inc/log.view.php',
]
sha = lambda b: hashlib.sha256(b).hexdigest()


def archive(path, files):
    with zipfile.ZipFile(path,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as z:
        for name,data in sorted(files.items()): z.writestr(name,data)
    with zipfile.ZipFile(path) as z:
        assert z.testzip() is None
        assert all(sha(z.read(n)) == sha(data) for n,data in files.items())


qa = json.loads((ROOT/'qa/artifacts/privacy-verification-v4.json').read_text('utf-8'))
assert qa['accepted'] and qa['failed'] == 0
assert qa['checks']['regression-privacy-v4']['pass'] == 860
assert all(sha((ROOT/n).read_bytes()) == qa['code_hashes'][n] for n in FILES)
assert not OUT.exists(), 'Immutable release exists; choose a new version.'
parent = json.loads((PARENT/'검증결과.json').read_text('utf-8'))
assert sha((PARENT/SOURCE).read_bytes()) == parent['archives'][SOURCE]
with zipfile.ZipFile(PARENT/SOURCE) as z: customer = {n:z.read(n) for n in z.namelist()}
rollback = {n:customer['webroot/'+n] for n in FILES if 'webroot/'+n in customer}
patch = {n:(ROOT/n).read_bytes() for n in FILES}
for n,data in patch.items(): customer['webroot/'+n] = data

ops = '''블루스퀘어 v4 실서버 적용 순서 / 2026-11-13
대상: v2 엔진 전환·웹 보안 보완, v3 정리를 이미 적용한 블루스퀘어 서버.
이번 변경은 웹6파일뿐이며 DB 변경 SQL/.env/계정/SMTP/SSL 변경은 없습니다.

1. 현재 서버 파일과 DB를 백업하고, 관리자 게시판·댓글 쓰기를 잠시 중지합니다.
2. 01_실서버반영_웹6파일.zip을 PC에서 풉니다. 신규 파일
   inc/lib/classes/Security/BoardPrivacyService.php를 먼저 같은 경로로 올립니다.
3. ZIP의 나머지5파일을 기존과 같은 경로에 Binary 덮어쓰기합니다.
   admin/pages/board/ajax/board.process.php
   admin/pages/board/ajax/board.comment.process.php
   admin/pages/board/board.view.php
   admin/pages/board/board.comment.view.php
   admin/inc/log.view.php
   기존 폴더 전체를 지우거나 .env·.env.gate·uploads를 교체하지 않습니다.
4. 호스팅에서 PHP 캐시를 사용하는 경우 지원되는 방법으로 새 코드 반영을 확인합니다.
5. 로그인/MFA 후 게시물 상세·등록·수정·복사·삭제·댓글 등록/삭제를 확인합니다.
   개인정보 접속기록에 실제 게시글 번호와 마스킹된 정보주체·수행업무가 남는지 확인합니다.
   작업자 ID 목록 마스킹, 검색/필터/페이지 이동, 결과 복사·드래그 제한을 확인합니다.
   검색 입력창은 정상 입력·복사가 가능해야 합니다. 홈페이지/자료실/첨부도 확인합니다.
6. 정상 확인 후 쓰기를 재개합니다. 장애가 있으면 쓰기를 중지한 상태로
   04_복구용_서버업로드금지.zip의 기존5파일만 같은 경로로 복구합니다.
   신규 BoardPrivacyService.php는 옛 코드에서 참조하지 않으므로 남겨도 됩니다.
   DB 전체 복원이나 계정 초기화는 하지 않습니다.

전제: nb_board, nb_board_comment, nb_admin_audit, nb_admin_privacy_access가 InnoDB여야 합니다.
기존 v2 SQL 결과가 모두1이었던 서버는 이 전제를 충족합니다.
불확실하면 참고_SELECT만_엔진확인.sql을 읽기 전용으로 실행해 네 항목을 확인합니다.
이번 v4 때문에 이전 changes.sql/원본덤프/계정설정 SQL을 다시 실행하지 않습니다.
vendor189파일 정리는 보류된 범위이며 이번 ZIP은 vendor를 삭제하지 않습니다.
ZIP/SQL/문서/복구본 자체는 웹루트에 올리지 않습니다. 올릴 것은 01 ZIP의 내용물6파일입니다.
'''
guide = '''# 블루스퀘어 보안 개발 반영 안내 v4

기준일: 2026-11-13. v2 보안 기반·v3 불필요 코드 정리는 사용자 적용 완료 보고를 받았습니다. 이번 v4는 로컬 수정/QA 완료이며 실서버 추가 적용과 운영 확인은 진행 전입니다.

## 이번 반영

- 게시판 작성자 상세 열람과 입력·수정·동일값 수정·복사·단일/일괄삭제에 정보주체 처리기록을 연결했습니다.
- 댓글 화면의 작성자 열람, 댓글 입력·삭제, 게시글 삭제에 따른 댓글 정리를 기록합니다.
- 업무 데이터·일반 작업이력·필수 개인정보 기록을 같은 트랜잭션으로 확정합니다. 기록 실패 시 업무/첨부를 보존합니다.
- 작업자 ID는 DB와 검색 기능에 그대로 보관하며, 로그 화면 목록에서만 마스킹합니다. 결과 영역에는 Copy/Drag 제한을 적용하고 검색 입력·버튼 조작은 유지합니다.
- 계정·권한·MFA·세션·SMTP·허용 IP·SSL 및 기존 파일 저장 경로는 변경하지 않았습니다.

## 검증

PHP7.4.33/MySQL8 격리 Docker HTTP/DB 전체 회귀860건 통과, 실패0입니다. 이 안에 개인정보 집중29건, 기존 게시판28건, 로그 UI52건, 순수 로그뷰10건이 포함되어 중복 합산하지 않습니다. 이전 v3에서 기록 누락2건을 재현하고 수정본에서 차단을 확인했습니다. 문법·ZIP 무결성·배포 파일별 해시를 확인했습니다.

이 검증은 실서버 원문 공격변형 전체 재진단이나 실제 브라우저 클릭 검수의 완료를 뜻하지 않습니다. 실서버 적용 후 기관 IP/권한별 로그인·실첨부·복사/드래그·공개 반영을 확인해야 합니다. 실제 SMTP 발송·운영 계정 변경·운영 DB 쓰기는 이번 QA에서 실행하지 않았습니다.

## 고객 원본 대조

- 진단 PDF18건: SQL Injection, XSS, CSRF, 파일 업로드/삭제/다운로드, 관리자 인증, MFA/비밀번호/잠금, 오류 비노출·세션의 구현 및 QA 근거를 확인했습니다.
- 관리자 분리는 합의된 동일 호스팅 gate+IP/CIDR 방식입니다. 별도 서버/VPN을 구축했다고 설명하지 않습니다.
- NPG03: 주요 목록의 이름·ID·이메일·전화 마스킹과 Copy/Drag 제한을 적용합니다. 업무상 필요한 상세 원문은 인증·권한 및 기록으로 통제합니다.
- 엑셀 정책21·23·26~32: 개인정보 수행업무/정보주체 기록, 마스킹, 계정·권한 변경 사유/기간, 30분 유휴 만료, 비밀번호90일, 동시접속 제한, 장기 미접속 잠금의 근거가 있습니다.
- 정책29는 후속 합의에 따라 MFA 후 임시 비밀번호 변경→새 세션 관리자 진입입니다. 장기미접속 잠금은 로그인 시 평가합니다.
- 불필요 파일 제거의 vendor189개는 사용자 선택으로 보류되어 실서버 전체 제거 완료로 표시하지 않습니다.

## 고객 운영 확인

기관별 실제 IP/권한·임시 테스트 IP 회수, 기록 보관·백업/복원·정기점검·자료파기, 자산/YAMS·단말/시설 운영9건은 고객 운영 증거가 필요합니다. 개인정보처리방침의 가비아 재위탁·실제 이벤트 수집 항목은 고객이 사실관계를 확정한 뒤 현행화해야 합니다. 이 내용을 임의로 만들어 반영하지 않았습니다.
'''
source_guide = '''블루스퀘어 고객 전달 소스 v4 / 2026-11-13

webroot/: 현재 보안 개발 소스와 운영 추가 보존 자료를 병합한 전체 응용 소스입니다.
기존 v2 보안 기반, v3 초기화/디버그 정리, v4 개인정보 기록과 로그 UI 보완을 포함합니다.
실제 .env/.env.gate·비밀값·운영 DB 행·upload/uploads 저장소·로그·미사용 테스트/예제·내부 QA/배포도구는 포함하지 않습니다.
업무 BI/CI ZIP과 정상 라이브러리/폰트/편집기/라이선스는 유지했습니다.

설치 지원 기준: PHP7.4.33 + MySQL8. 제공된 운영 DB 백업은 MySQL8.0.44이고 Docker QA도 MySQL8입니다.
Apache PHP PATH_INFO, PDO MySQL, mbstring, GD, DOM 등 기존 실행환경이 필요합니다.
webroot/.env.example은 비밀번호 없는 양식입니다. 고객의 실제 DB/SMTP/도메인/IP로 별도 설정해야 합니다.
공용 gate SMTP 계정의 실제 앱 비밀번호와 관리자 계정 암호는 이 소스에 없습니다.

database/EMPTY-INSTALL-schema-only.sql은 빈 DB 설치용24테이블 스키마이며 데이터가 없습니다.
기존 운영 DB에 재입력하지 않습니다. 실제 데이터와 업로드 저장소는 고객 전용 보안 채널로 이관합니다.
database/EXISTING-SITE-audit-transactions.sql은 이전 v2의 엔진 전환용 증분 SQL입니다.
이번 v4는 추가 DB 변경이 없으므로 v2 적용을 마친 서버에서 이 SQL을 재실행할 필요가 없습니다.

gate 연결은 호스팅 DNS/도메인/SSL 설정과 GATE_HOST/GATE_ROUTE_MODE=php 설정을 사용합니다.
운영은 HTTPS, Secure cookie, MFA, 고객 허용IP를 유지합니다.
소스만으로 기존 데이터·계정·비밀값·업로드가 자동 생성되지는 않습니다.
다른 MySQL/MariaDB 버전이나 별도 호스팅으로의 신규 설치 호환성은 별도 검증이 필요합니다.
현 서버에 이번 수정만 적용하려면 별도 제공한 운영용6파일 ZIP을 사용합니다.
'''

# Replace obsolete v2/v3 delivery prose instead of stacking contradictory notes.
customer.pop('docs/후속정리_v3_적용안내.txt',None)
customer['docs/구성안내.txt'] = source_guide.encode('utf-8')
customer['docs/개발반영_체크리스트.md'] = guide.encode('utf-8')
customer['docs/v4_운영적용순서.txt'] = ops.encode('utf-8')
OUT.mkdir(parents=True)
archive(OUT/'01_실서버반영_웹6파일.zip',patch)
(OUT/'02_적용순서.txt').write_text(ops,'utf-8')
archive(OUT/SOURCE,customer)
archive(OUT/'04_복구용_서버업로드금지.zip',rollback)
(OUT/'05_고객전달_개발반영안내.md').write_text(guide,'utf-8')
(OUT/'참고_SELECT만_엔진확인.sql').write_text("SELECT DATABASE()='dbusrbluesq0211' AS database_correct;\nSELECT TABLE_NAME, ENGINE, ENGINE='InnoDB' AS ready FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('nb_board','nb_board_comment','nb_admin_audit','nb_admin_privacy_access') ORDER BY TABLE_NAME;\n",'utf-8')
web = {n[len('webroot/'):]:sha(b) for n,b in customer.items() if n.startswith('webroot/') and n!='webroot/.env.example'}
assert len(web) == parent['customer_runtime_files']+1
assert 'webroot/inc/lib/base.init.php' not in customer
assert all('webroot/'+n not in customer for n in ['.env','.env.gate'])
result = {'parent_release':PARENT.name,'production_modified':False,'schema_changes':False,'env_changes':False,'vendor_cleanup':'deferred','patch_files':FILES,'source_hashes':{n:sha(b) for n,b in patch.items()},'customer_runtime_files':len(web),'webroot_sha256':web,'qa':qa,'archives':{p.name:sha(p.read_bytes()) for p in OUT.glob('*.zip')}}
(OUT/'검증결과.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),'utf-8')
print(json.dumps({'output':str(OUT),'patch_files':len(FILES),'customer_runtime_files':len(web),'qa':860,'archives':result['archives']},ensure_ascii=False,indent=2))
