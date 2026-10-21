import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source = fs.readFileSync(new URL('../nol-gate/resource/js/temporary-password-dialog.js', import.meta.url), 'utf8');
const nodes = new Map();
const node = () => ({ value: '', type: 'password', hidden: true, textContent: '', attributes: {}, events: {}, setAttribute(k, v) { this.attributes[k] = v; }, addEventListener(k, v) { this.events[k] = v; }, focus() {}, select() { this.selected = true; } });
for (const id of ['temporary-ui-dialog', 'temporary-ui-password', 'temporary-ui-toggle', 'temporary-ui-manual', 'temporary-ui-status', 'temporary-ui-copy-text', 'temporary-ui-copy', 'temporary-ui-uid', 'opener', 'closer']) nodes.set('#' + id, node());
const dialog = nodes.get('#temporary-ui-dialog');
dialog.showModal = () => { dialog.open = true; };
dialog.close = () => { dialog.open = false; dialog.events.close(); };
nodes.get('#temporary-ui-password').value = 'DEMO-ONLY!482';
nodes.get('#temporary-ui-uid').value = 'demo_admin';
let clipboard = '';
let buffer;
let legacyText = '';
let legacyAllowed = true;
const alerts = [];
dialog.appendChild = value => { buffer = value; };
const context = vm.createContext({ window: { alert: text => alerts.push(text), location: { search: '?temporary-ui=open' } }, URLSearchParams, isSecureContext: false, navigator: { clipboard: { writeText: async text => { clipboard = text; } } }, document: {
  createElement: () => Object.assign(node(), { remove() { this.removed = true; } }),
  execCommand: () => { legacyText = buffer.value; return legacyAllowed; },
  querySelector: key => nodes.get(key),
  querySelectorAll: key => [nodes.get(key.includes('open') ? '#opener' : '#closer')]
} });
vm.runInContext(source, context);
let passed = 0;
function check(name, fn) { fn(); passed++; console.log('PASS ' + name); }
check('direct URL opens dialog', () => assert.equal(dialog.open, true));
nodes.get('#temporary-ui-toggle').events.click();
check('sample can be revealed', () => assert.equal(nodes.get('#temporary-ui-password').type, 'text'));
nodes.get('#closer').events.click();
check('close masks password and resets UI', () => { assert.equal(dialog.open, false); assert.equal(nodes.get('#temporary-ui-password').type, 'password'); });
nodes.get('#opener').events.click();
check('row button opens compact dialog', () => assert.equal(dialog.open, true));
await nodes.get('#temporary-ui-copy').events.click();
check('HTTP copy succeeds with exact format and alert', () => { assert.equal(legacyText, '아이디 : demo_admin\n임시비밀번호 : DEMO-ONLY!482'); assert.equal(alerts.at(-1), '복사되었습니다.'); });
check('temporary copy buffer cleared and removed', () => { assert.equal(buffer.value, ''); assert.equal(buffer.removed, true); });
context.isSecureContext = true;
await nodes.get('#temporary-ui-copy').events.click();
check('clipboard has exact two-line sample format', () => assert.equal(clipboard, '아이디 : demo_admin\n임시비밀번호 : DEMO-ONLY!482'));
context.navigator.clipboard.writeText = async () => { throw new Error('denied'); };
await nodes.get('#temporary-ui-copy').events.click();
check('clipboard denial uses legacy fallback', () => assert.equal(alerts.at(-1), '복사되었습니다.'));
legacyAllowed = false;
await nodes.get('#temporary-ui-copy').events.click();
check('copy failure never announces success', () => assert.ok(alerts.at(-1).startsWith('복사하지 못했습니다.')));
const markup = fs.readFileSync(new URL('../nol-gate/inc/account.temporary-preview.php', import.meta.url), 'utf8');
check('no expanded manual copy area', () => assert.doesNotMatch(markup, /temporary-ui-manual|<textarea/));
check('no account requests or persistence', () => assert.doesNotMatch(source, /\b(fetch|XMLHttpRequest|localStorage|sessionStorage)\s*[.(]/));
console.log(`pass=${passed} fail=0`);
