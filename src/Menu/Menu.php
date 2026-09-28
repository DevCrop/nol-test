<?php

namespace Menu;

final class Menu
{
    /** 전역 레지스트리: 이름 → Menu 인스턴스 */
    public static array $created = [];

    /** 내부 자동 ID 시드 */
    private static int $menuCount = 1;

    /** 고유 ID */
    private int $id;

    /** @var string 메뉴 식별/표시용 이름 */
    private string $name;

    /** @var MenuItem[] 평면 구조의 아이템 목록 */
    private array $items = [];

    public function __construct(string $name = 'default', ?int $id = null)
    {
        $this->name = $name;
        $this->id   = $id ?? self::$menuCount++;

        self::$created[$name] = $this;
    }

    /** 선택: 이름으로 조회 */
    public static function get(string $name): ?self
    {
        return self::$created[$name] ?? null;
    }

    /** 메뉴 ID 조회 */
    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * 아이템 추가 (필수: label, url, slug)
     * @param array{
     *   parent?: MenuItem|null,
     *   orderIndex?: int,
     *   isBlank?: bool,
     *   isVisible?: bool
     * } $options
     */
    public function add(string $label, string $url, string $slug, array $options = [], ?int $id = null): MenuItem
    {
        $item = new MenuItem($label, $url, $slug, $this, $options, $id);
        $this->items[] = $item;

        if ($parent = $item->parent()) {
            $parent->addChild($item);
        }

        return $item;
    }

    /** 수동으로 아이템 추가 (이미 생성된 MenuItem) */
    public function addItem(MenuItem $item): self
    {
        if ($item->menu() !== $this) {
            throw new \InvalidArgumentException('MenuItem belongs to a different Menu instance.');
        }
        $this->items[] = $item;
        if ($parent = $item->parent()) {
            $parent->addChild($item);
        }
        return $this;
    }

    public function eachItem(callable $fn): void
    {
        $walk = function (array $items) use (&$walk, $fn) {
            foreach ($items as $it) {
                $fn($it);
                $children = method_exists($it, 'children') ? $it->children(false) : [];
                if ($children) $walk($children);
            }
        };
        $walk($this->items);
    }

    /** 모든 항목 active 초기화 */
    public function resetActive(): void
    {
        $this->eachItem(function ($item) {
            $item->setActive(false);
        });
    }

    /** URL로 항목 찾기 (쿼리 제거, 트레일링 슬래시 유연 비교) */
    public function findByUrl(string $url): ?\Menu\MenuItem
    {
        $needle = $this->normalizePath($url);
        $found  = null;

        $this->eachItem(function ($item) use ($needle, &$found) {
            if ($found) return;
            $path = $this->normalizePath($item->url());
            if ($path === $needle) {
                $found = $item;
            }
        });

        return $found;
    }

    /** 내부: 비교용 경로 정규화 */
    protected function normalizePath(string $url): string
    {
        // 호스트/쿼리 제거 → path만 비교
        $parts = parse_url($url);
        $path  = $parts['path'] ?? '/';

        // 트레일링 슬래시 유연 처리
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        // 빈 → 루트
        return $path === '' ? '/' : $path;
    }

    /** (있다면) 루트 아이템들 반환 */
    public function items(): array
    {
        return $this->items; // 이미 가지고 있는 내부 배열 이름에 맞춰주세요
    }

    /** @return MenuItem[] 모든 아이템(가시성 무시, 평면) */
    public function allItems(): array
    {
        return $this->items;
    }

    /** @return MenuItem[] 최상위 아이템만 */
    public function rootItems(bool $visibleOnly = true): array
    {
        $roots = array_filter($this->items, static function (MenuItem $i) use ($visibleOnly) {
            if ($visibleOnly && !$i->isVisible()) return false;
            return $i->parent() === null;
        });

        usort($roots, static fn(MenuItem $a, MenuItem $b) => $a->orderIndex() <=> $b->orderIndex());
        return $roots;
    }

    public function findByLabel(string $label): ?MenuItem
    {
        foreach ($this->items as $item) {
            if ($item->label() === $label) return $item;
        }
        return null;
    }

    // public function findByUrl(string $url): ?MenuItem
    // {
    //     foreach ($this->items as $item) {
    //         if ($item->url() === $url) return $item;
    //     }
    //     return null;
    // }

    public function findBySlug(string $slug): ?MenuItem
    {
        foreach ($this->items as $item) {
            if ($item->slug() === $slug) return $item;
        }
        return null;
    }

