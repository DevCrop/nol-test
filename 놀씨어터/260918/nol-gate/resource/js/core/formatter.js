// 전화번호 양식
export function attachPhoneAutoHyphen() {
  const phoneInputs = document.querySelectorAll('input[data-phone="true"]');
  if (!phoneInputs) {
    return;
  }
  phoneInputs.forEach((input) => {
    input.addEventListener("input", function (e) {
      let numbersOnly = e.target.value.replace(/[^0-9]/g, "");
      let formatted = "";
      if (numbersOnly.length < 4) {
        formatted = numbersOnly;
      } else if (numbersOnly.length < 8) {
        formatted = numbersOnly.slice(0, 3) + "-" + numbersOnly.slice(3);
      } else if (numbersOnly.length <= 11) {
        formatted =
          numbersOnly.slice(0, 3) +
          "-" +
          numbersOnly.slice(3, 7) +
          "-" +
          numbersOnly.slice(7);
      } else {
        formatted =
          numbersOnly.slice(0, 3) +
          "-" +
          numbersOnly.slice(3, 7) +
          "-" +
          numbersOnly.slice(7, 11);
      }
      e.target.value = formatted;
    });
  });
}
