<?php
/** Isolated acceptance of the incremental engine migration; never run on production. */
require __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/inc/lib/db.php';
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv') || APP_ENV !== 'development' || DB_USER !== 'qa_app') exit(1);
$db = DB::getInstance();
$tables = ['nb_banner','nb_board','nb_board_category','nb_board_comment','nb_board_lev_manage','nb_board_manage','nb_data','nb_opera','nb_popup','nb_request','nb_request_manage','nb_siteinfo','nb_works'];
$sql = preg_replace('/^\s*--[^\n]*$/m', '', file_get_contents(dirname(__DIR__).'/sql/manual/audit-transactions-20261113.sql'));
$apply = static function (\PDO $connection) use ($sql): void {
    foreach (explode(';', $sql) as $part) {
        if (trim($part) === '') continue;
        $statement = $connection->query($part);
        if ($statement->columnCount()) $statement->fetchAll();
    }
};
$snapshot = static function (\PDO $connection, string $table): array {
    $rows = $connection->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
    $hashes = array_map(static function ($row) { return hash('sha256', serialize($row)); }, $rows);
    sort($hashes);
    $columns = $connection->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
    $indexes = $connection->query('SHOW INDEX FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as &$index) unset($index['Cardinality']);
    unset($index);
    return [count($rows), hash('sha256', serialize($hashes)), $columns, $indexes];
};
$before = [];
foreach ($tables as $table) $before[$table] = $snapshot($db, $table);
$apply($db);
foreach ($tables as $table) {
    qa_expect($before[$table] === $snapshot($db, $table), $table.' rows/columns/indexes preserved');
    $q = $db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    $q->execute([$table]);
    qa_expect($q->fetchColumn() === 'InnoDB', $table.' transactional engine ready');
}
$apply($db);
qa_expect(!array_filter($tables, static function ($table) use ($before, $snapshot, $db) { return $before[$table] !== $snapshot($db, $table); }), 'second migration preserves exact business data and schema');
$root = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';charset=utf8mb4', 'root', getenv('DB_ROOT_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$decoy = 'qa_audit_guard_'.bin2hex(random_bytes(4));
try {
    $root->exec('CREATE DATABASE `'.$decoy.'`');
    $root->exec('USE `'.$decoy.'`');
    $root->exec('CREATE TABLE nb_board (no INT PRIMARY KEY) ENGINE=MyISAM');
    try { $apply($root); } catch (Throwable $error) { /* A hard precondition rejection is also safe. */ }
    qa_expect($root->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board'")->fetchColumn() === 'MyISAM', 'wrong selected database does not alter business table');
} finally {
    $root->exec('DROP DATABASE `'.$decoy.'`');
}
qa_done();
