import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const script=fs.readFileSync(new URL('../admin/resource/js/required-password.js',import.meta.url),'utf8');
let passed=0;
async function check(label,test){await test();passed++;console.log('PASS '+label)}
function fixture(fetcher,values={new_password:'NewPassword9!',new_password_confirm:'NewPassword9!',_csrf:'fixture-csrf'}){
 const state={calls:[],redirects:[],alerts:[],reset:0,handlers:{},attrs:{}};
 const list=()=>({toggle(){}});
 const input={type:'password',placeholder:'새 비밀번호'};
 const toggle={dataset:{passwordToggle:'new_password'},attrs:{},text:{},icon:{classList:list()},setAttribute(k,v){this.attrs[k]=v},querySelector(s){return s==='i'?this.icon:this.text},addEventListener(k,v){this[k]=v}};
 const button={disabled:false,textContent:'비밀번호 변경'};
 const error={hidden:true,textContent:'',classList:list()};
 const form={action:'https://gate.local/index.php/pages/account/ajax/password.process.php',querySelector:()=>button,querySelectorAll:()=>[toggle],addEventListener(k,v){state.handlers[k]=v},setAttribute(k,v){state.attrs[k]=v},removeAttribute(k){delete state.attrs[k]},reset(){state.reset++}};
 const context={document:{querySelector:s=>s==='#required-password-form'?form:error,getElementById:()=>input},window:{NO_ADMIN_BASE:'/index.php'},location:{replace:u=>state.redirects.push(u)},alert:m=>state.alerts.push(m),FormData:class{get(k){return values[k]}},fetch:async(u,o)=>{state.calls.push({url:u,...o});return fetcher(u,o)}};
 vm.runInNewContext(script,context);
 return {state,button,error,toggle,input,submit:()=>state.handlers.submit({preventDefault(){}})};
}
const response=(body,{status=200,type='application/json'}={})=>({status,ok:status>=200&&status<300,headers:{get:()=>type},json:async()=>body});
await check('actual client sends AJAX and JSON headers with credentials and CSRF',async()=>{
 const f=fixture(()=>response({result:'success',msg:'변경 완료'}));await f.submit();const r=f.state.calls[0];
 assert.equal(r.headers['X-Requested-With'],'XMLHttpRequest');assert.equal(r.headers.Accept,'application/json');assert.equal(r.credentials,'same-origin');assert.equal(r.body.get('_csrf'),'fixture-csrf');assert.ok(r.url.endsWith('/pages/account/ajax/password.process.php'));
 assert.equal(f.state.reset,1);assert.equal(f.state.redirects[0],'/index.php');
});
await check('mismatch stays on form without sending credentials',async()=>{const f=fixture(()=>{throw Error('must not send')},{new_password:'OnePass9!',new_password_confirm:'OtherPass9!'});await f.submit();assert.equal(f.state.calls.length,0);assert.equal(f.error.hidden,false);assert.match(f.error.textContent,/일치하지/)});
await check('HTML response shows Korean guidance instead of JSON parser error',async()=>{let parsed=false;const r=response(null,{type:'text/html'});r.json=async()=>{parsed=true;throw SyntaxError('Unexpected token <')};const f=fixture(()=>r);await f.submit();assert.equal(parsed,false);assert.equal(f.error.hidden,false);assert.match(f.error.textContent,/다시 로그인/);assert.doesNotMatch(f.error.textContent,/Unexpected|doctype/)});
await check('expired authentication redirects before attempting JSON parsing',async()=>{let parsed=false;const r=response(null,{status:401,type:'text/html'});r.json=async()=>{parsed=true};const f=fixture(()=>r);await f.submit();assert.equal(parsed,false);assert.equal(f.state.redirects[0],'/index.php')});
await check('server policy message remains visible and submit recovers',async()=>{const f=fixture(()=>response({result:'fail',msg:'기존 비밀번호와 다른 비밀번호를 입력하세요.'}));await f.submit();assert.match(f.error.textContent,/다른 비밀번호/);assert.equal(f.button.disabled,false);assert.equal(f.button.textContent,'비밀번호 변경')});
await check('visibility toggle keeps accessible state and copy',async()=>{const f=fixture(()=>response({}));f.toggle.click();assert.equal(f.input.type,'text');assert.equal(f.toggle.attrs['aria-pressed'],'true');assert.equal(f.toggle.text.textContent,'숨기기');f.toggle.click();assert.equal(f.input.type,'password');assert.equal(f.toggle.attrs['aria-pressed'],'false')});
await check('pending request prevents double submit and restores controls after failure',async()=>{let reject;const f=fixture(()=>new Promise((_,r)=>{reject=r}));const first=f.submit();assert.equal(f.button.disabled,true);assert.equal(f.button.textContent,'변경 중…');assert.equal(f.state.attrs['aria-busy'],'true');await f.submit();assert.equal(f.state.calls.length,1);reject(Error('연결 오류'));await first;assert.equal(f.button.disabled,false);assert.equal(f.state.attrs['aria-busy'],undefined);assert.equal(f.error.hidden,false)});
await check('invalid JSON releases controls with safe password outcome guidance',async()=>{const r=response({});r.json=async()=>{throw new SyntaxError('Unexpected <')};const f=fixture(()=>r);await f.submit();assert.equal(f.button.disabled,false);assert.doesNotMatch(f.error.textContent,/Unexpected/);assert.match(f.error.textContent,/로그인/)});
await check('null JSON restores controls without property access errors',async()=>{const f=fixture(()=>response(null));await f.submit();assert.equal(f.button.disabled,false);assert.doesNotMatch(f.error.textContent,/Cannot|properties/);assert.match(f.error.textContent,/로그인/)});
await check('temporary password success continues to server-selected authorized landing',async()=>{const f=fixture(()=>response({result:'success',msg:'변경되었습니다.',redirect:'/index.php/pages/board/board.list.php'}));await f.submit();assert.equal(f.state.redirects[0],'/index.php/pages/board/board.list.php');assert.equal(f.button.disabled,false)});
console.log(`${passed} PASS / 0 FAIL`);
