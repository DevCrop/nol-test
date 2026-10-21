<?php // Included only for the development-only synthetic account row. ?>
<dialog id="temporary-ui-dialog" data-temporary-ui-dialog aria-labelledby="temporary-ui-title" aria-describedby="temporary-ui-status">
    <div class="no-card">
        <div class="no-card-header no-card-header--detail">
            <h2 class="no-card-title" id="temporary-ui-title">계정정보 복사</h2>
            <button type="button" class="no-btn no-btn--sm no-btn--normal" data-temporary-ui-close aria-label="닫기"><i class="bx bx-x" aria-hidden="true"></i></button>
        </div>
        <div class="no-card-body no-admin-column no-admin-column--detail">
            <div class="no-admin-block">
                <h3 class="no-admin-title"><label for="temporary-ui-uid">아이디</label></h3>
                <div class="no-admin-content"><input type="text" id="temporary-ui-uid" class="no-input--detail" readonly value="demo_admin"></div>
            </div>
            <div class="no-admin-block">
                <h3 class="no-admin-title"><label for="temporary-ui-password">임시비밀번호</label></h3>
                <div class="no-admin-content">
                    <input type="password" id="temporary-ui-password" class="no-input--detail" readonly autocomplete="off" value="DEMO-ONLY!482">
                    <button type="button" class="no-btn no-btn--sm no-btn--normal" id="temporary-ui-toggle" aria-controls="temporary-ui-password" aria-pressed="false">보기</button>
                </div>
            </div>
            <p class="no-admin-acl__hint" id="temporary-ui-status">디자인 예시 · 실제 로그인에는 사용할 수 없습니다.</p>
            <div class="no-items-center center">
                <button type="button" class="no-btn no-btn--big no-btn--normal" data-temporary-ui-close>닫기</button>
                <button type="button" class="no-btn no-btn--big no-btn--main" id="temporary-ui-copy">계정정보 복사</button>
            </div>
        </div>
    </div>
</dialog>
