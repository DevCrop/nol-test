<?php

namespace Menu;

final class MenuItem
{
    /** 자동 ID 시드 */
    protected static int $itemCount = 1;

    private ?int $id = null;
    private ?MenuItem $parent = null;
    private Menu $menu;

    private string $label;
    private string $url;

    private bool $isActive = false;

    /** 고유키 (슬러그) */
    private string $slug;

    private int $orderIndex = 0;
    private bool $isBlank = false;
    private bool $isVisible = true;

    /** @var MenuItem[] */
    private array $children = [];

    /**
     * 필수: $label, $url, $slug, $menu
     * 옵션:
     *  - parent: MenuItem|null
     *  - orderIndex: int (기본 0)
     *  - isBlank: bool (기본 false)
     *  - isVisible: bool (기본 true)
     * @param array{
     *   parent?: MenuItem|null,
     *   orderIndex?: int,
     *   isBlank?: bool,
     *   isVisible?: bool
     * } $options
     */
    public function __construct(
        string $label,
        string $url,
        string $slug,
        Menu $menu,
        array $options = [],
        ?int $id = null
    ) {
        $this->label = $label;
        $this->url   = $url;
        $this->slug  = $slug;
        $this->menu  = $menu;

        $this->orderIndex = (int)($options['orderIndex'] ?? 0);
        $this->isBlank    = (bool)($options['isBlank'] ?? false);
        $this->isVisible  = (bool)($options['isVisible'] ?? true);

        if (array_key_exists('parent', $options) && $options['parent'] instanceof self) {
            $this->parent = $options['parent'];
        }

        // ID 부여 (주입 시 그대로 사용, 아니면 자동증가)
        $this->id = $id ?? static::$itemCount++;
    }

    /** 팩토리 헬퍼 */
    public static function make(string $label, string $url, string $slug, Menu $menu, array $options = [], ?int $id = null): self
    {
        return new self($label, $url, $slug, $menu, $options, $id);
    }

    // ----------- getters -----------
    public function isActive(): bool
    {
        return $this->isActive;
    }


    public function id(): ?int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function menu(): Menu
    {
        return $this->menu;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function orderIndex(): int
    {
        return $this->orderIndex;
    }

    public function isBlank(): bool
    {
        return $this->isBlank;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /** @return MenuItem[] */
    public function children(bool $visibleOnly = true): array
    {
        $children = $visibleOnly
            ? array_filter($this->children, static fn(self $c) => $c->isVisible())
            : $this->children;

        usort($children, static fn(self $a, self $b) => $a->orderIndex <=> $b->orderIndex);
        return $children;
    }

    // ----------- modifiers -----------
    public function setActive(bool $flag = true): self
    {
        $this->isActive = $flag;
        return $this;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        if ($parent) {
            $parent->addChild($this);
        }
        return $this;
    }

    public function addChild(self $child): self
    {
        if ($child->menu() !== $this->menu) {
            throw new \InvalidArgumentException('Child MenuItem belongs to a different Menu instance.');
        }
        foreach ($this->children as $c) {
            if ($c === $child) return $this;
        }
        $this->children[] = $child;
        if ($child->parent() !== $this) {
            $child->parent = $this; // 내부 역참조 정합성
        }
        return $this;
    }

    public function setOrderIndex(int $orderIndex): self
    {
        $this->orderIndex = $orderIndex;
        return $this;
    }

    public function openInNewTab(bool $flag = true): self
    {
        $this->isBlank = $flag;
        return $this;
    }

    public function activateChain(): self
    {
        $node = $this;
        while ($node) {
            $node->setActive(true);
            $node = $node->parent();
        }
        return $this;
    }

    public function show(): self
    {
        $this->isVisible = true;
        return $this;
    }

    public function hide(): self
    {
        $this->isVisible = false;
        return $this;
    }

    public function target(): string
    {
        return $this->isBlank ? '_blank' : '_self';
    }

    public function toArray(bool $withChildren = true, bool $visibleOnly = true): array
    {
        $data = [
            'id'         => $this->id,
            'slug'       => $this->slug,
            'label'      => $this->label,
            'url'        => $this->url,
            'target'     => $this->target(),
            'orderIndex' => $this->orderIndex,
            'isBlank'    => $this->isBlank,
            'isVisible'  => $this->isVisible,
        ];

        if ($withChildren) {
            $data['children'] = array_map(
                fn(self $c) => $c->toArray(true, $visibleOnly),
                $this->children($visibleOnly)
            );
        }
        return $data;
    }

    // 형제들(자신 제외)
    public function siblings(bool $visibleOnly = true): array
    {
        return $this->siblingsInternal(false, $visibleOnly);
    }

    // 형제들(자신 포함)
    public function siblingsIncludingSelf(bool $visibleOnly = true): array
    {
        return $this->siblingsInternal(true, $visibleOnly);
    }

    // 이전 형제 (orderIndex 기준)
    public function previousSibling(bool $visibleOnly = true): ?self
    {
        $list = $this->siblingsIncludingSelf($visibleOnly);
        $idx  = array_search($this, $list, true);
        return ($idx !== false && $idx > 0) ? $list[$idx - 1] : null;
    }

    // 다음 형제 (orderIndex 기준)
    public function nextSibling(bool $visibleOnly = true): ?self
    {
        $list = $this->siblingsIncludingSelf($visibleOnly);
        $idx  = array_search($this, $list, true);
        return ($idx !== false && isset($list[$idx + 1])) ? $list[$idx + 1] : null;
    }

    /** 내부 공통: 형제 배열 구성 + 정렬 */
    private function siblingsInternal(bool $includeSelf, bool $visibleOnly): array
    {
        if ($this->parent) {
            // 부모가 있으면 부모 children에서 가져오기 (children()은 정렬 포함)
            $list = $this->parent->children($visibleOnly);
        } else {
            // 최상위인 경우: 같은 메뉴에서 parent가 null인 아이템만 취합
            $list = array_filter(
                $this->menu->items(), // Menu::items() 필요
                function (self $i) use ($visibleOnly) {
                    if ($i->parent() !== null) return false;
                    return $visibleOnly ? $i->isVisible() : true;
                }
            );
            // orderIndex 기준 정렬 통일
            usort($list, static fn(self $a, self $b) => $a->orderIndex() <=> $b->orderIndex());
        }

        // 자신 포함 여부 처리
        if (!$includeSelf) {
            $list = array_filter($list, fn(self $i) => $i !== $this);
            // array_filter 후 인덱스 재정렬(검색/이전다음 인덱스 안전)
            $list = array_values($list);
        }

        return $list;
    }


}
