const $ = (selector) => {
  return document.querySelector(selector);
};

const initLogin = () => {
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
  const loginForm = $('#login_form');

  const showErrorText = (input, message) => {
    input.classList.add('invalid');
    const invalid = input.closest('.no-auth-field').querySelector('.no-invalid');
    invalid.classList.add('show');
    invalid.querySelector('span').innerText = message;
  };
  const hideErrorText = (input) => {
    input.classList.remove('invalid');
    input.closest('.no-auth-field').querySelector('.no-invalid').classList.remove('show');
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
      pwdButton.el.setAttribute('aria-label', '비밀번호 보기');
      pwdButton.text.textContent = '보기';
      pwdButton.icon.classList.replace('fa-eye-slash', 'fa-eye');
      return;
    }

    user.pwd.type = 'text';
    pwdButton.text.textContent = '숨기기';
    pwdButton.el.setAttribute('aria-label', '비밀번호 숨기기');
    pwdButton.icon.classList.replace('fa-eye', 'fa-eye-slash');
  };

  const processLogin = (e) => {
    e.preventDefault();

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

    // focus invalid text
    const invalidText = loginForm.querySelector('.no-invalid.show');
    if (invalidText) {
      invalidText.closest('.no-auth-field').querySelector('input').focus();
    }

    // check all input is success
    if (Object.values(isValid).some((valid) => !valid)) return;

    submitButton.disabled = true;
    window.NoAuthLoading?.show('로그인 정보를 확인하고 있습니다.');
    loginForm.submit();
  };

  pwdButton.el.addEventListener('click', switchPasswordType);
  loginForm.addEventListener('submit', processLogin);
};

initLogin();
