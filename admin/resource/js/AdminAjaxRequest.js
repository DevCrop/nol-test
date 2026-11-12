(() => {
  class AdminAjaxRequest {
    constructor(transport, notify) { this.transport = transport; this.notify = notify; }
    error(xhr) {
      const result = xhr?.responseJSON;
      this.notify(result?.msg || result?.message || '처리 중 문제가 발생했습니다. 다시 시도해주세요.');
    }
    send(options, state) {
      const readOnly = /mode=(board\.field|board\.category|category\.depth)(?:&|$)/.test(String(options.data));
      if (!readOnly && state.pending) return;
      if (!readOnly) state.pending = true;
      let finished = false;
      const finish = () => { if (finished) return; finished = true; if (!readOnly) state.pending = false; };
      const success = options.success, complete = options.complete;
      const owner = this;
      options.dataType = 'json';
      options.success = function (result, ...args) {
        finish();
        try {
          if (!result || typeof result !== 'object') throw new Error('Invalid response');
          success?.call(this, result, ...args);
        } catch { owner.error(); }
      };
      options.error = xhr => { finish(); owner.error(xhr); };
      options.complete = function (...args) {
        finish();
        try { complete?.apply(this, args); } catch { owner.error(); }
      };
      try { return this.transport.ajax(options); }
      catch { finish(); this.error(); }
    }
  }
  window.BlueAdminAjax = new AdminAjaxRequest(window.jQuery, message => alert(message));
})();
