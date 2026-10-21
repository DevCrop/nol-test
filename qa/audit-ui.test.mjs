import fs from 'node:fs';
import assert from 'node:assert/strict';
import vm from 'node:vm';

// Rendering tests use a small DOM stub, not a visual/browser verification.
const elements = new Map();
const element = () => ({ innerHTML: '', textContent: '', value: '', attributes: {}, events: {},
  setAttribute(key, value) { this.attributes[key] = value; },
  addEventListener(key, handler) { this.events[key] = handler; }
});
for (const id of ['#frm', '#entity', '#action', '#keyword', '#audit-rows', '#audit-pager', '#audit-total', '#audit-reset']) elements.set(id, element());
const source = fs.readFileSync(new URL('../nol-gate/resource/js/Controller/AuditController.js', import.meta.url), 'utf8')
  .replace(/^import .*;\r?\n/gm, '').replace('export class AuditController', 'class AuditController');
let requests = [];
const context = vm.createContext({ document: { querySelector: id => elements.get(id) }, FormData,
  API: { AUDIT: '/audit' }, fetcher: (_url, data) => new Promise((resolve, reject) => requests.push({ data, resolve, reject })) });
vm.runInContext(source + '\nglobalThis.controller = new AuditController();', context);
const controller = context.controller;
const body = elements.get('#audit-rows');
const pager = elements.get('#audit-pager');
let passes = 0;
function check(name, test) { test(); passes++; console.log('PASS ' + name); }
const rows = ['create', 'update', 'delete'].map(action => ({ action, created_at: '2026-10-20 13:40:00', actor_uid: '<script>bad</script>', actor_ip: '192.0.2.10', entity_label: '게시글', target_label: '<img src=x onerror=bad>', target_no: 42 }));
controller.renderRows(rows);
check('three action badges', () => { for (const action of ['create', 'update', 'delete']) assert.ok(body.innerHTML.includes('audit-badge-' + action)); });
check('escaped stored content', () => { assert.ok(!body.innerHTML.includes('<script>')); assert.ok(!body.innerHTML.includes('<img')); });
check('readable target and reference', () => assert.ok(body.innerHTML.includes('대상 번호 #42')));
controller.renderRows([{ action: '__proto__', summary: 'legacy', created_at: '' }]);
check('unknown action safe fallback', () => assert.ok(body.innerHTML.includes('audit-badge-other')));
controller.renderRows([]);
check('empty state five columns', () => { assert.ok(body.innerHTML.includes('colspan="5"')); assert.ok(body.innerHTML.includes('조건에 맞는')); });
controller.renderPager(1, 3, 30, 10);
check('range and current page', () => { assert.ok(pager.innerHTML.includes('1–10건')); assert.ok(pager.innerHTML.includes('aria-current="page"')); });
check('first previous disabled', () => assert.equal((pager.innerHTML.match(/ disabled /g) || []).length, 2));
controller.renderPager(3, 3, 25, 10);
check('last partial page', () => { assert.ok(pager.innerHTML.includes('21–25건')); assert.equal((pager.innerHTML.match(/ disabled /g) || []).length, 2); });
controller.renderPager(1, 1, 0);
check('no pagination when empty', () => assert.equal(pager.innerHTML, ''));
controller.filters = { action: 'update', entity: 'board', keyword: '검색' };
const first = controller.load();
const second = controller.load();
check('filters sent to server', () => assert.equal(requests[1].data.get('keyword'), '검색'));
requests[1].resolve({ rows, total: 3, pages: 1, page: 1, perpage: 10 });
await second;
requests[0].resolve({ rows: [], total: 0 });
await first;
check('stale response ignored', () => assert.ok(elements.get('#audit-total').textContent.includes('3건')));
const failed = controller.load();
requests[2].reject(new Error('<b>Network error</b>'));
await failed;
check('error escaped and busy cleared', () => { assert.ok(!body.innerHTML.includes('<b>')); assert.equal(body.attributes['aria-busy'], 'false'); assert.equal(pager.innerHTML, ''); });
for (const id of ['#privacy-access-rows', '#privacy-access-pager', '#privacy-access-total']) elements.set(id, element());
context.API.PRIVACY_ACCESS = '/privacy';
const privacySource = fs.readFileSync(new URL('../nol-gate/resource/js/Controller/PrivacyAccessController.js', import.meta.url), 'utf8')
  .replace(/^import .*;\r?\n/gm, '').replace('export class PrivacyAccessController', 'class PrivacyAccessController');
