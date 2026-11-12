<?php
$accountGrants=\Security\AdminAcl::grants($account);
$accountIsSuper=($account['role_code']??'admin')==='super';
?>
<input type="hidden" name="permissions_present" value="1">
<div class="no-admin-block account-permissions-row">
    <h3 class="no-admin-title" id="account-permissions-title">메뉴별 권한</h3>
    <div class="no-admin-content account-permissions" data-account-permissions>
        <p class="account-help" data-permission-role-hint><?=$accountIsSuper?'최고 관리자는 모든 메뉴 권한이 적용됩니다.':'선택한 메뉴 권한만 적용됩니다.'?></p>
        <div class="account-permissions__scroll" role="region" aria-labelledby="account-permissions-title" tabindex="0">
        <table class="no-table account-permissions__table">
            <colgroup><col class="account-permissions__menu"><?php for($i=0;$i<4;$i++): ?><col class="account-permissions__action"><?php endfor; ?></colgroup>
            <thead><tr><th scope="col">메뉴</th><?php foreach (['조회','등록','수정','삭제'] as $permissionLabel): ?><th scope="col"><?=$e($permissionLabel)?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach (\Security\AdminAcl::labels() as $menuKey=>$menuLabel): ?><tr data-permission-row>
                <th scope="row"><?=$e($menuLabel)?></th>
                <?php foreach (['view'=>'조회','create'=>'등록','update'=>'수정','delete'=>'삭제'] as $action=>$actionLabel): ?>
                <td><label class="no-checkbox-form"><input type="checkbox" name="permissions[<?=$e($menuKey)?>][<?=$e($action)?>]" value="1" data-permission-action="<?=$e($action)?>" data-regular-checked="<?=!empty($accountGrants[$menuKey][$action])?'1':'0'?>" <?=$accountIsSuper||!empty($accountGrants[$menuKey][$action])?'checked':''?> <?=$accountIsSuper?'disabled':''?> aria-label="<?=$e($menuLabel.' '.$actionLabel)?>"><span><i class="bx bx-check"></i></span></label></td>
                <?php endforeach; ?>
            </tr><?php endforeach; ?></tbody>
        </table>
        </div>
    </div>
</div>
<script src="<?=blue_admin_base()?>/resource/js/account-permissions.js?v=<?=filemtime(dirname(__DIR__).'/resource/js/account-permissions.js')?>" defer></script>
