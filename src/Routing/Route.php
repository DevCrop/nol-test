<?php 

namespace Routing;

class Route
{
    /** @var string */
    protected $uri;
    /** @var string */
    protected $method;
    /** @var callable|string|array */
    protected $action;

    /** @var array<string,string> */
    protected $params = [];
    /** @var array<string,string> */
    protected $wheres = [];
    /** @var string|null */
    protected $name = null;
    /** @var string[] */
    protected $middleware = [];

    /** @var string|null 캐싱된 정규식 */
    protected $compiledPattern = null;

    /** @var \Routing\Router|null 라우터 역참조(이름 등록용) */
    protected ?Router $router = null;

    public function __construct(string $method, string $uri, $action)
    {
        $this->method = strtoupper($method);
        $this->uri    = '/' . ltrim($uri, '/');
        $this->action = $action;
    }

    /** Router가 생성 직후 주입 */
    public function setRouter(Router $router): self
    {
        $this->router = $router;
        return $this;
    }

    public function name(string $name): self
    {
        $old = $this->name;
        $this->name = $name;

        // ★ 라우트 이름이 지정/변경되면 Router 인덱스 갱신
        if ($this->router) {
            $this->router->registerNamedRoute($this, $old);
        }
        return $this;
    }

    /**
     * @param string|string[] $names
     */
    public function middleware($names): self
    {
        $names = is_array($names) ? $names : [$names];
        $this->middleware = array_values(array_unique(array_merge($this->middleware, $names)));
        return $this;
    }

    public function where(string $param, string $regex): self
    {
        $this->wheres[$param] = $regex;
        $this->compiledPattern = null;
        return $this;
    }

    public function getName(): ?string     { return $this->name; }
    public function getMethod(): string    { return $this->method; }
    public function getUri(): string       { return $this->uri; }
    public function getAction()            { return $this->action; }
    public function getMiddleware(): array { return $this->middleware; }
    public function getParams(): array     { return $this->params; }

    /**
     * 경로 일치 검사 + params 추출
     */
    public function matches(string $method, string $path): bool
    {
        if (strtoupper($method) !== $this->method) {
            return false;
        }

        $pattern = $this->compilePattern();
        if (!preg_match($pattern, $path, $m)) {
            return false;
        }

        // 명명 그룹만 추출
        $params = [];
        foreach ($m as $k => $v) {
            if (!is_int($k)) {
                // 경로 파라미터: 퍼센트 디코드 후, 하이픈을 스페이스로 환원
                $decoded = rawurldecode($v);
                $decoded = str_replace('-', ' ', $decoded);
                $params[$k] = $decoded;
            }
        }
        $this->params = $params;
        return true;
    }

    /**
     * {param} → (?P<param>[^/]+) 치환, where 규칙 반영
     */
    protected function compilePattern(): string
    {
        if ($this->compiledPattern !== null) {
            return $this->compiledPattern;
        }

        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) {
            $name = $m[1];
            $rule = $this->wheres[$name] ?? '[^/]+';
            return '(?P<' . $name . '>' . $rule . ')';
        }, $this->uri);

        // 끝 슬래시 허용
        $regex = '#^' . rtrim($regex, '/') . '/?$#u';
        $this->compiledPattern = $regex;
        return $regex;
    }
}
