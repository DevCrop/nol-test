"""Package the current BlueSquare web fixes without SQL, env, uploads or QA."""
from pathlib import Path
from datetime import datetime,timezone
import hashlib,json,re,zipfile
root=Path(__file__).resolve().parents[1];out=root/'deploy/private-handoff/bluesquare-download-mfa-20261110-v9'
if out.exists():raise SystemExit('Immutable release exists')
sha=lambda b:hashlib.sha256(b).hexdigest()
prevp=root/'deploy/private-handoff/bluesquare-admin-final-261110-v8/검증결과.json';prev=json.loads(prevp.read_text(encoding='utf-8'))
base=json.loads((root/'deploy/candidates/production-review-261110-v2/operations/manifest.json').read_text(encoding='utf-8-sig'))['files']
new=['inc/lib/classes/Security/BinaryDownload.php','inc/lib/classes/Security/BoardAttachment.php','admin/pages/board/board.file.download.php'];files=sorted(set(prev['full_web'])|set(new))
logs={'server':root/'qa/artifacts/server-v9-final-20261110.log','mfa':root/'qa/artifacts/mfa-v9-final-20261110.log','mfa_unit':root/'qa/artifacts/mfa-flow-v9-final-20261110.log','download':root/'qa/artifacts/download-corruption-20261110/docker-http.log','download_no_buffer':root/'qa/artifacts/download-corruption-20261110/docker-output-buffer0.log','download_compression':root/'qa/artifacts/download-corruption-20261110/docker-compression-on.log'}
texts={k:p.read_text(encoding='utf-8-sig')for k,p in logs.items()}
assert texts['server'].rstrip().endswith('PHP ROUTER QA PASSED') and len(re.findall(r'^PASS ',texts['server'],re.M))==775
assert len(re.findall(r'^PASS ',texts['mfa'],re.M))==39 and texts['mfa'].rstrip().endswith('OK 24')
assert texts['mfa_unit'].rstrip().endswith('OK 7')
for k in ['download','download_no_buffer','download_compression']:assert texts[k].rstrip().endswith('OK 69')
assert not re.search(r'^(?:FAIL|SKIP|Warning:|Notice:|Fatal error|Parse error)','\n'.join(texts.values()),re.M)
secrets=[]
for line in (root/'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env').read_text(encoding='utf-8-sig').splitlines():
 if '=' in line and line.split('=',1)[0] in ['SMTP_PASS','DB_PASS']:
  v=line.split('=',1)[1].strip().strip('\"\'')
  if len(v)>=8:secrets.append(v.encode())
manifest={}
for n in files:
 assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy|storage)(/|$)|\.(sql|zip|log|key|pem)$',n,re.I),n
 p=root/n;assert p.is_file() and not p.is_symlink(),n
 b=p.read_bytes();assert not any(v in b for v in secrets),'Known private credential found; value suppressed';manifest[n]=sha(b)
 if n.endswith(('.js','.mjs')):assert prev['full_web'].get(n)==manifest[n],'Client JS changed; rerun VM acceptance'
changed=[n for n in files if base.get(n)!=manifest[n]];delta=[n for n in files if prev['full_web'].get(n)!=manifest[n]];assert len(delta)==12,delta
out.mkdir(parents=True);archives={}
for name,selected in [('01_누적수정_웹루트덮어쓰기.zip',changed),('02_v8대비_이번수정12파일_보관용.zip',delta)]:
 p=out/name
 with zipfile.ZipFile(p,'w',zipfile.ZIP_DEFLATED,compresslevel=6)as z:
  for n in selected:z.write(root/n,n)
 with zipfile.ZipFile(p)as z:
  assert z.testzip() is None and z.namelist()==selected
  for n in selected:assert sha(z.read(n))==manifest[n]==sha((root/n).read_bytes()),n
 archives[name]=sha(p.read_bytes())
