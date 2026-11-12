param([string]$SourceContainer = 'blue-phpgate-20261007-web-1')
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$container = 'blue-mfa-auto-qa-' + [Guid]::NewGuid().ToString('N').Substring(0, 8)
$envFile = Join-Path ([IO.Path]::GetTempPath()) ($container + '.env')
try {
    $source = (docker inspect $SourceContainer | ConvertFrom-Json)[0]
    if ($LASTEXITCODE -ne 0) { throw 'Local source Docker container is required.' }
    $values = @{}
    foreach ($line in $source.Config.Env) {
        $pair = $line.Split('=', 2)
        $values[$pair[0]] = $pair[1]
    }
    if ($values.APP_ENV -ne 'development' -or $values.DB_USER -ne 'qa_app') { throw 'Only development / qa_app may run this QA.' }
    $values.MFA_ENABLED = 'true'
    $values.SMTP_HOST = 'localhost'
    $values.SMTP_PORT = '2525'
    $values.SMTP_USER = 'qa-mfa@example.com'
    $values.SMTP_PASS = 'local-test-only'
    $values.SMTP_FROM = 'qa-mfa@example.com'
    $values.GATE_ALLOW_IPS = ''
    $values.TRUSTED_PROXY_CIDRS = ''
    [IO.File]::WriteAllLines($envFile, @($values.Keys | ForEach-Object { $_ + '=' + $values[$_] }), [Text.UTF8Encoding]::new($false))
    $network = @($source.NetworkSettings.Networks.PSObject.Properties.Name)[0]
    $startup = 'openssl req -x509 -newkey rsa:2048 -nodes -keyout /tmp/blue-mfa-key.pem -out /tmp/blue-mfa-cert.pem -days 1 -subj /CN=localhost -addext subjectAltName=DNS:localhost,IP:127.0.0.1 >/dev/null 2>&1 && echo openssl.cafile=/tmp/blue-mfa-cert.pem > /usr/local/etc/php/conf.d/zz-mfa-qa.ini && apache2-foreground'
    $null = docker run -d --name $container --network $network --env-file $envFile --mount "type=bind,source=$root,target=/var/www/html" --mount "type=bind,source=$root/apache.conf,target=/etc/apache2/sites-available/000-default.conf,readonly" $source.Config.Image sh -c $startup
    if ($LASTEXITCODE -ne 0) { throw 'Could not start isolated local MFA QA container.' }
    $ready = $false
    for ($i = 0; $i -lt 30; $i++) {
        docker exec $container php -r 'exit(@fsockopen("127.0.0.1",80)?0:1);' 2>$null
        if ($LASTEXITCODE -eq 0) { $ready = $true; break }
        Start-Sleep -Milliseconds 200
    }
    if (-not $ready) { throw 'Isolated QA Apache did not start.' }
    docker exec $container php qa/mfa-auto-send.php
    if ($LASTEXITCODE -ne 0) { throw 'Automatic MFA HTTP QA failed.' }
    docker exec $container php qa/password-session-transition.php
    if ($LASTEXITCODE -ne 0) { throw 'MFA password transition QA failed.' }
} finally {
    if (Test-Path -LiteralPath $envFile) { Remove-Item -LiteralPath $envFile }
    $null = docker rm -f $container 2>$null
}
