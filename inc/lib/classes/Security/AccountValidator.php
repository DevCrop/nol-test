<?php
namespace Security;

/** NOL AccountValidator/Validator 정책을 블루 PDO 모델과 분리하여 재사용. */
final class AccountValidator
{
    public const PASSWORD_HELP = '8~72바이트, 영문 대문자·소문자·숫자·특수문자 중 3가지 이상을 포함하세요.';

    public static function password(string $password, string $confirm, string $uid = ''): void
    {
        if ($password !== $confirm) throw new \RuntimeException('비밀번호 확인이 일치하지 않습니다.');
        $groups = 0;
        foreach (['/[A-Z]/', '/[a-z]/', '/\d/', '/[^A-Za-z0-9]/'] as $pattern) $groups += preg_match($pattern, $password) ? 1 : 0;
        if (strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false || $groups < 3) throw new \RuntimeException(self::PASSWORD_HELP);
        if ($uid !== '' && stripos($password, $uid) !== false) throw new \RuntimeException('비밀번호에 아이디를 포함할 수 없습니다.');
    }

    public static function name(string $name): string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 20 || !preg_match('/^[가-힣a-zA-Z\s]+$/u', $name)) throw new \RuntimeException('이름은 한글 또는 영문만 2~20자로 입력하세요.');
        return $name;
    }

    public static function email(string $email): string
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new \RuntimeException('올바른 이메일 주소를 입력하세요.');
        return $email;
    }
}
