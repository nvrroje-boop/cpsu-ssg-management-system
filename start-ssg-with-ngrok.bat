@echo off
setlocal

cd /d "%~dp0"

set "PORT=8000"
set "NGROK_PATH="

if not "%~1"=="" set "PORT=%~1"

set "NGROK_CMD="

if exist "%~dp0ngrok.exe" set "NGROK_CMD=%~dp0ngrok.exe"
if defined NGROK_PATH if exist "%NGROK_PATH%" set "NGROK_CMD=%NGROK_PATH%"

if not defined NGROK_CMD (
    where ngrok >nul 2>nul
    if not errorlevel 1 set "NGROK_CMD=ngrok"
)

if not defined NGROK_CMD (
    echo ngrok.exe was not found.
    echo.
    echo Do one of these:
    echo 1. Copy ngrok.exe into this project folder:
    echo    %~dp0
    echo 2. Edit this file and set NGROK_PATH to your full ngrok.exe path.
    echo 3. Add ngrok to your PATH.
    echo.
    pause
    exit /b 1
)

echo Stopping old ngrok tunnels...
taskkill /IM ngrok.exe /F >nul 2>nul

echo Releasing port %PORT% if it is already in use...
for /f "tokens=5" %%P in ('netstat -ano ^| findstr /R /C:":%PORT% .*LISTENING"') do (
    taskkill /PID %%P /F >nul 2>nul
)

timeout /t 2 >nul

echo Starting Laravel on port %PORT%...
start "SSG Laravel Server" cmd /k "cd /d ""%~dp0"" && php artisan serve --host=0.0.0.0 --port=%PORT%"

timeout /t 3 >nul

echo Restarting any existing queue workers...
php artisan queue:restart >nul 2>nul

echo Starting queue worker...
start "SSG Queue Worker" cmd /k "cd /d ""%~dp0"" && php artisan queue:work database --queue=default --tries=3 --timeout=120"

timeout /t 2 >nul

echo Starting ngrok tunnel...
start "SSG ngrok Tunnel" cmd /k "cd /d ""%~dp0"" && ""%NGROK_CMD%"" http %PORT%"

echo.
echo Three windows were opened:
echo - Laravel Server
echo - Queue Worker
echo - ngrok Tunnel
echo.
echo Keep both windows open while testing on your phone.
echo The live public URL will appear in the ngrok window.
echo.
pause
