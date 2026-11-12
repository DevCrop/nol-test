import { AccountCredentialDialog } from './AccountCredentialDialog.js?v=20261021';
const credentials=new AccountCredentialDialog();
let pending=false;
async function issue(buildData,url,form=null,button=null) {
  if(pending)return;pending=true;
  const controls=button?[button]:Array.from(form?.querySelectorAll('[type="submit"]') || []);
  const previous=controls.map(control=>({control,disabled:control.disabled,label:control.textContent}));
  const busy=form || button;
  const feedback=form?.querySelector('[data-account-feedback]');
  const release=window.BlueAdminLoading?.begin() || (()=>{});
  const controller=new AbortController();
  let timeout, timedOut=false, message='', credential=null, redirect=false;
  try {
    if(feedback){feedback.textContent='';feedback.hidden=true;}
    controls.forEach(control=>{control.disabled=true;control.textContent='처리 중…';});
    busy?.setAttribute('aria-busy','true');
    const data=buildData();
    data.set('_csrf',document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_csrf"]')?.value || '');
    timeout=setTimeout(()=>{timedOut=true;controller.abort();},30000);
    const response=await fetch(url,{method:'POST',body:data,signal:controller.signal,credentials:'same-origin',cache:'no-store',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
    if(response.status===401){redirect=true;return;}
    if(!(response.headers.get('Content-Type')||'').toLowerCase().includes('application/json'))throw new Error('서버 응답을 확인하지 못했습니다. 계정 목록에서 등록 여부를 먼저 확인해 주세요.');
    let result;
    try{result=await response.json();}catch{throw new Error('응답을 처리하지 못했습니다. 계정 목록에서 등록 여부를 확인해 주세요.');}
    if(!result || typeof result!=='object')throw new Error('응답을 처리하지 못했습니다. 계정 목록에서 등록 여부를 확인해 주세요.');
    if(!response.ok || !result.success)throw new Error(result.message || result.msg || '처리하지 못했습니다.');
    credential=result.credential;
  }catch(error){message=timedOut ? '응답 대기 시간이 지났습니다. 계정 목록에서 등록 여부를 먼저 확인해 주세요.' : (error instanceof TypeError ? '요청 결과를 확인하지 못했습니다. 계정 목록에서 등록 여부를 먼저 확인해 주세요.' : error.message || '요청에 실패했습니다.');}
  finally{
    clearTimeout(timeout);pending=false;
    previous.forEach(({control,disabled,label})=>{control.disabled=disabled;control.textContent=label;});
    busy?.removeAttribute('aria-busy');release();
    if(redirect)location.replace(window.BLUE_ADMIN_BASE || '/');
  }
  // Render feedback only after pending controls and loading scopes are released.
  if(credential){
    try{if(credentials.show(credential,()=>location.href=(window.BLUE_ADMIN_BASE || '/admin')+'/pages/account/index.php'))return;}catch{}
    message='발급은 완료되었으나 표시하지 못했습니다. 계정 목록에서 확인 후 필요한 경우 재발급하세요.';
  }
  if(message){if(feedback){feedback.textContent=message;feedback.hidden=false;feedback.focus();}else alert(message);}
}
document.querySelector('[data-credential-create]')?.addEventListener('submit',event=>{event.preventDefault();const form=event.currentTarget;void issue(()=>new FormData(form),form.action,form);});
document.querySelectorAll('[data-reissue-temporary]').forEach(button=>button.addEventListener('click',()=>{
  if(!confirm('기존 비밀번호와 로그인 세션이 무효화됩니다. 임시 비밀번호를 재발급할까요?'))return;
  const reason=prompt('변경 사유를 입력하세요. (2~300자)');if(reason===null)return;
  const data=new FormData();data.set('mode','reissue_temporary');data.set('no',button.dataset.reissueTemporary);
  data.set('change_reason',reason);
  void issue(()=>data,(window.BLUE_ADMIN_BASE || '/admin')+'/pages/account/process.php',null,button);
}));
document.querySelectorAll('form.account-inline').forEach(form=>form.addEventListener('submit',event=>{
  if(event.defaultPrevented)return;
  const reason=prompt('변경 사유를 입력하세요. (2~300자)');
  if(reason===null || reason.trim().length<2){event.preventDefault();return;}
  let input=form.querySelector('input[name="change_reason"]');
  if(!input){input=document.createElement('input');input.type='hidden';input.name='change_reason';form.append(input);}
  input.value=reason;
}));
