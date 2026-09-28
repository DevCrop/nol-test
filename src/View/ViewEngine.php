<?php

namespace View;

class ViewEngine
{
    protected static ?ViewEngine $instance = null;

    protected $template;
    protected $data;
    protected ?string $layout = 'main';
    protected array $sections = [];
    protected array $sectionStack = [];
    protected string $viewsPath;
    protected string $ext = '.php';

    public function __construct(string $template, array $data = [], ?string $viewsPath = null)
    {
        $this->template  = $template;
        $this->data      = $data;
        $this->viewsPath = $viewsPath ?: (defined('ROOT_PATH') ? rtrim(ROOT_PATH, '/').'/views' : getcwd().'/views');

        // 현재 뷰 엔진을 전역 접근 가능하도록 등록
        static::$instance = $this;
    }

    /** 현재 엔진 인스턴스 반환 */
    public static function getInstance(): self
    {
        // 이미 있으면 그대로 반환
        if (static::$instance) {
            return static::$instance;
        }

        $fallback = new self('errors.default', []);
        $fallback->setLayout(null); // 레이아웃 적용하지 않도록
        static::$instance = $fallback;

        return static::$instance;
    }


    /** 필요 시 교체/해제 */
    public static function setInstance(?self $engine): void
    {
        static::$instance = $engine;
    }

    /** 데이터 머지 (헬퍼의 share에서 사용) */
    public function mergeData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /** 레이아웃 지정 (예: layouts/main) */
    public function setLayout(?string $layout = null): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function include(string $template, array $data = []): string
    {
        return $this->renderFile($this->resolve($template), $data);
    }

    public function section(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            $this->sections[$name] = $content; 
            return; 
        }
        
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function endSection(): void
    {
        $content = ob_get_clean();
        $name = array_pop($this->sectionStack);
        if ($name === null) {
            throw new \RuntimeException('endSection() called without matching section().');
        }
        $this->sections[$name] = $content;
    }

    public function yield(string $name, string $default = ''): string
    {
        return array_key_exists($name, $this->sections) ? $this->sections[$name] : $default;
    }

    public function render(): string
    {
        $body = $this->renderFile($this->resolve($this->template), $this->data);

        if (!isset($this->sections['content']) && empty($this->sectionStack)) {
            $this->sections['content'] = $body;
        }

        if ($this->layout) {
            return $this->renderFile($this->resolve('layouts.'.$this->layout), $this->data);
        }

        return $body;
    }

    /* ---------------- Internals ---------------- */
    protected function resolve(string $template): string
    {
        // 1) 앞뒤 공백 제거
        $template = trim($template);

        // 2) dot-notation → path  (그리고 역슬래시도 정규화)
        $template = str_replace(['\\', '.'], ['/', '/'], $template);

        // 3) 중복 슬래시 정리
        $template = preg_replace('#/+#', '/', $template);

        // 4) 선행 슬래시 제거
        $template = ltrim($template, '/');

        // 5) 보안: 상위 디렉토리로 탈출 시도 차단
        if ($template === '' || strpos($template, '..') !== false) {
            throw new \RuntimeException('Invalid template path.');
        }

        // 6) 최종 경로 만들기
        $path = rtrim($this->viewsPath, '/') . '/' . $template;

        // 7) 확장자 보장
        if (substr($path, -strlen($this->ext)) !== $this->ext) {
            $path .= $this->ext;
        }

        // 8) 파일 존재 확인
        if (!is_file($path)) {
            throw new \RuntimeException("View file not found: {$path}");
        }

        return $path;
    }


    protected function renderFile(string $path, array $vars = []): string
    {
        $vars = array_merge(
            $this->data,
            $vars,
            [
                '__view' => $this,
                'e'      => static function ($v) {
                    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8', false);
                },
            ]
        );

        extract($vars, EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
