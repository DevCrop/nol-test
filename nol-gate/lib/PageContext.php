<?php

/**
 * 관리자 페이지 컨텍스트
 * - 현재 URL 기반으로 depthnum, pagenum, pageName 자동 감지
 * - 메뉴 설정과 분리된 페이지(edit/new/view)는 routeOverrides로 등록
 */
class PageContext
{
    public int $depthnum;
    public int $pagenum;
    public string $pageName;

    public function __construct(int $depthnum = 1, int $pagenum = 1, string $pageName = '')
    {
        $this->depthnum = $depthnum;
        $this->pagenum = $pagenum;
        $this->pageName = $pageName;
    }

    /**
     * 현재 요청 경로에서 컨텍스트 생성.
     * admin/pages/ 이하 경로로 루트 맵 조회, 없으면 부모 경로로 폴백.
     * $role 전달 시 외부인+사이트정보 메뉴는 노출 서브 순서(1,2,3)로 pagenum 보정.
     *
     * @param object|null $role Role 인스턴스 (외부인 siteinfo 서브탭 active용)
     */
    public static function fromRequest($role = null): self
    {
        $path = self::getCurrentPath();
        $map = self::buildRouteMap();

        // 정확 매칭
        if (isset($map[$path])) {
            $m = $map[$path];
            $ctx = new self($m['depth'], $m['page'], $m['title']);
        } else {
            // 부모 경로 폴백 (account/edit.php → account/index.php)
            $dir = dirname($path);
            if ($dir !== '.') {
                $index = $dir . '/index.php';
                if (isset($map[$index])) {
                    $m = $map[$index];
                    $ctx = new self($m['depth'], $m['page'], $m['title']);
                } else {
                    $ctx = new self(1, 1, '');
                }
            } else {
                $ctx = new self(1, 1, '');
            }
        }

        // 외부인 + 사이트정보: 노출 서브 순서(비밀번호=1, 개인정보=2, SEO=3)로 pagenum 보정
        if ($role !== null && method_exists($role, 'is') && $role->is('external') && $ctx->depthnum === 7) {
            $externalSiteinfoPage = [
                'setting/pwd.php'   => 1,
                'privacy/index.php' => 2,
                'setting/seo.php'   => 3,
            ];
            if (isset($externalSiteinfoPage[$path])) {
                $ctx->pagenum = $externalSiteinfoPage[$path];
            }
        }

        return $ctx;
    }

