# KBC Plus - Simple Deployment Build Script
# Creates a clean deployment folder with essential files

Write-Host "=== Creating KBC Plus Deployment Build ===" -ForegroundColor Green
Write-Host ""

# Get current directory
$projectPath = Get-Location
$buildFolder = "kbcplus_build_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
$buildPath = Join-Path $projectPath $buildFolder

# Create build directory
Write-Host "Creating build directory: $buildFolder" -ForegroundColor Yellow
New-Item -ItemType Directory -Path $buildPath | Out-Null

# Essential files and folders to copy
$essentialItems = @(
    "assets",
    "backend", 
    "index.html",
    "admin.html",
    "login.html",
    "admin.js"
)

Write-Host "Copying essential files..." -ForegroundColor Yellow

# Copy essential items
foreach ($item in $essentialItems) {
    $sourcePath = Join-Path $projectPath $item
    $destPath = Join-Path $buildPath $item
    
    if (Test-Path $sourcePath) {
        if (Test-Path $sourcePath -PathType Container) {
            Copy-Item $sourcePath $destPath -Recurse
            Write-Host "  ✓ Copied folder: $item" -ForegroundColor Green
        } else {
            Copy-Item $sourcePath $destPath
            Write-Host "  ✓ Copied file: $item" -ForegroundColor Green
        }
    } else {
        Write-Host "  ⚠ Not found: $item" -ForegroundColor Yellow
    }
}

# Clean up backend folder - remove development files
$backendPath = Join-Path $buildPath "backend"
if (Test-Path $backendPath) {
    $devFiles = Get-ChildItem $backendPath -File | Where-Object { 
        $_.Name -like "test_*" -or 
        $_.Name -like "debug_*" -or 
        $_.Name -like "check_*" -or 
        $_.Name -like "fix_*" -or 
        $_.Name -like "setup_*" -or 
        $_.Name -like "create_*" -or 
        $_.Name -like "examine_*" -or 
        $_.Name -like "import_*"
    }
    
    foreach ($file in $devFiles) {
        Remove-Item $file.FullName -Force
    }
    Write-Host "  ✓ Cleaned development files from backend" -ForegroundColor Green
}

# Copy production configuration
$prodConfigSource = Join-Path $projectPath "backend\config_production.php"
if (Test-Path $prodConfigSource) {
    Copy-Item $prodConfigSource (Join-Path $buildPath "backend\config_production.php")
    Write-Host "  ✓ Added production config template" -ForegroundColor Green
}

# Copy .htaccess if exists
$htaccessSource = Join-Path $projectPath ".htaccess"
if (Test-Path $htaccessSource) {
    Copy-Item $htaccessSource (Join-Path $buildPath ".htaccess")
    Write-Host "  ✓ Added .htaccess" -ForegroundColor Green
}

# Create database import folder with schema
$dbImportPath = Join-Path $buildPath "database_import"
New-Item -ItemType Directory -Path $dbImportPath | Out-Null

$schemaSource = Join-Path $projectPath "database\schema.sql"
if (Test-Path $schemaSource) {
    Copy-Item $schemaSource (Join-Path $dbImportPath "schema.sql")
    Write-Host "  ✓ Added database schema" -ForegroundColor Green
}

$sampleDataSource = Join-Path $projectPath "database\sample_data.sql"
if (Test-Path $sampleDataSource) {
    Copy-Item $sampleDataSource (Join-Path $dbImportPath "sample_data.sql")
    Write-Host "  ✓ Added sample data" -ForegroundColor Green
}

# Create logs directory with protection
$logsPath = Join-Path $buildPath "logs"
New-Item -ItemType Directory -Path $logsPath | Out-Null
New-Item -ItemType File -Path (Join-Path $logsPath "error.log") | Out-Null

# Create .htaccess for logs directory
$logsHtaccess = @"
Order allow,deny
Deny from all
"@
$logsHtaccess | Out-File -FilePath (Join-Path $logsPath ".htaccess") -Encoding ASCII
Write-Host "  ✓ Created protected logs directory" -ForegroundColor Green

# Create deployment instructions
$instructions = @"
KBC Plus - Deployment Instructions
==================================

QUICK START:
1. Upload all files to your web server's htdocs/public_html folder
2. Create MySQL database in your hosting control panel
3. Import database_import/schema.sql using phpMyAdmin
4. Rename config_production.php to config.php in backend folder
5. Edit config.php with your database details
6. Replace 'yourdomain.infinityfreeapp.com' with your actual domain

IMPORTANT:
- Update database credentials in backend/config.php
- Replace domain placeholders in config files
- Enable SSL if available
- Delete test_connection.php after testing

FILES INCLUDED:
- Essential website files only
- Production-ready configuration template
- Database schema for import
- Protected logs directory

"@

$instructions | Out-File -FilePath (Join-Path $buildPath "DEPLOYMENT_README.txt") -Encoding UTF8
Write-Host "  ✓ Created deployment instructions" -ForegroundColor Green

# Create connection test file
$testPhp = @'
<?php
// Simple connection test - DELETE after successful deployment
require_once 'backend/config.php';
echo "<h2>KBC Plus - Connection Test</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
try {
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result["test"] == 1) {
        echo "<p style=\"color: green;\">Database connection successful!</p>";
    }
} catch (Exception $e) {
    echo "<p style=\"color: red;\">Database connection failed: " . $e->getMessage() . "</p>";
}
echo "<p><strong>Remember to delete this file after testing!</strong></p>";
?>
'@

$testPhp | Out-File -FilePath (Join-Path $buildPath "test_connection.php") -Encoding UTF8
Write-Host "  ✓ Created connection test file" -ForegroundColor Green

Write-Host ""
Write-Host "=== Build Created Successfully! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Build Folder: $buildFolder" -ForegroundColor Cyan
Write-Host "Location: $buildPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Upload all files from build folder to your web server" -ForegroundColor White
Write-Host "2. Follow instructions in DEPLOYMENT_README.txt" -ForegroundColor White
Write-Host "3. Update config_production.php with your details" -ForegroundColor White
Write-Host "4. Rename config_production.php to config.php" -ForegroundColor White
Write-Host ""
Write-Host "Ready for deployment! 🚀" -ForegroundColor Green