vm.runInContext(privacySource + '\nglobalThis.privacy = new PrivacyAccessController();', context);
const privacy = context.privacy;
const privacyBody = elements.get('#privacy-access-rows');
privacy.renderRows([{ action: 'view', entity: 'admin_account', subject_label: '홍*동', task: '<script>bad</script>' },
  { action: 'download', entity: 'inquiry', subject_label: '김*수', reason: '<img src=x>', task: '첨부 확인' }]);
check('privacy badges distinct', () => { assert.ok(privacyBody.innerHTML.includes('audit-badge-view')); assert.ok(privacyBody.innerHTML.includes('audit-badge-download')); });
check('privacy fields escaped', () => { assert.ok(!privacyBody.innerHTML.includes('<script>')); assert.ok(!privacyBody.innerHTML.includes('<img')); });
check('masked subject and entity shown', () => { assert.ok(privacyBody.innerHTML.includes('홍*동')); assert.ok(privacyBody.innerHTML.includes('관리자 계정')); });
check('view reason not fabricated', () => assert.ok(privacyBody.innerHTML.includes('다운로드 사유 해당 없음')));
privacy.renderRows([{ action: 'download', reason: '', entity: '__proto__' }]);
check('missing download reason explicit', () => { assert.ok(privacyBody.innerHTML.includes('다운로드 사유 미기록')); assert.ok(privacyBody.innerHTML.includes('audit-missing')); });
privacy.renderRows([]);
check('privacy empty state', () => assert.ok(privacyBody.innerHTML.includes('조건에 맞는 개인정보')));
check('privacy endpoint selected', () => assert.equal(privacy.endpoint, '/privacy'));
privacy.renderPager(2, 3, 25, 10);
check('privacy uses shared pagination', () => assert.ok(elements.get('#privacy-access-pager').innerHTML.includes('11–20건')));
check('existing table badge and pager classes', () => {
  controller.renderRows(rows); controller.renderPager(1, 3, 30, 10);
  assert.ok(body.innerHTML.includes('class="no-badge'));
  for (const name of ['no-pagination', 'no-page-list', 'no-page-item', 'no-page-link active']) assert.ok(pager.innerHTML.includes(name));
});
const widgetCalls = [];
context.jQuery = select => ({ selectmenu: config => widgetCalls.push({ select, config }) });
context.jQuery.fn = { selectmenu: true };
elements.get('#frm').reset = () => { for (const id of ['#entity', '#action', '#keyword']) elements.get(id).value = ''; };
controller.init();
check('existing select widget initialized', () => assert.equal(widgetCalls.filter(call => call.config.width === '100%').length, 2));
elements.get('#entity').value = 'board';
widgetCalls[0].config.change();
check('select widget change triggers filter', () => assert.equal(requests.at(-1).data.get('entity'), 'board'));
elements.get('#audit-reset').events.click();
check('reset refreshes select widget and filters', () => {
  assert.equal(widgetCalls.filter(call => call.config === 'refresh').length, 2);
  assert.equal(requests.at(-1).data.get('entity'), '');
});
const filterMarkup = fs.readFileSync(new URL('../nol-gate/inc/log.filters.php', import.meta.url), 'utf8');
const logCss = fs.readFileSync(new URL('../nol-gate/resource/css/audit-ui.css', import.meta.url), 'utf8');
check('existing search component markup reused', () => { for (const name of ['no-admin-column', 'no-admin-block', 'no-search-wrap', 'no-search-input', 'no-btn--main no-btn--search']) assert.ok(filterMarkup.includes(name)); });
check('no replacement component skins', () => assert.doesNotMatch(logCss, /audit-(?:filter-grid|field|button|table\s+(?:th|td)|page\[)/));
console.log(`pass=${passes} fail=0`);
