<div class="no-card admin-log-filters">
    <div class="no-card-header"><h2 class="no-card-title">기록 검색</h2></div>
    <div class="no-card-body admin-log-filter-grid">
        <label class="admin-log-field" for="entity">
            대상
            <select name="entity" id="entity" class="no-input--detail">
                <option value="">전체 대상</option>
                <?php foreach ($logFilterEntities as $code => $label): ?>
                <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="admin-log-field" for="action">
            유형
            <select name="action" id="action" class="no-input--detail">
                <option value="">전체 유형</option>
                <?php foreach ($logFilterActions as $code => $label): ?>
                <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="admin-log-field admin-log-query" for="keyword">
            검색어
            <input class="no-input--detail" type="search" id="keyword" name="keyword" maxlength="100"
                   placeholder="<?= htmlspecialchars($logFilterPlaceholder, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <div class="admin-log-filter-actions">
            <button type="submit" class="no-btn no-btn--main">검색</button>
            <button type="button" id="audit-reset" class="no-btn no-btn--normal">초기화</button>
        </div>
    </div>
</div>
