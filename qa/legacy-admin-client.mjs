import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
let passed=0;
function screen(path, failFetch=false) {
    const values={'#skin':'notice','#title':'QA','#target':'main','#name_1':'A&B=문자','#sort_no_1':'1','#pwd_old':'old','#pwd_new':'new','#pwd_new_confirm':'new',
        '#logo_top_filename':'logo','#logo_footer_filename':'logo','#footer_name':'QA','#id':'1','#no':'1','#board_no':'1','#write_name':'QA','#category_no':'','#link':'https://example.com'};
    const requests=[],alerts=[];
    const document={querySelectorAll:()=>[],createElement:()=>({innerHTML:'',outerHTML:'<div>QA</div>',getElementsByTagName:()=>[],querySelectorAll:()=>[]})};
    function $(selector){const item={0:{},length:0,val(v){if(arguments.length){values[selector]=v;return item;}return values[selector]??'';},
        focus(){return item;},serialize:()=>'',ready(){},selectmenu(){},is:()=>true};return item;}
    $.ajax=options=>{requests.push(options);return {};};
    const ctx={$,jQuery:$,document,submitContents(){},FormData:class{get(){return 'QA';}set(){}append(){}},alert:m=>alerts.push(m),confirm:()=>true,location:{reload(){}},window:{location:{search:''}},URLSearchParams,
        fetch:async()=>{if(failFetch)throw Error('offline');return {ok:true,json:async()=>({result:'success',msg:'완료'})};}};
    ctx.window.jQuery=$;
    vm.createContext(ctx);vm.runInContext(fs.readFileSync(new URL('../admin/resource/js/AdminAjaxRequest.js',import.meta.url),'utf8'),ctx);
    vm.runInContext(fs.readFileSync(new URL('../admin/pages/'+path,import.meta.url),'utf8'),ctx);
    return {ctx,requests,alerts,values};
}
for(const [path,fn] of [
    ['board/js/board.manage.process.js','doRegSave'],
    ['setting/js/setting.process.js','doSave'],
    ['setting/js/setting.data.process.js','doRegSave'],
    ['opera/js/opera.process.js','doManageSave']
]){
    const s=screen(path);s.ctx[fn]();s.ctx[fn]();assert.equal(s.requests.length,1);assert.equal(s.requests[0].dataType,'json');passed+=2;
    s.requests[0].error({responseJSON:{msg:'중복 계정'}});assert.equal(s.alerts.at(-1),'중복 계정');assert.equal(s.ctx.legacyAdminPending,false);passed+=2;
    s.ctx[fn]();assert.equal(s.requests.length,2);passed++;
    Object.defineProperty(s.ctx.location,'href',{set(){throw Error('navigation failure');}});
    s.requests[1].success({result:'success',msg:'완료'});assert.equal(s.ctx.legacyAdminPending,false);passed++;
    s.ctx[fn]();s.requests.at(-1).error({});assert.ok(s.alerts.at(-1).includes('다시'));passed++;
}
let s=screen('setting/js/setting.process.js');s.ctx.doDeleteData(7);assert.equal(s.requests[0].data.no,7);assert.notEqual(s.requests[0].contentType,false);assert.notEqual(s.requests[0].processData,false);passed+=3;
s=screen('opera/js/opera.process.js');s.ctx.doDelete(7);assert.equal(s.requests[0].url,'./ajax/opera.process.php');passed++;
s=screen('board/js/board.manage.process.js');s.ctx.doCategorySave(1);assert.equal(s.requests[0].data.name,'A&B=문자');passed++;
s=screen('board/js/board.process.js',true);const a=s.ctx.doEditSave();await s.ctx.doEditSave();await a;assert.equal(s.ctx.legacyAdminPending,false);assert.equal(s.alerts.length,1);passed+=2;
s=screen('board/js/board.process.js');s.ctx.confirm=()=>false;s.ctx.doDelete(1);assert.equal(s.requests.length,0);passed++;
s=screen('board/js/board.process.js');s.ctx.doGetBoardManageInfo(1);assert.equal(s.requests.length,2);passed++;
console.log('PASS legacy admin client regressions '+passed);
