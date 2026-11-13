"""Immutable v3 cleanup delta and updated customer source; never edits FTP backup."""
from pathlib import Path
import hashlib
import json
import zipfile

ROOT = Path(__file__).resolve().parents[1]
BASE = ROOT/'deploy/private-handoff/bluesquare-secure-handoff-20261113-v2'
OUT = ROOT/'deploy/private-handoff/bluesquare-cleanup-20261113-v3'
CHANGED = 'inc/lib/menu/menu.function.php'
DELETED = 'inc/lib/base.init.php'
sha = lambda b: hashlib.sha256(b).hexdigest()


def write_zip(path, files):
    with zipfile.ZipFile(path, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
        for name, data in sorted(files.items()):
            z.writestr(name, data)
    with zipfile.ZipFile(path) as z:
        assert z.testzip() is None
        assert all(sha(z.read(name)) == sha(data) for name, data in files.items())


qa = json.loads((ROOT/'qa/artifacts/menu-cleanup-20261113.json').read_text('utf-8'))
assert qa['accepted'] and qa['fail'] == 0 and qa['pass'] == 40
assert not (ROOT/DELETED).exists()
assert not OUT.exists(), 'Release directory already exists; do not replace an issued release.'
receipt = json.loads((BASE/'검증결과.json').read_text('utf-8'))
source_name = '03_고객전달_소스_비밀값DB데이터제외.zip'
assert sha((BASE/source_name).read_bytes()) == receipt['archives'][source_name]
with zipfile.ZipFile(BASE/source_name) as z:
    files = {name: z.read(name) for name in z.namelist()}
old_menu = files['webroot/'+CHANGED]
old_init = files.pop('webroot/'+DELETED)
files['webroot/'+CHANGED] = (ROOT/CHANGED).read_bytes()
files['docs/개발반영_체크리스트.md'] = (ROOT/'보안보완_최종정리_20261113.md').read_bytes()
instructions = '''블루스퀘어 v3 후속 정리 / v2 보안 보완을 적용한 서버용
1. 현재 inc/lib/menu/menu.function.php와 inc/lib/base.init.php를 PC에 백업합니다.
2. 01 ZIP을 PC에서 풀고 inc/lib/menu/menu.function.php 1파일만 같은 서버 경로에 Binary 덮어쓰기합니다.
3. 서버에 inc/lib/base.init.php가 남아 있으면 그 파일만 삭제합니다.
   이 파일은 정상 업무에서 호출되지 않는 옛 초기화 코드이며 /upload 생성·오류출력 코드가 있습니다.
   확인 목적으로 브라우저에서 해당 PHP 파일을 실행하지 마세요.
4. 홈페이지 메뉴·공연장 소개·공지/자료실·대관 안내·첨부 다운로드를 확인합니다.
SQL, .env, .env.gate, 계정, SMTP, SSL 변경은 없습니다. uploads는 유지합니다.
복구가 필요하면 04 ZIP에서 menu.function.php만 원위치로 복구합니다.
base.init.php는 정상 기능에 필요하지 않아 일괄 복구하지 않습니다.

03 고객 소스 ZIP은 v2 전체 소스에 이번 정리를 병합한 최신 전달본입니다.
실제 비밀값/DB 행/업로드 저장소는 포함되지 않습니다. v2의 빈 DB용 스키마와 DB 이관 안내는 그대로 유지됩니다.
이번 v3 때문에 SQL을 재실행하거나 전체 고객 소스를 FTP에 다시 올릴 필요는 없습니다.
운영 FTP/DB와 prod/261113 원본은 이번 패키지 생성에서 수정하지 않았습니다.
'''
files['docs/후속정리_v3_적용안내.txt'] = instructions.encode('utf-8')
OUT.mkdir(parents=True)
(OUT/'00_적용순서.txt').write_text(instructions, 'utf-8')
(OUT/'02_서버에서삭제할파일.txt').write_text(DELETED+'\n', 'utf-8')
write_zip(OUT/'01_웹1파일_덮어쓰기.zip', {CHANGED: (ROOT/CHANGED).read_bytes()})
write_zip(OUT/source_name, files)
write_zip(OUT/'04_복구용_서버업로드금지.zip', {CHANGED: old_menu, DELETED: old_init})
hashes = {name[len('webroot/'):]: sha(data) for name, data in files.items() if name.startswith('webroot/') and name != 'webroot/.env.example'}
assert len(hashes) == len(receipt['webroot_sha256'])-1
assert not any(name in files for name in ['webroot/.env','webroot/.env.gate','webroot/'+DELETED])
result = {'parent_release': BASE.name, 'production_modified': False, 'web_patch': [CHANGED],
          'delete_on_server': [DELETED], 'SQL_changes': False, 'environment_changes': False,
          'customer_runtime_files': len(hashes), 'qa': qa, 'webroot_sha256': hashes,
          'archives': {p.name: sha(p.read_bytes()) for p in OUT.glob('*.zip')}}
(OUT/'검증결과.json').write_text(json.dumps(result, ensure_ascii=False, indent=2), 'utf-8')
print(json.dumps({'output': str(OUT), 'web_patch': [CHANGED], 'delete': [DELETED], 'qa': qa['pass'], 'archives': result['archives']}, ensure_ascii=False, indent=2))
