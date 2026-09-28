<?php 

namespace Core; 

class Config 
{
    /** @var static|null */
    protected static ?self $instance = null;

    /** @var array 설정 데이터 */
    protected array $items = [];

    protected function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /** 인스턴스 가져오기 (Singleton) */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /** 설정값 등록 (한 번에 여러 개 가능) 
     * @param string|array $key
    */
    public function set($key, $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return;
        }

        $segments = explode('.', $key);
        $target = &$this->items;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }

        $target = $value;
    }

    /** 설정값 조회 (dot notation 지원) */
    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** 존재 여부 확인 */
    public function has(string $key): bool
    {
        return $this->get($key, '__missing__') !== '__missing__';
    }

    /** 설정파일 로드 (예: config/app.php) */
    public function load(string $filePath): void
    {
        if (is_file($filePath)) {
            $data = include $filePath;
            if (is_array($data)) {
                $this->items = array_merge($this->items, $data);
            }
        }
    }

    /** 전체 설정 반환 */
    public function all(): array
    {
        return $this->items;
    }

    /** 설정 초기화 */
    public function reset(): void
    {
        $this->items = [];
    }
}