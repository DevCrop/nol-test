import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
let count = 0;
function screen(file, overrides = {}) {
    const values = {'#b_loc':'main','#b_title':'QA','#b_img':'qa.png','#p_title':'QA','#p_img':'qa.png','#p_left':'0','#p_top':'0','#r_title':'QA','#r_sdate':'2026-11-01','#r_edate':'2026-11-02',
        'input[name="b_none_limit"]:checked':'Y','input[name="b_none_view"]:checked':'Y','input[name="p_none_limit"]:checked':'Y','input[name="p_loc"]:checked':'M', ...overrides};
    const alerts=[], requests=[];
    function $(selector) {
        const object = {0:{},length:0,val(value) {if(arguments.length){values[selector]=value;return object;}return values[selector]??'';},
            focus(){return object;},attr(){return object;},removeAttr(){return object;},hide(){return object;},change(){return object;},ready(){return object;},summernote(){return object;}};
        return object;
    }
    $.ajax=options=>requests.push(options);
    const ctx={$,document:{},FormData:class{},alert:message=>alerts.push(message),confirm:()=>true,location:{}};
    vm.createContext(ctx);vm.runInContext(fs.readFileSync(new URL('../'+file,import.meta.url),'utf8'),ctx);
    return {ctx,alerts,requests,values};
}
for(const name of ['banner','popup']) {
    const file='admin/pages/design/js/'+name+'.process.js';
    let s=screen(file);s.ctx.doRegSave();s.ctx.doRegSave();assert.equal(s.requests.length,1);count++;
    s.requests[0].error({responseJSON:{msg:'검증 실패'}});assert.equal(s.alerts.at(-1),'검증 실패');count++;
    s.requests[0].complete();s.ctx.doRegSave();assert.equal(s.requests.length,2);count++;
    const prefix=name==='banner'?'b':'p';
    s=screen(file,{['input[name="'+prefix+'_none_limit"]:checked']:'N'});s.ctx.doRegSave();assert.equal(s.requests.length,0);assert.equal(s.alerts.length,1);count++;
    s=screen(file,{['input[name="'+prefix+'_none_limit"]:checked']:'N',['#'+prefix+'_sdate']:'2026-11-03',['#'+prefix+'_edate']:'2026-11-01'});s.ctx.doEditSave();assert.equal(s.requests.length,0);count++;
}
let s=screen('admin/pages/design/js/popup.process.js',{'input[name="p_loc"]:checked':'P','#p_left':''});s.ctx.doRegSave();assert.equal(s.requests.length,0);count++;
s=screen('admin/pages/request/js/request.process.js');s.ctx.doManageSave();s.ctx.doManageSave();assert.equal(s.requests.length,1);count++;
s.requests[0].error({});assert.ok(s.alerts.at(-1).includes('다시'));s.requests[0].complete();s.ctx.doManageEdit();assert.equal(s.requests.length,2);count++;
s=screen('admin/pages/request/js/request.process.js',{'#r_edate':'2026-10-01'});s.ctx.doManageSave();assert.equal(s.requests.length,0);count++;
console.log('PASS design/rental client regressions '+count);

