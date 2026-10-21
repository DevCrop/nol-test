<?php
// Private backup rehearsal. No application bootstrap, SMTP, account login or external DB.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv') || getenv('QA_BACKUP_REHEARSAL') !== '1') exit(1);
$root=dirname(__DIR__);
$pass=0;
function backupExpect(bool $ok,string $label):void {
 if(!$ok)throw new RuntimeException($label);
 $GLOBALS['pass']++;echo "PASS ".$label."\n";
}
function executeRelease(PDO $db,string $sql):void {
 $sql=preg_replace('/^\s*--[^\r\n]*$/m','',$sql);
 foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $statement){
  if(trim($statement)==='')continue;
  $s=$db->query(trim($statement));while($s->nextRowset()){} $s->closeCursor();
 }
}
function backupTables(PDO $db):array {
 return $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
}
function backupColumns(PDO $db,string $table):array {
 $s=$db->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');
 $s->execute([$table]);return $s->fetchAll(PDO::FETCH_COLUMN);
}
function backupSnapshot(PDO $db,array $spec):array {
 $out=[];
 foreach($spec as $table=>$columns){
  if(!preg_match('/^[a-zA-Z0-9_]+$/D',$table))throw new RuntimeException('Invalid table identifier');
  foreach($columns as $column)if(!preg_match('/^[a-zA-Z0-9_]+$/D',$column))throw new RuntimeException('Invalid column identifier');
  $s=$db->query('SELECT '.implode(',',array_map(static function($c){return '`'.$c.'`';},$columns)).' FROM `'.$table.'`');
  $hashes=[];while($row=$s->fetch(PDO::FETCH_NUM))$hashes[]=hash('sha256',serialize($row));
  sort($hashes,SORT_STRING);$out[$table]=['count'=>count($hashes),'hash'=>hash('sha256',implode('',$hashes))];
 }
 return $out;
}
try {
 // The caller runs with the isolated MySQL container's network namespace (--network none).
 $db=new PDO('mysql:host=127.0.0.1;dbname=dbusrdaehakro0605;charset=utf8mb4','root',getenv('QA_DB_PASSWORD'),
  [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_STRINGIFY_FETCHES=>true]);
 $manifest=json_decode(file_get_contents($root.'/sql/release/manifest.json'),true);
 backupExpect(($manifest['sitekey']??'')==='NOLTHE','release belongs to NOL');
 foreach($manifest['files'] as $file=>$hash)backupExpect(hash_equals($hash,hash_file('sha256',$root.'/sql/release/'.$file)),'approved SQL checksum '.$file);
 $spec=[];foreach(backupTables($db) as $table)$spec[$table]=backupColumns($db,$table);
 $originalAdminRows=$db->query('SELECT * FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC);
 // Filling new security dates fires the legacy updated_at ON UPDATE CURRENT_TIMESTAMP.
 $hasUpdatedAt=in_array('updated_at',$spec['nb_admin'],true);
 if($hasUpdatedAt)$spec['nb_admin']=array_values(array_diff($spec['nb_admin'],['updated_at']));
 $dates=[];
 foreach(['last_login_at','password_changed_at'] as $column){
  if(in_array($column,$spec['nb_admin'],true)){
   $dates[$column]=$db->query('SELECT no,`'.$column.'` FROM nb_admin WHERE sitekey=\'NOLTHE\'')->fetchAll(PDO::FETCH_KEY_PAIR);
   $spec['nb_admin']=array_values(array_diff($spec['nb_admin'],[$column]));
  }
 }
 $before=backupSnapshot($db,$spec);
 $pre=file_get_contents($root.'/sql/release/preflight.sql');$up=file_get_contents($root.'/sql/release/changes.sql');
 executeRelease($db,$pre);backupExpect(true,'provided live backup preflight');
 executeRelease($db,$up);backupExpect(true,'provided live backup changes first application');
 $after=backupSnapshot($db,$spec);
 foreach($before as $table=>$state)backupExpect($state===$after[$table],'original rows/columns preserved '.$table);
 if($hasUpdatedAt){
  $changedDates=0;$current=[];
  foreach($db->query('SELECT no,updated_at,last_login_at,password_changed_at FROM nb_admin')->fetchAll(PDO::FETCH_ASSOC) as $row)$current[$row['no']]=$row;
  foreach($originalAdminRows as $row){
   $afterRow=$current[$row['no']];
   $initializes=$row['sitekey']==='NOLTHE' && (($row['last_login_at']??null)===null || ($row['password_changed_at']??null)===null);
   if($initializes){
    $changedDates++;
    backupExpect($afterRow['updated_at']===$afterRow['last_login_at'] || $afterRow['updated_at']===$afterRow['password_changed_at'], 'legacy updated_at follows intentional security date initialization');
   }else backupExpect($afterRow['updated_at']===$row['updated_at'],'unaffected administrator updated_at preserved');
  }
 }
 foreach($dates as $column=>$values){
  $now=$db->query('SELECT no,`'.$column.'` FROM nb_admin WHERE sitekey=\'NOLTHE\'')->fetchAll(PDO::FETCH_KEY_PAIR);
  foreach($values as $id=>$value)backupExpect($value===null ? !empty($now[$id]) : ($now[$id]??null)===$value,'date initialization only affects prior NULL '.$column);
 }
 $target=json_decode(file_get_contents($root.'/sql/release/target-schema.json'),true);
 foreach($target as $table=>$schema){
  $s=$db->prepare('SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);
  $actual=[];foreach($s->fetchAll(PDO::FETCH_ASSOC) as $col)$actual[$col['COLUMN_NAME']]=$col;
  foreach($schema['columns'] as $col)backupExpect(($actual[$col['COLUMN_NAME']]??null)===$col,'target schema '.$table.'.'.$col['COLUMN_NAME']);
 }
 $fullSpec=[];foreach(backupTables($db) as $table)$fullSpec[$table]=backupColumns($db,$table);
 $firstFull=backupSnapshot($db,$fullSpec);
 executeRelease($db,$pre);executeRelease($db,$up);
 backupExpect($firstFull===backupSnapshot($db,$fullSpec),'second application preserves every row including new columns/tables');
 if(getenv('QA_VERIFY_MFA_EMAIL')==='1'){
  $emailSpec=$fullSpec;
  $emailSpec['nb_admin']=array_values(array_diff($emailSpec['nb_admin'],['email','updated_at']));
  $beforeEmail=backupSnapshot($db,$emailSpec);
  $otherAdmins=$db->query('SELECT * FROM nb_admin WHERE no<>1 ORDER BY no')->fetchAll(PDO::FETCH_ASSOC);
  $emailSql=file_get_contents($root.'/sql/manual/nol-super-mfa-email-20261019.sql');
  executeRelease($db,$emailSql);
  $targetEmail=$db->query("SELECT email FROM nb_admin WHERE no=1 AND uid='tmaster' AND sitekey='NOLTHE' AND role_id=1 AND active_status='Y'")->fetchColumn();
  backupExpect($targetEmail==='nineonelabs@gmail.com','approved super administrator MFA recipient registered');
  backupExpect($beforeEmail===backupSnapshot($db,$emailSpec) && $otherAdmins===$db->query('SELECT * FROM nb_admin WHERE no<>1 ORDER BY no')->fetchAll(PDO::FETCH_ASSOC),'email change preserves all other data and other administrators');
  $emailOnce=backupSnapshot($db,$fullSpec);executeRelease($db,$emailSql);
  backupExpect($emailOnce===backupSnapshot($db,$fullSpec),'email SQL repeat preserves all data and timestamps');
  executeRelease($db,$up);
  backupExpect($emailOnce===backupSnapshot($db,$fullSpec),'approved release repeat preserves newly registered MFA recipient');
 }
 $s=$db->query("SELECT email,role_id,active_status FROM nb_admin WHERE sitekey='NOLTHE'");$admins=$s->fetchAll(PDO::FETCH_ASSOC);
 $activeSuper=0;$superEmail=0;$missingEmail=0;
 foreach($admins as $admin){
  if($admin['active_status']!=='Y')continue;
  $valid=filter_var($admin['email'],FILTER_VALIDATE_EMAIL)!==false;
  if(!$valid)$missingEmail++;
  if((int)$admin['role_id']===1){$activeSuper++;if($valid)$superEmail++;}
 }
 echo json_encode(['status'=>'PASS','checks'=>$pass,'original_tables'=>count($spec),
  'original_rows'=>array_sum(array_column($before,'count')),'tables_after'=>count($fullSpec),
  'active_super_count'=>$activeSuper,'active_super_with_valid_mfa_email'=>$superEmail,
  'admin_rows_security_dates_initialized'=>$changedDates??0,
  'active_accounts_missing_valid_mfa_email'=>$missingEmail],JSON_UNESCAPED_SLASHES)."\n";
} catch(Throwable $e){
 // Never print SQL/row values from an imported customer backup.
 $label=get_class($e)==='RuntimeException' ? $e->getMessage() : 'SQL error details redacted';
 fwrite(STDERR,'FAIL '.get_class($e).' '.$label.' (customer values redacted)'."\n");exit(1);
}
