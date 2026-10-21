#requires -Version 7.0
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$basePath=Join-Path $root 'deploy/candidates/handoff-http-20261019-sortfix-v2/noltheater-web-http-20261019-sortfix-v2.zip'
$patchPath=Join-Path $root 'deploy/private-handoff/nol-dual-admin-20261019.zip'
$output=Join-Path $root 'deploy/candidates/noltheater-web-final-20261019.zip'
$building=$output+'.building'
foreach($path in @($output,$building,$output+'.manifest.json')){if(Test-Path -LiteralPath $path){throw 'Immutable output already exists'}}
if((Get-FileHash -LiteralPath $basePath -Algorithm SHA256).Hash -ne 'E1CCEA130F4AAC1F049D2A9DBD17F205DFD5B33435E87FF400FAF4E4CCB35FF3'){throw 'Base ZIP hash mismatch'}
if((Get-FileHash -LiteralPath $patchPath -Algorithm SHA256).Hash -ne '35F694061CB846150AE35B14072CF0F20AED4B4F27BF1DFD355BEE667A551319'){throw 'Patch ZIP hash mismatch'}
Add-Type -AssemblyName System.IO.Compression.FileSystem
function EntryHash($entry){
 $s=$entry.Open();$sha=[Security.Cryptography.SHA256]::Create()
 try{return [Convert]::ToHexString($sha.ComputeHash($s)).ToLowerInvariant()}finally{$s.Dispose();$sha.Dispose()}
}
$base=[IO.Compression.ZipFile]::OpenRead($basePath)
$patch=[IO.Compression.ZipFile]::OpenRead($patchPath)
$entries=[ordered]@{};$hashes=[ordered]@{};$overrides=@('inc/lib/Gate.php','inc/lib/PhpGateRouter.php')
try{
 foreach($entry in $base.Entries){
  $name=$entry.FullName.Replace('\','/')
  if($name -match '(^/|(^|/)\.\.(/|$)|(^|/)\.env($|\.)|^(qa|sql|logs|storage|deploy|\.git|\.well-known)/|\.(sql|zip|bak|backup|log)$)'){throw 'Forbidden web ZIP entry'}
  if($name -like 'uploads/*' -and [IO.Path]::GetFileName($name) -ne '.htaccess'){throw 'Customer upload content must be preserved, not packaged'}
  if($entries.Contains($name)){throw 'Duplicate entry'}
  $entries[$name]=$entry
 }
 foreach($name in $overrides){$entry=$patch.GetEntry($name);if(!$entry -or !$entries.Contains($name)){throw 'Missing approved patch entry'};$entries[$name]=$entry}
 foreach($item in $entries.GetEnumerator()){
  $expected=EntryHash $item.Value
  $source=Join-Path $root $item.Key
  if(!(Test-Path -LiteralPath $source) -or (Get-FileHash -LiteralPath $source -Algorithm SHA256).Hash.ToLowerInvariant() -ne $expected){throw ('Current source differs from approved merged delivery: '+$item.Key)}
  $hashes[$item.Key]=$expected
 }
 $zip=[IO.Compression.ZipFile]::Open($building,[IO.Compression.ZipArchiveMode]::Create)
 try{foreach($item in $entries.GetEnumerator()){
  $entry=$zip.CreateEntry($item.Key,[IO.Compression.CompressionLevel]::Optimal)
  $entry.LastWriteTime=$item.Value.LastWriteTime
  $inputStream=$item.Value.Open();$outputStream=$entry.Open()
  try{$inputStream.CopyTo($outputStream)}finally{$inputStream.Dispose();$outputStream.Dispose()}
 }}finally{$zip.Dispose()}
}finally{$base.Dispose();$patch.Dispose()}
$zip=[IO.Compression.ZipFile]::OpenRead($building)
try{
 if($zip.Entries.Count -ne $hashes.Count){throw 'Final entry count mismatch'}
 foreach($entry in $zip.Entries){if(!$hashes.Contains($entry.FullName) -or (EntryHash $entry) -ne $hashes[$entry.FullName]){throw 'Final ZIP integrity mismatch'}}
}finally{$zip.Dispose()}
Move-Item -LiteralPath $building -Destination $output
$receipt=[ordered]@{
 project='noltheater';purpose='Final web-only merge of QA-verified v2 and dual-admin patch';web_files=$hashes.Count
 sha256=(Get-FileHash -LiteralPath $output -Algorithm SHA256).Hash
 contains_real_env=$false;contains_sql=$false;contains_customer_uploads=$false
 preserves_existing_server_env=$true;database_action='Keep current latest DB; no SQL execution in this web-only handoff'
 required_server_settings=@('GATE_ROUTE_MODE=php','GATE_ALLOW_HTTP=true','GATE_ALLOW_LEGACY_ADMIN=true','GATE_ENFORCE=true','SESSION_SECURE=true')
 status='WEB_ARTIFACT_VERIFIED_DEPLOYMENT_WAITING_FOR_CUSTOMER_ACCOUNT_CLEANUP'
 files=$hashes
}
[IO.File]::WriteAllText($output+'.manifest.json',($receipt|ConvertTo-Json -Depth 5),[Text.UTF8Encoding]::new($false))
Write-Output ('PASS merged final web ZIP: '+$hashes.Count+' entries; all bytes match current final source and approved delivery.')
Write-Output $output
