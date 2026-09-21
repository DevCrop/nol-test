<?php

require_once __DIR__ . '/Acl.php';

class AclValidator
{
    public const MSG_ROLE = '계정 유형을 선택하세요. (최고 관리자 / 일반 관리자)';
    public const MSG_REQUIRED = '일반 관리자는 메뉴 권한을 1개 이상 선택하세요.';
    public const MSG_MENU = '허용되지 않은 메뉴 권한입니다.';
    public const MSG_ACTION = '허용되지 않은 권한 항목입니다.';
    public const MSG_VIEW = '등록·수정·삭제는 해당 메뉴 조회가 있어야 합니다.';
    public const MSG_ACCOUNT = '계정 메뉴 권한은 최고 관리자만 가질 수 있습니다.';

    /**
     * @param array<string, mixed> $post
     * @return array<string, array{view:bool,create:bool,update:bool,delete:bool}>
     */
    public static function assert(int $roleId, array $post): array
    {
        if ($roleId !== 1 && $roleId !== 2) {
            throw new RuntimeException(self::MSG_ROLE);
        }
        if ($roleId === 1) {
            return [];
        }

        $allowedMenus = Acl::assignableMenus();
        $raw = $post['acl'] ?? [];
        if (!is_array($raw)) {
            throw new RuntimeException(self::MSG_REQUIRED);
        }

        $out = [];
        foreach ($raw as $menu => $flags) {
            $menu = is_string($menu) ? $menu : '';
            if ($menu === 'account') {
                throw new RuntimeException(self::MSG_ACCOUNT);
            }
            if ($menu === '' || !isset($allowedMenus[$menu])) {
                throw new RuntimeException(self::MSG_MENU);
            }
            if (!is_array($flags)) {
                throw new RuntimeException(self::MSG_ACTION);
            }
            foreach (array_keys($flags) as $action) {
                if (!in_array((string) $action, Acl::ACTIONS, true)) {
                    throw new RuntimeException(self::MSG_ACTION);
                }
            }

            $view = self::flag($flags['view'] ?? null);
            $create = self::flag($flags['create'] ?? null);
            $update = self::flag($flags['update'] ?? null);
            $delete = self::flag($flags['delete'] ?? null);
            if (($create || $update || $delete) && !$view) {
                throw new RuntimeException($allowedMenus[$menu] . ': ' . self::MSG_VIEW);
            }
            if (!$view && !$create && !$update && !$delete) {
                continue;
            }
            $out[$menu] = [
                'view' => $view,
                'create' => $create,
                'update' => $update,
                'delete' => $delete,
            ];
        }

        if ($out === []) {
            throw new RuntimeException(self::MSG_REQUIRED);
        }

        return $out;
    }

    /** @param mixed $value */
    private static function flag($value): bool
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 'on') {
            return true;
        }
        if ($value === false || $value === 0 || $value === '0' || $value === null || $value === '') {
            return false;
        }
        throw new RuntimeException(self::MSG_ACTION);
    }
}
