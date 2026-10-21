<?php
namespace Http;

require_once dirname(__DIR__, 2) . '/inc/lib/GateAllowlist.php';

final class TransportSecurity
{

    /** Temporary HTTP is an explicit, exact-host exception, never a global downgrade. */
    public static function allowsTemporaryHttp(array $server, string $gateHost, bool $enabled, string $trustedProxyCidrs = ''): bool
    {
        $host = strtolower((string) ($server['HTTP_HOST'] ?? ''));
        $gateHost = strtolower(trim($gateHost));
        if (!$enabled || !preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/D', $gateHost)) return false;
        if (!preg_match('/^([a-z0-9]+(?:[.-][a-z0-9]+)*)(?::[0-9]{1,5})?$/D', $host, $matches)) return false;
        return $matches[1] === $gateHost && !self::isSecure($server, $trustedProxyCidrs);
    }
    /** Forwarded transport metadata is authoritative only from registered peers. */
    public static function isSecure(array $server, string $trustedProxyCidrs = ''): bool
    {
        $https = strtolower((string) ($server['HTTPS'] ?? ''));
        if ((!empty($server['HTTPS']) && $https !== 'off') || ($server['SERVER_PORT'] ?? 80) == 443) {
            return true;
        }
        $rules = \GateAllowlist::rulesFrom($trustedProxyCidrs);
        if ($rules === [] || !\GateAllowlist::allows((string) ($server['REMOTE_ADDR'] ?? ''), $rules)) {
            return false;
        }
        return strtolower(trim((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? ''))) === 'https';
    }
}
