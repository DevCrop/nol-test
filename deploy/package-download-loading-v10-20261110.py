"""Immutable two-file loading fix and cumulative fallback for BlueSquare."""
from pathlib import Path
import json,re,hashlib,zipfile
from datetime import datetime,timezone
r=Path(__file__).resolve().parents[1];out=r/'deploy/private-handoff/bluesquare-download-loading-20261110-v10'
if out.exists():raise SystemExit('Immutable release already exists')
sha=lambda b:hashlib.sha256(b).hexdigest()
vp=r/'deploy/private-handoff/bluesquare-download-mfa-20261110-v9/검증결과.json';v=json.loads(vp.read_text(encoding='utf-8'))
manifest={n:sha((r/n).read_bytes())for n in v['full_web']}
delta=[n for n in manifest if manifest[n]!=v['full_web'][n]]
assert sorted(delta)==['admin/pages/request/request.view.php','admin/resource/js/security.js'],delta
base=json.loads((r/'deploy/candidates/production-review-261110-v2/operations/manifest.json').read_text(encoding='utf-8-sig'))['files'];cumulative=sorted(n for n,h in manifest.items()if base.get(n)!=h)
logs={'client':r/'qa/artifacts/client-v10-final-20261110.log','http':r/'qa/artifacts/download-loading-http-v10-20261110.log','regression_red':r/'qa/artifacts/native-download-red-v9-20261110.log'}
c=logs['client'].read_text(encoding='utf-8-sig');h=logs['http'].read_text(encoding='utf-8-sig');red=logs['regression_red'].read_text(encoding='utf-8-sig')
assert 'pass=14 fail=0' in c and h.rstrip().endswith('OK 71')
assert not re.search(r'^(FAIL|SKIP|Warning:|Notice:|Fatal error)',c+'\n'+h,re.M)
assert 'AssertionError' in red and 'true !== false' in red
suites={'gate-client-regressions.mjs':14,'account-request-lifecycle.test.mjs':18,'legacy-admin-client.mjs':37,'design-client.mjs':14,'board-editor-client.mjs':3,'required-password-client.mjs':10,'account-credential-dialog.test.mjs':6,'account-loading.test.mjs':3,'optional-public-controls.mjs':8,'form-state.test.mjs':17,'native-download-loading.test.mjs':14};assert all(n in c for n in suites)and sum(suites.values())==144
assert 'data-native-download' in (r/delta[0]).read_text(encoding='utf-8') or 'data-native-download' in (r/delta[1]).read_text(encoding='utf-8')
secrets=[]
for line in (r/'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env').read_text(encoding='utf-8-sig').splitlines():
 if '=' in line and line.split('=',1)[0]in['DB_PASS','SMTP_PASS']:
  val=line.split('=',1)[1].strip().strip('\"\'')
  if len(val)>=8:secrets.append(val.encode())
for n in manifest:
 assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy|storage)(/|$)|\.(sql|zip|log|pem|key)$',n,re.I),n
 assert not any(s in (r/n).read_bytes()for s in secrets),'Known private value detected'
out.mkdir(parents=True);archives={}
for name,names in [('01_다운로드로딩수정_2파일.zip',sorted(delta)),('02_누적수정_웹루트덮어쓰기.zip',cumulative)]:
 p=out/name
 with zipfile.ZipFile(p,'w',zipfile.ZIP_DEFLATED,compresslevel=6)as z:
  for n in names:z.write(r/n,n)
 with zipfile.ZipFile(p)as z:
  assert z.testzip()is None and z.namelist()==names
  for n in names:assert sha(z.read(n))==manifest[n]==sha((r/n).read_bytes()),n
 archives[name]=sha(p.read_bytes())
guide='''블루스퀘어 v10 — 대관 첨부 다운로드 후 무한 로딩 수정

v9까지 이미 올렸다면 01_다운로드로딩수정_2파일.zip만 사용합니다.
압축을 PC에서 풀고 아래2파일을 같은 경로로 기존 웹 루트에 덮어쓰세요.
- admin/pages/request/request.view.php
- admin/resource/js/security.js

v9 수정이 아직 미적용이면 02_누적수정_웹루트덮어쓰기.zip을 사용합니다.
02는 이전 다운로드 파일 손상·MFA 자동발송·비밀번호 메뉴 정리와 이번 로딩 수정을 모두 포함합니다.
01과02를 둘 다 올릴 필요는 없습니다.

업로드 전 대상 기존파일을 PC에 백업합니다. FTP는 Binary.
기존 전체파일이나 uploads를 삭제하지 않습니다.
SQL 실행·.env 교체·계정 변경 없음. ZIP·안내문·QA/검증 로그는 서버에 올리지 않습니다.
전송 완료 후 관리자 페이지 Ctrl+F5 합니다.

원인: native 첨부파일 응답은 현재 페이지가 바뀌지 않아 일반 페이지 이동 로딩이 끝나지 않았음.
수정: 다운로드 폼만 native-download로 분류하여 페이지 이동 로딩에서 제외.
브라우저 기본 다운로드 진행표시 사용. 저장·로그인·AJAX 및 다른 활성 요청 로딩은 유지.
사유·CSRF·권한·개인정보 기록·파일 무결성 처리는 유지.

QA: v9에서 재현, 수정 후 클라이언트144건·Docker 다운로드HTTP71건 통과.
브라우저 도구 초기화 오류로 실제 운영154번 화면 직접클릭은 수행하지 못했습니다.
업로드 후 사유 입력→파일 다운로드→페이지 조작 및 재다운로드 가능 여부를 확인해주세요.
'''
(out/'먼저읽기.txt').write_text(guide,encoding='utf-8');(out/'이번수정_2파일.txt').write_text('\n'.join(sorted(delta))+'\n',encoding='utf-8');(out/'QA_다운로드로딩_v10_20261110.md').write_bytes((r/'QA_다운로드로딩_v10_20261110.md').read_bytes())
for k,p in logs.items():(out/('QA_'+k+'_서버업로드금지.log')).write_bytes(p.read_bytes())
receipt={'release':out.name,'created_utc':datetime.now(timezone.utc).isoformat(),'production_deployed':False,'browser_live_click_verified':False,'status':'LOCAL_LOADING_FIX_VERIFIED_UPLOAD_REQUIRED','sql_changes':False,'env_changes':False,'client_vm_checks':144,'client_suites':suites,'download_http_checks':71,'v9_original_JS_red_reproduction':True,'v9_server_775_mfa_39_evidence_reused_unchanged_handlers':True,'v9_receipt_sha256':sha(vp.read_bytes()),'known_secrets_absent':True,'zip_source_hash_match':True,'log_sha256':{k:sha(p.read_bytes())for k,p in logs.items()},'delta_from_v9':sorted(delta),'cumulative_patch_files':len(cumulative),'archives':archives,'patch':{n:manifest[n]for n in cumulative},'full_web':manifest}
(out/'검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8');print(json.dumps({'output':str(out),'delta':len(delta),'cumulative':len(cumulative),'archives':archives},ensure_ascii=False))
