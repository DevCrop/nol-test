// Do not restore sensitive form values from browser back/forward memory.
window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
window.addEventListener('pagehide', () => {
  document.querySelectorAll('input[type="password"]').forEach(input => { input.value = ''; });
});
