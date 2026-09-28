<?php 

use Core\Application;
use Core\Config;
use Routing\Route;
use View\ViewEngine;
use Http\Response;

function app(): Application
{
    return Application::getInstance();
}


/**
 * 새 ViewEngine 인스턴스 생성 (라라벨의 view() 느낌)
 * - 반환값: ViewEngine (->setLayout()->render() 체이닝 가능)
 */
if (!function_exists('view')) {
    function view(string $template, array $data = [], ?string $viewsPath = null): ViewEngine
    {
        return new ViewEngine($template, $data, $viewsPath);
    }
}

/**
 * 바로 렌더링 결과 문자열만 받고 싶을 때
 */
if (!function_exists('render')) {
    function render(string $template, array $data = [], ?string $viewsPath = null): string
    {
        return view($template, $data, $viewsPath)->render();
    }
}

/**
 * 레이아웃 지정 (extend == setLayout)
 */
if (!function_exists('extend')) {
    function extend(string $layout): void
    {
        ViewEngine::getInstance()->setLayout($layout);
    }
}

/**
 * 섹션 시작/종료
 */
if (!function_exists('section')) {
    function section(string $name, $content = null): void
    {
        ViewEngine::getInstance()->section($name, $content !== null ? (string)$content : null);
    }
}

if (!function_exists('end_section')) {
    function end_section(): void
    {
        ViewEngine::getInstance()->endSection();
    }
}

/**
 * 레이아웃에서 섹션 출력
 * (yield는 예약어라 yield_section 으로)
 */
if (!function_exists('yield_section')) {
    function yield_section(string $name, string $default = ''): string
    {
        return ViewEngine::getInstance()->yield($name, $default);
    }
}

/**
 * 부분 뷰 포함 (include는 예약어 → view_include)
 */
if (!function_exists('include_view')) {
    function include_view(string $template, array $data = []): string
    {
        return ViewEngine::getInstance()->include($template, $data);
    }
}

/**
 * 전역 데이터 공유 (모든 렌더에서 사용 가능)
 */
if (!function_exists('share_view')) {
    function share_view(array $data): void
    {
        ViewEngine::getInstance()->mergeData($data);
    }
}

/**
 * 이스케이프 헬퍼 (라라벨의 e() 느낌)
 */
if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('safe_url')) {
    function safe_url($url, array $allowedSchemes = ['http', 'https'], bool $allowRelative = true): string
    {
        $url = trim(html_entity_decode((string)$url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '') {
            return '';
        }

        $url = preg_replace('/[\x00-\x1F\x7F\s]+/u', '', $url);
        if ($url === '') {
            return '';
        }

        if ($allowRelative && preg_match('/^(\/|#|\?)/', $url) === 1) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === null || $scheme === false) {
            return $allowRelative ? $url : '';
        }

        if (!in_array(strtolower($scheme), $allowedSchemes, true)) {
            return '';
        }

        return $url;
    }
}

if (!function_exists('safe_asset_url')) {
    function safe_asset_url($url): string
    {
        return safe_url($url, ['http', 'https'], true);
    }
}

