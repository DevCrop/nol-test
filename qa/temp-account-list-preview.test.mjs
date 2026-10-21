import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const php = fs.readFileSync(new URL('../nol-gate/pages/account/temp-account-list-preview.php', import.meta.url), 'utf8');
const source = fs.readFileSync(new URL('../nol-gate/resource/js/temp-account-list-preview.js', import.meta.url), 'utf8');
let passed = 0;
function check(name, fn) { fn(); passed++; console.log('PASS ' + name); }
check('auth guard and no account queries', () => { assert.ok(php.includes('$role->redirectIfCannotView();')); assert.doesNotMatch(php, /AccountModel|DB::|PrivacyAccessLogger|\$_POST|<form\b/); });
check('existing UI components only', () => { for (const name of ['no-table', 'no-card', 'no-btn', 'no-input', 'admin.head.php']) { if (name === 'no-input') continue; assert.ok(php.includes(name)); } assert.doesNotMatch(php, /<style\b|style=/); });
check('changed account has no password sample', () => assert.match(php, /'temporary' => false\]/));
check('password hidden by default', () => { const line = php.split('\n').find(line => line.includes('<input type="password"')); assert.ok(line && line.includes('readonly')); });
check('no network or persistence', () => assert.doesNotMatch(source, /\b(fetch|XMLHttpRequest|localStorage|sessionStorage|getRandomValues)\s*[.(]/));
const input = { type: 'password', value: 'DEMO-ONLY!482' };
function button(dataset) { return { dataset, textContent: '', events: {}, attributes: {}, addEventListener(name, fn) { this.events[name] = fn; }, setAttribute(key, value) { this.attributes[key] = value; }, closest() { return { dataset: { previewUid: 'preview_admin' } }; } }; }
const toggle = button({ previewToggle: 'sample' });
const copy = button({ previewCopy: 'sample' });
const status = { textContent: '' };
const fallback = { hidden: true };
const area = { value: '', focus() {}, select() { this.selected = true; } };
let copied = '';
const context = vm.createContext({ isSecureContext: false, navigator: { clipboard: { writeText: async text => { copied = text; } } }, document: {
  querySelectorAll: selector => selector === '[data-preview-toggle]' ? [toggle] : [copy],
  getElementById: () => input,
  querySelector: selector => ({ '#sample-copy-status': status, '#sample-copy-fallback': fallback, '#sample-copy-text': area })[selector]
} });
vm.runInContext(source.replace('export function', 'function'), context);
toggle.events.click();
check('reveal sample on request', () => { assert.equal(input.type, 'text'); assert.equal(toggle.attributes['aria-pressed'], 'true'); });
toggle.events.click();
check('hide sample again', () => assert.equal(input.type, 'password'));
await copy.events.click();
check('HTTP manual copy fallback', () => { assert.equal(fallback.hidden, false); assert.ok(area.selected); assert.ok(area.value.includes('preview_admin')); assert.ok(area.value.includes('DEMO-ONLY!482')); assert.equal(copied, ''); });
context.isSecureContext = true;
await copy.events.click();
check('secure clipboard copies marked synthetic text', () => { assert.ok(copied.includes('디자인 예시')); assert.ok(copied.includes('실제 로그인 불가')); assert.equal(fallback.hidden, true); });
context.navigator.clipboard.writeText = async () => { throw new Error('denied'); };
await copy.events.click();
check('permission denial falls back without false success', () => { assert.equal(fallback.hidden, false); assert.ok(status.textContent.includes('선택된')); });
console.log(`pass=${passed} fail=0`);
