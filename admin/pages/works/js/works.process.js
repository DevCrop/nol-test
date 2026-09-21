
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
				if(!confirm('정말로 삭제하시겠습니까?')) return; 
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
					
					if(!response.ok){
						throw new Error('네트워크 에러가 발생하였습니다.');
					}

					const resData = await response.json();
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
					alert(error.message);
				}
			});
		});
	}

	on(form, 'submit', async (e) => {
		e.preventDefault(); 

		const params = new URLSearchParams(location.search);
		params.delete('id'); 

		fd = new FormData(form);
		
		console.log(Object.fromEntries(fd));
		try
		{
			const response = await fetch('./ajax/works.process.php', {
				method: 'POST', 
				body: fd
			});
			
			if(!response.ok){
				throw new Error('네트워크 에러가 발생하였습니다.');
			}

			const resData = await response.json();
			alert(resData.message);

			// 수정
			if (resData.success) {
			  const qs = params.toString();
			  location.href = './index.php' + (qs ? `?${qs}` : '');
			}
		}
		catch (error)
		{
			alert(error.message);
		}
		
	});
}

function bindPrice(){
	const priceHook = qs('#price-hook'); 
	const priceInput = qs('input[name=price]'); 

	if(!priceHook) return; 

	const jsonData = priceInput.value ? JSON.parse(priceInput.value) : Array(10).fill(null).map(() => ({name: '', price: ''}));
	if(!priceInput.value){
		priceInput.value = JSON.stringify(jsonData);
	}

	function getPriceInput({name, price}, index){
		const el = document.createElement('div');
		el.style.display = 'flex'; 
		el.style.alignItems = 'center'; 
		el.style.gap = '12px'; 

		el.innerHTML = `
			<input type="text" class="no-input--detail" placeholder="좌석을 입력해주세요." value="${name}" data-field="name"/>
			<input type="text" class="no-input--detail" placeholder="해당 좌석에 가격을 입력해주세요." value="${price}" data-field="price"/>
		`;
		
		on(el, 'input', function(e){
			const t = e.target; 
			const field = t.dataset.field;
			jsonData[index][field] = t.value; 
			priceInput.value = JSON.stringify(jsonData);
		});

		return el; 
	}

	jsonData.forEach((item, index) => {
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
		if(!confirm('현재 페이지의 모든 공연들이 삭제됩니다. 정말로 삭제하시겠습니까?')) return;
		const checkedIds = [...tableCheckboxes].filter(chk => chk.checked).map(chk => Number(chk.value)) || []; 
	
		try
		{
			const response = fetch('./ajax/works.process.php', {
				method: 'POST', 
				body: new URLSearchParams({
					_method: 'BATCH_DELETE',
					ids: JSON.stringify(checkedIds)
				})
			});
			if(!response.ok){
				throw new Error(`네트워크 에러가 발생했습니다.`);
			}
			const resData = await response.json();
			console.log(resData); 
		}
		catch (err)
		{
			alert(err.message); 
		}
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

					fetch('/admin/pages/works/upload.php', {
						method: 'POST',
						body: formData
					}).then(response => response.json())
					  .then(data => {
						  if (data.success) {
							  // 업로드 성공 시 에디터에 이미지 추가
							  $('#contents').summernote('insertImage', data.url);
						  } else {
							  alert(data.message || '이미지 업로드에 실패했습니다.');
						  }
					  })
					  .catch(error => console.error(error));
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
						fetch('/admin/pages/works/upload.php', {
							method: 'POST',
							body: formData
						}).then(response => response.json())
						  .then(data => {
							  if (data.success) {
								  // 업로드 성공 시 에디터에 이미지 추가
								  $('#price').summernote('insertImage', data.url);
							  } else {
								  alert(data.message || '이미지 업로드에 실패했습니다.');
							  }
						  })
						  .catch(error => console.error(error));
					}
				} // callback
			});
		}




});
