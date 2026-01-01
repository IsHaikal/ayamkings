@echo off
title AyamKings Dev Server
color 0A

echo ========================================
echo   AyamKings Dev Environment Starting...
echo ========================================
echo.

:: Check if MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [1/3] MySQL sudah running!
) else (
    echo [1/3] Starting MySQL...
    start "" /B "C:\xampp\mysql\bin\mysqld.exe"
    timeout /t 2 >nul
    echo       MySQL started!
)

echo [2/3] Testing database connection...
echo ^<?php $c=@new mysqli('localhost','root','','ayamkings_db'); echo $c-^>connect_error?'FAIL':'OK'; ?^> > "%TEMP%\test_db.php"
for /f %%i in ('C:\xampp\php\php.exe "%TEMP%\test_db.php"') do set DBRESULT=%%i

if "%DBRESULT%"=="OK" (
    echo       Database 'ayamkings_db' connected!
) else (
    echo       WARNING: Database connection failed!
    echo       Pastikan database 'ayamkings_db' wujud.
    pause
)

echo [3/3] Starting servers...
echo.

:: Start Backend in new window
start "Backend API - Port 8000" cmd /k "cd /d "%~dp0ayamkings_backend" && echo Backend API running at http://localhost:8000 && C:\xampp\php\php.exe -S localhost:8000"

:: Start Frontend in new window (using PHP server)
start "Frontend - Port 5500" cmd /k "cd /d "%~dp0ayamkings_frontend" && echo Frontend running at http://localhost:5500 && C:\xampp\php\php.exe -S localhost:5500"

timeout /t 3 >nul

echo.
echo ========================================
echo   All servers started!
echo ========================================
echo.
echo   Frontend: http://localhost:5500
echo   Backend:  http://localhost:8000
echo   Database: localhost:3306 (ayamkings_db)
echo.
echo   Tutup terminal windows untuk stop servers
echo.

:: Open browser
start "" "http://localhost:5500"

echo Press any key to close this window...
pause >nul
