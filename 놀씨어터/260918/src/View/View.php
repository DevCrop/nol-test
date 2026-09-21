<?php

namespace View;

class View
{
    /** 새 엔진 생성 */
    public static function make(string $template, array $data = [], ?string $viewsPath = null): ViewEngine
    {
        return new ViewEngine($template, $data, $viewsPath);
    }

    /** 바로 렌더 결과 문자열 */
    public static function render(string $template, array $data = [], ?string $viewsPath = null): string
    {
        return static::make($template, $data, $viewsPath)->render();
    }

    /** 레이아웃 지정 (extend == setLayout) */
    public static function extend(string $layout): void
    {
        ViewEngine::getInstance()->setLayout($layout);
    }

    /** 섹션 조작 */
    public static function section(string $name): void
    {
        ViewEngine::getInstance()->section($name);
    }

    public static function endSection(): void
    {
        ViewEngine::getInstance()->endSection();
    }

    /** 섹션 출력 */
    public static function yield(string $name, string $default = ''): string
    {
        return ViewEngine::getInstance()->yield($name, $default);
    }

    /** 부분 뷰 인클루드 */
    public static function include(string $template, array $data = []): string
    {
        return ViewEngine::getInstance()->include($template, $data);
    }

    /** 전역 데이터 공유 */
    public static function share(array $data): void
    {
        ViewEngine::getInstance()->mergeData($data);
    }

    /** 이스케이프 헬퍼 */
    public static function e($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8', false);
    }
}