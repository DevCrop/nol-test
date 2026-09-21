/**
 * 라디오 버튼 값에 따라 요소를 show/hide 또는 enable/disable 처리
 *
 * @param {string} radioName        name 속성 (예: "has_link")
 * @param {string[]} targetIds      영향을 받을 element ID 목록
 * @param {string} toggleOnValue    지정된 값일 때 토글 수행
 * @param {string} mode             "visibility" | "enable"
 */
export function attachRadioToggle({
  radioName,
  targetIds = [],
  toggleOnValue = "1",
  mode = "visibility",
}) {
  const radios = document.querySelectorAll(`input[name="${radioName}"]`);

  const toggleFn = () => {
    const isToggled =
      document.querySelector(`input[name="${radioName}"]:checked`)?.value ===
      toggleOnValue;

    targetIds.forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;

      if (mode === "visibility") {
        el.style.display = isToggled ? "flex" : "none";

        if (!isToggled) {
          const inputs = el.querySelectorAll("input, textarea, select");
          inputs.forEach((input) => {
            if (input.type === "checkbox" || input.type === "radio") {
              input.checked = false;
            } else {
              input.value = "";
            }
          });
        }
      } else if (mode === "enable") {
        el.disabled = isToggled;
        if (isToggled) el.value = "";
      }
    });
  };

  radios.forEach((radio) => {
    radio.addEventListener("change", toggleFn);
  });

  toggleFn(); // 초기 상태 적용
}
