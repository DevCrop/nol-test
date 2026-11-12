"""Build immutable BlueSquare v6 from the v5 web manifest and verified source."""
from pathlib import Path
import hashlib,json,re,zipfile
from datetime import datetime,timezone
root=Path(__file__).resolve().parents[1]
out=root/'deploy/private-handoff/bluesquare-admin-final-261110-v6'
if out.exists():raise SystemExit('Immutable release already exists; choose a new release.')
sha=lambda b:hashlib.sha256(b).hexdigest()
hfile=lambda p:sha(p.read_bytes())
v5p=root/'deploy/private-handoff/bluesquare-admin-final-261110-v5/검증결과.json'
v5=json.loads(v5p.read_text(encoding='utf-8'))
basep=root/'deploy/candidates/production-review-261110-v2/operations/manifest.json'
baseline=json.loads(basep.read_text(encoding='utf-8-sig'))['files']
files=sorted(v5['full_web'])
server=root/'qa/artifacts/live-20261110/server-v6-final.log'
client=root/'qa/artifacts/live-20261110/client-v6-final.log'
s=server.read_text(encoding='utf-8-sig');c=client.read_text(encoding='utf-8')
assert s.rstrip().endswith('PHP ROUTER QA PASSED')
assert not re.search(r'^(?:FAIL|SKIP|Warning:|Notice:|Fatal error|Parse error)',s,re.M)
passes=len(re.findall(r'^PASS ',s,re.M));assert passes==632
suites={'gate-client-regressions.mjs':14,'account-request-lifecycle.test.mjs':18,'legacy-admin-client.mjs':37,'design-client.mjs':14,'board-editor-client.mjs':3,'required-password-client.mjs':10,'account-credential-dialog.test.mjs':6,'account-loading.test.mjs':3,'optional-public-controls.mjs':8,'form-state.test.mjs':17}
assert all(name in c for name in suites) and 'AssertionError' not in c
secrets=[]
for line in (root/'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env').read_text(encoding='utf-8-sig').splitlines():
 if '=' in line and line.split('=',1)[0] in ['SMTP_PASS','DB_PASS']:
  value=line.split('=',1)[1].strip().strip('\"\'')
  if len(value)>=8:secrets.append(value.encode())
manifest={}
for name in files:
 assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy)(/|$)|\.(sql|zip|log|key|pem)$',name,re.I),name
 p=root/name;assert p.is_file() and not p.is_symlink(),name
 data=p.read_bytes();assert not any(secret in data for secret in secrets),'Private secret detected; value suppressed'
 manifest[name]=sha(data)
