<?php
namespace Security;

final class TemporaryPassword
{
    public static function generate(): string { return 'N!a7'.bin2hex(random_bytes(12)); }
    public static function deadline(): string { return date('Y-m-d H:i:s',time()+max(300,min(604800,(int)\blue_env('TEMP_PASSWORD_TTL_SECONDS',86400)))); }
    public static function assertUsable(array $row): void
    {
        if (!empty($row['password_must_change']) && !empty($row['temporary_password_expires_at']) && strtotime($row['temporary_password_expires_at'])<=time()) {
            throw new \RuntimeException('임시 비밀번호가 만료되었습니다. 최고관리자에게 재발급을 요청하세요.');
        }
    }
}
