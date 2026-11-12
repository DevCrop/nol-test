import fs from 'node:fs';import vm from 'node:vm';import assert from 'node:assert/strict';
const security=fs.readFileSync(process.env.QA_SECURITY_SOURCE||new URL('../admin/resource/js/security.js',import.meta.url),'utf8');
const account=fs.readFileSync(new URL('../admin/resource/js/account-credentials.js',import.meta.url),'utf8').replace(/^import .*;\r?\n/,'');
const tick=()=>new Promise(setImmediate);let passed=0;
function response(result={success:false,message:'이미 사용 중인 계정입니다.'}){return {status:200,ok:true,headers:{get:()=> 'application/json'},json:async()=>result};}
function screen(fetcher,formDataThrows=false,jquery=null){
 const active=new Set(),listeners={},microtasks=[],timers=new Map(),requests=[];let timerId=0,submitHandler,closed;
 const button={disabled:false,textContent:'등록'},feedback={hidden:true,textContent:'',focus(){}};
 const form={action:'http://gate.local:8628/index.php/pages/account/process.php',target:'',setAttribute(k,v){this[k]=v;},removeAttribute(k){delete this[k];},querySelectorAll:()=>[button],querySelector:()=>feedback,addEventListener:(k,v)=>submitHandler=v};
 const body={classList:{add:k=>active.add(k),remove:k=>active.delete(k)},setAttribute(k,v){this[k]=v;},removeAttribute(k){delete this[k];}};
 const ctx={document:{body,addEventListener:(k,v)=>listeners[k]=v,querySelector:s=>s==='[data-credential-create]'?form:(s==='meta[name="csrf-token"]'?{content:'csrf'}:null),querySelectorAll:()=>[]},
  location:{href:'http://gate.local:8628/index.php/pages/account/new.php',origin:'http://gate.local:8628',replace(s){this.href=s;}},URL,Headers,Request,AbortController,TypeError,
  queueMicrotask:fn=>microtasks.push(fn),setTimeout:(fn,delay)=>{timers.set(++timerId,{fn,delay});return timerId;},clearTimeout:id=>timers.delete(id),addEventListener:(k,v)=>listeners[k]=v,
  FormData:class{constructor(){if(formDataThrows)throw new Error('form construction failed');}set(){}},
  AccountCredentialDialog:class{show(c,fn){closed=fn;return true;}},alert:()=>assert.equal(active.has('is-loading'),false),
  fetch:(input,init)=>{requests.push({input,init});return fetcher?fetcher(input,init):Promise.resolve(response());}};
 ctx.window=ctx;if(jquery)ctx.jQuery=jquery;vm.createContext(ctx);vm.runInContext(security,ctx);vm.runInContext(account,ctx);
 const flush=()=>{for(const [id,t]of [...timers])if(t.delay===0){timers.delete(id);t.fn();}};
 const submit=()=>{const event={defaultPrevented:false,currentTarget:form,target:form,preventDefault(){this.defaultPrevented=true;}};listeners.submit(event);
  // Model the native-event checkpoint between the capture and target listeners.
  while(microtasks.length)microtasks.shift()();submitHandler(event);flush();};
 return {ctx,active,listeners,timers,requests,button,feedback,form,submit,flush,close:()=>closed?.()};
}
async function check(label,fn){await fn();passed++;console.log('PASS '+label);}
function recovered(s){assert.equal(s.active.has('is-loading'),false);assert.equal(s.button.disabled,false);assert.equal(s.button.textContent,'등록');assert.equal(s.form['aria-busy'],undefined);}
await check('duplicate account releases loading across native-event checkpoint',async()=>{const s=screen();s.submit();await tick();recovered(s);assert.equal(s.feedback.textContent,'이미 사용 중인 계정입니다.');assert.equal(s.feedback.hidden,false);});
await check('duplicate account allows corrected retry',async()=>{const s=screen();s.submit();await tick();s.submit();await tick();assert.equal(s.requests.length,2);recovered(s);});
await check('one request for double-submit and busy until JSON settles',async()=>{let finish;const s=screen(async()=>({...response(),json:()=>new Promise(r=>finish=r)}));s.submit();s.submit();await tick();assert.equal(s.requests.length,1);assert.equal(s.active.has('is-loading'),true);assert.equal(s.button.disabled,true);finish({success:false,message:'중복'});await tick();recovered(s);});
for(const [label,fetcher]of [
 ['HTML',async()=>({status:403,headers:{get:()=> 'text/html'}})],
 ['malformed JSON',async()=>({...response(),json:async()=>{throw new SyntaxError('Unexpected <');}})],
 ['null JSON',async()=>response(null)],['network failure',async()=>{throw new TypeError('Failed to fetch');}],
 ['synchronous fetch failure',()=>{throw new TypeError('Failed to fetch');}],
 ['server failure',async()=>({...response({success:false,message:'처리 실패'}),status:500,ok:false})]
])await check(label+' restores controls and safe error',async()=>{const s=screen(fetcher);s.submit();await tick();recovered(s);assert.equal(s.feedback.hidden,false);assert.doesNotMatch(s.feedback.textContent,/Unexpected|Failed to fetch/);});
await check('FormData failure restores controls',async()=>{const s=screen(null,true);s.submit();await tick();recovered(s);assert.equal(s.requests.length,0);});
await check('timeout recovers without retrying uncertain registration',async()=>{const s=screen((_u,init)=>new Promise((_r,reject)=>init.signal.addEventListener('abort',()=>reject(new Error('aborted')))));s.submit();[...s.timers.values()].find(t=>t.delay===30000).fn();await tick();recovered(s);assert.match(s.feedback.textContent,/등록 여부/);assert.equal(s.timers.size,0);});
await check('401 releases loading before redirect',async()=>{const s=screen(async()=>({status:401}));s.submit();await tick();recovered(s);assert.equal(s.ctx.location.href,'/');});
await check('success modal closes to one gate prefix',async()=>{const s=screen(async()=>response({success:true,credential:{temporary_password:'fixture'}}));s.ctx.BLUE_ADMIN_BASE='/index.php';s.submit();await tick();recovered(s);s.close();assert.equal(s.ctx.location.href,'/index.php/pages/account/index.php');});
await check('overlapping fetches keep independent loading scopes',async()=>{const done=[];const s=screen(()=>new Promise(r=>done.push(r)));const a=s.ctx.fetch('/a'),b=s.ctx.fetch('/b');done[0](response());await a;assert.equal(s.active.has('is-loading'),true);done[1](response());await b;assert.equal(s.active.has('is-loading'),false);});
await check('silent session request retains CSRF without loading',async()=>{const s=screen();await s.ctx.fetch('/ping',{method:'POST',headers:{'X-Loading-Silent':'1'}});assert.equal(s.active.has('is-loading'),false);assert.equal(s.requests[0].init.headers.get('X-CSRF-Token'),'csrf');});
await check('new-tab submission leaves parent usable',async()=>{const s=screen();s.listeners.submit({defaultPrevented:false,target:{target:'_blank'}});s.flush();assert.equal(s.active.has('is-loading'),false);});
await check('BFCache reset and stale release preserve new scopes',async()=>{const s=screen();s.listeners.submit({defaultPrevented:false});s.flush();assert.equal(s.active.has('is-loading'),true);const old=s.ctx.BlueAdminLoading.begin();s.listeners.pageshow({persisted:true});recovered(s);const current=s.ctx.BlueAdminLoading.begin();old();assert.equal(s.active.has('is-loading'),true);current();assert.equal(s.active.has('is-loading'),false);});
await check('jQuery cleanup precedes throwing page callback',async()=>{let prefilter;const jq=()=>({ajaxSend(){}});jq.ajaxPrefilter=fn=>prefilter=fn;const s=screen(null,false,jq);const callbacks=[];prefilter({},null,{always:fn=>callbacks.push(fn)});assert.equal(s.active.has('is-loading'),true);callbacks.push(()=>{throw new Error('callback');});try{for(const fn of callbacks)fn();}catch{}assert.equal(s.active.has('is-loading'),false);});
console.log(`pass=${passed} fail=0; VM lifecycle checks, not a rendered browser`);
