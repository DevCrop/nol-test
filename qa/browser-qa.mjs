import fs from 'node:fs/promises';

const mode = process.argv[2] || 'prepare';
const captcha = process.argv[3] || '';
const qaUid = process.argv[4] || '';
const qaPassword = process.argv[5] || '';
const targets = await (await fetch('http://127.0.0.1:9222/json/list')).json();
const target = targets.find((item) => item.type === 'page');
if (!target) throw new Error('Chrome page target not found');
const ws = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });
let seq = 0;
const pending = new Map();
ws.onmessage = (event) => {
  const message = JSON.parse(event.data);
  if (!message.id || !pending.has(message.id)) return;
  const {resolve, reject} = pending.get(message.id); pending.delete(message.id);
  message.error ? reject(new Error(message.error.message)) : resolve(message.result);
};
const call = (method, params = {}) => new Promise((resolve, reject) => {
  const id = ++seq; pending.set(id, {resolve, reject}); ws.send(JSON.stringify({id, method, params}));
});
const pause = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const evaluate = async (expression) => (await call('Runtime.evaluate', {expression, returnByValue: true, awaitPromise: true})).result.value;
const navigate = async (url) => { await call('Page.navigate', {url}); await pause(1800); };
const screenshot = async (name) => {
  const metrics = await call('Page.getLayoutMetrics');
  const size = metrics.cssContentSize || metrics.contentSize;
  const shot = await call('Page.captureScreenshot', {format: 'png', captureBeyondViewport: true, clip: {x: 0, y: 0, width: Math.max(1, size.width), height: Math.max(1, size.height), scale: 1}});
  await fs.writeFile(new URL(`./artifacts/${name}`, import.meta.url), Buffer.from(shot.data, 'base64'));
};

