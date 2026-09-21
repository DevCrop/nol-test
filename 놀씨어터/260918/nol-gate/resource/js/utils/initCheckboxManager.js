import { CheckboxManager } from "./CheckboxManager.js";

export function initCheckboxManager(onDelete) {
  new CheckboxManager({
    itemSelector: ".no-chk",
    masterSelector: "#selectAllCheckbox",
    selectAllBtnSelector: '[data-action="selectAll"]',
    deselectAllBtnSelector: '[data-action="deselectAll"]',
    deleteBtnSelector: '[data-action="deleteSelected"]',
    onDelete,
  });
}
