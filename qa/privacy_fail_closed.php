<?php
if(PHP_SAPI!=='cli') exit(1);
ob_start();
// Fault injection only in this CLI test, never into live schema or accounts.
class DB { public static $throw=true; public static function getInstance(){ return new self; } public function prepare($sql){if(self::$throw) throw new PDOException('QA simulated storage failure');return new class { public function execute($row){ return false; } };} }
require dirname(__DIR__).'/nol-gate/lib/PrivacyAccessLogger.php';
$NO_SITE_UNIQUE_KEY='QATEST';$_SESSION=[];$pass=0;
foreach([true,false] as $throw){
    DB::$throw=$throw;
    try { PrivacyAccessLogger::record('download','inquiry',1,'검증','QA','QA failure test'); }
    catch(RuntimeException $e){ if(http_response_code()===503 && strpos($e->getMessage(),'storage failure')===false){$pass++;echo 'PASS privacy disclosure blocked on '.($throw?'exception':'false insert')."\n";}}
}
echo "pass=$pass fail=".(2-$pass)."\n";exit($pass===2?0:1);