if (!function_exists('sanitize_html_fragment')) {
    function sanitize_html_fragment($html, array $allowedTags = [], array $allowedAttributes = []): string
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }

        if (!class_exists('DOMDocument')) {
            return strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        }

        $defaultAllowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
            'ul', 'ol', 'li', 'blockquote',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'div', 'span', 'figure', 'figcaption',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'a', 'img'
        ];
        $allowedTags = $allowedTags ?: $defaultAllowedTags;

        $defaultAllowedAttributes = [
            'a' => ['href', 'target', 'rel', 'title'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'loading', 'decoding'],
            'th' => ['colspan', 'rowspan', 'scope'],
            'td' => ['colspan', 'rowspan'],
        ];
        $allowedAttributes = $allowedAttributes ?: $defaultAllowedAttributes;

        $dangerousTags = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button', 'textarea', 'select'];

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="safe-html-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('safe-html-root');
        if (!$root instanceof DOMElement) {
            $root = $dom->getElementsByTagName('div')->item(0);
        }
        if (!$root instanceof DOMElement) {
            return '';
        }

        $sanitizeNode = static function (DOMNode $node) use (&$sanitizeNode, $dom, $allowedTags, $allowedAttributes, $dangerousTags): void {
            if ($node instanceof DOMElement) {
                $tagName = strtolower($node->tagName);

                if (in_array($tagName, $dangerousTags, true)) {
                    $parentNode = $node->parentNode;
                    if ($parentNode instanceof DOMNode) {
                        $parentNode->removeChild($node);
                    }
                    return;
                }

                if (!in_array($tagName, $allowedTags, true)) {
                    $parent = $node->parentNode;
                    if ($parent instanceof DOMNode) {
                        while ($node->firstChild) {
                            $parent->insertBefore($node->firstChild, $node);
                        }
                        $parent->removeChild($node);
                    }
                    return;
                }

                $allowedForTag = $allowedAttributes[$tagName] ?? [];
                $attributes = [];
                foreach ($node->attributes ?? [] as $attribute) {
                    $attributes[] = $attribute;
                }

                foreach ($attributes as $attribute) {
                    $name = strtolower($attribute->nodeName);
                    if (strpos($name, 'on') === 0 || !in_array($name, $allowedForTag, true)) {
                        $node->removeAttributeNode($attribute);
                        continue;
                    }

                    $value = $attribute->nodeValue ?? '';
                    if ($tagName === 'a' && $name === 'href') {
                        $safeHref = safe_url($value, ['http', 'https', 'mailto', 'tel'], true);
                        if ($safeHref === '') {
                            $node->removeAttribute('href');
                        } else {
                            $node->setAttribute('href', $safeHref);
                        }
                    }

                    if ($tagName === 'img' && $name === 'src') {
                        $safeSrc = safe_asset_url($value);
                        if ($safeSrc === '') {
                            $parentNode = $node->parentNode;
                            if ($parentNode instanceof DOMNode) {
                                $parentNode->removeChild($node);
                            }
                            return;
                        }
                        $node->setAttribute('src', $safeSrc);
                    }

                    if ($tagName === 'a' && $name === 'target') {
                        $target = strtolower((string)$value);
                        if (!in_array($target, ['_self', '_blank'], true)) {
                            $node->setAttribute('target', '_self');
                        }
                    }
                }

                if ($tagName === 'a') {
                    $target = strtolower($node->getAttribute('target'));
                    if ($target === '_blank') {
                        $node->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }

            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }

            foreach ($children as $child) {
                $sanitizeNode($child);
            }
        };

        $rootChildren = [];
        foreach ($root->childNodes as $child) {
            $rootChildren[] = $child;
        }
        foreach ($rootChildren as $child) {
            $sanitizeNode($child);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }
}

if (!function_exists('sanitize_embed_html')) {
    function sanitize_embed_html($html): string
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }

        if (!class_exists('DOMDocument')) {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="safe-embed-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('safe-embed-root');
        if (!$root instanceof DOMElement) {
            $root = $dom->getElementsByTagName('div')->item(0);
        }
        if (!$root instanceof DOMElement) {
            return '';
        }

        $allowedTags = ['script', 'noscript', 'iframe'];
        $allowedAttributes = [
            'script' => ['src', 'async', 'defer', 'type', 'crossorigin', 'referrerpolicy'],
            'iframe' => ['src', 'width', 'height', 'style', 'title', 'loading', 'referrerpolicy', 'allow', 'allowfullscreen', 'frameborder'],
            'noscript' => [],
        ];

        $sanitizeNode = static function (DOMNode $node) use (&$sanitizeNode, $allowedTags, $allowedAttributes): void {
            if ($node instanceof DOMElement) {
                $tagName = strtolower($node->tagName);

                if (!in_array($tagName, $allowedTags, true)) {
                    $parentNode = $node->parentNode;
                    if ($parentNode instanceof DOMNode) {
                        $parentNode->removeChild($node);
                    }
                    return;
                }

                $attrs = [];
                foreach ($node->attributes ?? [] as $attribute) {
                    $attrs[] = $attribute;
                }

                $allowedForTag = $allowedAttributes[$tagName] ?? [];
                foreach ($attrs as $attribute) {
                    $name = strtolower($attribute->nodeName);
                    if (strpos($name, 'on') === 0 || !in_array($name, $allowedForTag, true)) {
                        $node->removeAttributeNode($attribute);
                        continue;
                    }

                    if (($tagName === 'script' || $tagName === 'iframe') && $name === 'src') {
                        $safeSrc = safe_url($attribute->nodeValue ?? '', ['http', 'https'], true);
                        if ($safeSrc === '') {
                            $parentNode = $node->parentNode;
                            if ($parentNode instanceof DOMNode) {
                                $parentNode->removeChild($node);
                            }
                            return;
                        }
                        $node->setAttribute('src', $safeSrc);
                    }
                }

                if ($tagName === 'script') {
                    if (!$node->hasAttribute('src')) {
                        $parentNode = $node->parentNode;
                        if ($parentNode instanceof DOMNode) {
                            $parentNode->removeChild($node);
                        }
                        return;
                    }
                    while ($node->firstChild) {
                        $node->removeChild($node->firstChild);
                    }
                }
            }

            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }

            foreach ($children as $child) {
                $sanitizeNode($child);
            }
        };

        $rootChildren = [];
        foreach ($root->childNodes as $child) {
            $rootChildren[] = $child;
        }
        foreach ($rootChildren as $child) {
            $sanitizeNode($child);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, $default = null)
    {
        $instance = Config::getInstance();

        if ($key === null) {
            return $instance; 
        }

        return $instance->set($key, $default);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = [], bool $absolute = false): string {
        return Application::getInstance()->router()->route($name, $params, $absolute);
    }
}

