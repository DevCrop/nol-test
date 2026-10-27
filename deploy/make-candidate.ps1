#requires -Version 7.0
[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][ValidatePattern('^[A-Za-z0-9_-]{1,80}$')][string]$ReleaseId,
    [string]$BaselineRoot,
    [switch]$WhatIf
)
$ErrorActionPreference = 'Stop'
$projectRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$isBlue = Test-Path -LiteralPath (Join-Path $projectRoot 'admin')
$project = if ($isBlue) { 'bluesquare' } else { 'noltheater' }
$allowed = if ($isBlue) { @('admin','api','app','inc','json','lang','module','pages','resource') } else { @('api','bootstrap','captcha','config','inc','lang','module','nol-gate','resource','routes','src','views') }
$rootFiles = @('index.php','.htaccess','robots.txt','sitemap.xml')
if (-not $isBlue) { $rootFiles += '.user.ini' }
function Should-Package([string]$relative) {
    if ($relative -match '(?i)(^|/)resource/previews?(/|$)|(^|/)(password-preview|temp-account-preview|temp-account-list-preview|account\.temporary-preview|account-preview)\.(php|html|css|js)$') { return $false }
    if ($relative -match '(^|/)\.(?!htaccess$|user\.ini$)') { return $false }
    if ($relative -match '^resource/file(/|$)' -or $relative -match '(^|/)(examples?|tests?)(/|$)') { return $false }
    if ($relative -match 'resource/vendor/tinymce/plugins/(jbimages|example)(/|$)') { return $false }
    if ($relative -match '(?i)(^|/)(index\.test\.php|test\.js)$|\.(sql|log|md|bak|backup|old|orig|zip|7z|rar|pdf|docx|xlsx|pem|key|pfx|p12)$|\.test\.') { return $false }
    return $true
}
function Collect-Source([string]$root) {
    $result = [ordered]@{}
    foreach ($dir in $allowed) {
        $path = Join-Path $root $dir
        if (-not (Test-Path -LiteralPath $path)) { continue }
        foreach ($file in Get-ChildItem -LiteralPath $path -File -Recurse -Force) {
            if (($file.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) { throw 'Symlink/reparse file requires explicit review' }
            $relative = $file.FullName.Substring($root.TrimEnd('\','/').Length + 1).Replace('\','/')
            if (Should-Package $relative) { $result[$relative] = $file.FullName }
        }
    }
    foreach ($relative in $rootFiles) {
        $path=Join-Path $root $relative
        if (Test-Path -LiteralPath $path) { $result[$relative]=$path }
    }
    return $result
}
$files = Collect-Source $projectRoot
foreach ($entry in $files.GetEnumerator()) {
    if ($entry.Key -notmatch '\.php$') { continue }
    $text = [IO.File]::ReadAllText($entry.Value)
    $literalSecret = 'define\s*\(\s*[''"]DB_PASS[''"]\s*,\s*[''"][^''"]+[''"]'
    if ($text -match $literalSecret) { throw ('Hard-coded DB credential; values suppressed. Review '+$entry.Key) }
}
$manifest = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'sql/release/manifest.json') | ConvertFrom-Json
if ($manifest.project -ne $project) { throw 'Wrong SQL release project' }
foreach($entry in $manifest.files.PSObject.Properties) {
    if ((Get-FileHash -Algorithm SHA256 -LiteralPath (Join-Path $projectRoot ('sql/release/'+$entry.Name))).Hash.ToLowerInvariant() -ne $entry.Value) { throw 'SQL release checksum mismatch' }
}
$changes = [ordered]@{ added=@(); changed=@(); baseline_only_review=@() }
$baselineStatus = 'MISSING_CURRENT_PRODUCTION_SNAPSHOT'
if ($BaselineRoot) {
    $baseline = [IO.Path]::GetFullPath($BaselineRoot)
    if ($baseline -eq $projectRoot -or -not (Test-Path -LiteralPath $baseline -PathType Container)) { throw 'Invalid baseline directory' }
    $before = Collect-Source $baseline
    foreach ($entry in $files.GetEnumerator()) {
        if (-not $before.Contains($entry.Key)) { $changes.added += $entry.Key }
        elseif ((Get-FileHash -Algorithm SHA256 -LiteralPath $entry.Value).Hash -ne (Get-FileHash -Algorithm SHA256 -LiteralPath $before[$entry.Key]).Hash) { $changes.changed += $entry.Key }
    }
    foreach ($key in $before.Keys) { if (-not $files.Contains($key)) { $changes.baseline_only_review += $key } }
    $baselineStatus = 'SUPPLIED_SNAPSHOT_REQUIRES_CONTENT_REVIEW_AND_FRESHNESS_CONFIRMATION'
}
if ($WhatIf) { Write-Output ("CANDIDATE PLAN "+$project+": "+$files.Count+" files; no files written; NOT production-approved"); return }
$outputParent = Join-Path $PSScriptRoot 'candidates'
$output = Join-Path $outputParent $ReleaseId
$zip = $output+'.zip'
if ((Test-Path -LiteralPath $output) -or (Test-Path -LiteralPath $zip)) { throw 'Immutable candidate already exists; choose a new release id' }
$web = Join-Path $output 'web-files'
$ops = Join-Path $output 'operations'
$null=New-Item -ItemType Directory -Path $web,$ops -Force
$fileHashes=[ordered]@{}
foreach ($entry in $files.GetEnumerator()) {
    $destination=Join-Path $web $entry.Key
    $null=New-Item -ItemType Directory -Path ([IO.Path]::GetDirectoryName($destination)) -Force
    Copy-Item -LiteralPath $entry.Value -Destination $destination
    $fileHashes[$entry.Key]=(Get-FileHash -Algorithm SHA256 -LiteralPath $destination).Hash.ToLowerInvariant()
}
# Runtime customer uploads/downloads are preserved. Include only reviewed Apache protection files.
Get-ChildItem -LiteralPath (Join-Path $projectRoot 'uploads') -Filter .htaccess -Recurse -File -Force | ForEach-Object {
    $relative=$_.FullName.Substring($projectRoot.TrimEnd('\','/').Length + 1).Replace('\','/')
    $destination=Join-Path $web $relative
    $null=New-Item -ItemType Directory -Path ([IO.Path]::GetDirectoryName($destination)) -Force
    Copy-Item -LiteralPath $_.FullName -Destination $destination
    $fileHashes[$relative]=(Get-FileHash -Algorithm SHA256 -LiteralPath $destination).Hash.ToLowerInvariant()
}
$null=New-Item -ItemType Directory -Path (Join-Path $ops 'sql'),(Join-Path $ops 'deploy') -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'sql/release') -Destination (Join-Path $ops 'sql/release') -Recurse
Copy-Item -LiteralPath (Join-Path $projectRoot 'sql/release.php') -Destination (Join-Path $ops 'sql/release.php')
$null=New-Item -ItemType Directory -Path (Join-Path $ops 'sql/manual') -Force
$incrementalHashes=[ordered]@{}
foreach($file in @('nol-temporary-password-20261021.sql','admin-transactions-20261027.sql','account-authority-20261027.sql')) {
    Copy-Item -LiteralPath (Join-Path $projectRoot ('sql/manual/'+$file)) -Destination (Join-Path $ops ('sql/manual/'+$file))
    $incrementalHashes[$file]=(Get-FileHash -Algorithm SHA256 -LiteralPath (Join-Path $ops ('sql/manual/'+$file))).Hash.ToLowerInvariant()
}
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'UPGRADE-20261027.md') -Destination (Join-Path $ops 'UPGRADE-20261027.md')
Copy-Item -LiteralPath (Join-Path $projectRoot '.env.production.example') -Destination (Join-Path $ops 'production.env.example')
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'preflight.php') -Destination (Join-Path $ops 'deploy/preflight.php')
Copy-Item -LiteralPath (Join-Path $projectRoot '운영배포.md') -Destination (Join-Path $ops '운영배포.md')
$receipt=[ordered]@{
    project=$project; release=$ReleaseId; status='STAGING_ONLY_DO_NOT_UPLOAD_DIRECTLY'
    created_utc=[DateTime]::UtcNow.ToString('o'); baseline_status=$baselineStatus
    required_gates=@('latest DB copy','production environment preflight','hosting PHP/MySQL/extensions','TLS or explicitly accepted temporary HTTP risk (HTTPS incomplete)','IP','content review','SQL up twice on copy','MFA receipt','full staging QA','maintenance window','tested rollback')
    excluded=@('.env','.env.gate','secrets','original SQL dump','uploads content','resource/file content','logs','QA','Docker','old preview packages')
    no_automatic_deletions=$true; changes=$changes; files=$fileHashes; incremental_sql=$incrementalHashes
}
[IO.File]::WriteAllText((Join-Path $ops 'manifest.json'),($receipt | ConvertTo-Json -Depth 10),[Text.UTF8Encoding]::new($false))
$warning="STAGING ONLY / 운영 직접 업로드 금지. web-files 전체를 바로 FTP 덮어쓰지 마세요. operations/운영배포.md 순서와 Go/No-Go 조건을 먼저 확인하세요. 운영 .env와 기존 uploads/resource/file/.well-known을 보존합니다. operations는 웹 공개 디렉터리에 올리지 않습니다. 자동 파일 삭제 없음."
[IO.File]::WriteAllText((Join-Path $output '업로드전_필독.txt'),$warning,[Text.UTF8Encoding]::new($false))
Add-Type -AssemblyName System.IO.Compression
[IO.Compression.ZipFile]::CreateFromDirectory($output,$zip,[IO.Compression.CompressionLevel]::Optimal,$false)
Write-Output ("STAGING CANDIDATE ONLY "+$project+": "+$fileHashes.Count+" files")
Write-Output $zip
