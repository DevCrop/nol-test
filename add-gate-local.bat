@echo off
findstr /i /c:"gate.local" %SystemRoot%\System32\drivers\etc\hosts >nul
if %errorlevel%==0 (
  echo already in hosts
) else (
  echo 127.0.0.1 gate.local>>%SystemRoot%\System32\drivers\etc\hosts
  echo added
)
ipconfig /flushdns
pause
