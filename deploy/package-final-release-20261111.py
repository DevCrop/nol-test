from pathlib import Path
import hashlib,json,re,zipfile,shutil
from datetime import datetime,timezone
r=Path(__file__).resolve().parents[1]
out=r/'deploy/private-handoff/bluesquare-final-20261111'
if out.exists():raise SystemExit('Immutable release directory already exists')
sha=lambda b:hashlib.sha256(b).hexdigest()
v=json.loads((r/'deploy/private-handoff/bluesquare-download-loading-20261110-v10/검증결과.json').read_text(encoding='utf-8'))
base=json.loads((r/'deploy/candidates/production-review-261110-v2/operations/manifest.json').read_text(encoding='utf-8-sig'))['files']
manifest={n:sha((r/n).read_bytes()) for n in v['full_web']}
delta=sorted(n for n,h in manifest.items() if v['full_web'].get(n)!=h)
assert delta==['inc/lib/classes/Security/HtmlSanitizer.php','inc/lib/classes/Security/PiiMask.php'],delta
selected=sorted(n for n,h in manifest.items() if base.get(n)!=h)
server=(r/'qa/artifacts/server-release-20261111.log').read_text(encoding='utf-8-sig')
assert server.rstrip().endswith('PHP ROUTER QA PASSED') and not re.search(r'^(FAIL|SKIP|Warning:|Notice:|Fatal error)',server,re.M)
additional=(r/'qa/artifacts/additional-release-20261111.log').read_text(encoding='utf-8-sig')
assert 'OK 6' in additional and '42 PASS / 0 FAIL' in additional and not re.search(r'^(FAIL|SKIP|Warning:|Notice:|Fatal error)',additional,re.M)
client=(r/'qa/artifacts/client-release-20261111.log').read_text(encoding='utf-8-sig')
assert 'pass=14 fail=0' in client and not re.search(r'^(FAIL|SKIP)',client,re.M)
mfa=(r/'qa/artifacts/mfa-release-20261111.log').read_text(encoding='utf-8-sig')
assert len(re.findall(r'^PASS ',mfa,re.M))==39 and mfa.rstrip().endswith('OK 24')
secrets=[]
for line in (r/'deploy/private-handoff/bluesquare-production-261110-final/04_환경설정/.env').read_text(encoding='utf-8-sig').splitlines():
 if '=' in line and line.split('=',1)[0] in ['DB_PASS','SMTP_PASS']:
  value=line.split('=',1)[1].strip().strip('"'+chr(39))
  if len(value)>=8:secrets.append(value.encode())
for n in manifest:
 assert not re.search(r'(^|/)(\.env[^/]*|qa|sql|deploy|storage)(/|$)|\.(sql|zip|log|key|pem)$',n,re.I),n
 assert not any(s in (r/n).read_bytes() for s in secrets),'Private credential detected (redacted)'
