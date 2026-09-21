/*function show(something){
	return console.log(something);
}

show('[modal.js]');



const catgAddButtons = document.querySelectorAll('.no-catg-btn');
const catgModals = document.querySelectorAll('.no-modal__wrap');
const catgCloseButtons = document.querySelectorAll('.no-modal__close');
const cateEditButtons = document.querySelectorAll('.no-catg-edit');

const CATG_VALUES = {
	'inp_lg': null,
	'inp_md': null,
	'inp_sm': null,
	'inp_item': null,
}
const modalInputs = document.querySelectorAll('.no-modal__input');

for (const inp of modalInputs){
	CATG_VALUES[inp.id] = inp.value;
}


function showModal(e){
	e.preventDefault();
	const catgType = e.target.dataset.catgType;
	const isEdit = e.target.dataset.edit;
	const selectedModal = document.getElementById(`no-modal__${catgType}`);
	selectedModal.style.display = 'block';

	if(isEdit){
		selectedModal.querySelector('input').value = CATG_VALUES[`inp_${catgType}`];
		return;
	}

	selectedModal.querySelector('input').value = null;
}

function addEventToButton(buttons){
	for (const btn of buttons){
		btn.addEventListener('click', showModal);
	}
}

function hideModal(e){
	for (const modal of catgModals){
		modal.style.display = 'none';
	}
}

addEventToButton(catgAddButtons);
addEventToButton(cateEditButtons);


for (const btn of catgCloseButtons){
	btn.addEventListener('click', hideModal);
}
*/



function doAddEdit(_category_type, _submit_type){
	
	var title = "";
	if(_category_type == "b")
		title = "대분류";
	else if(_category_type == "m")
		title = "업종";
	else if(_category_type == "s")
		title = "소업종";
	else if(_category_type == "i")
		title = "아이템";
	$(".no-modal__name").text(title);
	$("#no-modal__lg").show();

}

function doModalClose(_id){
	$("#"+_id).hide();
}