    /** admin/pages/ 이하 상대 경로 반환 (예: account/index.php) */
    private static function getCurrentPath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = $GLOBALS['NO_ADMIN_PAGES_BASE'] ?? '/pages';
        $base = rtrim($base, '/') . '/';
        $pos = strpos($script, $base);
        if ($pos !== false) {
            return ltrim(substr($script, $pos + strlen($base)), '/');
        }
        $parts = explode('/', trim($script, '/'));
        $idx = array_search('pages', $parts, true);
        if ($idx !== false && isset($parts[$idx + 1])) {
            return implode('/', array_slice($parts, $idx + 1));
        }
        return basename($script);
    }

    /** 메뉴 설정 + 오버라이드로 루트 맵 생성 */
    private static function buildRouteMap(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $configPath = __DIR__ . '/../config/menu.config.php';
        $menus = is_file($configPath) ? require $configPath : [];

        $map = [];
        foreach ($menus as $depth => $menu) {
            $subs = $menu['subs'] ?? [];
            if (empty($subs)) {
                $url = $menu['url'] ?? '';
                if ($url !== '') {
                    $pagesBasePattern = preg_quote(rtrim((string)($GLOBALS['NO_ADMIN_PAGES_BASE'] ?? '/pages'), '/'), '#');
                    $rel = preg_replace('#^' . $pagesBasePattern . '/?#', '', $url);
                    $map[$rel] = ['depth' => $depth, 'page' => 0, 'title' => $menu['title'] ?? ''];
                }
                continue;
            }
            foreach ($subs as $i => $sub) {
                $url = $sub['url'] ?? '';
                if ($url !== '') {
                    $map[$url] = [
                        'depth' => $depth,
                        'page'  => $i + 1,
                        'title' => $sub['title'] ?? '',
                    ];
                }
            }
        }

        foreach (self::getRouteOverrides() as $path => $ctx) {
            $map[$path] = $ctx;
        }

        return $map;
    }

    /**
     * 메뉴에 없는 페이지(edit/new/view 등) 경로별 컨텍스트 오버라이드.
     * 새 탭/페이지 추가 시 여기에 한 줄 등록.
     */
    private static function getRouteOverrides(): array
    {
        return [
            'account/edit.php'      => ['depth' => 8, 'page' => 1, 'title' => '계정'],
            'account/access.php'    => ['depth' => 8, 'page' => 4, 'title' => '개인정보 접속기록'],
            'account/new.php'       => ['depth' => 8, 'page' => 2, 'title' => '계정'],
            'board/board.add.php'   => ['depth' => 1, 'page' => 1, 'title' => '게시글'],
            'board/board.list.php'  => ['depth' => 1, 'page' => 1, 'title' => '게시글 관리'],
            'board/board.view.php'  => ['depth' => 1, 'page' => 1, 'title' => '게시글'],
            'board/board.category.view.php' => ['depth' => 1, 'page' => 1, 'title' => '게시글'],
            'board/board.comment.view.php'  => ['depth' => 1, 'page' => 1, 'title' => '게시글'],
            'board/board.manage.add.php'    => ['depth' => 1, 'page' => 1, 'title' => '게시판'],
            'board/board.manage.list.php'   => ['depth' => 1, 'page' => 1, 'title' => '게시판'],
            'board/board.manage.view.php'   => ['depth' => 1, 'page' => 1, 'title' => '게시판'],
            'board/board.role.php'          => ['depth' => 1, 'page' => 1, 'title' => '게시판'],
            'board/board.role.view.php'     => ['depth' => 1, 'page' => 1, 'title' => '게시판'],
            'design/banner.edit.php' => ['depth' => 2, 'page' => 1, 'title' => '메인 배너'],
            'design/banner.new.php'  => ['depth' => 2, 'page' => 1, 'title' => '메인 배너'],
            'design/popup.edit.php'  => ['depth' => 2, 'page' => 2, 'title' => '팝업 배너'],
            'design/popup.new.php'   => ['depth' => 2, 'page' => 2, 'title' => '팝업 배너'],
            'faq/edit.php'   => ['depth' => 3, 'page' => 0, 'title' => 'FAQ'],
            'faq/new.php'    => ['depth' => 3, 'page' => 0, 'title' => 'FAQ'],
            'inquiry/view.php' => ['depth' => 4, 'page' => 1, 'title' => '대관 신청 관리'],
            'privacy/edit.php' => ['depth' => 7, 'page' => 4, 'title' => '개인정보처리방침'],
            'privacy/new.php'  => ['depth' => 7, 'page' => 4, 'title' => '개인정보처리방침'],
            'setting/edit.php'   => ['depth' => 7, 'page' => 2, 'title' => '외부 태그'],
            'setting/external.tag.php' => ['depth' => 7, 'page' => 2, 'title' => '사이트 외부 태그'],
            'setting/new.php'    => ['depth' => 7, 'page' => 2, 'title' => '외부 태그'],
            'setting/seo.edit.php' => ['depth' => 7, 'page' => 3, 'title' => '페이지별 SEO'],
            'setting/seo.new.php'  => ['depth' => 7, 'page' => 3, 'title' => '페이지별 SEO'],
            'setting/pwd.php'      => ['depth' => 7, 'page' => 2, 'title' => '비밀번호 변경'],
            'inquiry/setting.php'  => ['depth' => 4, 'page' => 2, 'title' => '대관 신청 설정'],
            'works/edit.php' => ['depth' => 6, 'page' => 0, 'title' => 'Works'],
            'works/new.php'  => ['depth' => 6, 'page' => 0, 'title' => 'Works'],
        ];
    }

    public function toArray(): array
    {
        return [
            'depthnum' => $this->depthnum,
            'pagenum'  => $this->pagenum,
            'pageName' => $this->pageName,
        ];
    }
}
