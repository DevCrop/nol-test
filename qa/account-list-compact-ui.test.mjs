import fs from 'node:fs';
import assert from 'node:assert/strict';
const source = fs.readFileSync(new URL('../nol-gate/pages/account/index.php', import.meta.url), 'utf8');
let passes = 0;
function check(name, fn) { fn(); passes++; console.log('PASS ' + name); }
check('no synthetic rows in actual list',()=>assert.doesNotMatch(source,/data-temporary-ui-sample|showTemporaryUiSample/));
check('actual temporary status from DB',()=>{assert.ok(source.includes('a.must_change_password'));assert.ok(source.includes("$statusLabel = '임시'"));});
check('compact reissue instead of secret retrieval',()=>{assert.ok(source.includes('data-reissue-temporary'));assert.ok(source.includes('>재발급</button>'));assert.ok(source.includes('account.credentials.php'));});
check('no plaintext selected',()=>assert.doesNotMatch(source,/SELECT[^;]*\bupwd\b/s));
check('existing account actions retained', () => { assert.ok(source.includes('AccountModel::countByRole(1)')); assert.ok(source.includes('PrivacyAccessLogger::record')); assert.ok(source.includes('data-locked=')); });
console.log(`pass=${passes} fail=0`);
