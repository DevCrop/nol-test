<dialog id="account-credential-dialog" data-account-credential-dialog aria-labelledby="account-credential-title">
    <div class="no-card">
        <div class="no-card-header no-card-header--detail"><h2 class="no-card-title" id="account-credential-title">계정정보 복사</h2><button type="button" class="no-btn no-btn--sm" data-credential-close aria-label="닫기">×</button></div>
        <div class="no-card-body no-admin-column no-admin-column--detail">
            <div class="no-admin-block"><h3 class="no-admin-title"><label for="credential-uid">아이디</label></h3><div class="no-admin-content"><input id="credential-uid" class="no-input--detail" readonly></div></div>
            <div class="no-admin-block"><h3 class="no-admin-title"><label for="credential-password">임시비밀번호</label></h3><div class="no-admin-content"><input id="credential-password" type="password" class="no-input--detail" readonly autocomplete="off"><button type="button" class="no-btn no-btn--sm" id="credential-toggle" aria-pressed="false">보기</button></div></div>
            <p class="no-admin-acl__hint">이 화면을 닫으면 다시 확인할 수 없습니다. 전달하지 못했다면 재발급해 주세요.<br>담당자는 로그인·이메일 인증 후 새 비밀번호로 변경해야 합니다.</p>
            <p class="no-admin-acl__hint" id="credential-expires"></p>
            <div class="no-items-center center"><button type="button" class="no-btn no-btn--big no-btn--normal" data-credential-close>닫기</button><button type="button" class="no-btn no-btn--big no-btn--main" id="credential-copy">계정정보 복사</button></div>
        </div>
    </div>
</dialog>
