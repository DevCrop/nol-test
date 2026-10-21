import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const code=fs.readFileSync(new URL('../admin/resource/js/AccountCredentialDialog.js',import.meta.url),'utf8');
const listeners={};const alerts=[];let copied='',callbacks=0,removed=false;
function element(){return {value:'',type:'password',textContent:'',events:{},addEventListener(k,v){this.events[k]=v;},setAttribute(){},focus(){},select(){},remove(){removed=true;}};}
const ids=Object.fromEntries(['account-credential-dialog','credential-uid','credential-password','credential-toggle','credential-copy','credential-expires'].map(id=>['#'+id,element()]));
const dialog=ids['#account-credential-dialog'];dialog.querySelectorAll=()=>[];dialog.showModal=()=>{dialog.open=true;};dialog.close=()=>{dialog.open=false;dialog.events.close();};dialog.appendChild=b=>{copied=b.value;};
const context=vm.createContext({document:{querySelector:s=>ids[s],createElement:()=>element(),execCommand:()=>true},window:{addEventListener:(key,fn)=>listeners[key]=fn},navigator:{},isSecureContext:false,alert:s=>alerts.push(s)});
vm.runInContext(code.replace('export class','class')+'\nglobalThis.instance=new AccountCredentialDialog();',context);
let pass=0;function check(label,fn){fn();pass++;console.log('PASS '+label);}
const payload={uid:'fixture',temporary_password:'DemoOnly7!',expires_at:'test'};
check('one-time response consumed',()=>{assert.equal(context.instance.show(payload,()=>callbacks++),true);assert.equal(payload.temporary_password,'');assert.equal(ids['#credential-password'].value,'DemoOnly7!');});
check('masked by default',()=>assert.equal(ids['#credential-password'].type,'password'));
await context.instance.copy();
check('HTTP fallback copies exact two-line format',()=>{assert.equal(copied,'아이디 : fixture\n임시비밀번호 : DemoOnly7!');assert.equal(removed,true);assert.deepEqual(alerts,['복사되었습니다.']);});
dialog.close();
check('close clears secret and calls callback once',()=>{assert.equal(ids['#credential-password'].value,'');assert.equal(ids['#credential-uid'].value,'');assert.equal(callbacks,1);});
context.instance.show({uid:'fixture',temporary_password:'DemoOnly8!',expires_at:'test'},()=>callbacks++);listeners.pagehide();
check('pagehide clears without navigation callback',()=>{assert.equal(ids['#credential-password'].value,'');assert.equal(dialog.open,false);assert.equal(callbacks,1);});
check('no persistent browser storage or retrieval',()=>assert.doesNotMatch(code,/localStorage|sessionStorage|fetch\(/));
console.log(`pass=${pass} fail=0`);
