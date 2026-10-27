<?php
/** Read-only deployment checks. Never loads application bootstrap or changes DB/data/settings. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

final class DeploymentPreflight
{
    public static function parseSettings(string $path): array
    {
        $result = [];
        if (!is_readable($path)) return $result;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (array_key_exists($key, $result)) throw new RuntimeException('Duplicate configuration key (values redacted).');
            $result[$key] = trim(trim($value), "\"'");
        }
        return $result;
    }

    public static function enabled($value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'on'], true);
    }

    /** Returned labels never contain a credential or a configured address. */
    public static function configuration(array $c, bool $blue): array
    {
        $domain = $blue ? 'bluesquare.kr' : 'noltheater-daehakro.com';
        $ipRules = array_filter(preg_split('/[\s,]+/', trim((string) ($c['GATE_ALLOW_IPS'] ?? ''))));
        $ipsValid = count($ipRules) > 0;
        foreach ($ipRules as $rule) {
            $pair = explode('/', $rule);
            $ipsValid = $ipsValid && count($pair) <= 2 && filter_var($pair[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                && (!isset($pair[1]) || (ctype_digit($pair[1]) && (int) $pair[1] <= 32));
        }
        $out = [
            'production_environment' => ($c['APP_ENV'] ?? '') === 'production',
            'expected_gate_host' => strtolower($c['GATE_HOST'] ?? '') === 'gate.' . $domain,
            'public_host' => in_array(strtolower($c['PUBLIC_HOST'] ?? ''), [$domain, 'www.' . $domain], true),
            'php_routing' => ($c['GATE_ROUTE_MODE'] ?? '') === 'php',
            'gate_enforcement' => self::enabled($c['GATE_ENFORCE'] ?? ''),
            'http_preview_disabled' => !self::enabled($c['GATE_HTTP_PREVIEW'] ?? ''),
            'admin_ip_rules_configured' => $ipsValid,
            'secure_cookie' => self::enabled($c['SESSION_SECURE'] ?? ''),
            'host_only_cookie' => empty($c['SESSION_DOMAIN']),
            'same_site_cookie' => ($c['SESSION_SAMESITE'] ?? '') === 'Lax',
            'project_session_name' => ($c['SESSION_NAME'] ?? '') === ($blue ? 'BLUESQGATESESSID' : 'NOLGATESESSID'),
            'mfa_enabled' => self::enabled($c['MFA_ENABLED'] ?? ''),
            'smtp_host' => !empty($c['SMTP_HOST']),
            'smtp_port' => in_array((int) ($c['SMTP_PORT'] ?? 0), [465, 587], true),
            'smtp_sender' => ($c['SMTP_FROM'] ?? '') === ($blue ? 'bluesquare@nol-theater.com' : 'daehakro@nol-theater.com'),
            'smtp_credentials_present' => !empty($c['SMTP_USER']) && !empty($c['SMTP_PASS']),
            'database_name' => ($c['DB_NAME'] ?? '') === ($blue ? 'dbusrbluesq0211' : 'dbusrdaehakro0605'),
            'database_configuration_present' => !empty($c['DB_HOST']) && !empty($c['DB_USER']) && !empty($c['DB_PASS']),
            'not_qa_database_user' => ($c['DB_USER'] ?? '') !== 'qa_app',
        ];
        return $out;
    }

    public static function run(string $root, string $phase): int
    {
        $blue = is_file($root . '/inc/lib/security.bootstrap.php');
        $site = $blue ? 'BLUESQ' : 'NOLTHE';
        $pass = 0; $fail = 0;
        $check = static function ($ok, string $label) use (&$pass, &$fail): void {
            if ($ok) { $pass++; echo "PASS " . $label . "\n"; }
            else { $fail++; echo "HOLD " . $label . "\n"; }
        };
        if (!in_array($phase, ['source', 'config', 'compat', 'schema', 'all'], true)) {
            fwrite(STDERR, "Usage: php deploy/preflight.php source|config|compat|schema|all\n"); return 2;
        }
        try {
            if ($phase === 'source' || $phase === 'all') {
                $check(PHP_VERSION_ID >= 70400, 'PHP >= 7.4 syntax/runtime requirement');
                if (PHP_VERSION_ID >= 80000) echo "REVIEW PHP differs from the PHP 7.4 acceptance baseline; rerun full QA on hosting version.\n";
                foreach (['pdo_mysql','gd','mbstring','dom','openssl','fileinfo','session'] as $extension) {
                    $check(extension_loaded($extension), 'extension ' . $extension);
                }
                foreach (['index.php','inc/lib/base.class.php','inc/lib/PhpGateRouter.php',
                    $blue ? 'inc/lib/security.bootstrap.php' : 'config/env.php',
                    ($blue ? 'admin' : 'nol-gate') . '/index.php'] as $file) $check(is_file($root . '/' . $file), 'required file ' . $file);
                $manifest = json_decode((string) file_get_contents($root . '/sql/release/manifest.json'), true);
                $check(($manifest['sitekey'] ?? '') === $site, 'SQL release identity');
                foreach (['preflight.sql','changes.sql','target-schema.json'] as $file) {
                    $hash = $manifest['files'][$file] ?? '';
                    $check($hash !== '' && is_file($root . '/sql/release/' . $file)
                        && hash_equals($hash, hash_file('sha256', $root . '/sql/release/' . $file)), 'immutable SQL checksum ' . $file);
                }
                echo "NOTE No production readiness claim: source checks do not validate hosting/TLS/data/content.\n";
            }
            if ($phase === 'config' || $phase === 'all') {
                $base = self::parseSettings($root . '/.env');
                $overlay = self::parseSettings($root . '/.env.gate');
                if ($overlay) {
                    foreach (['GATE_HOST','GATE_ROUTE_MODE'] as $key) {
                        $check(!isset($overlay[$key]) || (isset($base[$key]) && $base[$key] === $overlay[$key]),
                            'dotenv and gate overlay agree: ' . $key);
                    }
                    if (!$blue) $check(false, 'remove legacy NOL preview overlay outside web root before full security deployment');
                }
                $settings = array_merge($base, $overlay);
                foreach (array_keys($settings + array_fill_keys(['APP_ENV','GATE_HOST','GATE_ROUTE_MODE','GATE_HTTP_PREVIEW',
                    'GATE_ENFORCE','GATE_ALLOW_IPS','GATE_ALLOW_HTTP','SESSION_SECURE','MFA_ENABLED'], '')) as $key) {
                    if (getenv($key) !== false) $settings[$key] = getenv($key);
                }
                foreach (self::configuration($settings, $blue) as $label => $ok) $check($ok, $label);
                if (self::enabled($settings['GATE_ALLOW_HTTP'] ?? '')) {
                    echo "WARNING TEMPORARY_HTTP: gate credentials, OTP, sessions and uploads are unencrypted; HTTPS security requirement remains DEFERRED.\n";
                }
                echo "NOTE SMTP presence only; actual From/MFA receipt and hosting TLS/IP must be confirmed separately.\n";
            }
            if (in_array($phase, ['compat','schema','all'], true)) {
                $settings = self::parseSettings($root . '/.env');
                foreach (['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASS'] as $key) {
                    if (getenv($key) !== false) $settings[$key] = getenv($key);
                }
                $pdo = new PDO('mysql:host=' . ($settings['DB_HOST'] ?? '') . ';port=' . ($settings['DB_PORT'] ?? 3306)
                    . ';dbname=' . ($settings['DB_NAME'] ?? '') . ';charset=utf8mb4',
                    $settings['DB_USER'] ?? '', $settings['DB_PASS'] ?? '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
                $check(true, 'database connection (credentials redacted)');
                $sql = (string) file_get_contents($root . '/sql/release/changes.sql');
                preg_match_all('/COLLATE[=\s]+([a-zA-Z0-9_]+)/i', $sql, $collations);
                foreach (array_unique($collations[1]) as $collation) {
                    $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLLATIONS WHERE COLLATION_NAME=?');
                    $st->execute([$collation]);
                    $check((int) $st->fetchColumn() === 1, 'server supports SQL collation ' . $collation);
                }
                $st = $pdo->prepare('SELECT COUNT(*) FROM nb_admin WHERE sitekey=?');
                $st->execute([$site]); $check((int) $st->fetchColumn() > 0, 'expected project admin data');
                if ($phase !== 'compat') {
                    $spec = json_decode((string) file_get_contents($root . '/sql/release/target-schema.json'), true);
                    $complete = true;
                    $st = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin'");
                    $transactional = strcasecmp((string) $st->fetchColumn(), 'InnoDB') === 0;
                    $check($transactional, 'nb_admin transactional engine InnoDB');
                    $complete = $complete && $transactional;
                    {
                        foreach ($blue?['nb_admin_audit']:['nb_admin_acl', 'nb_roles', 'nb_admin_audit'] as $dependency) {
                            $st = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
                            $st->execute([$dependency]);
                            $ok = strcasecmp((string) $st->fetchColumn(), 'InnoDB') === 0;
                            $check($ok, 'transaction dependency engine ' . $dependency);
                            $complete = $complete && $ok;
                        }
                    }
                    $st = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='temporary_password_expires_at' AND DATA_TYPE='datetime' AND IS_NULLABLE='YES'");
                    $temporarySchema = (int) $st->fetchColumn() === 1;
                    $check($temporarySchema, 'temporary password expiry schema');
                    $complete = $complete && $temporarySchema;
                    foreach(['authority_valid_from','authority_valid_until'] as $policyColumn){
                        $st=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME=? AND DATA_TYPE='datetime' AND IS_NULLABLE='YES'");
                        $st->execute([$policyColumn]);$ok=(int)$st->fetchColumn()===1;
                        $check($ok,'account authority period schema '.$policyColumn);$complete=$complete&&$ok;
                    }
                    foreach ($spec as $table => $tableSpec) {
                        $st = $pdo->prepare('SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
                        $st->execute([$table]); $actual = [];
                        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $col) $actual[$col['COLUMN_NAME']] = $col;
                        foreach ($tableSpec['columns'] as $column) {
                            $ok = isset($actual[$column['COLUMN_NAME']]) && $actual[$column['COLUMN_NAME']] === $column;
                            $check($ok, 'schema ' . $table . '.' . $column['COLUMN_NAME']); $complete = $complete && $ok;
                        }
                    }
                    if ($complete) {
                        $role = $blue ? "role_code='super'" : 'role_id=1';
                        $st = $pdo->prepare("SELECT email FROM nb_admin WHERE sitekey=? AND active_status='Y' AND " . $role);
                        $st->execute([$site]); $superEmails = $st->fetchAll(PDO::FETCH_COLUMN);
                        $check(count($superEmails) > 0, 'active super administrator exists');
                        $validEmails = array_filter($superEmails, static function ($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; });
                        $check(count($validEmails) > 0, 'at least one active super administrator has a valid MFA email');
                    }
                }
                echo "NOTE Read-only SELECT checks only. check/up and full staging QA are still required.\n";
            }
        } catch (Throwable $e) {
            $check(false, 'preflight could not finish: ' . get_class($e) . ' (details/credentials not printed)');
        }
        echo "RESULT {$pass} PASS / {$fail} HOLD\n";
        return $fail ? 1 : 0;
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $targetRoot = isset($argv[2]) ? realpath($argv[2]) : dirname(__DIR__);
    if ($targetRoot === false || !is_dir($targetRoot)) { fwrite(STDERR, "Invalid project root.\n"); exit(2); }
    exit(DeploymentPreflight::run($targetRoot, $argv[1] ?? 'source'));
}
