import fs from 'node:fs';
import assert from 'node:assert/strict';
const php = fs.readFileSync(new URL('../nol-gate/pages/account/new.php', import.meta.url), 'utf8');
let passed = 0;
function check(name, fn) { fn(); passed++; console.log('PASS ' + name); }
check('existing authorization retained', () => assert.ok(php.includes('$role->redirectIfCannotView();')));
check('one disabled password UI', () => { assert.equal((php.match(/id="upwd"/g) || []).length, 1); assert.match(php, /id="upwd"[^>]*disabled/); });
check('automatic issuance placeholder', () => assert.ok(php.includes('placeholder="계정 등록 시 임시 비밀번호가 자동 발급됩니다."')));
check('password confirmation removed', () => assert.ok(!php.includes('upwd_confirm')));
check('no manual password submitted', () => assert.doesNotMatch(php, /name="upwd"|type="password"/));
check('actual submit enabled for automatic issuance', () => { assert.doesNotMatch(php, /id="submitBtn"\s+disabled/); assert.ok(php.includes('data-temporary-credentials')); assert.ok(php.includes('account.credentials.php')); });
check('existing identity and ACL preserved', () => { for (const name of ['id="uid"', 'id="uname"', 'id="email"', 'id="phone"', 'name="role_id"', 'admin.acl.php']) assert.ok(php.includes(name)); });
check('no new password mutation', () => assert.doesNotMatch(php, /password_hash|random_bytes|UPDATE\s+nb_admin|INSERT\s+INTO/));
console.log(`pass=${passed} fail=0`);
