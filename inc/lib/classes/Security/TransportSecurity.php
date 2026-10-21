<?php
namespace Security;

require_once __DIR__ . '/ClientIp.php';

final class TransportSecurity
{
    public static function isSecure(array $server, string $trustedProxyCidrs = ''): bool
    {
        $https = strtolower((string) ($server['HTTPS'] ?? ''));
        if ($https === 'on' || $https === '1' || ($server['SERVER_PORT'] ?? 80) == 443) return true;
        return ClientIp::allowed((string) ($server['REMOTE_ADDR'] ?? ''), ClientIp::items($trustedProxyCidrs))
            && strtolower(trim((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? ''))) === 'https';
    }

    /** Temporary HTTP is an explicit, exact-host exception, never a global downgrade. */
    public static function allowsTemporaryHttp(array $server, string $gateHost, bool $enabled, string $trustedProxyCidrs = ''): bool
    {
        $host = strtolower((string) ($server['HTTP_HOST'] ?? ''));
        $gateHost = strtolower(trim($gateHost));
        if (!$enabled || !preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/D', $gateHost)) return false;
        if (!preg_match('/^([a-z0-9]+(?:[.-][a-z0-9]+)*)(?::[0-9]{1,5})?$/D', $host, $matches)) return false;
        return $matches[1] === $gateHost && !self::isSecure($server, $trustedProxyCidrs);
    }
}
