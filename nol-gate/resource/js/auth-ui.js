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

  document.querySelectorAll("form[data-loading-message]").forEach((form) => {
    form.addEventListener("submit", () => {
      if (!form.checkValidity()) return;
      const button = form.querySelector('[type="submit"]');
      if (button) button.disabled = true;
      show(form.dataset.loadingMessage);
    });
  });

  document.querySelectorAll("a[data-loading-message]").forEach((link) => {
    link.addEventListener("click", () => show(link.dataset.loadingMessage));
  });

  window.addEventListener("pageshow", hide);
})();
