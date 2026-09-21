@echo off
findstr /C:"127.0.0.1 gate.local" "%SystemRoot%\System32\drivers\etc\hosts" >nul 2>&1
if errorlevel 1 echo 127.0.0.1 gate.local>>"%SystemRoot%\System32\drivers\etc\hosts"
echo gate.local ready
