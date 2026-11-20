(() => {
  const overlay = document.querySelector("[data-global-loading]");
  if (!overlay) return;

  const message = overlay.querySelector("[data-loading-message]");

  const hide = () => {
    overlay.hidden = true;
    document.body.classList.remove("no-is-loading");
  };

  const show = (text = "잠시만 기다려 주세요.") => {
    message.textContent = text;
    overlay.hidden = false;
    document.body.classList.add("no-is-loading");
  };

  window.NoAuthLoading = { show, hide };

  let submitting = false;
  const lockedButtons = new Set();
  const forms = Array.from(document.querySelectorAll("form[data-loading-message]"));
  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (submitting) {
        event.preventDefault();
        return;
      }
      if (event.defaultPrevented || !form.checkValidity()) return;
      submitting = true;
      forms.forEach((item) => { item.dataset.submitting = "true"; });
      document.querySelectorAll('button[type="submit"]').forEach((button) => {
        if (forms.includes(button.form) && !button.disabled) {
          lockedButtons.add(button);
          button.disabled = true;
        }
      });
      show(form.dataset.loadingMessage);
    });
  });

  document.querySelectorAll("a[data-loading-message]").forEach((link) => {
    link.addEventListener("click", () => show(link.dataset.loadingMessage));
  });

  window.addEventListener("pageshow", () => {
    submitting = false;
    forms.forEach((form) => { delete form.dataset.submitting; });
    lockedButtons.forEach((button) => { button.disabled = false; });
    lockedButtons.clear();
    hide();
  });
})();
