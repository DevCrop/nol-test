
let workRequestPending = false;
async function worksJson(response) {
    if (response.status === 401) { location.href = window.BLUE_ADMIN_BASE || '/'; throw new Error('다시 로그인해 주세요.'); }
    if (!(response.headers.get('Content-Type') || '').includes('application/json')) throw new Error('서버 응답을 확인하지 못했습니다. 목록에서 처리 여부를 확인해 주세요.');
    let data;
    try { data = await response.json(); } catch { throw new Error('응답을 처리하지 못했습니다. 목록에서 저장 여부를 확인해 주세요.'); }
    if (!data || typeof data !== 'object') throw new Error('응답을 처리하지 못했습니다. 목록에서 저장 여부를 확인해 주세요.');
    if (!response.ok) throw new Error(data.message || '요청을 처리하지 못했습니다.');
    return data;
}
function workError(error) { return error?.name === 'TypeError' ? '요청 결과를 확인하지 못했습니다. 목록에서 저장 여부를 확인해 주세요.' : error?.message || '요청에 실패했습니다.'; }
function qs(selector){
	return document.querySelector(selector); 
}

function qsAll(selector){
	return document.querySelectorAll(selector); 
}

function on(element, event, callback){
	if(!element) return; 
	element.addEventListener(event, callback); 
}


async function bindForm(){
	const form = qs('#frm');
	const deleteBtns = qsAll('[data-id]'); 

	if(deleteBtns.length > 0){
		deleteBtns.forEach(deleteBtn => {
			on(deleteBtn, 'click', async (e) => {
				e.preventDefault(); if (workRequestPending) return;
				if(!confirm('정말로 삭제하시겠습니까?')) return; 
				workRequestPending = true;
				const id = deleteBtn.dataset.id; 
				try
				{
					const response = await fetch('./ajax/works.process.php', {
						method: 'POST', 
						body: new URLSearchParams({
							id: id,
							_method: 'delete'
						})
					});
					

					const resData = await worksJson(response);
					alert(resData.message);
				
					// 수정
					if (resData.success) {
					  const params = new URLSearchParams(location.search);
					  params.delete('id'); // 상세 id만 제거
					  const qs = params.toString();
					  location.href = './index.php' + (qs ? `?${qs}` : '');
					}
				}
				catch (error)
				{
					alert(workError(error));
				}
				finally { workRequestPending = false; }
			});
		});
	}

	on(form, 'submit', async (e) => {
		e.preventDefault(); 
		if (workRequestPending) return;

		const params = new URLSearchParams(location.search);
		params.delete('id'); 

		const fd = new FormData(form);
		workRequestPending = true;
		const submitButtons = form.querySelectorAll('[type="submit"]');
		submitButtons.forEach(button => button.disabled = true);
		form.setAttribute('aria-busy', 'true');
		try
		{
			const response = await fetch('./ajax/works.process.php', {
				method: 'POST', 
				body: fd
			});
			

			const resData = await worksJson(response);
			alert(resData.message);

			// 수정
			if (resData.success) {
			  const qs = params.toString();
			  location.href = './index.php' + (qs ? `?${qs}` : '');
			}
		}
		catch (error)
		{
			alert(workError(error));
		}
		finally { workRequestPending = false; submitButtons.forEach(button => button.disabled = false); form.removeAttribute('aria-busy'); }
		
	});
}

function bindPrice(){
	const priceHook = qs('#price-hook'); 
	const priceInput = qs('input[name=price]'); 

	if(!priceHook || !priceInput) return;

	let jsonData;
	try { jsonData = priceInput.value ? JSON.parse(priceInput.value) : Array(10).fill(null).map(() => ({name: '', price: ''})); }
	catch { return; } // Preserve legacy text rather than breaking all form handlers.
	if (!Array.isArray(jsonData)) return;
	if(!priceInput.value){
		priceInput.value = JSON.stringify(jsonData);
	}

	function getPriceInput({name, price}, index){
		const el = document.createElement('div');
		el.style.display = 'flex'; 
		el.style.alignItems = 'center'; 
		el.style.gap = '12px'; 

		for (const [field, value, placeholder] of [['name', name, '좌석을 입력해주세요.'], ['price', price, '해당 좌석에 가격을 입력해주세요.']]) {
			const input = document.createElement('input'); input.type = 'text'; input.className = 'no-input--detail';
			input.placeholder = placeholder; input.value = String(value ?? ''); input.dataset.field = field; el.append(input);
		}
		
		on(el, 'input', function(e){
			const t = e.target; 
			const field = t.dataset.field;
			jsonData[index][field] = t.value; 
			priceInput.value = JSON.stringify(jsonData);
		});

		return el; 
	}

	jsonData.forEach((item, index) => {
		if (!item || typeof item !== 'object') return;
		const el = getPriceInput(item, index);
		priceHook.append(el);
	});
}



