<?php

namespace Http;

class Request
{
    protected array $get;
    protected array $post;
    protected array $cookies;
    protected array $files;
    protected array $server;
    protected ?string $rawContent;

    public function __construct()
    {
        $this->get    = $_GET ?? [];
        $this->post   = $_POST ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->files  = $_FILES ?? [];
        $this->server = $_SERVER ?? [];

        $this->rawContent = file_get_contents('php://input') ?: null;
    }

    /**
     * GET/POST/Cookie/Server 값 가져오기
     */
    public function input(string $key, $default = null)
    {
        return $this->post[$key]
            ?? $this->get[$key]
            ?? $this->cookies[$key]
            ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function query(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * 서버 관련 정보
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri    = $this->server['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        return $pos !== false ? substr($uri, 0, $pos) : $uri;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function header(string $key, $default = null)
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$headerKey] ?? $default;
    }

    public function headers(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function isSecure(): bool
    {
        return (
            (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ($this->server['SERVER_PORT'] ?? 80) == 443
        );
    }

    public function getContent(): ?string
    {
        return $this->rawContent;
    }

    public function json(bool $assoc = true)
    {
        if (empty($this->rawContent)) {
            return null;
        }
        return json_decode($this->rawContent, $assoc);
    }
}
