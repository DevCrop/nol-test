<?php
/** CLI-only additive SQL release; exports schema, never account data. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$isBlue = is_file($root . '/inc/lib/security.bootstrap.php');
require_once $isBlue ? $root . '/inc/lib/security.bootstrap.php' : $root . '/config/env.php';
$env = $isBlue ? 'blue_env' : 'env';
$pdo = new PDO('mysql:host='.$env('DB_HOST','db').';port='.$env('DB_PORT',3306).';dbname='.$env('DB_NAME','').';charset=utf8mb4', $env('DB_USER',''), $env('DB_PASS',''), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$project = $isBlue ? 'bluesquare' : 'noltheater';
$site = $isBlue ? 'BLUESQ' : 'NOLTHE';
$dir = __DIR__ . '/release';
$cmd = $argv[1] ?? 'check';
$q = static function(string $s): string { return "'".str_replace("'", "''", $s)."'"; };
$parts = static function(string $sql): array {
    $sql = preg_replace('/^\s*--[^\r\n]*$/m', '', $sql);
    return array_values(array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql))));
};
$guard = static function(string $condition, string $label) use ($q): string {
    return "-- Precondition: {$label}\nSET @release_check = IF({$condition}, 'SELECT 1', ".$q('RELEASE_ABORT_'.$label).");\nPREPARE release_check FROM @release_check;\nEXECUTE release_check;\nDEALLOCATE PREPARE release_check;\n";
};
try {
    if ($cmd === 'export') {
        if ($env('APP_ENV','production') !== 'development') throw new RuntimeException('Export only from development baseline.');
        if (is_file($dir.'/changes.sql')) throw new RuntimeException('Release already exists. Do not overwrite an approved release.');
        $baseline = file_get_contents($root.'/260918.sql');
        preg_match('/CREATE TABLE IF NOT EXISTS `nb_admin` \((.*?)\n\) ENGINE=/s', $baseline, $match);
        if (empty($match[1])) throw new RuntimeException('Baseline nb_admin DDL not found.');
        preg_match_all('/^\s*`([^`]+)`/m', $match[1], $baseColumns);
        $tables = ['nb_admin', 'nb_admin_audit', 'nb_admin_privacy_access', $isBlue ? 'blue_schema_migrations' : 'nb_schema_migrations'];
        if (!$isBlue) $tables[] = 'nb_admin_acl';
        $pre = "-- {$project}: read-only preflight; run without --force; stop at first error.\n";
        $pre .= $guard("(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin')=1", 'missing_admin_table');
        $pre .= $guard("(SELECT COUNT(*) FROM nb_admin WHERE sitekey=".$q($site).")>0", 'wrong_project_or_empty_admin');
        if ($isBlue) $pre .= $guard("(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='role_code')=1 OR (SELECT COUNT(*) FROM nb_admin WHERE sitekey='BLUESQ' AND active_status='Y')=1", 'select_initial_super_explicitly');
        $changes = ''; $snapshot = [];
        foreach ($tables as $table) {
            $ddl = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM)[1];
            $ddl = preg_replace('/ AUTO_INCREMENT=\d+/', '', $ddl);
            $columns = $pdo->query("SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=".$q($table)." ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
            $snapshot[$table] = ['ddl'=>$ddl, 'columns'=>$columns];
            if ($table !== 'nb_admin') $changes .= preg_replace('/^CREATE TABLE /', 'CREATE TABLE IF NOT EXISTS ', $ddl).";\n";
            foreach ($columns as $c) {
                $name = $c['COLUMN_NAME'];
                $where = "TABLE_SCHEMA=DATABASE() AND TABLE_NAME=".$q($table)." AND COLUMN_NAME=".$q($name);
                $exists = "(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE {$where})";
                $matches = "(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE {$where} AND COLUMN_TYPE=".$q($c['COLUMN_TYPE'])." AND IS_NULLABLE=".$q($c['IS_NULLABLE']).")";
                $widen = $isBlue && $table === 'nb_admin' && $name === 'upwd';
                $allowOld = $widen ? " OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE {$where} AND COLUMN_TYPE='varchar(64)' AND IS_NULLABLE='NO')=1" : '';
                $required = $table === 'nb_admin' && in_array($name, $baseColumns[1], true);
                $missingAllowed = $required ? '' : ($table === 'nb_admin' ? "{$exists}=0 OR " : "(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=".$q($table).")=0 OR ");
                $pre .= $guard("{$missingAllowed}{$matches}=1{$allowOld}", $table.'_'.$name.'_type');
                if ($table !== 'nb_admin') continue;
                preg_match('/^\s*(`'.preg_quote($name,'/').'` .+?)(?:,)?$/m', $ddl, $line);
                $definition = rtrim(trim($line[1] ?? ''), ',');
                if ($definition === '') throw new RuntimeException('Missing column DDL: '.$name);
                if (!$required || $widen) {
                    $ddlPart = "ALTER TABLE `nb_admin` ".($widen ? 'MODIFY COLUMN ' : 'ADD COLUMN ').$definition;
                    $condition = $widen ? "{$matches}=0" : "{$exists}=0";
                    $changes .= "SET @release_ddl = IF({$condition}, ".$q($ddlPart).", 'SELECT 1');\nPREPARE release_ddl FROM @release_ddl;\nEXECUTE release_ddl;\nDEALLOCATE PREPARE release_ddl;\n";
                }
            }
        }
        $changes .= "UPDATE nb_admin SET last_login_at=NOW() WHERE sitekey=".$q($site)." AND last_login_at IS NULL;\n";
        $changes .= "UPDATE nb_admin SET password_changed_at=NOW() WHERE sitekey=".$q($site)." AND password_changed_at IS NULL;\n";
        if ($isBlue) {
            $changes .= "UPDATE nb_admin SET created_at=NOW() WHERE sitekey='BLUESQ' AND created_at IS NULL;\n";
            $changes .= "UPDATE nb_admin SET role_code='super' WHERE sitekey='BLUESQ' AND active_status='Y' AND (SELECT n FROM (SELECT COUNT(*) n FROM nb_admin WHERE sitekey='BLUESQ' AND active_status='Y') a)=1 AND NOT EXISTS (SELECT 1 FROM (SELECT no FROM nb_admin WHERE sitekey='BLUESQ' AND role_code='super') b);\n";
            foreach (['202609210001_security','202609210002_account_management'] as $v) $changes .= "INSERT IGNORE INTO blue_schema_migrations(version,applied_at) VALUES(".$q($v).",NOW());\n";
        } else {
            foreach (glob(__DIR__.'/migrations/*.up.sql') as $file) $changes .= "INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES(".$q(basename($file,'.up.sql')).",NOW());\n";
        }
        if (!is_dir($dir)) mkdir($dir,0700,true);
        file_put_contents($dir.'/preflight.sql', $pre);
        file_put_contents($dir.'/changes.sql', $pre."\n-- Additive changes. DDL auto-commits; backup before applying.\n".$changes);
        file_put_contents($dir.'/target-schema.json', json_encode($snapshot, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n");
        $manifest=['project'=>$project,'sitekey'=>$site,'baseline_sha256'=>hash_file('sha256',$root.'/260918.sql'),'generated_at'=>date(DATE_ATOM),'files'=>[]];
        foreach (['preflight.sql','changes.sql','target-schema.json'] as $file) $manifest['files'][$file]=hash_file('sha256',$dir.'/'.$file);
        file_put_contents($dir.'/manifest.json', json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n");
        echo "EXPORTED {$project}: schema only, no credentials/accounts.\n"; exit;
    }
    if (!in_array($cmd,['check','up'],true)) throw new RuntimeException('Usage: php sql/release.php check|up|export');
    $manifest=json_decode(file_get_contents($dir.'/manifest.json'),true);
    if (($manifest['project']??'')!==$project) throw new RuntimeException('Wrong release project.');
    foreach ($manifest['files'] as $file=>$hash) if (!hash_equals($hash,hash_file('sha256',$dir.'/'.$file))) throw new RuntimeException('Release checksum mismatch: '.$file);
    $lock=$project.'_security_release';
    $stmt=$pdo->prepare('SELECT GET_LOCK(?,0)'); $stmt->execute([$lock]);
    if ((int)$stmt->fetchColumn()!==1) throw new RuntimeException('Another migration is running.');
    try {
        $sql=file_get_contents($dir.'/'.($cmd==='up'?'changes.sql':'preflight.sql'));
        foreach ($parts($sql) as $statement) { $s=$pdo->query($statement); while($s->nextRowset()) {} $s->closeCursor(); }
        echo strtoupper($cmd)." OK {$project}\n";
    } finally { $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]); }
} catch (Throwable $e) { fwrite(STDERR, "RELEASE STOP: ".$e->getMessage()."\n"); exit(1); }
