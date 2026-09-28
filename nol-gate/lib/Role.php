<?php

/**
 * 관리자 권한 클래스 (단일 소스)
 * base.class.php에서 로드하며 전역 $role 로 사용.
 * role_id / admin_roles 기반.
 */
class Role {
    private string $code;
    private int $id;
    private Acl $acl;

    public function __construct(?int $roleId = null)
    {
        global $admin_roles;

        $this->id = $roleId ?? ($_SESSION['no_adm_login_role_id'] ?? 3);
        $this->code = $admin_roles[$this->id]['code'] ?? 'guest';
        require_once __DIR__ . '/Acl.php';
        $this->acl = Acl::forAdmin((int) ($_SESSION['no_adm_login_no'] ?? 0), $this->isSuper());
    }

    public function acl(): Acl
    {
        return $this->acl;
    }

    public function isSuper(): bool
    {
        return $this->is('superadmin');
    }

    public function isGeneral(): bool
    {
        return !$this->isSuper();
    }

    public function is(string $roleCode): bool
    {
        return $this->code === $roleCode;
    }

    public function in(array $allowedCodes): bool
    {
        return in_array($this->code, $allowedCodes, true);
    }

    public function canEdit(): bool
    {
        return $this->acl->allowsAction('update');
    }

    public function canCreate(): bool
    {
        return $this->acl->allowsAction('create');
    }

    public function canDelete(): bool
    {
        return $this->acl->allowsAction('delete');
    }
    
    public function canView() : bool 
    {
        return $this->isSuper();
    }

    public function canViewMenu(string $menuKey): bool
    {
        return $this->acl->allows($menuKey, 'view');
    }

    public function getName(): string
    {
        global $admin_roles;
        return $admin_roles[$this->id]['name'] ?? '알 수 없음';
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 역할별 최대 계정 수. null이면 제한 없음.
     * 최고관리자=2 (고객 1 + 나인원랩스 1), 일반=10
     */
    public static function getRoleLimit(int $roleId): ?int
    {
        $limits = [1 => 2, 2 => 10];
        return $limits[$roleId] ?? null;
    }

    public static function roleLimitMessage(int $roleId): string
    {
        if ($roleId === 1) {
            return '최고 관리자는 고객 1명, 나인원랩스 1명으로 최대 2개입니다.';
        }
        $limit = self::getRoleLimit($roleId);
        global $admin_roles;
        $name = $admin_roles[$roleId]['name'] ?? '해당 권한';
        return $name . ' 계정은 최대 ' . (int) $limit . '개까지 생성할 수 있습니다.';
    }

    public function getRestrictedMenuKeys(): array
    {
        $restricted = [];
        $config = require __DIR__ . '/../config/menu.config.php';
        foreach ($config as $menu) {
            $key = (string) ($menu['key'] ?? '');
            if ($key !== '' && !$this->canViewMenu($key)) {
                $restricted[] = $key;
            }
        }
        return $restricted;
    }

    /**
     * 수정·등록 불가 여부 (외부인 = 조회만 가능)
     */
    public function isReadOnly(): bool
    {
        return !$this->canCreate() && !$this->canEdit() && !$this->canDelete();
    }

    /**
     * API 전용: 로그인하지 않았으면 403 JSON 응답 후 종료.
     * Controller / process.php 상단에서 호출.
     */
    public function requireLogin(): void
    {
        if (!empty($_SESSION['no_adm_login_uid'])) {
            return;
        }
        require_once __DIR__ . '/AuthSession.php';
        AuthSession::denyLogin();
    }

    /**
     * API 전용: 수정 권한이 없으면(조회전용이면) 403 JSON 응답 후 종료.
     * Controller / process.php 에서 requireLogin() 다음에 호출.
     */
    public function requireCanModify(): void
    {
        if (!$this->isReadOnly()) {
            return;
        }
        $this->send403Json('권한이 없습니다.');
    }

    /**
     * API 전용: 403 + JSON 응답 후 exit.
     */
    private function send403Json(string $message): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
        }
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    /**
     * 계정 메뉴 전용: canView()가 false(중간관리자·외부인)이면 '권한이 없습니다.' 메시지 후 리다이렉트하고 종료.
     * account/index.php, new.php, edit.php 상단에서 호출.
     *
     * @param string|null $backUrl 이동할 URL (미지정 시 게시판 목록)
     */
    public function redirectIfCannotView(?string $backUrl = null): void
    {
        if (empty($_SESSION['no_adm_login_uid'])) {
            require_once __DIR__ . '/AuthSession.php';
            AuthSession::denyLogin();
        }
        if ($this->canView()) {
            return;
        }
        $this->redirectWithError('권한이 없습니다.', $backUrl);
    }

