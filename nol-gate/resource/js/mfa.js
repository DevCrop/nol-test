(() => {
  const timer = document.querySelector("[data-mfa-expires]");
  if (!timer) return;

  const output = timer.querySelector("[data-mfa-countdown]");
  const submit = document.querySelector('[data-mfa-form] [type="submit"]');
  const expiresAt = Number(timer.dataset.mfaExpires) * 1000;

  const format = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    return `${String(minutes).padStart(2, "0")}:${String(seconds % 60).padStart(2, "0")}`;
  };

  const update = () => {
    const seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
    output.textContent = seconds > 0 ? format(seconds) : "만료됨";
    timer.classList.toggle("is-expired", seconds === 0);
    if (submit) submit.disabled = seconds === 0;
    return seconds;
  };

  update();
  const interval = window.setInterval(() => {
    if (update() === 0) window.clearInterval(interval);
  }, 1000);
})();
