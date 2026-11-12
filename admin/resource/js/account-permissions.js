(() => {
  'use strict';
  const root = document.querySelector('[data-account-permissions]');
  if (!root) return;
  const form = root.closest('form');
  if (!form) return;
  const inputs = Array.from(root.querySelectorAll('input[data-permission-action]'));
  const roles = form.querySelectorAll('input[name="role_code"]');
  const hint = root.querySelector('[data-permission-role-hint]');
  let regular = inputs.map(input => input.dataset.regularChecked === '1');
  let superMode = form.querySelector('input[name="role_code"]:checked')?.value === 'super';

  const applyRole = () => {
    const nextSuper = form.querySelector('input[name="role_code"]:checked')?.value === 'super';
    if (nextSuper && !superMode) regular = inputs.map(input => input.checked);
    inputs.forEach((input, index) => {
      if (nextSuper) input.checked = true;
      else if (superMode) input.checked = regular[index];
      input.disabled = nextSuper;
    });
    superMode = nextSuper;
    if (hint) hint.textContent = nextSuper ? '최고 관리자는 모든 메뉴 권한이 적용됩니다.' : '선택한 메뉴 권한만 적용됩니다.';
  };

  roles.forEach(role => role.addEventListener('change', applyRole));
  root.addEventListener('change', event => {
    const input = event.target;
    if (!inputs.includes(input) || input.disabled) return;
    const row = input.closest('[data-permission-row]');
    if (!row) return;
    if (input.dataset.permissionAction === 'view' && !input.checked) {
      row.querySelectorAll('input[data-permission-action]').forEach(item => { item.checked = false; });
    } else if (input.checked) {
      const view = row.querySelector('input[data-permission-action="view"]');
      if (view) view.checked = true;
    }
  });
  applyRole();
})();