    /** 트리 형태 배열로 변환 (렌더링/직렬화 용) */
    public function toArray(bool $visibleOnly = true): array
    {
        $build = function (MenuItem $item) use (&$build, $visibleOnly): array {
            $children = [];
            foreach ($item->children($visibleOnly) as $child) {
                $children[] = $build($child);
            }
            return [
                'id'         => $item->id(),
                'slug'       => $item->slug(),
                'label'      => $item->label(),
                'url'        => $item->url(),
                'target'     => $item->target(),
                'orderIndex' => $item->orderIndex(),
                'isBlank'    => $item->isBlank(),
                'isVisible'  => $item->isVisible(),
                'children'   => $children,
            ];
        };

        $result = [];
        foreach ($this->rootItems($visibleOnly) as $root) {
            $result[] = $build($root);
        }
        return $result;
    }

    /**
     * 간단한 HTML 렌더러 (원한다면 분리 가능)
     * - $ulClass, $liClass 로 클래스 제어
     */
    public function render(string $ulClass = 'menu', string $liClass = 'menu-item'): string
    {
        $renderItems = function (array $items) use (&$renderItems, $ulClass, $liClass): string {
            if (!$items) return '';
            $html = '<ul class="' . htmlspecialchars($ulClass, ENT_QUOTES, 'UTF-8') . '">';
            foreach ($items as $item) {
                /** @var MenuItem $item */
                $html .= '<li class="' . htmlspecialchars($liClass, ENT_QUOTES, 'UTF-8') . '">';
                $html .= sprintf(
                    '<a href="%s" target="%s">%s</a>',
                    htmlspecialchars($item->url(), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($item->target(), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($item->label(), ENT_QUOTES, 'UTF-8')
                );
                $children = $item->children(true);
                if (!empty($children)) {
                    $html .= $renderItems($children);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
            return $html;
        };

        return $renderItems($this->rootItems(true));
    }

    public function findBy(string $field, $value): ?MenuItem
    {
        foreach ($this->items as $item) {
            switch ($field) {
                case 'url':
                    if ($item->url() === $value) return $item;
                    break;
                case 'slug':
                    if ($item->slug() === $value) return $item;
                    break;
                case 'label':
                    if ($item->label() === $value) return $item;
                    break;
                // 필요하면 옵션 확장 필드도 추가:
                // case 'target':
                //     if ($item->target() === $value) return $item;
                //     break;
            }
        }
        return null;
    }


    // // 1) 현재 요청 경로 정규화
    // private function normalizePath(string $path): string
    // {
    //     $p = parse_url($path, PHP_URL_PATH) ?: '/';
    //     return rtrim($p, '/') ?: '/';
    // }

    // 2) 경로로 최적 매칭 노드 찾기 (정확 일치 > 상위 경로 후퇴 일치)
    public function findBestByUrl(string $path): ?MenuItem
    {
        $path = $this->normalizePath($path);

        // 정확 일치
        if ($exact = $this->findByUrl($path)) return $exact;

        // 상위 경로로 한 단계씩 후퇴하며 탐색: /a/b/c → /a/b → /a
        $parts = explode('/', trim($path, '/'));
        while (count($parts) > 0) {
            $guess = '/'.implode('/', $parts);
            if ($node = $this->findByUrl($guess)) return $node;
            array_pop($parts);
        }

        // 루트 시도
        return $this->findByUrl('/');
    }

    // 3) children 가져오기 (보이는 것만/전체 선택)
    public function childrenOf(MenuItem $parent, bool $visibleOnly = true): array
    {
        $children = $parent->children($visibleOnly) ?? [];
        usort($children, static fn(MenuItem $a, MenuItem $b) => $a->orderIndex() <=> $b->orderIndex());
        return $children;
    }

    // 4) Sub-Nav용 pages 배열 만들기 (현재 섹션의 직계 children)
    public function buildSubNavPages(string $currentPath): array
    {
        $currentPath = $this->normalizePath($currentPath);
        $node = $this->findBestByUrl($currentPath);
        if (!$node) return [];

        // 섹션 루트(=부모가 없음)라면 자기 children, 아니면 "부모의 children"을 탭처럼 노출
        $section = $node->parent() ?: $node;
        $children = $this->childrenOf($section, true);

        $pages = [];
        foreach ($children as $child) {
            $isActive = $this->normalizePath($child->url()) === $currentPath;
            $pages[] = [
                'title'    => $child->label(),
                'path'     => $child->url(),
                'isActive' => $isActive,
                // 필요 시 route/params 등을 MenuItem 확장 필드에서 꺼내면 됨
            ];
        }
        return $pages;
    }

}