out.mkdir(parents=True)
archives={}
p=out/'01_누적수정_웹루트덮어쓰기.zip'
with zipfile.ZipFile(p,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as z:
 for n in selected:z.write(r/n,n)
with zipfile.ZipFile(p) as z:
 assert z.testzip() is None and z.namelist()==selected
 for n in selected:assert sha(z.read(n))==manifest[n]
archives[p.name]=sha(p.read_bytes())
a=r/'deploy/private-handoff/bluesquare-download-assets-20261110'
p=out/'02_원본자료교체_웹루트3파일.zip'
source=a/'01_원본인코딩수정_3파일_웹루트덮어쓰기.zip'
shutil.copyfile(source,p)
with zipfile.ZipFile(p) as z:
 assert z.testzip() is None and len(z.namelist())==3
 for n in z.namelist():assert n.startswith('uploads/board/') and sha(z.read(n))==sha((a/n).read_bytes())
archives[p.name]=sha(p.read_bytes())
ops=out/'03_SQL_설정_서버업로드금지';ops.mkdir()
shutil.copyfile(r/'sql/manual/rental-transactions-20261111.sql',ops/'01_대관_InnoDB전환.sql')
shutil.copyfile(r/'deploy/preflight.php',ops/'preflight.php')
flags="""# Merge these keys into CURRENT production .env; this is NOT a complete .env.
APP_ENV=production
PUBLIC_HOST=bluesquare.kr
GATE_HOST=gate.bluesquare.kr
GATE_ROUTE_MODE=php
GATE_ENFORCE=true
GATE_ALLOW_HTTP=false
GATE_ALLOW_LEGACY_ADMIN=false
SESSION_SECURE=true
MFA_ENABLED=true
GATE_ALLOW_IPS=211.58.123.26,219.250.86.69,1.255.23.106,39.123.86.210,211.181.69.138,1.237.79.1,1.237.79.2,1.237.79.3,58.227.3.0/24
"""
(ops/'02_SSL완료_설정변경값.env.fragment').write_text(flags,encoding='utf-8')
(ops/'03_적용순서.txt').write_text("""대상: 이미 보안업데이트된 블루스퀘어 운영 사이트. 구형 사이트 최초 설치용 아님.
1. 최신 DB와 교체대상 웹파일, 원본자료3개, 현재 .env를 PC에 백업합니다.
2. 대관 쓰기를 잠시 중지한 유지보수 시간에 01_대관_InnoDB전환.sql만 실행합니다.
   database_correct / rental_transactions_ready 결과가 모두 1이어야 합니다.
   기존 테이블이 InnoDB면 실질 변경이 없습니다. ALTER는 잠금/재구축이 가능하며 자동롤백되지 않습니다.
   전체 원본덤프·계정초기화 SQL·예전 changes.sql을 재실행하지 마세요.
3. 01 누적웹ZIP을 PC에서 풀고 내용물을 기존 웹루트 같은 경로로 Binary 덮어쓰기합니다.
4. 02 원본교체ZIP의 uploads/board 지정3개만 같은 경로로 덮어씁니다.
   기존 전체 폴더나 uploads를 삭제하지 않습니다.
5. 현재 .env의 DB/SMTP/계정정보는 유지하고 fragment의 설정키만 병합합니다.
   fragment 자체를 .env로 올리면 필수 DB/SMTP 값이 없으므로 안 됩니다.
   고객기관에서 로그인 가능한지 확인한 뒤 임시 테스트IP 175.192.246.121만 회수합니다.
   병합목록은 고객 허용IP9개규칙만 포함합니다. 사무실접속 확인 전 무조건 교체하지 마세요.
   보관된 .env.gate 또는 호스팅 환경변수가 같은 키를 덮어쓰는지도 대조합니다.
6. 셸이 있으면 preflight.php를 웹루트 밖에 두고 다음 읽기검사를 합니다.
   php preflight.php config-https /www_root
   셸이 없으면 fragment 키를 대조하고 실제 HTTP/TLS/로그인을 확인합니다.
7. gate로그인·MFA·임시비번변경·고객계정권한·세션·CRUD·업로드/다운로드·기록·공개화면 확인.
   QA계정은 no=8만 보고 삭제하지 말고 ID qa_ui_261110 / 비활성·무권한을 확인한 뒤 UI에서 정리합니다.
8. 이상 발생 시 교체파일과 .env를 백업본으로 복구합니다. InnoDB를 MyISAM으로 자동되돌리지 않습니다.
   SQL전환은 내용변경이 없으므로 복구가 필요하면 원인을 먼저 확인하고 별도 승인·백업기준으로 진행합니다.
03폴더·PDF·검증JSON·ZIP 자체·로그·SQL은 웹루트에 업로드하지 않습니다.
실서버 DB변경·FTP업로드는 자동수행하지 않았습니다.
""",encoding='utf-8')
report=r/'output/pdf/블루스퀘어_보안개선_개발반영보고서_20261111.pdf'
shutil.copyfile(report,out/'04_고객전달_개발반영보고서.pdf')
(out/'00_먼저읽기.txt').write_text("""블루스퀘어 최종 누적 반영본 / 2026-11-11
01 ZIP: 보안업데이트된 운영 웹루트에 내용물만 덮어쓰기. v10·본문보존·영문마스킹 포함.
02 ZIP: 지정 원본자료3개만 교체. SQL/내용/도면 변경 없음.
03 폴더: DB백업 후 대관엔진전환 SQL, 현재env 병합값, 적용순서. 서버업로드금지.
04 PDF: 고객 전달용 개발 보고서. 최신 운영 미확인 항목 명시.
이전 v8/v9/v10/font 단독 ZIP을 다시 조합할 필요 없습니다.
운영기관 IP·고객별권한·최신 UI/모바일·QA계정정리·보관/백업/고객운영조치는 별도 확인합니다.
""",encoding='utf-8')
receipt={'release':out.name,'created_utc':datetime.now(timezone.utc).isoformat(),'production_deployed':False,'production_db_mutation':False,'browser_current_run':'initialization_failed_kernel_assets_os_error_3','runtime_delta_from_v10':delta,'web_files':len(selected),'archives':archives,'source_hashes':{n:manifest[n] for n in selected},'full_web':manifest,'fresh_qa':{'server_pass':len(re.findall(r'^PASS ',server,re.M)),'mfa_http_pass':39,'client_vm_pass':144,'additional_pass':len(re.findall(r'^PASS ',additional,re.M))},'known_credentials_absent':True,'zip_source_hash_match':True,'sql':{'file':'rental-transactions-20261111.sql','sha256':sha((r/'sql/manual/rental-transactions-20261111.sql').read_bytes()),'operation':'conditional InnoDB engine conversion only','repeatable':True,'backup_required':True},'live_readonly':json.loads((r/'qa/artifacts/live-release-boundary-20261111.json').read_text(encoding='utf-8'))}
(out/'검증결과.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8')
(out/'웹교체파일목록.txt').write_text('\n'.join(selected)+'\n',encoding='utf-8')
print(json.dumps({'output':str(out),'files':len(selected),'archives':archives,'qa':receipt['fresh_qa']},ensure_ascii=False,indent=2))
