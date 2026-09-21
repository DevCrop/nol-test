/**
 * Helper 유틸리티 함수들
 * - 전화번호 포맷팅, 날짜 포맷팅 등
 */

/**
 * 전화번호 포맷팅 (자동 하이픈 추가)
 * 다국어 지원: 한국(11자), 일본(11자), 중국(11자), 미국(10자), 국제형식(최대 15자)
 * @param {string} value - 전화번호 값
 * @param {number} maxLength - 최대 숫자 길이 (기본값: 15자)
 * @returns {string} - 포맷팅된 전화번호
 */
export function formatPhoneNumber(value, maxLength = 15) {
  // 숫자만 추출
  const numbers = value.replace(/[^\d]/g, "");

  // 최대 길이 제한 (숫자만)
  const limitedNumbers = numbers.slice(0, maxLength);

  // 길이에 따라 포맷팅
  if (limitedNumbers.length <= 3) {
    return limitedNumbers;
  } else if (limitedNumbers.length <= 7) {
    // 010-1234
    return limitedNumbers.replace(/(\d{3})(\d{1,4})/, "$1-$2");
  } else if (limitedNumbers.length <= 10) {
    // 010-123-4567 (지역번호 2자리)
    return limitedNumbers.replace(/(\d{2,3})(\d{3,4})(\d{4})/, "$1-$2-$3");
  } else if (limitedNumbers.length <= 11) {
    // 010-1234-5678 (한국/일본/중국 휴대폰)
    return limitedNumbers.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
  } else {
    // 15자리 이상: 국제 전화번호 형식 (+82-10-1234-5678)
    // 최대 15자리까지만 허용
    return limitedNumbers.replace(
      /(\d{2,3})(\d{3,4})(\d{4})(\d{0,4})/,
      function (match, p1, p2, p3, p4) {
        if (p4) {
          return `${p1}-${p2}-${p3}-${p4}`;
        }
        return `${p1}-${p2}-${p3}`;
      }
    );
  }
}

/**
 * 전화번호 입력 필드에 자동 포맷팅 적용
 * @param {HTMLInputElement} input - 전화번호 input 요소
 * @param {number} maxLength - 최대 숫자 길이 (기본값: 15자)
 */
export function applyPhoneFormat(input, maxLength = 15) {
  if (!input) return;

  // maxlength 속성 설정 (하이픈 포함 최대 20자)
  if (!input.hasAttribute("maxlength")) {
    input.setAttribute("maxlength", "20");
  }

  input.addEventListener("input", function (e) {
    const cursorPosition = this.selectionStart;
    const beforeLength = this.value.length;

    // 포맷팅 적용 (최대 길이 제한 포함)
    const formatted = formatPhoneNumber(this.value, maxLength);
    this.value = formatted;

    // 커서 위치 조정 (하이픈이 추가된 경우 커서 위치 유지)
    const afterLength = formatted.length;
    const diff = afterLength - beforeLength;

    if (diff > 0) {
      this.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
    } else {
      this.setSelectionRange(cursorPosition, cursorPosition);
    }
  });

  // 붙여넣기 시에도 적용
  input.addEventListener("paste", function (e) {
    setTimeout(() => {
      this.value = formatPhoneNumber(this.value, maxLength);
    }, 10);
  });
}

/**
 * 페이지 내 모든 전화번호 입력 필드에 자동 포맷팅 적용
 * data-phone 속성이나 name="phone" 또는 id에 "phone"이 포함된 input에 적용
 * data-phone-max 속성으로 최대 길이 커스터마이징 가능 (기본값: 15자)
 */
export function initPhoneFormatting() {
  // data-phone 속성이 있는 input
  const phoneInputs = document.querySelectorAll("input[data-phone]");

  // name이나 id에 phone이 포함된 input (type="tel" 또는 일반 text)
  const phoneNameInputs = document.querySelectorAll(
    'input[name*="phone"], input[id*="phone"], input[type="tel"]'
  );

  // 모든 전화번호 input에 포맷팅 적용
  [...phoneInputs, ...phoneNameInputs].forEach((input) => {
    // data-phone-max 속성으로 최대 길이 커스터마이징 가능
    const maxLength = input.dataset.phoneMax
      ? parseInt(input.dataset.phoneMax, 10)
      : 15; // 기본값: 15자 (다국어 지원)
    applyPhoneFormat(input, maxLength);
  });
}

/**
 * 생년월일 포맷팅 (YYYYMMDD)
 * @param {string} value - 생년월일 값
 * @returns {string} - 포맷팅된 생년월일
 */
export function formatBirthDate(value) {
  // 숫자만 추출
  const numbers = value.replace(/[^\d]/g, "");

  // 최대 8자리까지만
  return numbers.slice(0, 8);
}

/**
 * 생년월일 입력 필드에 자동 포맷팅 적용
 * @param {HTMLInputElement} input - 생년월일 input 요소
 */
export function applyBirthFormat(input) {
  if (!input) return;

  input.addEventListener("input", function (e) {
    this.value = formatBirthDate(this.value);
  });

  input.addEventListener("paste", function (e) {
    setTimeout(() => {
      this.value = formatBirthDate(this.value);
    }, 10);
  });
}

/**
 * 페이지 내 모든 생년월일 입력 필드에 자동 포맷팅 적용
 */
export function initBirthFormatting() {
  const birthInputs = document.querySelectorAll(
    'input[data-birth], input[name*="birth"], input[id*="birth"]'
  );

  birthInputs.forEach((input) => {
    applyBirthFormat(input);
  });
}

/**
 * 숫자만 입력 가능하도록 제한
 * @param {HTMLInputElement} input - input 요소
 */
export function numbersOnly(input) {
  if (!input) return;

  input.addEventListener("input", function (e) {
    this.value = this.value.replace(/[^\d]/g, "");
  });
}

// 모든 함수가 이미 export 되었으므로 추가 코드 불필요