guide='''블루스퀘어 v9 — 다운로드 손상·등록메일 자동 MFA

서버에 올릴 것: 01_누적수정_웹루트덮어쓰기.zip의 내용물만.
현재 보안 업데이트가 반영된 블루스퀘어 웹 루트에 덮어쓰는 누적 수정본입니다.
v8·하위 비밀번호 메뉴 제거·이번 다운로드/MFA 수정이 포함됩니다.

1. 수정파일목록.txt에 해당하는 기존 서버 파일을 PC에 백업합니다.
2. 01 ZIP을 PC에서 풉니다.
3. 내부 admin/inc/module/pages/resource 등의 폴더 구조 그대로 웹 루트에 덮어씁니다.
4. FTP는 Binary(바이너리) 전송. uploads나 기존 전체 파일을 삭제하지 않습니다.
5. 전체 전송 완료 후 Ctrl+F5, 로그인부터 다시 확인합니다.

SQL 실행 없음 / .env·.env.gate 변경 없음 / 기존 계정·이메일 변경 없음.
ZIP·이 안내문·문서·검증 JSON·QA 로그는 웹 루트에 올리지 않습니다.
02 ZIP은 v8 대비12개 변경 파일을 따로 보관한 것이며 일반 업로드에는 01만 사용하세요.

다운로드: 공개 자료실의 원본 응답 앞 LF6바이트가 붙는 결함 수정.
공통 바이너리 응답으로 앞뒤 바이트·파일 길이 보존, 관리자 링크를 gate 인증 경로로 교체.
대관 다운로드의 사유·권한·기록 유지. 구형 인증 없는 다운로드는 폐기.

MFA: 로그인 성공 직후 계정 등록 주소로 자동 발송. 이메일 입력 필드 제거.
정상: 로그인 → MFA → 관리자.
임시: 로그인 → MFA → 본인 새 비밀번호 변경 → 관리자. 중간 재MFA 없음.
새로고침 중복 발송 방지, 실패 재시도·30초 간격·15분 만료·5회 제한 유지.
사이트정보관리 하위 비밀번호 메뉴는 제거하고 상단 링크만 유지.

자동 QA: Docker PHP·HTTP775, MFA 활성 HTTP39, MFA 흐름7 통과.
다운로드69는 기본/버퍼0/압축On 세 환경에서 통과.
실제 운영 SMTP 발송과 v9 운영 파일 변경은 하지 않았습니다.
업로드 후 공개6개 실제 다운로드와 실제 등록 이메일 수신을 확인해야 최종 운영 완료입니다.
운영 원본 파일 자체의 무손상은 아직 단정하지 않습니다.
'''
(out/'먼저읽기.txt').write_text(guide,encoding='utf-8');(out/'수정파일목록.txt').write_text('\n'.join(changed)+'\n',encoding='utf-8');(out/'v8대비_이번변경12개.txt').write_text('\n'.join(delta)+'\n',encoding='utf-8')
for n in ['QA_최종반영_v9_20261110.md','QA_다운로드_파일무결성_20261110.md','고객원본_요구대조_체크리스트_20261110.md']:(out/n).write_bytes((root/n).read_bytes())
for k,p in logs.items():(out/('QA_'+k+'_서버업로드금지.log')).write_bytes(p.read_bytes())
receipt={'release':out.name,'created_utc':datetime.now(timezone.utc).isoformat(),'status':'LOCAL_ACCEPTANCE_PASSED_PRODUCTION_UPLOAD_REQUIRED','production_deployed':False,'sql_changes':False,'env_changes':False,'php_pass_lines':775,'mfa_enabled_http':39,'mfa_unit':7,'download_http_each_mode':69,'client_vm_130_evidence_reused_unchanged_JS':True,'browser_ui_verified':False,'production_original_hash_not_obtained':True,'v8_receipt_sha256':sha(prevp.read_bytes()),'log_sha256':{k:sha(p.read_bytes())for k,p in logs.items()},'known_secrets_absent':True,'zip_source_hash_match':True,'patch_files':len(changed),'delta_from_v8':delta,'new_runtime_files':new,'full_web_files':len(files),'archives':archives,'patch':{n:manifest[n]for n in changed},'full_web':manifest}
(out/'검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'output':str(out),'patch_files':len(changed),'delta_v8':len(delta),'new_runtime_files':new,'archives':archives},ensure_ascii=False))
