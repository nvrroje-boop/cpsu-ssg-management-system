@echo off
setlocal

cd /d "%~dp0"

echo Stopping Laravel, queue workers, and ngrok...

taskkill /FI "WINDOWTITLE eq SSG Laravel Server*" /F >nul 2>nul
taskkill /FI "WINDOWTITLE eq SSG Queue Worker*" /F >nul 2>nul
taskkill /FI "WINDOWTITLE eq SSG ngrok Tunnel*" /F >nul 2>nul

taskkill /IM ngrok.exe /F >nul 2>nul

for /f "tokens=5" %%P in ('netstat -ano ^| findstr /R /C:":8000 .*LISTENING"') do (
    taskkill /PID %%P /F >nul 2>nul
)

echo.
echo Stop command finished.
echo If any window is still open, you can close it manually.
echo.
pause