    /**
     * 메뉴 접근 제한: getRestrictedMenuKeys에 포함된 메뉴에 접근 시 '권한이 없습니다.' 후 리다이렉트.
     * inquiry, siteinfo(외 pwd) 등 메뉴 노출이 숨겨진 페이지에서 직접 URL 접근 시 차단.
     *
     * @param string $menuKey 메뉴 키 (account, siteinfo, inquiry 등)
     */
    public function redirectIfRestrictedMenu(string $menuKey): void
    {
        if (!in_array($menuKey, $this->getRestrictedMenuKeys(), true)) {
            return;
        }
        $this->redirectWithError('권한이 없습니다.');
    }

    /**
     * 메시지와 함께 리다이렉트. 메시지는 세션에만 저장(URL·레퍼러·로그에 노출되지 않음).
     * 이동 URL은 동일 오리진 또는 상대 경로만 허용(오픈 리다이렉트 방지).
     *
     * @param string $message 알럿 메시지 (서버에서만 설정, 사용자 입력 아님)
     * @param string|null $backUrl 이동할 URL (미지정 시 동일 오리진 REFERER 또는 게시판 목록)
     */
    public function redirectWithError(string $message, ?string $backUrl = null): void
    {
        $_SESSION['admin_flash_error'] = $message;
        global $NO_IS_SUBDIR;
        $base = $NO_IS_SUBDIR ?? '';
        $defaultBack = ($GLOBALS['NO_ADMIN_PAGES_BASE'] ?? ($base . '/pages')) . '/board/board.list.php';

        $back = $this->resolveSafeRedirectUrl($backUrl, $defaultBack);
        header('Location: ' . $back);
        exit;
    }

    /**
     * 오픈 리다이렉트 방지: 동일 오리진 또는 서버 내 상대 경로만 허용.
     *
     * @param string|null $preferUrl 호출부에서 지정한 URL (null이면 REFERER 검토)
     * @param string $default 허용할 URL을 못 찾을 때 사용할 기본 URL
     * @return string 안전한 리다이렉트 URL
     */
    private function resolveSafeRedirectUrl(?string $preferUrl, string $default): string
    {
        $base = $GLOBALS['NO_IS_SUBDIR'] ?? '';

        if ($preferUrl !== null && $preferUrl !== '') {
            // 상대 경로만 허용: / 시작, // 및 .. 미포함 (오픈 리다이렉트·경로 조작 방지)
            if (strpos($preferUrl, '//') === false && strpos($preferUrl, '..') === false && preg_match('#^/[^\\\\]*$#', $preferUrl)) {
                return $base . $preferUrl;
            }
            return $default;
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if ($ref === '') {
            return $default;
        }

        $refHost = parse_url($ref, PHP_URL_HOST);
        $refScheme = parse_url($ref, PHP_URL_SCHEME);
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($currentHost, ':') !== false) {
            $currentHost = explode(':', $currentHost, 2)[0];
        }
        $refHost = $refHost ? strtolower($refHost) : '';
        $currentHost = strtolower($currentHost);

        if ($refScheme !== 'http' && $refScheme !== 'https') {
            return $default;
        }
        if ($refHost !== '' && $refHost === $currentHost) {
            return $ref;
        }
        return $default;
    }

    /**
     * 수정/등록 페이지 전용: 조회전용(외부인)이면 '권한이 없습니다.' 메시지 후 이전 페이지로 이동하고 종료.
     *
     * @param string|null $backUrl 이동할 URL (미지정 시 REFERER 또는 게시판 목록)
     */
    public function redirectIfReadOnly(?string $backUrl = null): void
    {
        if (!$this->isReadOnly()) {
            return;
        }
        $this->redirectWithError('권한이 없습니다.', $backUrl);
    }

    /**
     * 리다이렉트 후 도착 페이지에서 호출. 세션의 admin_flash_error가 있으면 alert로 띄운 뒤 세션에서 제거.
     * admin.footer 등 공통 include에서 한 번만 호출. (GET이 아닌 세션 사용으로 URL/레퍼러/로그 노출 방지)
     */
    public function echoFlashErrorAlert(): void
    {
        $msg = $_SESSION['admin_flash_error'] ?? '';
        if ($msg !== '') {
            unset($_SESSION['admin_flash_error']);
        }
        if ($msg === '') {
            return;
        }
        $msgEscaped = json_encode($msg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo '<script>';
        echo '(function(){ var m = ' . $msgEscaped . '; if (typeof window !== "undefined" && m) window.alert(m); })();';
        echo '</script>';
    }
}
