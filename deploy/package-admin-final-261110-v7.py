"""Immutable v7 package. Web hashes, native QA and private-secret exclusions."""
from pathlib import Path
from datetime import datetime,timezone
import hashlib,json,re,zipfile
root=Path(__file__).resolve().parents[1]
out=root/'deploy/private-handoff/bluesquare-admin-final-261110-v7'
if out.exists():raise SystemExit('Immutable release exists; choose a new release')
sha=lambda b:hashlib.sha256(b).hexdigest()
prevp=root/'deploy/private-handoff/bluesquare-admin-final-261110-v6/검증결과.json'
prev=json.loads(prevp.read_text(encoding='utf-8'))
base=json.loads((root/'deploy/candidates/production-review-261110-v2/operations/manifest.json').read_text(encoding='utf-8-sig'))['files']
new=['inc/lib/classes/Security/AdminLogReader.php','inc/lib/classes/Security/AdminLogView.php','inc/lib/classes/Security/RentalRequestService.php','admin/inc/log.filters.php','admin/inc/log.pager.php','admin/inc/log.view.php','admin/resource/css/admin-logs.css']
files=sorted(set(prev['full_web'])|set(new))
logs={k:root/'qa/artifacts'/v for k,v in {'server':'server-v7-integration-20261110.log','client':'client-v7-final-20261110.log','mfa':'mfa-v7-final-20261110.log'}.items()}
s=logs['server'].read_text(encoding='utf-8-sig');c=logs['client'].read_text(encoding='utf-8-sig');m=logs['mfa'].read_text(encoding='utf-8-sig')
assert s.rstrip().endswith('PHP ROUTER QA PASSED') and len(re.findall(r'^PASS ',s,re.M))==702
assert m.rstrip().endswith('OK 24') and len(re.findall(r'^PASS ',m,re.M))==24
assert not re.search(r'^(?:FAIL|SKIP|Warning:|Notice:|Fatal error|Parse error)',s+'\n'+c+'\n'+m,re.M)
assert 'AssertionError' not in c
suites=prev['client_suites'];assert all(name in c for name in suites) and sum(suites.values())==130
secrets=[]
p=root/'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env'
for line in p.read_text(encoding='utf-8-sig').splitlines():
 if '=' in line and line.split('=',1)[0] in ['DB_PASS','SMTP_PASS']:
  value=line.split('=',1)[1].strip().strip('\"\'')
  if len(value)>=8:secrets.append(value.encode())
manifest={}
for n in files:
 assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy)(/|$)|\.(sql|zip|log|key|pem)$',n,re.I),n
 p=root/n;assert p.is_file() and not p.is_symlink(),n
 data=p.read_bytes();assert not any(v in data for v in secrets),'Private secret detected; value suppressed'
 manifest[n]=sha(data)
changed=[n for n in files if base.get(n)!=manifest[n]]
delta=[n for n in files if prev['full_web'].get(n)!=manifest[n]]
assert len(delta)==16,delta
out.mkdir(parents=True)
archives={}
for name,selected in [('01_누적수정_웹루트덮어쓰기.zip',changed),('02_전체웹_보관용.zip',files)]:
 p=out/name
 with zipfile.ZipFile(p,'w',zipfile.ZIP_DEFLATED,compresslevel=6)as z:
  for n in selected:z.write(root/n,n)
 with zipfile.ZipFile(p)as z:
  assert z.testzip() is None and z.namelist()==selected
  for n in selected:assert sha(z.read(n))==manifest[n]==sha((root/n).read_bytes()),n
 archives[name]=sha(p.read_bytes())
