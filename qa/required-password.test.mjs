import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const code=fs.readFileSync(new URL('../nol-gate/resource/js/required-password.js',import.meta.url),'utf8');
let submit,reset=0,requests=0,result={ok:true,status:200,json:async()=>({result:'success',msg:'done'})};
const button={disabled:false},error={textContent:''},values={pwd_new:'NewPassword9!',pwd_new_confirm:'wrong'},redirects=[],alerts=[];
const form={action:'/setting',querySelector:()=>button,addEventListener:(name,fn)=>submit=fn,reset:()=>reset++};
const context=vm.createContext({document:{querySelector:s=>s==='#required-password-form'?form:error},FormData:class{get(key){return values[key];}},fetch:async(url,options)=>{requests++;assert.equal(options.cache,'no-store');assert.equal(options.credentials,'same-origin');return result;},window:{NO_ADMIN_BASE:'/index.php'},location:{replace:url=>redirects.push(url)},alert:s=>alerts.push(s)});
vm.runInContext(code,context);const event={preventDefault(){}};let pass=0;
function check(label,fn){fn();pass++;console.log('PASS '+label);}
await submit(event);check('mismatch blocks request',()=>{assert.equal(requests,0);assert.ok(error.textContent);});
values.pwd_new_confirm=values.pwd_new;
result={ok:true,status:200,json:async()=>({result:'fail',msg:'rejected'})};await submit(event);
check('server rejection shown without navigation',()=>{assert.equal(error.textContent,'rejected');assert.equal(redirects.length,0);assert.equal(button.disabled,false);});
result={ok:true,status:200,json:async()=>({result:'success',msg:'done'})};await submit(event);
check('success clears form and returns to login',()=>{assert.equal(reset,1);assert.deepEqual(redirects,['/index.php']);assert.deepEqual(alerts,['done']);});
const legacy=fs.readFileSync(new URL('../nol-gate/pages/setting/js/setting.process.js',import.meta.url),'utf8');
check('legacy password form accepts parsed JSON',()=>assert.ok(legacy.includes("typeof data === 'string' ? $.parseJSON(data) : data")));
console.log(`pass=${pass} fail=0`);
