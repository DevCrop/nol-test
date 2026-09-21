const $ = (selector) => {
  return document.querySelector(selector);
};

const initLogin = () => {
  const invalidText = document.querySelectorAll('.no-invalid');
  const user = {
    id: $('#uid'),
    pwd: $('#upwd'),
    captcha: $('#r_captcha'),
  };
  const pwdButton = {
    el: $('.no-pwd-btn'),
    text: $('.no-pwd-text'),
    icon: $('.no-pwd-icon'),
  };
  const submitButton = $('.no-btn--submit');

  const showErrorText = (input, message) => {
    input.classList.add('invalid');
    input.parentElement.querySelector('.no-invalid').classList.add('show');
	input.parentElement.querySelector('.no-invalid').innerText = message;
  };
  const hideErrorText = (input) => {
    input.classList.remove('invalid');
    input.parentElement.querySelector('.no-invalid').classList.remove('show');
  };

  const isEmpty = (input, type = 'text') => {
    if (type === 'number') {
      return input.value.trim() === '';
    }
    return input.value.trim() === '' || input.value.trim().length === 0;
  };

  const switchPasswordType = () => {
    if (user.pwd.type === 'text') {
      user.pwd.type = 'password';
      pwdButton.text.textContent = 'Show';
      pwdButton.icon.classList.replace('fa-eye-slash', 'fa-eye');
      return;
    }

    user.pwd.type = 'text';
    pwdButton.text.textContent = 'Hide';
    pwdButton.icon.classList.replace('fa-eye', 'fa-eye-slash');
  };

  const incorrectCaptcha = () => {
	  alert('이상합니다.');
	validation({inputValue: user.captcha, isValid: isValid.captcha, type: 'number'}, '보안코드가 일치하지 않습니다. 정확히 입력해주세요.');
  }

  const processLogin = (e) => {
    e.preventDefault();
    const loginForm = $('#login_form');

    const isValid = {
      id: false,
      pwd: false,
      captcha: false,
    };

    const validation = (validInfo, message) => {
      if (isEmpty(validInfo.inputValue, validInfo.type)) {
        showErrorText(validInfo.inputValue, message);
        isValid[validInfo.isValid] = false;
      } else {
        hideErrorText(validInfo.inputValue);
        isValid[validInfo.isValid] = true;
      }
    };

    // validate input value
    validation({inputValue: user.id, isValid: "id" }, '아이디를 입력하세요.');
    validation({inputValue: user.pwd, isValid: "pwd"}, '비밀번호를 입력하세요.');
    validation({inputValue: user.captcha, isValid:"captcha", type: 'number'}, '보안코드를 5자리를 입력하세요.');

	// match id and password
	/*
	const matchUserInfo = (isMatch = true) => {
		const loginError = $('.no-login-error');

		if(!isMatch){
			loginError.classList.add('show');
			isValid.match = false;
			return; 
		}
		loginError.classList.add('hide');
		isValid.match = true;
	}
	*/

    // focus invalid text
    const invalidText = loginForm.querySelector('.no-invalid.show');
    if (invalidText) {
      invalidText.parentElement.querySelector('input').focus();
    }

    // check all input is success
    for (input in isValid) {
		
      if (isValid[input] === false) return;
    }

    loginForm.submit();
	console.log(loginForm);
  };

  pwdButton.el.addEventListener('click', switchPasswordType);
  submitButton.addEventListener('click', processLogin);
};

initLogin();
