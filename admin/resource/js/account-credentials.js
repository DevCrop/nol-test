import { AccountCredentialDialog } from './AccountCredentialDialog.js?v=20261021';
const credentials=new AccountCredentialDialog();
let pending=false;
async function issue(data,url) {
  if(pending)return;pending=true;
  try {
    data.set('_csrf',document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_csrf"]')?.value || '');
    const response=await fetch(url,{method:'POST',body:data,credentials:'same-origin',cache:'no-store',headers:{'X-Requested-With':'XMLHttpRequest'}});
    const result=await response.json();
    if(!response.ok || !result.success)throw new Error(result.message || result.msg || '처리하지 못했습니다.');
    if(!credentials.show(result.credential,()=>location.href=(window.BLUE_ADMIN_BASE || '/admin')+'/pages/account/index.php'))throw new Error('발급은 완료되었으나 표시하지 못했습니다. 목록에서 재발급하세요.');
  }catch(error){alert(error.message || '요청에 실패했습니다.');}
  finally{pending=false;}
}
document.querySelector('[data-credential-create]')?.addEventListener('submit',event=>{event.preventDefault();issue(new FormData(event.currentTarget),event.currentTarget.action);});
document.querySelectorAll('[data-reissue-temporary]').forEach(button=>button.addEventListener('click',()=>{
  if(!confirm('기존 비밀번호와 로그인 세션이 무효화됩니다. 임시 비밀번호를 재발급할까요?'))return;
  const data=new FormData();data.set('mode','reissue_temporary');data.set('no',button.dataset.reissueTemporary);
  issue(data,(window.BLUE_ADMIN_BASE || '/admin')+'/pages/account/process.php');
}));
