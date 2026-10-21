<?php // Shared markup, using the existing admin search components. ?>
<div class="no-search no-toolbar-container">
    <div class="no-card">
        <div class="no-card-header"><h2 class="no-card-title">검색 조건</h2></div>
        <div class="no-card-body">
            <p class="no-admin-acl__hint"><?= htmlspecialchars($logFilterHelp, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="no-admin-column">
                <div class="no-admin-block">
                    <h3 class="no-admin-title"><label for="entity"><?= htmlspecialchars($logFilterTargetLabel, ENT_QUOTES, 'UTF-8') ?></label></h3>
                    <div class="no-admin-content">
                        <select name="entity" id="entity">
                            <option value="">전체 대상</option>
                            <?php foreach ($logFilterEntities as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="no-admin-block">
                    <h3 class="no-admin-title"><label for="action"><?= htmlspecialchars($logFilterActionLabel, ENT_QUOTES, 'UTF-8') ?></label></h3>
                    <div class="no-admin-content">
                        <select name="action" id="action">
                            <option value="">전체 유형</option>
                            <?php foreach ($logFilterActions as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="no-admin-block wide">
                    <h3 class="no-admin-title"><label for="keyword">검색어</label></h3>
                    <div class="no-search-wrap">
                        <div class="no-search-input">
                            <i class="bx bx-search-alt-2" aria-hidden="true"></i>
                            <input type="search" id="keyword" name="keyword" maxlength="100" placeholder="<?= htmlspecialchars($logFilterPlaceholder, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="no-search-btn audit-search-actions">
                            <button type="submit" class="no-btn no-btn--main no-btn--search">검색</button>
                            <button type="button" id="audit-reset" class="no-btn no-btn--normal no-btn--search">초기화</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
