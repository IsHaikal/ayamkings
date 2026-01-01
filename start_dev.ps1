# ==========================================
# AyamKings Development Server Startup Script
# ==========================================
# Double-click untuk start semua servers
# CTRL+C untuk stop servers

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  AyamKings Dev Environment Starting..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$backendPath = Join-Path $projectPath "ayamkings_backend"
$frontendPath = Join-Path $projectPath "ayamkings_frontend"

# ==========================================
# 1. Start MySQL dari XAMPP
# ==========================================
Write-Host "[1/3] Starting MySQL..." -ForegroundColor Yellow

$mysqlProcess = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "      MySQL sudah running!" -ForegroundColor Green
} else {
    Start-Process "C:\xampp\mysql\bin\mysqld.exe" -WindowStyle Hidden
    Start-Sleep -Seconds 2
    Write-Host "      MySQL started!" -ForegroundColor Green
}

# ==========================================
# 2. Test Database Connection
# ==========================================
Write-Host "[2/3] Testing database connection..." -ForegroundColor Yellow

$testDbScript = @"
<?php
`$conn = @new mysqli('localhost', 'root', '', 'ayamkings_db');
if (`$conn->connect_error) {
    echo 'FAIL:' . `$conn->connect_error;
} else {
    echo 'OK';
    `$conn->close();
}
?>
"@

$phpPath = "C:\xampp\php\php.exe"

$testDbScript | Out-File -FilePath "$env:TEMP\test_db.php" -Encoding UTF8
$dbResult = & $phpPath "$env:TEMP\test_db.php" 2>&1

if ($dbResult -eq "OK") {
    Write-Host "      Database 'ayamkings_db' connected!" -ForegroundColor Green
} else {
    Write-Host "      Database connection FAILED!" -ForegroundColor Red
    Write-Host "      Error: $dbResult" -ForegroundColor Red
    Write-Host ""
    Write-Host "      Pastikan:" -ForegroundColor Yellow
    Write-Host "      - Database 'ayamkings_db' wujud dalam phpMyAdmin" -ForegroundColor Yellow
    Write-Host "      - MySQL sudah start" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to continue anyway..."
}

# ==========================================
# 3. Start Backend & Frontend Servers
# ==========================================
Write-Host "[3/3] Starting servers..." -ForegroundColor Yellow

# Start Backend PHP Server
$backendJob = Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$backendPath'; Write-Host 'Backend API running at http://localhost:8000' -ForegroundColor Green; C:\xampp\php\php.exe -S localhost:8000" -PassThru

# Start Frontend Server
$frontendJob = Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$frontendPath'; Write-Host 'Frontend running at http://localhost:5500' -ForegroundColor Green; npx -y http-server -p 5500 -c-1" -PassThru

Start-Sleep -Seconds 2

# ==========================================
# Done!
# ==========================================
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  All servers started!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Frontend: http://localhost:5500" -ForegroundColor Cyan
Write-Host "  Backend:  http://localhost:8000" -ForegroundColor Cyan
Write-Host "  Database: localhost:3306 (ayamkings_db)" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Tutup semua terminal windows untuk stop servers" -ForegroundColor Yellow
Write-Host ""

# Open browser automatically
Start-Process "http://localhost:5500"

Read-Host "Press Enter to exit this window..."
