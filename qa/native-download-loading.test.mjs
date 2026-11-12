import fs from 'node:fs';import vm from 'node:vm';import assert from 'node:assert/strict';
const source=fs.readFileSync(process.env.QA_SECURITY_SOURCE||new URL('../admin/resource/js/security.js',import.meta.url),'utf8');let passed=0;
function screen(){
 const active=new Set(),events={},tasks=[],children=[];let settle;
 const body={classList:{add:k=>active.add(k),remove:k=>active.delete(k)},setAttribute(k,v){this[k]=v;},removeAttribute(k){delete this[k];}};
 const form={target:'_self',attrs:new Set(['data-native-download']),hasAttribute(k){return this.attrs.has(k);},querySelector:()=>children.find(e=>e.name==='_csrf')||null,appendChild:e=>children.push(e)};
 const window={fetch:()=>new Promise(r=>settle=r),addEventListener:(k,v)=>events[k]=v};
 const context={document:{body,addEventListener:(k,v)=>events[k]=v,querySelector:s=>s==='meta[name="csrf-token"]'?{content:'fixture-csrf'}:null,querySelectorAll:s=>s.startsWith('form[')?[form]:[],createElement:()=>({})},window,location:{href:'http://gate.local:8628/index.php/pages/request/request.view.php?no=154',origin:'http://gate.local:8628'},URL,Headers,Request,setTimeout:f=>tasks.push(f)};
 vm.runInNewContext(source,context);
 const submit=(target=form,submitter)=>{events.submit({target,submitter,defaultPrevented:false});while(tasks.length)tasks.shift()();};
 return {active,events,form,body,context,submit,children,flush:()=>{while(tasks.length)tasks.shift()();},resolve:()=>settle({ok:true})};
}
async function check(name,fn){await fn();passed++;console.log('PASS '+name);}
await check('attachment POST keeps document usable without waiting for pageshow',()=>{const s=screen();s.submit();assert.equal(s.active.has('is-loading'),false);assert.equal(s.body['aria-busy'],undefined);});
await check('repeated attachment downloads never accumulate navigation scopes',()=>{const s=screen();for(let i=0;i<5;i++)s.submit();assert.equal(s.active.size,0);});
await check('download form still receives CSRF token',()=>{const s=screen();assert.equal(s.children.length,1);assert.equal(s.children[0].name,'_csrf');assert.equal(s.children[0].value,'fixture-csrf');});
await check('download does not release another active operation',()=>{const s=screen();const release=s.context.window.BlueAdminLoading.begin();s.submit();assert.equal(s.active.has('is-loading'),true);release();assert.equal(s.active.has('is-loading'),false);});
await check('normal save after a download still starts navigation loading',()=>{const s=screen();s.submit();s.form.attrs.clear();s.submit();assert.equal(s.active.has('is-loading'),true);});
for(const kind of ['login','MFA verification','MFA resend','account save'])await check(kind+' native submit retains navigation loading',()=>{const s=screen();s.form.attrs.clear();s.submit();assert.equal(s.active.has('is-loading'),true);});
await check('non-self form target still leaves parent usable',()=>{const s=screen();s.form.attrs.clear();s.form.target='_blank';s.submit();assert.equal(s.active.has('is-loading'),false);});
await check('submitter download marker is supported without altering its form',()=>{const s=screen();s.form.attrs.clear();s.submit(s.form,{hasAttribute:k=>k==='data-native-download'});assert.equal(s.active.has('is-loading'),false);});
await check('AJAX cancellation does not create navigation loading',()=>{const s=screen();s.form.attrs.clear();const event={target:s.form,defaultPrevented:false};s.events.submit(event);event.defaultPrevented=true;s.flush();assert.equal(s.active.has('is-loading'),false);});
await check('fetch still tracks its own loading while native download occurs',async()=>{const s=screen();const p=s.context.window.fetch('/some-request');assert.equal(s.active.has('is-loading'),true);s.submit();assert.equal(s.active.has('is-loading'),true);s.resolve();await p;assert.equal(s.active.has('is-loading'),false);});
await check('BFCache reset still recovers unrelated navigation state',()=>{const s=screen();s.form.attrs.clear();s.submit();assert.equal(s.active.has('is-loading'),true);s.events.pageshow({persisted:true});assert.equal(s.active.has('is-loading'),false);s.form.attrs.add('data-native-download');s.submit();assert.equal(s.active.has('is-loading'),false);});
console.log(`pass=${passed} fail=0; VM event lifecycle, not a rendered browser`);