changed=[name for name in files if baseline.get(name)!=manifest[name]]
delta=[name for name in files if v5['full_web'][name]!=manifest[name]]
assert len(delta)==11,delta
out.mkdir(parents=True)
archive_hashes={}
for filename,selected in [('01_누적수정_웹루트덮어쓰기.zip',changed),('02_전체웹_보관용.zip',files)]:
 p=out/filename
 with zipfile.ZipFile(p,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as z:
  for name in selected:z.write(root/name,name)
 with zipfile.ZipFile(p) as z:
  assert z.testzip() is None and z.namelist()==selected
  for name in selected:assert sha(z.read(name))==manifest[name]==hfile(root/name),name
 archive_hashes[filename]=hfile(p)
guide='''블루스퀘어 관리자 누적 수정 v6 — 2026-11-10

서버에 올릴 것: 01_누적수정_웹루트덮어쓰기.zip의 내용물만.
v5를 포함한 누적 수정본입니다. 이미 보안 업데이트가 적용된 현재 블루스퀘어 웹 루트에 덮어씁니다.

1. 웹 루트에서 수정파일목록.txt에 있는 기존 파일을 PC에 백업합니다.
2. 01 ZIP을 PC에서 풀고 내부의 admin / inc / resource 등 폴더 구조 그대로 기존 웹 루트에 덮어씁니다.
3. FTP 전송은 Binary(바이너리). 기존 전체 파일이나 uploads를 지우거나 동기화 삭제하지 마세요.
4. 전송이 모두 완료된 뒤 관리자 페이지를 Ctrl+F5 하고 재확인합니다.

이번에는 SQL 실행 없음. .env 및 .env.gate 변경 없음. 계정·비밀번호 초기화 없음.
ZIP 자체, 이 안내문·검증 문서·JSON·로그는 서버에 올리지 않습니다.
02_전체웹_보관용.zip은 같은 소스의 전체 웹 보관본입니다. 현재 서버 수정에는 01만 사용합니다.

수정: 공지 체크 복원/해제, 기존 링크·추가필드 보존, gate 폼 값 변형 방지, 팝업 저장·기간·링크·순위, 배너 신규 순위, 무기한 날짜/링크 라디오 충돌, 잘못된 화면 문구.

검증: 최종 Docker PHP632 PASS/0FAIL/0SKIP, 클라이언트 VM130 PASS/0FAIL, 소스와 ZIP 항목별 SHA256 일치.
실서버에서는 기존 v5의 QA 게시판/배너/공연 생성·수정·업로드·공개 반영·숨김/삭제를 확인했습니다.
v6 자체는 실서버 미적용입니다. 모든 모달/모바일/권한별 실서버 검증 완료라고 판정하지 않습니다.

업로드 후 우선 확인:
- 팝업 신규 등록 → 공개 홈 이미지/링크/기간 → 수정 → 숨김 → 삭제
- 배너 순위 입력값 재조회, 같은창/새창 선택 후 무기한 날짜 비활성 유지
- 공지 체크 저장/재조회 및 체크 해제, 기존 URL·본문 변경 없는 저장
- 계정 생성 모달 및 로그인/MFA/본인 비밀번호

추가 정리: 실서버 비활성·권한없는 QA 계정 qa_ui_261110(번호8)은 브라우저 정책 중단 당시 삭제 미확인입니다.
아직 남아 있다면 해당 QA 계정만 변경 사유 'QA 종료'로 삭제하세요. 다른 계정은 변경하지 마세요.
'''
(out/'먼저읽기.txt').write_text(guide,encoding='utf-8')
(out/'수정파일목록.txt').write_text('\n'.join(changed)+'\n',encoding='utf-8')
(out/'v5대비_이번변경11개.txt').write_text('\n'.join(delta)+'\n',encoding='utf-8')
for doc in ['QA_최종수정_v6_20261110.md','QA_실서버_확인_20261110.md']:(out/doc).write_bytes((root/doc).read_bytes())
for filename,source in [('자동QA_서버업로드금지.log',server),('클라이언트QA_서버업로드금지.log',client)]:(out/filename).write_bytes(source.read_bytes())
receipt={'release':'bluesquare-admin-final-261110-v6','created_utc':datetime.now(timezone.utc).isoformat(),'status':'LOCAL_FINAL_ACCEPTANCE_PASSED_LIVE_V6_RECHECK_REQUIRED','sql_changes':False,'env_changes':False,'production_deployed':False,'production_qa_account_cleanup':'no8 pending user verification','php_pass_lines':passes,'php_fail':0,'php_skip':0,'client_vm_checks':sum(suites.values()),'client_suites':suites,'prior_mfa_http24_evidence_reused':True,'v5_receipt_sha256':hfile(v5p),'server_log_sha256':hfile(server),'client_log_sha256':hfile(client),'known_secrets_absent':True,'zip_source_hash_match':True,'patch_files':len(changed),'delta_from_v5':delta,'full_web_files':len(files),'archives':archive_hashes,'patch':{n:manifest[n] for n in changed},'full_web':manifest}
(out/'검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'output':str(out),'patch_files':len(changed),'full_web_files':len(files),'php_pass':passes,'client_vm_pass':sum(suites.values()),'archives':archive_hashes},ensure_ascii=False))
