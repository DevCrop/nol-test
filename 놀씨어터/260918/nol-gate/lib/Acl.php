<?php

require_once dirname(__DIR__) . '/Model/AclModel.php';

class Acl
{
    public const ACTIONS = ['view', 'create', 'update', 'delete'];

    private static ?self $current = null;

    private int $adminNo;
    private bool $super;
    /** @var array<string, array{view:bool,create:bool,update:bool,delete:bool}> */
    private array $grants;

    public function __construct(int $adminNo, bool $super, array $grants = [])
    {
        $this->adminNo = $adminNo;
        $this->super = $super;
        $this->grants = $grants;
    }

    public static function forAdmin(int $adminNo, bool $super): self
    {
        $grants = $super || $adminNo < 1 ? [] : AclModel::fetch($adminNo);
        $acl = new self($adminNo, $super, $grants);
        self::$current = $acl;
        return $acl;
    }

    public static function current(): self
    {
        if (self::$current instanceof self) {
            return self::$current;
        }
        return self::forAdmin(0, false);
    }

    public function isSuper(): bool
    {
        return $this->super;
    }

    public static function assignableMenus(): array
    {
        $config = require dirname(__DIR__) . '/config/menu.config.php';
        $out = [];
        foreach ($config as $menu) {
            $key = (string) ($menu['key'] ?? '');
            if ($key === '' || $key === 'account') {
                continue;
            }
            $out[$key] = (string) ($menu['title'] ?? $key);
        }
        return $out;
    }

    public function allows(string $menu, string $action): bool
    {
        if ($this->super) {
            return true;
        }
        if ($menu === 'account') {
            return false;
        }
        if (!in_array($action, self::ACTIONS, true)) {
            return false;
        }
        $row = $this->grants[$menu] ?? null;
        if ($row === null) {
            return false;
        }
        if (!empty($row[$action])) {
            return true;
        }
        return $action === 'view' && (!empty($row['create']) || !empty($row['update']) || !empty($row['delete']));
    }

    public function allowsAction(string $action): bool
    {
        [$menu] = self::resolve();
        if ($menu === null) {
            return $this->super;
        }
        return $this->allows($menu, $action);
    }

    public function grantsFor(int $adminNo): array
    {
        return AclModel::fetch($adminNo);
    }

    public function guard(): void
    {
        if (empty($_SESSION['no_adm_login_uid'])) {
            return;
        }
        [$menu, $action] = self::resolve();
        if ($menu === null || $action === null) {
            return;
        }
        if ($this->allows($menu, $action)) {
            return;
        }
        $this->deny();
    }

    private function deny(): void
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $ajax = strpos($script, '/ajax/') !== false
            || substr($script, -12) === '/process.php'
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($ajax) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
            }
            echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
            exit;
        }

        global $role;
        if ($role instanceof Role) {
            $role->redirectWithError('권한이 없습니다.');
        }
        http_response_code(403);
        echo '권한이 없습니다.';
        exit;
    }

    /** @return array{0:?string,1:?string} */
    public static function resolve(): array
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if (preg_match('#/Controller/([A-Za-z]+)Controller\\.php$#', $script, $cm)) {
            $ctrl = [
                'Account' => 'account',
                'Audit' => 'account',
                'PrivacyAccess' => 'account',
                'Banner' => 'design',
                'Popup' => 'design',
                'Board' => 'board',
                'Faq' => 'faq',
                'Seo' => 'siteinfo',
                'Setting' => 'siteinfo',
            ];
            $menu = $ctrl[$cm[1]] ?? null;
            if ($menu === null) {
                return [null, null];
            }
            $mode = strtolower((string) ($_POST['mode'] ?? $_GET['mode'] ?? ''));
            if (in_array($mode, ['delete', 'delete_array', 'delete.array'], true)) {
                return [$menu, 'delete'];
            }
            if (in_array($mode, ['save', 'insert'], true)) {
                return [$menu, 'create'];
            }
            if (in_array($mode, ['update', 'edit', 'sort', 'unlock_idle'], true)) {
                return [$menu, 'update'];
            }
            return [$menu, 'view'];
        }
        if (!preg_match('#/pages/([^/]+)/(.+)$#', $script, $m)) {
            return [null, null];
        }

        $dir = $m[1];
        $file = strtolower((string) basename($m[2]));
        if ($file === 'pwd.php') {
            return [null, null];
        }
        if ($file === 'setting.process.php' && strtolower((string) ($_POST['mode'] ?? '')) === 'pwd.change') {
            return [null, null];
        }

        $menus = [
            'board' => 'board',
            'design' => 'design',
            'faq' => 'faq',
            'inquiry' => 'inquiry',
            'log' => 'log',
            'works' => 'works',
            'setting' => 'siteinfo',
            'privacy' => 'siteinfo',
            'account' => 'account',
        ];
        $menu = $menus[$dir] ?? null;
        if ($menu === null) {
            return [null, null];
        }

        $mode = strtolower((string) ($_POST['mode'] ?? $_GET['mode'] ?? ''));
        if (in_array($mode, ['delete', 'delete_array', 'delete.array'], true) || strpos($file, 'delete') !== false) {
            return [$menu, 'delete'];
        }
        if (in_array($mode, ['save', 'insert'], true) || strpos($file, 'add') !== false || strpos($file, 'new') !== false) {
            return [$menu, 'create'];
        }
        if (in_array($mode, ['update', 'edit', 'sort', 'unlock_idle'], true) || strpos($file, 'edit') !== false) {
            return [$menu, 'update'];
        }
        return [$menu, 'view'];
    }
}
