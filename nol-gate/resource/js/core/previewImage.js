function readPreview(input,file,target) {
  const reader=new FileReader();
  reader.onload=event=>{
    if(input.files?.[0]!==file || !target.isConnected)return;
    target.src=event.target.result;target.style.display='block';
  };
  reader.onerror=()=>{if(input.files?.[0]===file)target.style.display='none';};
  reader.readAsDataURL(file);
}
export function previewImage(input,imgId,txtId) {
  const file=input.files?.[0],preview=document.getElementById(imgId),name=document.getElementById(txtId);
  if(name)name.value=file?.name || '';
  if(!preview)return;
  if(file && /^image\/(jpeg|png|gif|webp)$/.test(file.type))readPreview(input,file,preview);
  else {preview.removeAttribute('src');preview.style.display='none';}
}
export function previewBannerFile(input,previewId,txtId) {
  const file=input.files?.[0],name=document.getElementById(txtId);
  const container=document.getElementById(previewId==='bannerMobilePreview'?'bannerMobilePreviewContainer':'bannerPreviewContainer');
  if(name)name.value=file?.name || '';
  if(!container)return;
  container.replaceChildren();
  if(!file)return;
  const video=/^video\//.test(file.type),image=/^image\/(jpeg|png|gif|webp)$/.test(file.type);
  if(!video && !image)return;
  const media=document.createElement(video?'video':'img');media.id=previewId;
  if(video)media.controls=true;else media.alt='배너 미리보기';
  media.style.maxWidth='300px';media.style.width='100%';media.style.height='auto';media.style.marginTop='10px';
  container.append(media);readPreview(input,file,media);
}
window.previewImage=previewImage;
window.previewBannerFile=previewBannerFile;
document.addEventListener('change',event=>{
  const input=event.target;
  if(input?.type!=='file' || input.hasAttribute('onchange'))return;
  const widget=input.closest('.no-file-control');
  const name=widget?.querySelector('.no-fake-file');
  if(name)name.value=Array.from(input.files||[]).map(file=>file.name).join(', ');
  const image=input.closest('.no-admin-content')?.querySelector('.no-image-preview img');
  if(image)previewImage(input,image.id,name?.id);
});
