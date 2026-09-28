<?php

namespace Http;

class Response
{
    protected string $content = '';
    protected int $status = 200;
    protected array $headers = [];

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->setContent($content);
        $this->setStatus($status);
        $this->headers = $headers;
    }

    /**
     * 콘텐츠 설정
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 상태 코드
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * 헤더
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function headers(array $headers): self
    {
        foreach ($headers as $k => $v) {
            $this->header($k, $v);
        }
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * JSON 응답
     */
    public function json($data, int $status = 200, int $options = JSON_UNESCAPED_UNICODE): self
    {
        $this->setContent(json_encode($data, $options));
        $this->setStatus($status);
        $this->header('Content-Type', 'application/json; charset=utf-8');
        return $this;
    }

    /**
     * Redirect
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->setStatus($status);
        $this->header('Location', $url);
        return $this;
    }

    /**
     * 쿠키
     */
    public function cookie(
        string $name,
        string $value = '',
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $cookie = urlencode($name) . '=' . urlencode($value);

        if ($expire > 0) {
            $cookie .= '; Expires=' . gmdate('D, d-M-Y H:i:s T', $expire);
        }

        if ($path) {
            $cookie .= '; Path=' . $path;
        }

        if ($domain) {
            $cookie .= '; Domain=' . $domain;
        }

        if ($secure) {
            $cookie .= '; Secure';
        }

        if ($httpOnly) {
            $cookie .= '; HttpOnly';
        }

        if ($sameSite) {
            $cookie .= '; SameSite=' . $sameSite;
        }

        $this->header('Set-Cookie', $cookie);
        return $this;
    }

    /**
     * 최종 출력
     */
    public function send(): void
    {
        // 상태 코드
        http_response_code($this->status);

        // 헤더 전송
        foreach ($this->headers as $key => $value) {
            header("$key: $value", true, $this->status);
        }

        // 본문 출력
        echo $this->content;
    }

    /**
     * 헬퍼: 텍스트 응답
     */
    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new static($content, $status, $headers);
    }
}
