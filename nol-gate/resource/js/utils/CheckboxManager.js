// utils/CheckboxManager.js

export class CheckboxManager {
  /**
   * @param {Object} options
   * @param {string} options.itemSelector - 개별 체크박스 셀렉터
   * @param {string} options.masterSelector - 마스터 체크박스 셀렉터
   * @param {string} options.selectAllBtnSelector - 전체 선택 버튼 셀렉터
   * @param {string} options.deselectAllBtnSelector - 선택 해제 버튼 셀렉터
   * @param {string} options.deleteBtnSelector - 선택 삭제 버튼 셀렉터
   * @param {function} options.onDelete - 삭제 콜백 (선택된 값 배열 전달됨)
   */

  constructor({
    itemSelector,
    masterSelector,
    selectAllBtnSelector,
    deselectAllBtnSelector,
    deleteBtnSelector,
    onDelete,
  }) {
    this.itemSelector = itemSelector;
    this.masterCheckbox = document.querySelector(masterSelector);
    this.selectAllBtn = document.querySelector(selectAllBtnSelector);
    this.deselectAllBtn = document.querySelector(deselectAllBtnSelector);
    this.deleteBtn = document.querySelector(deleteBtnSelector);
    this.onDelete = onDelete;

    this.bindEvents();
  }

  get items() {
    return document.querySelectorAll(this.itemSelector);
  }

  get checkedItems() {
    return document.querySelectorAll(`${this.itemSelector}:checked`);
  }

  getSelectedValues() {
    return Array.from(this.checkedItems).map((el) => el.value);
  }

  selectAll() {
    this.items.forEach((el) => (el.checked = true));
    this.syncMasterCheckbox();
  }

  deselectAll() {
    this.items.forEach((el) => (el.checked = false));
    this.syncMasterCheckbox();
  }

  syncMasterCheckbox() {
    const all = this.items.length;
    const checked = this.checkedItems.length;
    if (this.masterCheckbox) {
      this.masterCheckbox.checked = all > 0 && all === checked;
    }
  }

  bindEvents() {
    // 마스터 체크박스
    if (this.masterCheckbox) {
      this.masterCheckbox.addEventListener("change", () => {
        this.masterCheckbox.checked ? this.selectAll() : this.deselectAll();
      });
    }

    // 개별 체크박스 변경 시 마스터 체크박스 상태 갱신
    this.items.forEach((el) => {
      el.addEventListener("change", () => this.syncMasterCheckbox());
    });

    // 전체 선택 버튼
    if (this.selectAllBtn) {
      this.selectAllBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.selectAll();
      });
    }

    // 선택 해제 버튼
    if (this.deselectAllBtn) {
      this.deselectAllBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.deselectAll();
      });
    }

    // 선택 삭제 버튼
    if (this.deleteBtn && typeof this.onDelete === "function") {
      this.deleteBtn.addEventListener("click", () => {
        const selected = this.getSelectedValues();
        if (selected.length === 0) {
          alert("삭제할 항목을 선택해주세요.");
          return;
        }

        if (!confirm("정말 삭제하시겠습니까?")) return;
        this.onDelete(selected);
      });
    }
  }
}
