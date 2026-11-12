<?php
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || APP_ENV!=='development' || DB_USER!=='qa_app') exit(1);
$db=DB::getInstance();
$table='qa_engine_'.bin2hex(random_bytes(5));
function engineSnapshot(PDO $db,string $table): string {
 $rows=$db->query('SELECT * FROM `'.$table.'` ORDER BY no')->fetchAll(PDO::FETCH_ASSOC);
 return hash('sha256',json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE));
}
function runEngineSql(PDO $db,string $sql): array {
 $last=[];
 foreach(explode(';',$sql) as $part){if(trim($part)==='')continue;$st=$db->query($part);if($st->columnCount())$last=$st->fetchAll(PDO::FETCH_ASSOC);}
 return $last;
}
try {
 $db->exec('CREATE TABLE `'.$table.'` LIKE nb_request');
 $db->exec('INSERT INTO `'.$table.'` SELECT * FROM nb_request');
 $db->exec('ALTER TABLE `'.$table.'` ENGINE=MyISAM');
 $before=engineSnapshot($db,$table);$cols=$db->query('SHOW FULL COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
 $sql=str_replace('nb_request',$table,file_get_contents(dirname(__DIR__).'/sql/manual/rental-transactions-20261111.sql'));
 $flags=runEngineSql($db,$sql);
 qa_expect(($flags[0]['database_correct']??0)==1 && ($flags[0]['rental_transactions_ready']??0)==1,'release SQL reports correct DB and InnoDB');
 qa_expect(engineSnapshot($db,$table)===$before,'all rental backup rows and values preserved');
 qa_expect($db->query('SHOW FULL COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC)===$cols,'all rental column definitions preserved');
 runEngineSql($db,$sql);qa_expect(engineSnapshot($db,$table)===$before,'second SQL run is idempotent');
 $db->beginTransaction();$db->exec('DELETE FROM `'.$table.'`');$db->rollBack();
 qa_expect(engineSnapshot($db,$table)===$before,'failure after DELETE rolls all rental rows back');
 $db->exec('ALTER TABLE `'.$table.'` ENGINE=MyISAM');
 runEngineSql($db,str_replace("DATABASE()='dbusrbluesq0211'","DATABASE()='wrong_database'",$sql));
 $q=$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$table."'");
 qa_expect($q->fetchColumn()==='MyISAM','wrong database guard leaves table unchanged');
} finally {if($db->inTransaction())$db->rollBack();$db->exec('DROP TABLE IF EXISTS `'.$table.'`');}
qa_done();
