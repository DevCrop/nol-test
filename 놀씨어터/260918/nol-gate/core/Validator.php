<?php

require_once dirname(__DIR__) . '/lib/EmailValidator.php';

class Validator {
    protected $errors = [];

    public function require($field, $value, $label = '') {
        if (is_null($value) || trim($value) === '') {
            $this->errors[] = "{$label}을(를) 입력해주세요.";
        }
    }

    public function email($field, $value, $label = '') {
        if (empty($value)) {
            return;
        }
        try {
            EmailValidator::assert((string) $value, false);
        } catch (RuntimeException $e) {
            $this->errors[] = $label !== '' ? "{$label}: " . $e->getMessage() : $e->getMessage();
        }
    }

    public function phone($field, $value, $label = '') {
        if (!empty($value) && !preg_match('/^\d{3}-\d{3,4}-\d{4}$/', $value)) {
            $this->errors[] = "{$label} 형식이 올바르지 않습니다. (예: 010-1234-5678)";
        }
    }

    public function passwordPolicy($field, $value, $label = '') {
        if ($value === null || $value === '') {
            return;
        }

        $name = $label ?: '비밀번호';
        $value = (string)$value;

        if (strlen($value) > 72 || strpos($value, "\0") !== false) {
            $this->errors[] = "{$name}는 72바이트 이하여야 하며 null 문자를 포함할 수 없습니다.";
            return;
        }

        if (strlen($value) < 8) {
            $this->errors[] = "{$name}는 8자 이상이어야 합니다.";
            return;
        }

        $groups = 0;
        $groups += preg_match('/[A-Z]/', $value) ? 1 : 0;
        $groups += preg_match('/[a-z]/', $value) ? 1 : 0;
        $groups += preg_match('/\d/', $value) ? 1 : 0;
        $groups += preg_match('/[^A-Za-z0-9]/', $value) ? 1 : 0;

        if ($groups < 3) {
            $this->errors[] = "{$name}는 영문 대문자, 영문 소문자, 숫자, 특수문자 중 3가지 이상을 포함해야 합니다.";
        }
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function fails(): bool {
        return !empty($this->errors);
    }
}
