<?php

final class TemporaryPassword
{
    public static function generate(): string
    {
        return 'N!a7' . bin2hex(random_bytes(12));
    }

    public static function deadline(): string
    {
        $seconds = max(300, min(604800, (int) env('TEMP_PASSWORD_TTL_SECONDS', 86400)));
        return date('Y-m-d H:i:s', time() + $seconds);
    }

    public static function assertUsable(array $row): void
    {
        $expires = (string) ($row['temporary_password_expires_at'] ?? '');
        if (!empty($row['must_change_password']) && $expires !== '') {
            $at = strtotime($expires);
            if ($at === false || $at <= time()) {
                throw new RuntimeException('임시 비밀번호가 만료되었습니다. 최고관리자에게 재발급을 요청하세요.');
            }
        }
    }
}
