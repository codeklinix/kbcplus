# KBC Plus - Simple Deployment ZIP Creator
Write-Host "=== Creating KBC Plus Deployment ZIP ===" -ForegroundColor Green

# Get current directory and create ZIP filename
$projectPath = Get-Location
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$zipFileName = "kbcplus_deployment_$timestamp.zip"
$zipPath = Join-Path $projectPath $zipFileName

# Create temporary directory
$tempPath = Join-Path $projectPath "temp_deployment"
if (Test-Path $tempPath) {
    Remove-Item $tempPath -Recurse -Force
}
New-Item -ItemType Directory -Path $tempPath | Out-Null

Write-Host "Copying essential files..." -ForegroundColor Yellow

# Copy essential folders
$folders = @("assets", "backend")
foreach ($folder in $folders) {
    $sourcePath = Join-Path $projectPath $folder
    if (Test-Path $sourcePath) {
        Copy-Item $sourcePath (Join-Path $tempPath $folder) -Recurse
        Write-Host "  ✓ Copied folder: $folder" -ForegroundColor Green
    }
}

# Copy essential files
$files = @("index.html", "admin.html", "login.html", "admin.js")
foreach ($file in $files) {
    $sourcePath = Join-Path $projectPath $file
    if (Test-Path $sourcePath) {
        Copy-Item $sourcePath (Join-Path $tempPath $file)
        Write-Host "  ✓ Copied file: $file" -ForegroundColor Green
    }
}

# Clean backend folder
$backendPath = Join-Path $tempPath "backend"
if (Test-Path $backendPath) {
    Get-ChildItem $backendPath -File | Where-Object { 
        $_.Name -match "^(test_|debug_|check_|fix_|setup_|create_|examine_|import_)" 
    } | Remove-Item -Force
    Write-Host "  ✓ Cleaned development files from backend" -ForegroundColor Green
}

# Copy production config
$prodConfig = Join-Path $projectPath "backend\config_production.php"
if (Test-Path $prodConfig) {
    Copy-Item $prodConfig (Join-Path $tempPath "backend\config_production.php")
    Write-Host "  ✓ Added production config" -ForegroundColor Green
}

# Copy optimized htaccess
$htaccess = Join-Path $projectPath ".htaccess_infinityfree"
if (Test-Path $htaccess) {
    Copy-Item $htaccess (Join-Path $tempPath ".htaccess")
    Write-Host "  ✓ Added optimized .htaccess" -ForegroundColor Green
}

# Create database import folder
$dbFolder = Join-Path $tempPath "database_import"
New-Item -ItemType Directory -Path $dbFolder | Out-Null

$schema = Join-Path $projectPath "database\schema.sql"
if (Test-Path $schema) {
    Copy-Item $schema (Join-Path $dbFolder "schema.sql")
    Write-Host "  ✓ Added database schema" -ForegroundColor Green
}

$sampleData = Join-Path $projectPath "database\sample_data.sql"
if (Test-Path $sampleData) {
    Copy-Item $sampleData (Join-Path $dbFolder "sample_data.sql")
    Write-Host "  ✓ Added sample data" -ForegroundColor Green
}

# Create logs directory
$logsPath = Join-Path $tempPath "logs"
New-Item -ItemType Directory -Path $logsPath | Out-Null
New-Item -ItemType File -Path (Join-Path $logsPath "error.log") | Out-Null
"Order allow,deny`nDeny from all" | Out-File (Join-Path $logsPath ".htaccess") -Encoding ASCII
Write-Host "  ✓ Created logs directory" -ForegroundColor Green

# Create instructions file
$instructions = @"
KBC Plus - InfinityFree Deployment Instructions
==============================================

QUICK SETUP:
1. Extract this ZIP to your InfinityFree htdocs folder
2. Create MySQL database in InfinityFree control panel
3. Import database_import/schema.sql using phpMyAdmin
4. Rename config_production.php to config.php in backend folder
5. Edit config.php with your InfinityFree database details
6. Replace 'yourdomain.infinityfreeapp.com' with your actual domain

IMPORTANT UPDATES NEEDED:
- Database credentials in backend/config.php
- Domain name in config.php and .htaccess
- Enable SSL in InfinityFree after upload

FILES INCLUDED:
- Essential website files only
- Production configuration template
- Optimized .htaccess for shared hosting
- Database schema for import

For detailed instructions, see the deployment guide files.
"@

$instructions | Out-File (Join-Path $tempPath "DEPLOYMENT_README.txt") -Encoding UTF8
Write-Host "  ✓ Created deployment instructions" -ForegroundColor Green

Write-Host ""
Write-Host "Creating ZIP file..." -ForegroundColor Yellow

# Create ZIP
try {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempPath, $zipPath)
    Write-Host "  ✓ ZIP created successfully" -ForegroundColor Green
} catch {
    Write-Host "  ✗ ZIP creation failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Cleanup
Remove-Item $tempPath -Recurse -Force

# Show results
$zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host ""
Write-Host "=== SUCCESS ===" -ForegroundColor Green
Write-Host "ZIP File: $zipFileName" -ForegroundColor Cyan
Write-Host "Size: $zipSize MB" -ForegroundColor Cyan
Write-Host "Location: $zipPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Ready for InfinityFree deployment!" -ForegroundColor Green
