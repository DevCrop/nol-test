// UI transitions only. Fixed synthetic password; no requests, storage or clipboard access.
const confirmPanel = document.querySelector('#preview-confirm');
const issuedPanel = document.querySelector('#preview-issued');
const accountFields = document.querySelector('#preview-account-fields');
const resetButton = document.querySelector('#preview-reset-password');
const createButton = document.querySelector('#preview-create-account');
function reveal(panel) {
  panel.hidden = false;
  const heading = panel.querySelector('h2');
  heading.focus();
  panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function showIssued() {
  confirmPanel.hidden = true;
  accountFields.hidden = true;
  document.querySelector('#preview-issued-uid').textContent = document.querySelector('#preview-uid').value.trim() || 'preview_admin';
  reveal(issuedPanel);
}
createButton?.addEventListener('click', () => {
  // Navigate to a fixed sample list; no account creation or form values are sent.
  window.location.assign('./temp-account-list-preview.php?created=1');
});
resetButton?.addEventListener('click', () => { issuedPanel.hidden = true; reveal(confirmPanel); });
document.querySelector('#preview-cancel').addEventListener('click', () => { confirmPanel.hidden = true; resetButton?.focus(); });
document.querySelector('#preview-confirm-reset').addEventListener('click', showIssued);
document.querySelector('#preview-close-issued').addEventListener('click', () => { issuedPanel.hidden = true; accountFields.hidden = false; (resetButton || createButton)?.focus(); });
// Presentation-only role selection: no permission persistence.
document.querySelectorAll('[name="preview-role"]').forEach(radio => radio.addEventListener('change', () => {
  const acl = document.querySelector('#acl-block');
  if (acl) acl.hidden = radio.value === 'super';
}));
