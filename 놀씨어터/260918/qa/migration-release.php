<?php
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) exit(1);
$root=dirname(__DIR__); $blue=is_file($root.'/inc/lib/security.bootstrap.php');
require_once $blue ? $root.'/inc/lib/security.bootstrap.php' : $root.'/config/env.php';
$env=$blue?'blue_env':'env';
if ($env('APP_ENV','production')!=='development') exit(1);
$pdo=new PDO('mysql:host='.$env('DB_HOST','db').';charset=utf8mb4','root',$env('DB_ROOT_PASSWORD',''),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$name='qa_release_'.bin2hex(random_bytes(5));
$run=static function(string $sql) use($pdo): void {
    $sql=preg_replace('/^\s*--[^\r\n]*$/m','',$sql);
    foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $s) if(trim($s)!=='') { $stmt=$pdo->query(trim($s)); while($stmt->nextRowset()) {} $stmt->closeCursor(); }
};
$checks=0;
$expect=static function(bool $ok,string $label) use(&$checks): void { if(!$ok) throw new RuntimeException($label); $checks++; echo "PASS {$label}\n"; };
try {
    $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4");
    $pdo->exec("USE `{$name}`");
    preg_match('/CREATE TABLE IF NOT EXISTS `nb_admin` \(.*?\n\) ENGINE=.*?;/s',file_get_contents($root.'/260918.sql'),$m);
    $pdo->exec($m[0]);
    $site=$blue?'BLUESQ':'NOLTHE'; $password=hash('sha256','QaOriginalPassword');
    $pdo->prepare('INSERT INTO nb_admin(sitekey,uid,upwd,uname,active_status) VALUES(?,?,?,?,?)')->execute([$site,'qa_release_admin',$password,'검증관리자','Y']);
    $baseline=$pdo->query('SELECT no,sitekey,uid,upwd,uname,active_status FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC);
    $sql=file_get_contents($root.'/sql/release/changes.sql');
    $pre=file_get_contents($root.'/sql/release/preflight.sql');
    $run($pre); $expect(true,'original dump schema preflight');
    // Simulate a previous DDL auto-commit before a failed migration.
    $snapshot=json_decode(file_get_contents($root.'/sql/release/target-schema.json'),true);
    preg_match('/^\s*(`login_token` [^\n]+?)(?:,)?$/m',$snapshot['nb_admin']['ddl'],$column);
    $pdo->exec('ALTER TABLE nb_admin ADD COLUMN '.rtrim(trim($column[1]),','));
    $run($sql);
    $expect($baseline===$pdo->query('SELECT no,sitekey,uid,upwd,uname,active_status FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC),'account ids/passwords/names/status preserved after partial resume');
    $before=$pdo->query('SELECT * FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC);
    $run($sql);
    $expect($before===$pdo->query('SELECT * FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC),'second run preserves all account state');
    foreach($snapshot as $table=>$spec) {
        $columns=$pdo->query("SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=".$pdo->quote($table)." ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
        $actual=[]; foreach($columns as $c) $actual[$c['COLUMN_NAME']]=$c;
        foreach($spec['columns'] as $c) $expect(($actual[$c['COLUMN_NAME']]??null)===$c,'target '.$table.'.'.$c['COLUMN_NAME']);
    }
    $pdo->exec('ALTER TABLE nb_admin MODIFY login_token varchar(12) NULL');
    $failed=false; try{$run($sql);}catch(PDOException $e){$failed=true;}
    $expect($failed,'unexpected schema drift aborts before changes');
    $pdo->exec("UPDATE nb_admin SET sitekey='OTHER'");
    $failed=false; try{$run($pre);}catch(PDOException $e){$failed=true;}
    $expect($failed,'wrong project rejected');
    echo "OK {$checks}\n";
} catch(Throwable $e) { fwrite(STDERR,'FAIL '.$e->getMessage()."\n"); $qaFailed=true; }
finally {
    if(preg_match('/^qa_release_[a-f0-9]{10}$/D',$name)) $pdo->exec("DROP DATABASE `{$name}`");
}
exit(empty($qaFailed) ? 0 : 1);
