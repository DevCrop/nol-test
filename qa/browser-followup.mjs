import fs from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
const fixture=(mode,...args)=>JSON.parse(execFileSync('docker',['exec','260918-web-1','php','qa/browser-fixture.php',mode,...args],{encoding:'utf8'}).trim()||'null');
const auth=fixture('create'); const mfa=fixture('mfa');
const targets=await(await fetch('http://127.0.0.1:9223/json/list')).json();
const ws=new WebSocket(targets.find(t=>t.type==='page').webSocketDebuggerUrl);
await new Promise((r,j)=>{ws.onopen=r;ws.onerror=j;});
let seq=0;const pending=new Map(),checks=[],errors=[];
ws.onmessage=e=>{const m=JSON.parse(e.data);if(m.method==='Page.javascriptDialogOpening')call('Page.handleJavaScriptDialog',{accept:true}).catch(()=>{});if(m.method==='Runtime.exceptionThrown')errors.push(m.params.exceptionDetails.text);if(!m.id)return;const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(Error(m.error.message)):p.resolve(m.result);};
const call=(method,params={})=>new Promise((resolve,reject)=>{const id=++seq;const timer=setTimeout(()=>{pending.delete(id);reject(Error('CDP timeout: '+method));},15000);pending.set(id,{resolve:x=>{clearTimeout(timer);resolve(x);},reject:e=>{clearTimeout(timer);reject(e);}});ws.send(JSON.stringify({id,method,params}));});
const pause=ms=>new Promise(r=>setTimeout(r,ms));
const evalJS=async expression=>(await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true})).result.value;
const go=async p=>{await call('Page.navigate',{url:'http://gate.local:8618'+p});await pause(1300);};
const expect=(ok,name)=>{checks.push({name,ok:!!ok});console.log((ok?'PASS ':'FAIL ')+name);if(!ok)throw Error(name);};
const shot=async name=>fs.writeFile(new URL('./artifacts/'+name+'.png',import.meta.url),Buffer.from((await call('Page.captureScreenshot',{format:'png'})).data,'base64'));
try{
    await call('Page.enable');await call('Runtime.enable');await call('Network.enable');
    await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
    await go('/index.php');expect(await evalJS("!!document.querySelector('[name=uid]')"),'login form available');
    await call('Network.setCookie',{name:auth.name,value:auth.sid,url:'http://gate.local:8618/',httpOnly:true,sameSite:'Lax'});
    await go('/pages/account/index.php');
    expect(await evalJS("document.querySelector('tbody')?.textContent.includes('qa_***') && !document.querySelector('tbody').textContent.includes("+JSON.stringify(auth.uid)+")"),'account list masks the synthetic account ID');
    await go('/pages/account/new.php');expect(await evalJS("!!document.querySelector('#email')"),'account create accessible');
    expect(await evalJS("!document.querySelector('label').dispatchEvent(new Event('copy',{bubbles:true,cancelable:true}))"),'account label copy prevented');
    expect(await evalJS("document.querySelector('#uid').dispatchEvent(new Event('copy',{bubbles:true,cancelable:true}))"),'account input copy allowed');
    expect(await evalJS("(()=>{const x=document.querySelector('#uid');x.readOnly=true;const blocked=!x.dispatchEvent(new Event('copy',{bubbles:true,cancelable:true}));x.readOnly=false;return blocked;})()"),'readonly account input copy prevented');
    const first=await evalJS("document.querySelector('[data-session-countdown]').textContent");await pause(2100);
    expect(first!==await evalJS("document.querySelector('[data-session-countdown]').textContent"),'idle countdown decreases');
    const ping=await evalJS("fetch('/ajax/session.activity.php',{method:'POST'}).then(async r=>({status:r.status,...await r.json()}))");
    expect(ping.status===200&&ping.remaining>=1798,'activity renews server expiry');await shot('nol-account-desktop-20260928');
    const xhr=await evalJS("new Promise(resolve=>{const x=new XMLHttpRequest();x.open('POST','/ajax/session.activity.php');x.onload=()=>resolve(x.status);x.send();})");
    expect(xhr===200,'legacy raw XHR receives CSRF token');
    const jq=await evalJS("new Promise(resolve=>jQuery.ajax({url:'/ajax/session.activity.php',method:'POST',complete:x=>resolve(x.status)}))");
    expect(jq===200,'legacy jQuery receives CSRF token');
    expect(await evalJS("Array.from(document.forms).filter(f=>f.method.toLowerCase()==='post').every(f=>!!f.querySelector('[name=_csrf]'))"),'native POST forms contain CSRF');
    for(const page of ['board/board.list.php','design/banner.list.php','design/popup.list.php','faq/index.php','inquiry/index.php','inquiry/setting.php','log/log.day.php','log/log.time.php','log/log.month.php','log/log.year.php','works/index.php','setting/index.php','setting/seo.php','privacy/index.php','account/audit.php','account/access.php']) {
        await go('/pages/'+page);
        expect(await evalJS("!!document.querySelector('[data-session-countdown]') && !/Fatal error|SQLSTATE|Internal Server Error/.test(document.body.innerText)"),'active menu renders '+page);
    }
    await go('/pages/account/new.php');
    await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});await pause(400);
    expect(await evalJS('document.documentElement.scrollWidth<=innerWidth'),'mobile account no overflow');await shot('nol-account-mobile-20260928');
    await go('/pages/setting/pwd.php');expect(await evalJS("!!document.querySelector('#pwd_new')"),'password page accessible');
    expect(await evalJS('document.documentElement.scrollWidth<=innerWidth'),'mobile password no overflow');
    fixture('expire',auth.uid,auth.sid);
    await evalJS("import('/resource/js/utils/SessionIdleTimer.js').then(({SessionIdleTimer})=>{setTimeout(()=>new SessionIdleTimer(document.querySelector('[data-session-idle]')).sync(false),20);return true;})");
    await pause(1600);
    expect(await evalJS("location.pathname==='/index.php' && !!document.querySelector('[name=uid]')"),'expired session lands on login instead of CSRF error');
    await call('Network.setCookie',{name:mfa.name,value:mfa.sid,url:'http://gate.local:8618/',httpOnly:true,sameSite:'Lax'});
    await go('/mfa.php');expect(await evalJS("document.body.textContent.includes('계정에 등록된 이메일')&&!!document.querySelector('#mfa_email')"),'MFA registered recipient wording');
    expect(await evalJS('innerWidth===390 && document.documentElement.scrollWidth<=innerWidth'),'mobile MFA uses device width without overflow');await shot('nol-mfa-mobile-20260928');
    expect(errors.length===0,'no browser JavaScript exceptions');
    console.log(JSON.stringify({passed:checks.length,failed:0}));
}finally{
    await fs.writeFile(new URL('./artifacts/browser-followup-20260928.json',import.meta.url),JSON.stringify({checks,errors},null,2));
    fixture('cleanup',auth.uid,auth.sid);fixture('cleanup',mfa.uid,mfa.sid);
    await call('Browser.close').catch(()=>{});ws.close();
}
