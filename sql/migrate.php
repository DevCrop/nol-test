<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/inc/lib/security.bootstrap.php';
if (APP_ENV === 'production') { fwrite(STDERR, "Production: use php sql/release.php check|up\n"); exit(1); }
require_once dirname(__DIR__) . '/inc/lib/db.php';

$pdo = DB::getInstance();
if (!$pdo) { fwrite(STDERR, "database unavailable\n"); exit(1); }

$pdo->exec("CREATE TABLE IF NOT EXISTS blue_schema_migrations (
    version varchar(32) NOT NULL PRIMARY KEY,
    applied_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$version = '202609210001_security';
$check = $pdo->prepare('SELECT COUNT(*) FROM blue_schema_migrations WHERE version = ?');
$check->execute([$version]);
$securityApplied = (int) $check->fetchColumn() > 0;

function blue_has_column(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!$securityApplied) {
$columns = [
    'email' => "varchar(190) NULL AFTER uname",
    'login_fail_count' => "int NOT NULL DEFAULT 0",
    'login_locked_until' => "datetime NULL",
    'last_login_at' => "datetime NULL",
    'password_changed_at' => "datetime NULL",
    'password_must_change' => "tinyint(1) NOT NULL DEFAULT 0",
    'login_token' => "varchar(64) NULL",
];
foreach ($columns as $name => $definition) {
    if (!blue_has_column($pdo, 'nb_admin', $name)) {
        $pdo->exec("ALTER TABLE nb_admin ADD COLUMN `{$name}` {$definition}");
    }
}
$pdo->exec("ALTER TABLE nb_admin MODIFY upwd varchar(255) NOT NULL");

$pdo->exec("CREATE TABLE IF NOT EXISTS nb_admin_audit (
    no bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sitekey varchar(16) NOT NULL,
    actor_no int NOT NULL DEFAULT 0,
    actor_uid varchar(64) NOT NULL DEFAULT '',
    actor_ip varchar(45) NOT NULL DEFAULT '',
    action varchar(16) NOT NULL,
    entity varchar(64) NOT NULL,
    target_no bigint NOT NULL DEFAULT 0,
    target_label varchar(255) NOT NULL DEFAULT '',
    detail_json text NULL,
    created_at datetime NOT NULL,
    KEY idx_actor_created (actor_no, created_at),
    KEY idx_target (entity, target_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS nb_admin_privacy_access (
    no bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sitekey varchar(16) NOT NULL,
    actor_no int NOT NULL DEFAULT 0,
    actor_uid varchar(64) NOT NULL DEFAULT '',
    actor_ip varchar(45) NOT NULL DEFAULT '',
    action varchar(16) NOT NULL,
    entity varchar(64) NOT NULL,
    target_no bigint NOT NULL DEFAULT 0,
    subject_label varchar(255) NOT NULL DEFAULT '',
    task varchar(255) NOT NULL DEFAULT '',
    reason varchar(500) NOT NULL DEFAULT '',
    created_at datetime NOT NULL,
    KEY idx_actor_created (actor_no, created_at),
    KEY idx_target (entity, target_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $pdo->prepare('INSERT INTO blue_schema_migrations (version, applied_at) VALUES (?, NOW())');
$stmt->execute([$version]);
echo "applied {$version}\n";
} else {
    echo "already applied {$version}\n";
}

$accountVersion = '202609210002_account_management';
$check->execute([$accountVersion]);
if ((int) $check->fetchColumn() === 0) {
    $accountColumns = [
        'role_code' => "varchar(16) NOT NULL DEFAULT 'admin'",
        'idle_locked_at' => "datetime NULL",
        'created_at' => "datetime NULL",
        'updated_at' => "datetime NULL",
    ];
    foreach ($accountColumns as $name => $definition) {
        if (!blue_has_column($pdo, 'nb_admin', $name)) {
            $pdo->exec("ALTER TABLE nb_admin ADD COLUMN `{$name}` {$definition}");
        }
    }
    $pdo->exec("UPDATE nb_admin SET created_at = COALESCE(created_at, last_login_at, password_changed_at, NOW())");
    $pdo->exec("UPDATE nb_admin SET role_code = 'super' WHERE no = (SELECT first_no FROM (SELECT MIN(no) AS first_no FROM nb_admin WHERE sitekey = 'BLUESQ') first_admin)");
    $stmt = $pdo->prepare('INSERT INTO blue_schema_migrations (version, applied_at) VALUES (?, NOW())');
    $stmt->execute([$accountVersion]);
    echo "applied {$accountVersion}\n";
} else {
    echo "already applied {$accountVersion}\n";
}
