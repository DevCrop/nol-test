import http from 'node:http';
import vm from 'node:vm';
import fs from 'node:fs';
import assert from 'node:assert/strict';
let passed=0;
const base=process.env.QA_GATE_URL||'http://127.0.0.1:18628';
async function served(path){return new Promise((resolve,reject)=>{http.get(base+'/index.php'+path,{headers:{Host:'gate.local'}},r=>{let text='';r.setEncoding('utf8');r.on('data',s=>text+=s);r.on('end',()=>r.statusCode===200?resolve(text):reject(new Error('HTTP '+r.statusCode+' '+path)));}).on('error',reject);});}
const account=await served('/resource/js/account-credentials.js');
function accountScreen(response){const alerts=[],requests=[];let closed,formSubmit;const location={href:'',replace(s){this.href=s;}};
  class Dialog{show(payload,callback){closed=callback;return true;}}
  const form={action:'/index.php/pages/account/process.php',querySelectorAll:()=>[],querySelector:()=>null,setAttribute(){},removeAttribute(){},addEventListener(k,v){formSubmit=v;}};
  const ctx={AccountCredentialDialog:Dialog,document:{querySelector:s=>s==='[data-credential-create]'?form:{content:'csrf'},querySelectorAll:()=>[]},window:{BLUE_ADMIN_BASE:'/index.php'},location,FormData:class{set(){}},alert:s=>alerts.push(s),fetch:async(url)=>{requests.push(url);return await response();},TypeError};
  Object.assign(ctx,{AbortController,setTimeout,clearTimeout});
  vm.createContext(ctx);vm.runInContext(account.replace(/^import .*;\r?\n/,''),ctx);
  return {ctx,alerts,requests,location,submit:()=>formSubmit({preventDefault(){},currentTarget:form}),close:()=>closed()};
}
const json=result=>({status:200,ok:true,headers:{get:()=> 'application/json'},json:async()=>result});
let s=accountScreen(async()=>json({success:true,credential:{}}));s.submit();s.submit();await new Promise(setImmediate);assert.equal(s.requests.length,1);s.close();assert.equal(s.location.href,'/index.php/pages/account/index.php');passed++;
s=accountScreen(async()=>json({success:false,message:'중복 아이디'}));s.submit();await new Promise(setImmediate);assert.deepEqual(s.alerts,['중복 아이디']);assert.equal(s.location.href,'');passed++;
s=accountScreen(async()=>({status:403,headers:{get:()=> 'text/html'}}));s.submit();await new Promise(setImmediate);assert.match(s.alerts[0],/등록 여부/);assert.doesNotMatch(s.alerts[0],/Unexpected/);passed++;
s=accountScreen(async()=>({...json({}),json:async()=>{throw new SyntaxError('Unexpected <');}}));s.submit();await new Promise(setImmediate);assert.match(s.alerts[0],/계정 목록/);assert.doesNotMatch(s.alerts[0],/Unexpected/);passed++;
s=accountScreen(async()=>{throw new TypeError('Failed to fetch');});s.submit();await new Promise(setImmediate);assert.match(s.alerts[0],/등록 여부/);passed++;
s=accountScreen(async()=>({status:401}));s.submit();await new Promise(setImmediate);assert.equal(s.location.href,'/index.php');assert.equal(s.alerts.length,0);passed++;
const security=await served('/resource/js/security.js');assert.ok(security.includes("+ '/lib/session/ping.php'"));assert.ok(!security.includes("+ '/index.php/lib/session/ping.php'"));passed++;
const works=await served('/pages/works/js/works.process.js');assert.ok(!works.includes("+ '/index.php/pages/works/upload.php'"));passed++;
// Execute the served works save handler, holding the HTTP request to prove duplicate suppression.
const handlers={};let resolveFetch,calls=0;const button={disabled:false},form={addEventListener:(k,v)=>handlers[k]=v,querySelectorAll:()=>[button],setAttribute(){},removeAttribute(){}};
const ctx={window:{BLUE_ADMIN_BASE:'/index.php'},document:{querySelector:s=>s==='#frm'?form:null,querySelectorAll:()=>[],addEventListener(){}},$:()=>({ready(){}}),FormData:class{},URLSearchParams,location:{search:''},alert(){},confirm:()=>true,fetch:()=>{calls++;return new Promise(r=>resolveFetch=r);}};
vm.createContext(ctx);vm.runInContext(works,ctx);ctx.bindForm();const first=handlers.submit({preventDefault(){}});handlers.submit({preventDefault(){}});assert.equal(calls,1);assert.equal(button.disabled,true);resolveFetch(json({success:false,message:'rejected'}));await first;assert.equal(button.disabled,false);passed++;
await assert.rejects(ctx.worksJson({...json({}),json:async()=>{throw new SyntaxError('Unexpected <');}}),/응답을 처리하지/);passed++;
await assert.rejects(ctx.worksJson(json(null)),/응답을 처리하지/);passed++;
await assert.rejects(ctx.worksJson({status:401}),/다시 로그인/);assert.equal(ctx.location.href,'/index.php');passed++;
// Permission controls retain regular grants across role toggles and keep view/action consistent.
let selected='admin';const roleEvents=[],change={};const inputs=['view','create','update','delete'].map(action=>({checked:action==='view',disabled:false,dataset:{permissionAction:action,regularChecked:action==='view'?'1':'0'},closest:()=>row}));
const row={querySelectorAll:()=>inputs,querySelector:()=>inputs[0]},root={closest:()=>permissionForm,querySelectorAll:()=>inputs,querySelector:()=>({}),addEventListener:(k,v)=>change[k]=v};
const permissionForm={querySelectorAll:()=>[0,1].map(()=>({addEventListener:(k,v)=>roleEvents.push(v)})),querySelector:()=>({value:selected})};
const p=vm.createContext({document:{querySelector:()=>root}});vm.runInContext(fs.readFileSync(new URL('../admin/resource/js/account-permissions.js',import.meta.url),'utf8'),p);
selected='super';roleEvents[0]();assert.ok(inputs.every(i=>i.checked&&i.disabled));selected='admin';roleEvents[0]();assert.deepEqual(inputs.map(i=>i.checked),[true,false,false,false]);passed++;
inputs[0].checked=false;inputs[1].checked=true;change.change({target:inputs[1]});assert.equal(inputs[0].checked,true);inputs[0].checked=false;change.change({target:inputs[0]});assert.ok(inputs.every(i=>!i.checked));passed++;
console.log(`PASS served gate/account/works/permissions JS ${passed}; browser rendering not exercised`);