await call('Page.enable'); await call('Runtime.enable'); await call('Network.enable');
if (mode === 'prepare') {
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('http://gate.local:8628/admin/');
  await screenshot('login-desktop.png');
  const cookies = (await call('Network.getAllCookies')).cookies.filter((item) => item.domain === 'gate.local').map((item) => ({name:item.name,value:item.value,httpOnly:item.httpOnly}));
  const state = await evaluate(`({url:location.href,title:document.title,inputs:[...document.querySelectorAll('input')].map(e=>({id:e.id,height:getComputedStyle(e).height})),captcha:document.querySelector('img[alt="captcha"]')?.complete})`);
  state.cookies = cookies;
  console.log(JSON.stringify(state));
} else if (mode === 'login') {
  if (!/^\d{5}$/.test(captcha) || !qaUid || !qaPassword) throw new Error('captcha, QA uid and QA password are required');
  await evaluate(`document.querySelector('#uid').value=${JSON.stringify(qaUid)};document.querySelector('#upwd').value=${JSON.stringify(qaPassword)};document.querySelector('#r_captcha').value=${JSON.stringify(captcha)};document.querySelector('#login_form').submit();`);
  await pause(2200);
  await screenshot('admin-desktop.png');
  const desktop = await evaluate(`({url:location.href,title:document.title,timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),error:document.querySelector('[role="alert"]')?.textContent?.trim(),body:document.body.innerText.slice(0,500)})`);
  await call('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true});
  await pause(500); await screenshot('admin-mobile.png');
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('http://gate.local:8628/admin/pages/account/password.php'); await screenshot('account-security.png');
  const account = await evaluate(`({url:location.href,timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),email:!!document.querySelector('input[type="email"]'),csrf:!!document.querySelector('form[method="post"] input[name="_csrf"]')})`);
  await navigate('http://gate.local:8628/admin/pages/request/request.list.php'); await screenshot('privacy-list.png');
  const privacy = await evaluate(`({url:location.href,locked:!!document.querySelector('[data-pii-lock]'),timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),text:document.body.innerText.slice(0,800)})`);
  console.log(JSON.stringify({desktop, account, privacy}));
} else if (mode === 'inspect') {
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('http://gate.local:8628/admin/pages/account/password.php'); await screenshot('account-security.png');
  const desktop = await evaluate(`({url:location.href,title:document.title,timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),email:!!document.querySelector('input[type="email"]'),csrf:[...document.querySelectorAll('form[method="post"]')].every(f=>!!f.querySelector('input[name="_csrf"]'))})`);
  await call('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true});
  await pause(500); await screenshot('account-security-mobile.png');
  console.log(JSON.stringify(desktop));
} else if (mode === 'email') {
  await navigate('http://gate.local:8628/admin/pages/account/password.php');
  await evaluate(`const f=[...document.forms].find(x=>x.action.includes('email.process.php'));f.querySelector('input[name="email"]').value='qa-updated@example.com';f.requestSubmit();`);
  await pause(1800);
  const result = await evaluate(`({url:location.href,message:document.querySelector('[role="alert"]')?.textContent?.trim(),value:document.querySelector('input[name="email"]')?.value})`);
  console.log(JSON.stringify(result));
} else if (mode === 'privacy-detail') {
  await navigate('http://gate.local:8628/admin/pages/request/request.list.php');
  const href = await evaluate(`[...document.querySelectorAll('a')].find(a=>a.textContent.trim()==='보기')?.href || ''`);
  if (!href) throw new Error('privacy detail link not found');
  await navigate(href); await screenshot('privacy-detail.png');
  const result = await evaluate(`({url:location.href,locked:!!document.querySelector('[data-pii-lock]'),reason:!!document.querySelector('input[name="reason"],textarea[name="reason"]'),directPrivate:[...document.querySelectorAll('a[href*="uploads/board/file_"]')].length,timer:document.querySelector('[data-session-countdown]')?.textContent?.trim()})`);
  console.log(JSON.stringify(result));
} else if (mode === 'accounts') {
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('http://gate.local:8628/admin/pages/account/index.php'); await screenshot('account-list.png');
  const list = await evaluate(`({url:location.href,timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),hasTmaster:document.body.innerText.includes('tmaster'),hasCreate:[...document.querySelectorAll('a')].some(a=>a.textContent.includes('계정 등록')),csrf:[...document.querySelectorAll('form[method="post"]')].every(f=>!!f.querySelector('input[name="_csrf"]')),body:document.body.innerText.slice(0,900)})`);
  await navigate('http://gate.local:8628/admin/pages/account/new.php'); await screenshot('account-new.png');
  const create = await evaluate(`({url:location.href,fields:['uid','uname','email','password','password_confirm','role_code','active_status'].every(id=>!!document.getElementById(id)),csrf:[...document.querySelectorAll('form[method="post"]')].every(f=>!!f.querySelector('input[name="_csrf"]')),timer:document.querySelector('[data-session-countdown]')?.textContent?.trim()})`);
  await call('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true}); await pause(500); await screenshot('account-new-mobile.png');
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('http://gate.local:8628/admin/pages/account/audit.php'); await screenshot('account-audit.png');
  const audit = await evaluate(`({url:location.href,table:!!document.querySelector('table'),timer:document.querySelector('[data-session-countdown]')?.textContent?.trim(),text:document.body.innerText.slice(0,500)})`);
  console.log(JSON.stringify({list,create,audit}));
} else if (mode === 'account-create') {
  if (!qaUid || !qaPassword) throw new Error('QA uid and password are required');
  await navigate('http://gate.local:8628/admin/pages/account/new.php');
  await evaluate(`document.querySelector('#uid').value=${JSON.stringify(qaUid)};document.querySelector('#uname').value='브라우저QA';document.querySelector('#email').value=${JSON.stringify(qaUid + '@example.com')};document.querySelector('#password').value=${JSON.stringify(qaPassword)};document.querySelector('#password_confirm').value=${JSON.stringify(qaPassword)};document.querySelector('form[action$="/account/process.php"]').requestSubmit();`);
  await pause(2200);
  const created = await evaluate(`({url:location.href,flash:document.querySelector('[role="alert"]')?.textContent?.trim(),row:[...document.querySelectorAll('tbody tr')].some(tr=>tr.textContent.includes(${JSON.stringify(qaUid)})),csrf:[...document.querySelectorAll('form[method="post"]')].every(f=>!!f.querySelector('input[name="_csrf"]'))})`);
  await screenshot('account-created.png');
  console.log(JSON.stringify(created));
} else if (mode === 'cookies') {
  const cookies = (await call('Network.getAllCookies')).cookies.filter((item) => item.domain === 'gate.local').map((item) => ({name:item.name,value:item.value,httpOnly:item.httpOnly}));
  console.log(JSON.stringify(cookies));
} else if (mode === 'mfa') {
  await call('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true});
  await navigate('http://gate.local:8628/admin/mfa.php'); await screenshot('mfa-mobile.png');
  const result = await evaluate(`({url:location.href,countdown:document.querySelector('#mfa-countdown')?.textContent?.trim(),inputHeight:getComputedStyle(document.querySelector('#code')).height,text:document.body.innerText})`);
  console.log(JSON.stringify(result));
} else if (mode === 'close') {
  await call('Browser.close');
  console.log('browser closed');
}
ws.close();