if (!function_exists('to_route')) {
    /**
     * 라우트 이름 기반 리다이렉트
     *
     * 사용 예:
     *   return to_route('clinic.intro');
     *   return to_route('order.show', ['id' => 1]);
     */
    function to_route(string $name, array $params = [], int $status = 302, bool $absolute = true): Response
    {
        $url = route($name, $params, $absolute);
        return redirect($url, $status);
    }
}



if (!function_exists('response')) {
    /**
     * 텍스트 응답(Response 인스턴스 반환)
     */
    function response(string $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    /**
     * URL로 리다이렉트 (Response 인스턴스 반환)
     *
     * 사용:
     *   return redirect('/news');
     */
    function redirect(string $url, int $status = 302): Response
    {
        return (new Response())->redirect($url, $status);
    }
}

/**
 * 로그인 필요 링크 속성 생성
 * @param bool $isLoggedIn - 로그인 여부
 * @param string $class - 추가할 클래스명 (기본값: 'require-login')
 * @return string - HTML 속성 문자열 (data-require-login만 반환, class는 별도로 추가)
 */
if (!function_exists('require_login_attr')) {
    function require_login_attr(bool $isLoggedIn, string $class = 'require-login'): string
    {
        if ($isLoggedIn) {
            return '';
        }
        return 'data-require-login="true"';
    }
}

/**
 * 로그인 필요 링크 클래스만 반환
 * @param bool $isLoggedIn - 로그인 여부
 * @param string $class - 추가할 클래스명 (기본값: 'require-login')
 * @return string - 클래스 문자열
 */
if (!function_exists('require_login_class')) {
    function require_login_class(bool $isLoggedIn, string $class = 'require-login'): string
    {
        return $isLoggedIn ? '' : e($class);
    }
}

if (!function_exists('redirect_route')) {
    /**
     * 라우트 네임으로 리다이렉트
     *
     * 사용:
     *   return redirect_route('company.news'); // 파라미터 있을 때: ['id' => 1]
     */
    function redirect_route(string $name, array $params = [], int $status = 302, bool $absolute = true): Response
    {
        $url = route($name, $params, $absolute);
        return redirect($url, $status);
    }
}

if (!function_exists('back')) {
    /**
     * 이전 페이지로 리다이렉트 (HTTP_REFERER 우선, 없으면 기본값)
     */
    function back(string $fallback = '/', int $status = 302): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        return redirect($referer ?: $fallback, $status);
    }
}

function current_route(): ?Route
{
    return Application::getInstance()->router()->current();
}

// 필요하면 이름 비교 헬퍼도
function current_route_is(string $name): bool
{
    $route = current_route();
    return $route ? ($route->getName() === $name) : false;
}

function current_route_name(): ?string
{
    $route = current_route();
    return $route ? $route->getName() : null;
}

/**
 * 현재 경로에서 언어 prefix를 변경한 URL 반환
 * 
 * @param string $locale 변경할 언어 코드 (ko, en)
 * @return string 언어 prefix가 변경된 URL
 */
if (!function_exists('locale_url')) {
    function locale_url(string $locale): string
    {
        // 유효한 언어 코드인지 확인
        if (!in_array($locale, locales(), true)) {
            $locale = DEFAULT_LOCALE;
        }

        // 현재 경로 가져오기
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        
        // 쿼리스트링 분리
        $queryString = '';
        $pathOnly = $currentPath;
        if (($pos = strpos($currentPath, '?')) !== false) {
            $pathOnly = substr($currentPath, 0, $pos);
            $queryString = substr($currentPath, $pos);
        }

        // 경로에서 언어 prefix 제거
        $segments = array_values(array_filter(explode('/', trim($pathOnly, '/')), 'strlen'));
        $firstSegment = $segments[0] ?? null;
        
        // 첫 번째 세그먼트가 언어 코드이면 제거
        if ($firstSegment !== null && in_array($firstSegment, locales(), true)) {
            array_shift($segments);
        }

        // 새로운 경로 구성
        if (empty($segments)) {
            // 루트 경로인 경우
            $newPath = '/' . $locale;
        } else {
            // 하위 경로가 있는 경우
            $newPath = '/' . $locale . '/' . implode('/', $segments);
        }

        // 쿼리스트링 추가
        return $newPath . $queryString;
    }
}
