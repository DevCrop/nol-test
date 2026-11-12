"""Package the reviewed web baseline plus explicit new runtime dependencies; no SQL/env."""
import hashlib
import json
from pathlib import Path
import re
import zipfile
from datetime import datetime, timezone

root = Path(__file__).resolve().parent.parent
baseline_path = root / 'deploy/candidates/production-review-261110-v2/operations/manifest.json'
baseline = json.loads(baseline_path.read_text(encoding='utf-8-sig'))['files']
added = ['admin/resource/js/account-permissions.js', 'admin/resource/js/AdminAjaxRequest.js', 'inc/lib/classes/Security/UploadLifecycle.php', 'inc/lib/classes/Security/WorksInput.php']
files = sorted(set(baseline) | set(added))
out = root / 'deploy/private-handoff/bluesquare-admin-final-261110-v5'
if out.exists():
    raise SystemExit('Immutable output already exists; use a new release name.')
hash_data = lambda data: hashlib.sha256(data).hexdigest()
hash_file = lambda path: hash_data(path.read_bytes())
qa_log = root / 'deploy/private-handoff/loading-repro-261110/server-final.log'
client_log = root / 'deploy/private-handoff/loading-repro-261110/client-final.log'
qa_text = qa_log.read_text(encoding='utf-8-sig')
assert 'PHP ROUTER QA PASSED' in qa_text and not re.search(r'^(?:FAIL|Warning:|Notice:|Fatal error|Parse error)', qa_text, re.M)
php_pass_lines = len(re.findall(r'^PASS ', qa_text, re.M))
client_checks = {'gate-client-regressions.mjs':14, 'account-request-lifecycle.test.mjs':18, 'legacy-admin-client.mjs':37, 'design-client.mjs':14, 'board-editor-client.mjs':3, 'required-password-client.mjs':10, 'account-credential-dialog.test.mjs':6, 'account-loading.test.mjs':3, 'optional-public-controls.mjs':8}
client_text = client_log.read_text(encoding='utf-8')
assert all(name in client_text for name in client_checks) and 'AssertionError' not in client_text
mfa_log = root / 'deploy/private-handoff/loading-repro-261110/mfa-transition-final.log'
mfa_text = mfa_log.read_text(encoding='utf-8-sig')
assert 'OK 24' in mfa_text and not re.search(r'^(?:FAIL|Warning|Notice|Fatal|Deprecated)',mfa_text,re.M)
manifest = {name: hash_file(root / name) for name in files}
changed = [name for name in files if baseline.get(name) != manifest[name]]
# Check known private credential values without logging them or putting env in web archives.
config = root / 'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env'
secrets = []
for line in config.read_text(encoding='utf-8-sig').splitlines():
    if '=' in line and line.split('=', 1)[0] in ('SMTP_PASS', 'DB_PASS'):
        value = line.split('=', 1)[1].strip().strip('"\'')
        if len(value) >= 8:
            secrets.append(value.encode())
for name in files:
    assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy)(/|$)|\.(sql|zip|log|key|pem)$', name, re.I), name
    data = (root / name).read_bytes()
    assert not any(secret in data for secret in secrets), 'Private secret detected; value suppressed'
out.mkdir(parents=True)
archive_hashes = {}
for filename, selected in [('01_누적수정_웹루트덮어쓰기.zip', changed), ('02_전체웹_보관용.zip', files)]:
    archive = out / filename
    with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
        for name in selected:
            z.write(root / name, name)
    with zipfile.ZipFile(archive) as z:
        assert z.testzip() is None
        assert z.namelist() == selected
        for name in selected:
            assert hash_data(z.read(name)) == manifest[name] == hash_file(root / name), name
    archive_hashes[filename] = hash_file(archive)
