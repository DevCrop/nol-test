#requires -Version 7.0
[CmdletBinding()]
param([string]$CandidateId='staging-http-20261019',[string]$HandoffId='handoff-http-20261019')
$ErrorActionPreference='Stop'
foreach($id in @($CandidateId,$HandoffId)){ if($id -notmatch '^[A-Za-z0-9_-]{1,80}$'){throw 'Invalid artifact id'} }
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$candidate=Join-Path $PSScriptRoot ('candidates/'+$CandidateId)
$m=Get-Content -Raw -LiteralPath (Join-Path $candidate 'operations/manifest.json') | ConvertFrom-Json
if($m.project -notin @('bluesquare','noltheater')){throw 'Unknown project'}
if(!$m.incremental_sql -or @($m.incremental_sql.PSObject.Properties).Count -ne 3){throw 'Latest incremental SQL manifest required'}
foreach($entry in $m.incremental_sql.PSObject.Properties){
 if((Get-FileHash -Algorithm SHA256 -LiteralPath (Join-Path $candidate ('operations/sql/manual/'+$entry.Name))).Hash.ToLowerInvariant() -ne $entry.Value){throw 'Incremental SQL integrity failure'}
}
foreach($entry in $m.files.PSObject.Properties){
 $path=Join-Path $candidate ('web-files/'+$entry.Name)
 if(!(Test-Path -LiteralPath $path) -or (Get-FileHash -Algorithm SHA256 -LiteralPath $path).Hash.ToLowerInvariant() -ne $entry.Value){throw 'Candidate integrity failure'}
}
$release=Get-Content -Raw -LiteralPath (Join-Path $candidate 'operations/sql/release/manifest.json') | ConvertFrom-Json
if($release.project -ne $m.project){throw 'Wrong SQL project'}
foreach($entry in $release.files.PSObject.Properties){
 if((Get-FileHash -Algorithm SHA256 -LiteralPath (Join-Path $candidate ('operations/sql/release/'+$entry.Name))).Hash.ToLowerInvariant() -ne $entry.Value){throw 'SQL integrity failure'}
}
$output=Join-Path $PSScriptRoot ('candidates/'+$HandoffId)
if(Test-Path -LiteralPath $output){throw 'Immutable handoff already exists'}
$null=New-Item -ItemType Directory -Path $output
$ops=Join-Path $output 'DO_NOT_UPLOAD_sql_and_guide'
$null=New-Item -ItemType Directory -Path $ops
Copy-Item -LiteralPath (Join-Path $candidate 'operations/sql') -Destination (Join-Path $ops 'sql') -Recurse
Copy-Item -LiteralPath (Join-Path $candidate 'operations/deploy') -Destination (Join-Path $ops 'deploy') -Recurse
Copy-Item -LiteralPath (Join-Path $candidate 'operations/UPGRADE-20261027.md') -Destination (Join-Path $ops 'UPGRADE-20261027.md')
Copy-Item -LiteralPath (Join-Path $candidate 'operations/UPGRADE-20261027.md') -Destination (Join-Path $ops '서버업로드_SQL순서.md')
Copy-Item -LiteralPath (Join-Path $root '최종검토_계정준비_20261027.md') -Destination (Join-Path $ops 'QA_계정준비_20261027.md')
Copy-Item -LiteralPath (Join-Path $root '운영배포.md') -Destination (Join-Path $ops '운영배포.md')
Copy-Item -LiteralPath (Join-Path $candidate 'operations/manifest.json') -Destination (Join-Path $ops 'web-file-manifest.json')
$domain=if($m.project -eq 'bluesquare'){'bluesquare.kr'}else{'noltheater-daehakro.com'}
$envTemplate=[IO.File]::ReadAllText((Join-Path $candidate 'operations/production.env.example'))
$envTemplate=$envTemplate -replace '(?m)^GATE_ALLOW_HTTP=false\r?$','GATE_ALLOW_HTTP=true'
$envTemplate=$envTemplate -replace '(?m)^PUBLIC_HOST=\r?$',('PUBLIC_HOST='+$domain)
$envTemplate=$envTemplate -replace '(?m)^GATE_HOST=\r?$',('GATE_HOST=gate.'+$domain)
[IO.File]::WriteAllText((Join-Path $ops 'production-http.env.example'),$envTemplate,[Text.UTF8Encoding]::new($false))
Add-Type -AssemblyName System.IO.Compression
$webZip=Join-Path $output ($m.project+'-web-http-20261019.zip')
$sqlZip=Join-Path $output ($m.project+'-SQL-guide-DO-NOT-UPLOAD-20261019.zip')
[IO.Compression.ZipFile]::CreateFromDirectory((Join-Path $candidate 'web-files'),$webZip,[IO.Compression.CompressionLevel]::Optimal,$false)
[IO.Compression.ZipFile]::CreateFromDirectory($ops,$sqlZip,[IO.Compression.CompressionLevel]::Optimal,$false)
$zip=[IO.Compression.ZipFile]::OpenRead($webZip)
try{
 if($zip.Entries.Count -ne @($m.files.PSObject.Properties).Count){throw 'Unexpected ZIP entry count'}
 foreach($entry in $zip.Entries){
  $key=$entry.FullName.Replace('\','/')
  $expected=$m.files.PSObject.Properties[$key]
  if($null -eq $expected){throw 'Unexpected ZIP entry'}
  $stream=$entry.Open()
  $sha=[Security.Cryptography.SHA256]::Create()
  try{$actual=[Convert]::ToHexString($sha.ComputeHash($stream)).ToLowerInvariant()}finally{$sha.Dispose();$stream.Dispose()}
  if($actual -ne $expected.Value){throw 'ZIP content hash mismatch'}
 }
}finally{$zip.Dispose()}
$receipt=[ordered]@{project=$m.project;status='CONDITIONAL_HANDOFF_PRODUCTION_BACKUP_DB_REHEARSAL_REQUIRED';web_files=@($m.files.PSObject.Properties).Count;web_sha256=(Get-FileHash -Algorithm SHA256 -LiteralPath $webZip).Hash;sql_sha256=(Get-FileHash -Algorithm SHA256 -LiteralPath $sqlZip).Hash;contains_real_env=$false;contains_customer_uploads=$false}
[IO.File]::WriteAllText((Join-Path $output 'receipt.json'),($receipt|ConvertTo-Json),[Text.UTF8Encoding]::new($false))
Write-Output ($receipt|ConvertTo-Json -Compress)
