"""Build separate customer source and operational patch from a verified FTP backup.

Never reads account rows or writes to the backup/live site. Refuses unverified QA.
"""
from pathlib import Path
import hashlib
import json
import shutil
import zipfile

ROOT = Path(__file__).resolve().parents[1]
AUDIT = ROOT / 'deploy/private-handoff/production-audit-261113'
OUT = ROOT / 'deploy/private-handoff/bluesquare-secure-handoff-20261113-v2'
PATCH = [
    'inc/lib/db.php',
    'inc/lib/classes/Security/AuditedConnection.php',
    'inc/lib/classes/Security/AuditedStatement.php',
    'inc/lib/classes/Security/AuditLogger.php',
    'inc/lib/classes/Security/AccountValidator.php',
    'admin/pages/account/password.php',
]
sha = lambda b: hashlib.sha256(b).hexdigest()


def archive(path, entries):
    with zipfile.ZipFile(path, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
        for name, data in sorted(entries.items()):
            z.writestr(name, data)
    with zipfile.ZipFile(path) as z:
        assert z.testzip() is None
        assert set(z.namelist()) == set(entries)
        assert all(sha(z.read(n)) == sha(b) for n, b in entries.items())


def main():
    evidence = json.loads((AUDIT / 'fix-verification.json').read_text('utf-8'))
    assert evidence['accepted'] is True and evidence['failed'] == 0
    inventory = json.loads((AUDIT / 'production-inventory.json').read_text('utf-8-sig'))
    original = Path(inventory['source'])
    files = inventory['files']
    deletions = json.loads((AUDIT / 'deletion-candidates.json').read_text('utf-8-sig'))
    removed = {x['path'] for x in deletions}
    assert len(removed) == 222
    assert all(sha((original / n).read_bytes()) == item['sha256'] for n, item in files.items())
    assert not OUT.exists(), 'Release directory is immutable; select a new version.'
    # These are hosting/account artifacts, not application source. They remain on server.
    separated = {'.env', '.env.gate', '.bashrc', '.bash_profile', '.bash_logout',
                 '.well-known/pki-validation/gsdv.txt', 'gabia.html'}
    runtime = {n: (original / n).read_bytes() for n in files if n not in removed | separated}
    for n in PATCH:
        runtime[n] = (ROOT / n).read_bytes()
    # Check known credentials without printing either keys or values.
    secrets = []
    for env in ['.env', '.env.gate']:
        for line in (original / env).read_text('utf-8-sig').splitlines():
            if '=' in line and line.split('=', 1)[0].strip() in {'SMTP_PASS', 'DB_PASS'}:
                value = line.split('=', 1)[1].strip().strip('\"\'')
                if len(value) >= 8:
                    secrets.append(value.encode())
    assert all(not any(s in b for s in secrets) for b in runtime.values()), 'Private credential detected'
    assert not any(n in runtime for n in removed | separated)
    schema = (AUDIT / 'schema-only.sql').read_bytes()
    assert b'CREATE TABLE' in schema and b'INSERT INTO' not in schema and b'REPLACE INTO' not in schema
    OUT.mkdir(parents=True)
    archive(OUT / '01_운영반영_웹6파일만.zip', {n: runtime[n] for n in PATCH})
    ops = OUT / '02_운영절차_SQL_삭제목록_서버업로드금지'
    ops.mkdir()
    shutil.copyfile(ROOT / 'sql/manual/audit-transactions-20261113.sql', ops / '01_감사트랜잭션_InnoDB전환.sql')
    shutil.copyfile(AUDIT / '삭제후보_222파일_서버상대경로.txt', ops / '02_삭제대상_정확한222파일.txt')
    shutil.copyfile(AUDIT / 'deletion-candidates.json', ops / '03_삭제대상_원본SHA256.json')
    shutil.copyfile(AUDIT / '운영적용순서_20261113.txt', ops / '00_운영적용순서.txt')
    shutil.copyfile(AUDIT / '고객전달_구성안내_20261113.txt', OUT / '00_먼저읽기.txt')
    customer = {'webroot/' + n: b for n, b in runtime.items()}
    customer['webroot/.env.example'] = (ROOT / '.env.production.example').read_bytes()
    customer['database/EMPTY-INSTALL-schema-only.sql'] = schema
    customer['database/EXISTING-SITE-audit-transactions.sql'] = (ROOT / 'sql/manual/audit-transactions-20261113.sql').read_bytes()
    customer['docs/구성안내.txt'] = (AUDIT / '고객전달_구성안내_20261113.txt').read_bytes()
    customer['docs/개발반영_체크리스트.md'] = (ROOT / '보안보완_최종정리_20261113.md').read_bytes()
    archive(OUT / '03_고객전달_소스_비밀값DB데이터제외.zip', customer)
    # Only the exact old patched files; newly introduced files have no old version.
    archive(OUT / '04_웹복구용_서버업로드금지.zip', {n: (original / n).read_bytes() for n in PATCH if n in files})
    receipt = {'production_modified': False, 'production_backup_hashes_unchanged': True,
               'runtime_files': len(runtime), 'patch_files': PATCH, 'cleanup_files': len(removed),
               'source_excludes': sorted(separated), 'qa': evidence,
               'webroot_sha256': {n: sha(b) for n, b in runtime.items()},
               'archives': {p.name: sha(p.read_bytes()) for p in OUT.glob('*.zip')}}
    (OUT / '검증결과.json').write_text(json.dumps(receipt, ensure_ascii=False, indent=2), 'utf-8')
    print(json.dumps({'output': str(OUT), 'runtime_files': len(runtime), 'archives': receipt['archives']}, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    main()
