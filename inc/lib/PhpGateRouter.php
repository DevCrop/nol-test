<?php

/**
 * Shared-host PHP front controller; never includes a client-supplied filesystem path.
 * Apache's normal PHP PATH_INFO handling is sufficient; no RewriteRule is used.
 */
final class PhpGateRouter
{
    private $root;
    private $admin;
    private $settings = [];

    public function __construct(string $root, string $admin)
    {
        $this->root = $root;
        $this->admin = $admin;
        foreach (['.env', '.env.gate'] as $settingsFile) {
          if (is_readable($root . '/' . $settingsFile)) {
            foreach (file($root . '/' . $settingsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (preg_match('/^(GATE_HOST|GATE_ROUTE_MODE)=(.*)$/', trim($line), $match)) {
                    $this->settings[$match[1]] = trim(trim($match[2]), "\"'");
                }
            }
          }
        }
    }

    private function setting(string $key): string
    {
        $value = getenv($key);
        return $value !== false ? (string) $value : (string) ($this->settings[$key] ?? '');
    }

    /** Null means the existing public homepage should handle this request. */
    public function start(): ?string
    {
        if (PHP_SAPI === 'cli' || $this->setting('GATE_ROUTE_MODE') !== 'php') return null;
        $host = strtolower((string) preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        if ($host === '' || $host !== strtolower($this->setting('GATE_HOST'))) {
            if (!empty($_SERVER['PATH_INFO'])) self::deny();
            return null;
        }
        $raw = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($raw !== '/' && $raw !== '/index.php' && strpos($raw, '/index.php/') !== 0) self::deny();
        $path = in_array($raw, ['/', '/index.php', '/index.php/'], true) ? '/index.php' : substr($raw, 10);
        // Do not decode twice or normalize traversal into an allowed target.
        if (preg_match('/[%\\\\\x00-\x20\x7f]/', $path) || strpos($path, '//') !== false) self::deny();
        foreach (explode('/', $path) as $segment) {
            if ($segment === '.' || $segment === '..' || (isset($segment[0]) && $segment[0] === '.')) self::deny();
        }
        if (preg_match('#^/pages/(?:admission|member|employment|calendar|sms)(?:/|$)#i', $path) && $this->admin === 'admin') self::deny();
        $relative = ltrim($path, '/');
        $target = $this->resolve($relative);
        if ($target === null) self::deny();
        if (strtolower(pathinfo($target, PATHINFO_EXTENSION)) !== 'php') {
            if (!preg_match('#^(?:resource/|uploads/)#', $relative)) self::deny();
            $this->serve($target);
        }
        if (!preg_match('#^(?:index\.php|mfa\.php|pages/.+\.php|ajax/.+\.php|Controller/.+\.php|lib/login/.+\.php|lib/session/ping\.php|captcha/[^/]+\.php|inc/lib/captcha\.[^/]+\.php)$#D', $relative)) self::deny();
        $physical = '/' . $this->admin . '/' . $relative;
        if (strpos($target, $this->root . '/' . $this->admin . '/') !== 0) $physical = '/' . $relative;
        $_SERVER['SCRIPT_FILENAME'] = $target;
        // Blue's legacy policy keys use physical admin paths; NOL uses its URL base.
        $_SERVER['SCRIPT_NAME'] = $this->admin === 'admin' ? $physical : '/index.php/' . $relative;
        $_SERVER['PHP_SELF'] = '/index.php/' . $relative;
        $_SERVER['PATH_INFO'] = '';
        define('PHP_GATE_ACTIVE', true);
        define('PHP_GATE_ROUTE', $path);
        ob_start(function (string $body): string {
            foreach (headers_list() as $header) {
                if (stripos($header, 'Location:') === 0) {
                    header('Location: ' . $this->url(trim(substr($header, 9))), true);
                }
            }
            $type = '';
            foreach (headers_list() as $header) if (stripos($header, 'Content-Type:') === 0) $type = strtolower($header);
            // Never rewrite binary downloads or JSON payload data.
            if ($type !== '' && strpos($type, 'text/html') === false) return $body;
            // Legacy AJAX handlers may omit a JSON content type; preserve their payload.
            if (preg_match('/^\\s*[\\[{]/', $body)) {
                json_decode($body);
                if (json_last_error() === JSON_ERROR_NONE) return $body;
            }
            if (strpos($body, '<head') !== false) {
                $base = dirname('/index.php' . PHP_GATE_ROUTE) . '/';
                $body = preg_replace('/<head([^>]*)>/i', '<head$1><base href="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '">', $body, 1);
            }
            return $this->rewrite($body);
        });
        chdir(dirname($target));
        return $target;
    }

    private function resolve(string $relative): ?string
    {
        if (preg_match('#^resource/vendor/(?:.*(?:/|^)(?:examples?|tests?)(?:/|$)|tinymce/plugins/(?:example|jbimages)(?:/|$))#i', $relative)) return null;
        $shared = preg_match('#^(?:resource/(?:vendor|images)/|uploads/|captcha/|inc/lib/captcha\.[^/]+\.php$)#', $relative);
        $base = $shared ? $this->root : $this->root . '/' . $this->admin;
        $target = realpath($base . '/' . $relative);
        if ($target === false || !is_file($target) || strpos($target, $base . '/') !== 0) return null;
        if (strpos($relative, 'uploads/') === 0) {
            if (!preg_match('#^uploads/(?:banners?|popups?|works|board|logo|meta)/[^/]+\.(?:png|jpg|jpeg|gif|webp)$#iD', $relative)
                || preg_match('#(?:\.php|\.phtml|\.phar|/file_)#i', $relative)) return null;
        }
        return $target;
    }

    private function serve(string $target): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
            http_response_code(405); header('Allow: GET, HEAD'); exit;
        }
        $types = ['css'=>'text/css', 'js'=>'application/javascript', 'mjs'=>'application/javascript',
            'png'=>'image/png', 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'gif'=>'image/gif',
            'webp'=>'image/webp', 'svg'=>'image/svg+xml', 'ico'=>'image/x-icon',
            'woff'=>'font/woff', 'woff2'=>'font/woff2', 'ttf'=>'font/ttf', 'eot'=>'application/vnd.ms-fontobject',
            'html'=>'text/html', 'htm'=>'text/html', 'json'=>'application/json'];
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (!isset($types[$ext])) self::deny();
        header('Content-Type: ' . $types[$ext]);
        header('X-Content-Type-Options: nosniff');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') {
            if (in_array($ext, ['css','js','mjs','html','htm'], true)) echo $this->rewrite((string) file_get_contents($target));
            else readfile($target);
        }
        exit;
    }

