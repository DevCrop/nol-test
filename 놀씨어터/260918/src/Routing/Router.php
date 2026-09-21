<?php

namespace Routing;

use Http\Request;
use Http\Response;

class Router
{
    /** @var array<string,Route[]> */
    protected array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    protected ?Route $currentRoute = null;

    /** @var array<string,Route> 이름 → Route 맵 */
    protected array $named = [];

    /**
     * 그룹 스택: 각 요소는 ['prefix'=>string, 'middleware'=>string[]]
     * @var array<int,array{prefix:string, middleware:array}>
     */
    protected array $groupStack = [];

    /* ---------- registration ---------- */

    public function get(string $uri, $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, $action): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param callable|string|array $action
     */
    public function addRoute(string $method, string $uri, $action): Route
    {
        $method = strtoupper($method);

        // 그룹 prefix/middleware 적용
        [$fullUri, $groupMiddleware] = $this->applyGroupAttributes($uri);

        $route = (new Route($method, $fullUri, $action))
            ->setRouter($this); // ★ 라우터 역주입(이름 등록용)

        if (!empty($groupMiddleware)) {
            $route->middleware($groupMiddleware);
        }

        $this->routes[$method][] = $route;
        return $route;
    }

    /**
     * route 그룹
     *
     * @param array{prefix?:string, middleware?:string|string[]} $attributes
     * @param callable $callback function(Router $router): void
     */
    public function group(array $attributes, callable $callback): void
    {
        $prefix = isset($attributes['prefix']) ? '/' . trim($attributes['prefix'], '/') : '';
        $mw     = isset($attributes['middleware'])
            ? (is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']])
            : [];

        // 부모 그룹 상속 고려
        $parent = end($this->groupStack) ?: ['prefix' => '', 'middleware' => []];
        $accPrefix = rtrim($parent['prefix'] . $prefix, '/');
        if ($accPrefix === '') $accPrefix = '';

        $accMw = array_values(array_unique(array_merge($parent['middleware'], $mw)));

        $this->groupStack[] = [
            'prefix'     => $accPrefix,
            'middleware' => $accMw,
        ];

        try {
            $callback($this);
        } finally {
            array_pop($this->groupStack);
        }
    }

    /* ---------- named routes ---------- */

    /**
     * Route::name()에서 호출됨
     *
     * @param Route $route
     * @param string|null $oldName 이전 이름(변경 시 제거용)
     */
    public function registerNamedRoute(Route $route, ?string $oldName = null): void
    {
        if ($oldName && isset($this->named[$oldName]) && $this->named[$oldName] === $route) {
            unset($this->named[$oldName]);
        }

        $name = $route->getName();
        if ($name === null || $name === '') {
            return;
        }

        if (isset($this->named[$name]) && $this->named[$name] !== $route) {
            throw new \RuntimeException("Route name already registered: {$name}");
        }

        $this->named[$name] = $route;
    }

    public function hasRoute(string $name): bool
    {
        return isset($this->named[$name]);
    }

    public function getRouteByName(string $name): ?Route
    {
        return $this->named[$name] ?? null;
    }

    /**
     * 이름 기반 URL 생성
     * - {param}을 $params로 치환 (공백→하이픈→rawurlencode)
     * - 남은 $params는 쿼리스트링으로
     * - $absolute=true면 스킴/호스트 포함
     */
    public function route(string $name, array $params = [], bool $absolute = false): string
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            throw new \RuntimeException("Named route not found: {$name}");
        }

        $used = [];
        $uri = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) use (&$used, $params, $name) {
            $key = $m[1];
            if (!array_key_exists($key, $params)) {
                throw new \RuntimeException("Missing parameter '{$key}' for route '{$name}'");
            }
            $val = (string)$params[$key];

            // 공백류 → 하이픈, 연속 하이픈 정리
            $val = preg_replace('/\s+/u', '-', trim($val));
            $val = preg_replace('/-+/', '-', $val);

            $used[] = $key;
            return rawurlencode($val);
        }, $route->getUri());

        // 남은 파라미터는 쿼리로
        $leftover = array_diff_key($params, array_flip($used));
        if (!empty($leftover)) {
            $qs = http_build_query($leftover);
            if ($qs !== '') {
                $uri .= (strpos($uri, '?') === false ? '?' : '&') . $qs;
            }
        }

        // ★ 로케일 prefix 비활성화 (prefix 없이 URL 생성)
        // $current = function_exists('locale') ? locale() : DEFAULT_LOCALE;
        // if ($current && $current !== DEFAULT_LOCALE) {
        //     $uri = '/' . rawurlencode($current) . $uri;
        // }

        if ($absolute) {
            $isHttps = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
                (($_SERVER['SERVER_PORT'] ?? 80) == 443)
            );
            $scheme = $isHttps ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $uri    = $scheme . '://' . $host . $uri;
        }

        return $uri;
    }

    /* ---------- dispatch ---------- */

    /**
     * 성공 시: ['handler' => mixed, 'params' => array]
     * 실패 시: null
     */

    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        $path   = '/' . ltrim($path, '/');

        // 매치 재시도 전 초기화
        $this->currentRoute = null;

        foreach ($this->routes[$method] ?? [] as $route) {
            if ($route->matches($method, $path)) {
                // ★ 현재 라우트 저장
                $this->currentRoute = $route;

                return [
                    'handler' => $route->getAction(),
                    'params'  => $route->getParams(),
                    'route'   => $route,               // ★ 필요하면 꺼내쓸 수 있게 포함
                    // 'middleware' => $route->getMiddleware(),
                ];
            }
        }

        return null;
    }


    /* ---------- helpers ---------- */

    /**
     * 그룹 누적 prefix/middleware 적용 결과 반환
     * @return array{0:string,1:string[]}
     */
    protected function applyGroupAttributes(string $uri): array
    {
        $prefix = '';
        $mw     = [];

        if (!empty($this->groupStack)) {
            $top = end($this->groupStack);
            $prefix = $top['prefix'] ?? '';
            $mw     = $top['middleware'] ?? [];
        }

        $fullUri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
        $fullUri = '/' . ltrim($fullUri, '/');

        return [$fullUri, $mw];
    }

    /**
     * (선택) 405 판별용 – 같은 path의 다른 메서드 목록 수집
     */
    protected function allowedMethodsForPath(string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $m => $routes) {
            foreach ($routes as $route) {
                if ($route->getUri() === $path) {
                    $allowed[] = $m;
                }
            }
        }
        return array_values(array_unique($allowed));
    }

    public function dispatch(Request $request): ?array
    {
        return $this->match($request->method(), $request->path());
    }

    public function current(): ?Route
    {
        return $this->currentRoute;
    }

    /**
     * 이름이 있는 모든 라우트 반환
     * @return array<string,Route> 이름 => Route 맵
     */
    public function getNamedRoutes(): array
    {
        return $this->named;
    }

    /**
     * 모든 라우트 반환 (메서드별)
     * @return array<string,Route[]>
     */
    public function getAllRoutes(): array
    {
        return $this->routes;
    }
}