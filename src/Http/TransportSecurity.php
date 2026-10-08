<?php
namespace Http;

require_once dirname(__DIR__, 2) . '/inc/lib/GateAllowlist.php';

final class TransportSecurity
{
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