    public function url(string $url): string
    {
        if ($url === '' || $url[0] === '#' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $url)) return $url;
        if ($url === '/index.php' || strpos($url, '/index.php/') === 0) return $url;
        $prefix = '/' . $this->admin;
        if ($url === $prefix) return '/index.php';
        if (strpos($url, $prefix . '/') === 0) $url = substr($url, strlen($prefix));
        if ($url[0] !== '/') {
            $url = dirname(defined('PHP_GATE_ROUTE') ? PHP_GATE_ROUTE : '/index.php') . '/' . $url;
            $parts = [];
            foreach (explode('/', $url) as $part) {
                if ($part === '' || $part === '.') continue;
                if ($part === '..') array_pop($parts); else $parts[] = $part;
            }
            $url = '/' . implode('/', $parts);
        }
        return $url === '/' ? '/' : '/index.php' . $url;
    }

    private function rewrite(string $text): string
    {
        // Only application URL prefixes, never arbitrary strings / JSON / user data.
        $prefixes = 'admin|nol-gate|pages|lib/login|lib/session|ajax|Controller|resource|captcha|uploads';
        $text = preg_replace_callback('#(["\x27\x60])(/(?:' . $prefixes . ')(?=[/"\x27\x60?]))#',
            function (array $match): string { return $match[1] . $this->url($match[2]); }, $text);
        return preg_replace_callback('#url\((["\x27]?)(/(?:resource|uploads)/[^)\s"\x27]+)\1\)#',
            function (array $match): string { return 'url(' . $match[1] . $this->url($match[2]) . $match[1] . ')'; }, $text);
    }

    private static function deny(): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
        exit;
    }
}
