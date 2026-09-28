<?php

require_once dirname(__DIR__) . '/core/Validator.php';
require_once dirname(__DIR__) . '/Model/AccountModel.php';
require_once __DIR__ . '/EmailValidator.php';

class AccountValidator
{
    /** @var list<string> */
    private $errors = [];
    /** @var array<string, string> */
    private $values = [];

    public static function create(array $input): self
    {
        return (new self())->run($input, true, null);
    }

    public static function update(array $input, int $exceptId): self
    {
        return (new self())->run($input, false, $exceptId);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, string> */
    public function values(): array
    {
        return $this->values;
    }

    private function run(array $input, bool $needPassword, ?int $exceptId): self
    {
        $uid = trim((string) ($input['uid'] ?? ''));
        $uname = trim(preg_replace('/\s+/u', ' ', (string) ($input['uname'] ?? '')));
        $emailRaw = (string) ($input['email'] ?? '');
        $phoneRaw = (string) ($input['phone'] ?? '');
        $upwd = (string) ($input['upwd'] ?? '');
        $confirm = (string) ($input['upwd_confirm'] ?? '');

        $this->uid($uid, $exceptId);
        $this->uname($uname);
        $this->email($emailRaw, $exceptId);
        $this->phone($phoneRaw, $exceptId);
        $this->password($upwd, $confirm, $needPassword);

        return $this;
    }

    private function uid(string $uid, ?int $exceptId): void
    {
        if ($uid === '') {
            $this->errors[] = '아이디를 입력해주세요.';
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $uid)) {
            $this->errors[] = '아이디는 영문, 숫자, 밑줄만 4~20자로 입력하세요.';
            return;
        }
        $this->values['uid'] = $uid;
        if (AccountModel::taken('uid', $uid, $exceptId)) {
            $this->errors[] = '이미 사용 중인 아이디입니다.';
        }
    }

    private function uname(string $uname): void
    {
        if ($uname === '') {
            $this->errors[] = '이름을 입력해주세요.';
            return;
        }
        if (mb_strlen($uname, 'UTF-8') < 2 || mb_strlen($uname, 'UTF-8') > 20 || !preg_match('/^[가-힣a-zA-Z\s]+$/u', $uname)) {
            $this->errors[] = '이름은 한글 또는 영문만 2~20자로 입력하세요.';
            return;
        }
        $this->values['uname'] = $uname;
    }

    private function email(string $raw, ?int $exceptId): void
    {
        if (trim($raw) === '') {
            $this->errors[] = '이메일을 입력해주세요.';
            return;
        }
        try {
            $email = EmailValidator::assert($raw, false);
        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
            return;
        }
        $this->values['email'] = $email;
        if (AccountModel::taken('email', $email, $exceptId)) {
            $this->errors[] = '이미 사용 중인 이메일입니다.';
        }
    }

    private function phone(string $raw, ?int $exceptId): void
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            $this->values['phone'] = '';
            return;
        }
        if (!preg_match('/^01[016789]\d{7,8}$/', $digits)) {
            $this->errors[] = '연락처 형식이 올바르지 않습니다. (예: 010-1234-5678)';
            return;
        }
        $formatted = strlen($digits) === 11
            ? substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7)
            : substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        $this->values['phone'] = $formatted;
        if (AccountModel::taken('phone', $digits, $exceptId)) {
            $this->errors[] = '이미 사용 중인 연락처입니다.';
        }
    }

    private function password(string $upwd, string $confirm, bool $required): void
    {
        if (!$required && $upwd === '' && $confirm === '') {
            return;
        }
        if ($upwd === '') {
            $this->errors[] = '비밀번호를 입력해주세요.';
            return;
        }
        if ($confirm === '') {
            $this->errors[] = '비밀번호 확인을 입력해주세요.';
            return;
        }
        if ($upwd !== $confirm) {
            $this->errors[] = '비밀번호와 비밀번호 확인이 일치하지 않습니다.';
            return;
        }
        $v = new Validator();
        $v->passwordPolicy('upwd', $upwd, '비밀번호');
        if ($v->fails()) {
            foreach ($v->getErrors() as $err) {
                $this->errors[] = $err;
            }
            return;
        }
        $this->values['upwd'] = $upwd;
    }
}
