import fs from 'node:fs';
import assert from 'node:assert/strict';
const source = fs.readFileSync(new URL('../nol-gate/pages/account/password-preview.php', import.meta.url), 'utf8');
let passed = 0;
function check(name, fn) { fn(); passed++; console.log('PASS ' + name); }
check('existing auth guard', () => assert.ok(source.includes('$role->redirectIfCannotView();')));
check('non-GET requests denied', () => assert.ok(source.includes('http_response_code(405)')));
check('login layout reused instead of admin shell', () => { for (const name of ['no-sub-body', 'no-sub-wrap', 'no-login-header', 'no-login-wrap', 'no-login-content', 'no-login-top', 'no-login-footer']) assert.ok(source.includes(name)); assert.ok(!source.includes('admin.drawer.php')); assert.ok(!source.includes('admin.header.php')); });
check('existing authentication components reused', () => { for (const name of ['no-auth-field', 'no-auth-input', 'no-btn--submit', 'no-login-control', 'no-login-bot']) assert.ok(source.includes(name)); });
check('no account controller initialization', () => assert.ok(!source.includes('data-page="account"')));
check('no form or submit handler', () => assert.doesNotMatch(source, /<form\b|type="submit"|onclick=|<script\b/));
check('presentation-only button without submit', () => { assert.match(source, /<button[^>]*type="button"[^>]*class="no-btn--submit"/); assert.ok(source.includes('디자인 미리보기 · 실제 비밀번호는 변경되지 않습니다.')); });
check('no database or password mutations', () => assert.doesNotMatch(source, /AccountModel|password_hash|UPDATE\s|INSERT\s|DELETE\s|\$_POST|fetch\(/));
check('existing CSS without dedicated skin', () => { assert.match(source, /\/css\/style.css/); assert.doesNotMatch(source, /<style\b|style=|temporary-password.css/); });
console.log(`pass=${passed} fail=0`);
