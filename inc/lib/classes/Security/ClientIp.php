<?php
namespace Security;

final class ClientIp
{
    public static function get(array $server = null): string
    {
        $server = $server ?? $_SERVER;
        $peer = (string) ($server['REMOTE_ADDR'] ?? '');
        if (!self::allowed($peer, self::items(TRUSTED_PROXY_CIDRS))) return $peer;
        $chain = array_reverse(array_map('trim', explode(',', (string) ($server['HTTP_X_FORWARDED_FOR'] ?? ''))));
        foreach ($chain as $candidate) {
            if (!filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return $peer;
            // ALBs append the observed client; attacker-controlled leftmost entries are not authoritative.
            if (!self::allowed($candidate, self::items(TRUSTED_PROXY_CIDRS))) return $candidate;
        }
        return $peer;
    }

    public static function allowed(string $ip, array $rules): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
        foreach ($rules as $rule) {
            if ($rule === $ip) return true;
            if (strpos($rule, '/') === false) continue;
            [$network, $bits] = array_pad(explode('/', $rule, 2), 2, '');
            $bits = filter_var($bits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 32]]);
            $ipLong = ip2long($ip); $netLong = ip2long($network);
            if ($bits === false || $ipLong === false || $netLong === false) continue;
            $mask = $bits === 0 ? 0 : (-1 << (32 - $bits));
            if (($ipLong & $mask) === ($netLong & $mask)) return true;
        }
        return false;
    }

    public static function items(string $csv): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $csv)), 'strlen'));
    }
}