guide='''블루스퀘어 v7 누적 수정 — 2026-11-10

서버에 올릴 것: 01_누적수정_웹루트덮어쓰기.zip 내용물만.
현재 보안 업데이트/v6가 반영된 웹 루트에 덮어쓰는 누적본입니다.

1. 수정파일목록.txt의 기존 서버 파일을 PC에 백업합니다.
2. 01 ZIP을 PC에서 풉니다.
3. 내부 admin/inc/module/resource 등 폴더 구조 그대로 기존 웹 루트에 덮어씁니다.
4. FTP 전송은 Binary(바이너리). uploads나 기존 전체 파일을 삭제하지 않습니다.
5. 업로드가 모두 끝난 뒤 관리자 페이지 Ctrl+F5 후 확인합니다.

이번 SQL 실행 없음 / .env·.env.gate 교체 없음 / 기존 계정·비밀번호 초기화 없음.
ZIP·SQL·QA·로그·안내문·JSON·PDF는 웹 루트에 올리지 않습니다.
02는 같은 소스의 전체 웹 보관본입니다. 현재 서버에는 01만 사용합니다.

이번 보완: 작업이력·개인정보기록 검색/필터/페이지/배지/상세,
계정·대관 개인정보 변경기록, 게시판 제목 출력·작성자 마스킹,
일괄 삭제 실제 대상 ID 기록, 대관 DB 저장 후 알림 발송.

gate 서브도메인/IP 방식과 임시 비밀번호 추가 흐름을 유지합니다.
자동 QA: Docker PHP·HTTP702, 클라이언트 VM130, MFA 활성 HTTP24 통과.
실제 Chrome: 작업이력 상세·2페이지·삭제 필터 및 개인정보 화면 표시 확인.
모바일 시각 검수와 v7 운영 반영 후 확인은 완료로 표시하지 않았습니다.

업로드 후 확인:
- gate 로그인/MFA, 임시 비밀번호 변경→관리자, 일반 본인 비밀번호 변경
- 작업이력/개인정보기록 기간·유형·대상·검색/페이지/상세
- 게시판·배너·팝업·공연·대관 CRUD, 첨부와 공개 홈페이지 반영
- 고객별 권한, 기관별 허용/차단 IP, SSL 후 HTTP/기존admin 예외 정책
- 임시 테스트IP 및 실서버 QA계정 no8 잔존 여부 정리

실서버 운영데이터가 변경된 뒤 과거 DB 전체복원으로 되돌리지 않습니다.
문제시 이번 변경 파일만 사전 백업본으로 복원합니다.
'''
(out/'먼저읽기.txt').write_text(guide,encoding='utf-8')
(out/'수정파일목록.txt').write_text('\n'.join(changed)+'\n',encoding='utf-8')
(out/'v6대비_이번변경16개.txt').write_text('\n'.join(delta)+'\n',encoding='utf-8')
for n in ['QA_최종반영_v7_20261110.md','고객원본_요구대조_체크리스트_20261110.md']:(out/n).write_bytes((root/n).read_bytes())
pdf=root/'output/pdf/블루스퀘어_보안개선_개발반영보고서_v7_20261110.pdf'
assert pdf.is_file();(out/pdf.name).write_bytes(pdf.read_bytes())
for k,p in logs.items():(out/('QA_'+k+'_서버업로드금지.log')).write_bytes(p.read_bytes())
receipt={'release':out.name,'created_utc':datetime.now(timezone.utc).isoformat(),'status':'LOCAL_AUTOMATED_ACCEPTANCE_PASSED_LIVE_AND_MOBILE_PENDING','production_deployed':False,'sql_changes':False,'env_changes':False,'php_pass_lines':702,'php_fail':0,'php_skip':0,'client_vm_checks':130,'client_suites':suites,'mfa_enabled_http':24,'browser_verified':['audit details','audit page2','audit delete filter','privacy screen displayed'],'browser_pending':['privacy search submission result','mobile visual checks'],'customer_operations_pending':True,'v6_receipt_sha256':sha(prevp.read_bytes()),'log_sha256':{k:sha(p.read_bytes())for k,p in logs.items()},'pdf_sha256':sha(pdf.read_bytes()),'known_secrets_absent':True,'zip_source_hash_match':True,'patch_files':len(changed),'delta_from_v6':delta,'new_runtime_files':new,'full_web_files':len(files),'archives':archives,'patch':{n:manifest[n]for n in changed},'full_web':manifest}
(out/'검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'output':str(out),'cumulative_patch':len(changed),'delta_from_v6':len(delta),'full_web':len(files),'archives':archives},ensure_ascii=False))