function handleCheck(){
	const checkAllInput = qs('#chkAll');
	const checkAllBtn = qs('#check-all-btn');
	const uncheckAllBtn = qs('#uncheck-all-btn');
	const deleteCheckedBtn = qs('#check-delete-btn');
	
	const tableCheckboxes = qsAll('.no-table-responsive .no-chk-item'); 
	
	function handleAllCheck(e){
		const checked = e.target.checked;
		if(checked) {
			tableCheckboxes.forEach(chk => chk.checked = true);
		} else {
			tableCheckboxes.forEach(chk => chk.checked = false);
		}
	}

	function handleForceCheck(e){
		tableCheckboxes.forEach(chk => chk.checked = true);
	}

	function handleForceUncheck(e){
		tableCheckboxes.forEach(chk => chk.checked = false);
	}


	async function handleBatch(){
		if (workRequestPending) return;
		const checkedIds = [...tableCheckboxes].filter(chk => chk.checked).map(chk => Number(chk.value)) || []; 
		if (!checkedIds.length) { alert('삭제할 공연을 선택하세요.'); return; }
		if(!confirm('선택한 공연을 삭제하시겠습니까?')) return;
		workRequestPending = true;
	
		try
		{
			const response = await fetch('./ajax/works.process.php', {
				method: 'POST', 
				body: new URLSearchParams({
					_method: 'BATCH_DELETE',
					ids: JSON.stringify(checkedIds)
				})
			});
			const resData = await worksJson(response);
			alert(resData.message);
			if (resData.success) location.reload();
		}
		catch (err)
		{
			alert(workError(err));
		}
		finally { workRequestPending = false; }
	}

	on(checkAllInput, 'change', handleAllCheck);
	on(checkAllBtn, 'click', handleForceCheck);
	on(uncheckAllBtn, 'click', handleForceUncheck); 
	on(deleteCheckedBtn, 'click', handleBatch); 
}


document.addEventListener('DOMContentLoaded', async () => {
	await bindForm(); 
	bindPrice(); 
	handleCheck(); 

	if ($('#contents').length > 0) {
		$('#contents').summernote({
			lang: 'ko-KR',
			height: 400,
			callbacks: {
				onImageUpload: function(files) {
					const formData = new FormData();
					formData.append('file', files[0]);

					fetch((window.BLUE_ADMIN_BASE ?? '/admin') + '/pages/works/upload.php', {
						method: 'POST',
						body: formData
					}).then(worksJson)
					  .then(data => {
						  if (data.success) {
							  // 업로드 성공 시 에디터에 이미지 추가
							  $('#contents').summernote('insertImage', data.url);
						  } else {
							  alert(data.message || '이미지 업로드에 실패했습니다.');
						  }
					  })
					  .catch(error => alert(workError(error)));
				}
			} // callback
		});
	}

		if ($('#price').length > 0) {
			$('#price').summernote({
				lang: 'ko-KR',
				height: 400,
				callbacks: {
					onImageUpload: function(files) {
						const formData = new FormData();
						formData.append('file', files[0]);
						fetch((window.BLUE_ADMIN_BASE ?? '/admin') + '/pages/works/upload.php', {
							method: 'POST',
							body: formData
						}).then(worksJson)
						  .then(data => {
							  if (data.success) {
								  // 업로드 성공 시 에디터에 이미지 추가
								  $('#price').summernote('insertImage', data.url);
							  } else {
								  alert(data.message || '이미지 업로드에 실패했습니다.');
							  }
						  })
						  .catch(error => alert(workError(error)));
					}
				} // callback
			});
		}




});
