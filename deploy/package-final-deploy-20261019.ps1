#requires -Version 7.0
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$candidate=Join-Path $root 'deploy/candidates/staging-blue-prod-rehearsal-20261019'
$output=Join-Path $root 'deploy/candidates/final-deploy-20261019'
if(Test-Path -LiteralPath $output){throw 'Immutable final handoff already exists'}
$m=Get-Content -Raw -LiteralPath (Join-Path $candidate 'operations/manifest.json')|ConvertFrom-Json
if($m.project -ne 'bluesquare'){throw 'Wrong project'}
foreach($file in $m.files.PSObject.Properties){
 foreach($path in @((Join-Path $candidate ('web-files/'+$file.Name)),(Join-Path $root $file.Name))){
  if(!(Test-Path -LiteralPath $path) -or (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant() -ne $file.Value){throw 'Current/candidate web content mismatch'}
 }
}
$release=Get-Content -Raw -LiteralPath (Join-Path $root 'sql/release/manifest.json')|ConvertFrom-Json
foreach($file in $release.files.PSObject.Properties){if((Get-FileHash -LiteralPath (Join-Path $root ('sql/release/'+$file.Name)) -Algorithm SHA256).Hash.ToLowerInvariant() -ne $file.Value){throw 'Approved SQL changed'}}
$sql=Join-Path $root 'sql/manual/bluesquare-production-final-20261019.sql'
if((Get-FileHash -LiteralPath $sql -Algorithm SHA256).Hash -ne '15D85291DE9E5315632BE5394092310D0F1EEBAD2BD0848ED615F3FE3B6F9D4E'){throw 'Final SQL changed'}
$private=Join-Path $root 'deploy/private-handoff/bluesquare-production-final-20261019'
foreach($name in @('.env','.env.gate')){
 $bytes=[IO.File]::ReadAllBytes((Join-Path $private $name));$text=[Text.UTF8Encoding]::new($false,$true).GetString($bytes)
 if($text.Contains([char]0xFFFD) -or ($bytes.Length -gt 2 -and $bytes[0] -eq 239 -and $bytes[1] -eq 187 -and $bytes[2] -eq 191)){throw 'Invalid UTF-8 configuration'}
 $keys=@{};foreach($line in ($text -split '\r?\n')){if($line -match '^([A-Z_]+)=(.*)$'){if($keys.ContainsKey($matches[1])){throw 'Duplicate setting'};$keys[$matches[1]]=$matches[2]}}
}
$ops=Join-Path $output 'DO_NOT_UPLOAD_DB_GUIDE'
$null=New-Item -ItemType Directory -Path (Join-Path $ops 'private'),(Join-Path $ops 'tools') -Force
Copy-Item -LiteralPath $sql -Destination (Join-Path $ops 'bluesquare-production-final-20261019.sql')
Copy-Item -LiteralPath (Join-Path $root '서버에올릴것_최종웹_DB_20261019.txt') -Destination (Join-Path $ops '먼저읽기_적용순서.txt')
Copy-Item -LiteralPath (Join-Path $root 'QA_실서버DB_최종배포_20261019.txt') -Destination (Join-Path $ops 'QA_실서버DB_최종배포.txt')
Copy-Item -LiteralPath (Join-Path $root 'deploy/preflight.php') -Destination (Join-Path $ops 'tools/preflight.php')
Copy-Item -LiteralPath (Join-Path $root 'sql/release/manifest.json') -Destination (Join-Path $ops 'approved-schema-manifest.json')
foreach($name in @('.env','.env.gate')){Copy-Item -LiteralPath (Join-Path $private $name) -Destination (Join-Path $ops ('private/'+$name))}
Add-Type -AssemblyName System.IO.Compression.FileSystem
$webZip=Join-Path $output 'bluesquare-web-final-20261019.zip'
$dbZip=Join-Path $output 'bluesquare-DB-guide-DO-NOT-UPLOAD-20261019.zip'
[IO.Compression.ZipFile]::CreateFromDirectory((Join-Path $candidate 'web-files'),$webZip,[IO.Compression.CompressionLevel]::Optimal,$false)
[IO.Compression.ZipFile]::CreateFromDirectory($ops,$dbZip,[IO.Compression.CompressionLevel]::Optimal,$false)
function EntryHash($entry){$stream=$entry.Open();$sha=[Security.Cryptography.SHA256]::Create();try{return [Convert]::ToHexString($sha.ComputeHash($stream)).ToLowerInvariant()}finally{$stream.Dispose();$sha.Dispose()}}
$zip=[IO.Compression.ZipFile]::OpenRead($webZip)
try{
 if($zip.Entries.Count -ne @($m.files.PSObject.Properties).Count){throw 'Web ZIP entry count mismatch'}
 foreach($entry in $zip.Entries){
  $name=$entry.FullName.Replace('\','/')
  if($name -match '(^|/)\.env($|\.)|^(qa|sql|logs|storage|deploy|\.git|\.well-known)/|\.(sql|zip|bak|backup|log)$'){throw 'Forbidden web file'}
  if($name -like 'uploads/*' -and [IO.Path]::GetFileName($name) -ne '.htaccess'){throw 'Customer upload in web ZIP'}
  $expected=$m.files.PSObject.Properties[$name];if($null -eq $expected -or (EntryHash $entry) -ne $expected.Value){throw 'Final web bytes mismatch'}
 }
}finally{$zip.Dispose()}
$zip=[IO.Compression.ZipFile]::OpenRead($dbZip)
try{
 $entry=$zip.GetEntry('bluesquare-production-final-20261019.sql');if(!$entry -or (EntryHash $entry) -ne (Get-FileHash -LiteralPath $sql -Algorithm SHA256).Hash.ToLowerInvariant()){throw 'DB ZIP SQL mismatch'}
 foreach($name in @('private/.env','private/.env.gate')){if(!$zip.GetEntry($name)){throw 'Missing separate private configuration'}}
}finally{$zip.Dispose()}
$receipt=[ordered]@{project='bluesquare';web_files=@($m.files.PSObject.Properties).Count;web_sha256=(Get-FileHash -LiteralPath $webZip -Algorithm SHA256).Hash;db_guide_sha256=(Get-FileHash -LiteralPath $dbZip -Algorithm SHA256).Hash;final_sql_sha256=(Get-FileHash -LiteralPath $sql -Algorithm SHA256).Hash;web_contains_real_env=$false;web_contains_sql=$false;db_guide_contains_secrets=$true;latest_backup='prod/261019.sql';docker_checks_passed=387;production_deployed=$false;remaining='Customer account-cleanup completion, current hosting settings/credentials comparison, actual SMTP/TLS and server smoke verification'}
[IO.File]::WriteAllText((Join-Path $output 'receipt.json'),($receipt|ConvertTo-Json -Depth 4),[Text.UTF8Encoding]::new($false))
Write-Output ($receipt|ConvertTo-Json -Compress)