guide = '''블루스퀘어 관리자 누적 수정 v5 — 2026-11-10

현재 보안 업데이트를 이미 올린 운영 서버에는 01_누적수정_웹루트덮어쓰기.zip 하나만 사용합니다.
계정 생성 후 Unexpected/404, 관리자 CRUD/권한 UI, 첨부 실패 보존, 기존 경로/폰트/CAPTCHA/비밀번호 수정까지 포함합니다.
v3 이후 중복 계정 오류 때 로딩 고착, 네트워크/파싱 예외/중복 클릭, 게시판 삭제 취소시 이미지 선삭제, 오페라 삭제경로, 설정 삭제 폼 전송까지 보완했습니다. 구형 화면은 공통 AdminAjaxRequest 클래스를 재사용합니다.
v5는 MFA → 임시 비밀번호 변경 → 관리자 화면으로 바로 이어집니다. 이 설정 과정에서 재로그인/MFA를 반복하지 않습니다. 세션ID·DB토큰·CSRF는 교체되며 로그아웃 후 새 로그인에는 MFA가 그대로 필요합니다. 일반 비밀번호 변경의 로그아웃 정책은 유지합니다.

할 일
1. 웹 루트의 교체 대상 파일을 PC로 백업합니다. 수정 파일 목록은 수정파일목록.txt입니다.
2. 01 ZIP을 PC에서 풀어 내용물을 기존 웹 루트에 경로 그대로 덮어씁니다. ZIP 자체를 서버에 올리지 않습니다.
3. FTP 전송 형식은 Binary(바이너리)로 설정합니다. 폰트/이미지 파일이 텍스트 변환되지 않아야 합니다.
4. 업로드가 모두 완료되면 Ctrl+F5 후 https://gate.bluesquare.kr/ 에서 새로 로그인해 확인합니다.

기존 파일/고객 uploads/resource/file/.well-known 전체 삭제 금지. 자동 동기화 삭제 금지.
이번 패치에 SQL·.env·.env.gate 변경은 없습니다. 예전 SQL을 다시 실행하거나 계정/비밀번호를 초기화하지 않습니다.
계정 생성 요청에서 오류가 났었다면 계정 목록에 이미 만들어졌는지 먼저 확인합니다. 존재하면 중복 생성하지 않고 필요한 경우 최고관리자 재발급 기능을 사용합니다.

02_전체웹_보관용.zip은 동일 소스의 전체 웹 보관본이며 현재 서버 후속 수정에는 01만으로 충분합니다.
이 폴더의 문서/JSON/QA 로그는 서버에 올리지 않습니다. 기존 SQL/환경 비밀값은 이전 비공개 최종 배포 폴더에서 계속 보관합니다.

검증: 최신 운영 DB 사본 Docker 전체 QA PASS 출력557 / 실패0. MFA_ENABLED=true 별도 Docker 실제 HTTP24 / 실패0. JS VM113 / 실패0. 폰트66 디코딩은 변경 없는 기존 증적을 재사용했습니다. 원본 소스/ZIP 해시 일치 검사 완료.
한계: 실제 브라우저 시각 검수는 도구 차단으로 미완료. 운영 서버에는 직접 적용하지 않았습니다.
배포 후 확인: 로그인/MFA/본인 비밀번호, 신규 계정 모달 복사·닫기·목록 이동, 권한 변경, 게시글 Summernote 입력/저장, 공연/배너/팝업/대관 CRUD 및 파일 선택 미리보기, 공개 사이트 메인/주차/편의시설.
문제가 있으면 신규 게시물/계정을 삭제하거나 DB를 전체 복원하지 말고 작업을 멈추고 교체 파일 백업과 현상/네트워크 응답을 확보합니다.
'''
(out / '먼저읽기.txt').write_text(guide, encoding='utf-8')
(out / '수정파일목록.txt').write_text('\n'.join(changed) + '\n', encoding='utf-8')
(out / 'QA_관리자최종_20261110.md').write_bytes((root / 'QA_관리자최종_20261110.md').read_bytes())
(out / 'QA_로딩_프라미스_20261110.md').write_bytes((root / 'QA_로딩_프라미스_20261110.md').read_bytes())
(out / 'QA_MFA_임시비밀번호_연속인증_20261110.md').write_bytes((root / 'QA_MFA_임시비밀번호_연속인증_20261110.md').read_bytes())
(out / 'MFA전환QA_서버업로드금지.log').write_bytes(mfa_log.read_bytes())
(out / '자동QA_서버업로드금지.log').write_bytes(qa_log.read_bytes())
(out / '클라이언트QA_서버업로드금지.log').write_bytes(client_log.read_bytes())
receipt = {'project':'bluesquare','release':'admin-final-261110-v5','created_utc':datetime.now(timezone.utc).isoformat(), 'status':'LOCAL_AUTOMATED_QA_PASSED_BROWSER_AND_LIVE_ACCEPTANCE_PENDING',
    'baseline_manifest_sha256':hash_file(baseline_path),'source_db_backup_sha256':hash_file(root.parent / '261110/261110.sql'),
    'sql_changes':False,'env_changes':False,'live_writes':False,'patch_files':len(changed),'full_web_files':len(files),
    'known_secrets_absent_from_web':True,'zip_source_hash_match':True,'automated_php_pass_lines':php_pass_lines,'client_vm_checks':sum(client_checks.values()),'client_suites':client_checks,
    'server_qa_log_sha256':hash_file(qa_log),'client_qa_log_sha256':hash_file(client_log),
    'mfa_enabled_http_checks':24,'mfa_qa_log_sha256':hash_file(mfa_log),
    'visual_browser_qa':'NOT_PERFORMED_CUA_FAILURE_AND_BROWSER_LAUNCH_POLICY_REJECTION',
    'archives':archive_hashes,'patch':{name:manifest[name] for name in changed},'full_web':manifest}
(out / '검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({k:v for k,v in receipt.items() if k not in ('patch','full_web')},ensure_ascii=False))
print(out)
