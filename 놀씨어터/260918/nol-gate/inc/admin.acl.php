<?php
/** @var array<string, array{view?:bool,create?:bool,update?:bool,delete?:bool}> $aclGrants */
$aclGrants = $aclGrants ?? [];
$aclMenus = Acl::assignableMenus();
$aclActions = ['view' => '조회', 'create' => '등록', 'update' => '수정', 'delete' => '삭제'];
?>
<div class="no-admin-block wide no-admin-acl" id="acl-block" data-acl-form<?= !empty($aclStartHidden) ? ' hidden' : '' ?>>
    <h3 class="no-admin-title">메뉴 권한</h3>
    <div class="no-admin-content">
        <p class="no-admin-acl__hint">일반 관리자가 쓸 메뉴만 선택하세요. 등록·수정·삭제는 조회가 필요합니다.</p>
        <div class="no-table-responsive no-admin-acl__table">
            <table class="no-table">
                <thead>
                    <tr>
                        <th scope="col">메뉴</th>
                        <?php foreach ($aclActions as $label): ?>
                        <th scope="col" class="no-admin-acl__col"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aclMenus as $key => $title): ?>
                    <?php $row = $aclGrants[$key] ?? []; ?>
                    <tr data-acl-row>
                        <th scope="row"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></th>
                        <?php foreach ($aclActions as $action => $label): ?>
                        <td class="no-admin-acl__col">
                            <div class="no-checkbox-form">
                                <label for="acl-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>-<?= $action ?>">
                                    <input type="checkbox"
                                        name="acl[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>][<?= $action ?>]"
                                        id="acl-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>-<?= $action ?>"
                                        value="1"
                                        data-acl-action="<?= $action ?>"
                                        <?= !empty($row[$action]) ? 'checked' : '' ?>>
                                    <span><i class="bx bxs-check-square"></i></span>
                                </label>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="no-admin-acl__error" data-acl-error hidden></p>
    </div>
</div>
