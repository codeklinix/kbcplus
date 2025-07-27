# KBC Plus - Create Clean Deployment ZIP
# This script creates a ZIP file with only the essential files for InfinityFree deployment

Write-Host "=== Creating KBC Plus Deployment ZIP ===" -ForegroundColor Green
Write-Host ""

# Get current directory
$projectPath = Get-Location
$zipFileName = "kbcplus_deployment_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip"
$zipPath = Join-Path $projectPath $zipFileName

# Create temporary directory for clean files
$tempPath = Join-Path $projectPath "temp_deployment"
if (Test-Path $tempPath) {
    Remove-Item $tempPath -Recurse -Force
}
New-Item -ItemType Directory -Path $tempPath | Out-Null

Write-Host "Copying essential files..." -ForegroundColor Yellow

# Essential files and folders to include
$essentialItems = @(
    @{Source = "assets"; Type = "Folder"},
    @{Source = "backend"; Type = "Folder"},
    @{Source = "index.html"; Type = "File"},
    @{Source = "admin.html"; Type = "File"},
    @{Source = "login.html"; Type = "File"},
    @{Source = "admin.js"; Type = "File"}
)

# Copy essential items
foreach ($item in $essentialItems) {
    $sourcePath = Join-Path $projectPath $item.Source
    $destPath = Join-Path $tempPath $item.Source
    
    if (Test-Path $sourcePath) {
        if ($item.Type -eq "Folder") {
            Copy-Item $sourcePath $destPath -Recurse
            Write-Host "  ✓ Copied folder: $($item.Source)" -ForegroundColor Green
        } else {
            Copy-Item $sourcePath $destPath
            Write-Host "  ✓ Copied file: $($item.Source)" -ForegroundColor Green
        }
    } else {
        Write-Host "  ⚠ Not found: $($item.Source)" -ForegroundColor Yellow
    }
}

# Clean up backend folder - remove development files
$backendPath = Join-Path $tempPath "backend"
if (Test-Path $backendPath) {
    $devFilePatterns = @("test_*", "debug_*", "check_*", "fix_*", "setup_*", "create_*", "examine_*", "import_*")
    
    foreach ($pattern in $devFilePatterns) {
        Get-ChildItem $backendPath -Recurse -File -Name $pattern | ForEach-Object {
            $filePath = Join-Path $backendPath $_
            Remove-Item $filePath -Force
        }
    }
    Write-Host "  ✓ Cleaned development files from backend" -ForegroundColor Green
}

# Add production configuration
$prodConfigSource = Join-Path $projectPath "backend\config_production.php"
if (Test-Path $prodConfigSource) {
    Copy-Item $prodConfigSource (Join-Path $tempPath "backend\config_production.php")
    Write-Host "  ✓ Added production config template" -ForegroundColor Green
}

# Add optimized .htaccess
$htaccessSource = Join-Path $projectPath ".htaccess_infinityfree"
if (Test-Path $htaccessSource) {
    Copy-Item $htaccessSource (Join-Path $tempPath ".htaccess")
    Write-Host "  ✓ Added optimized .htaccess" -ForegroundColor Green
}

# Create database import folder with schema
$dbImportPath = Join-Path $tempPath "database_import"
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
$logsPath = Join-Path $tempPath "logs"
New-Item -ItemType Directory -Path $logsPath | Out-Null
New-Item -ItemType File -Path (Join-Path $logsPath "error.log") | Out-Null
"Order allow,deny`nDeny from all" | Out-File -FilePath (Join-Path $logsPath ".htaccess") -Encoding ASCII
Write-Host "  ✓ Created protected logs directory" -ForegroundColor Green

# Create deployment instructions
$instructionsContent = @"
KBC Plus - InfinityFree Deployment Instructions
==============================================

QUICK START:
1. Extract this ZIP to your InfinityFree htdocs folder
2. Create MySQL database in InfinityFree control panel
3. Import database_import/schema.sql using phpMyAdmin
4. Rename config_production.php to config.php in backend folder
5. Edit config.php with your database details
6. Replace 'yourdomain.infinityfreeapp.com' with your actual domain

IMPORTANT:
- Update database credentials in backend/config.php
- Replace domain placeholders in config files
- Enable SSL in InfinityFree control panel after upload
- Delete test_connection.php after testing (if included)

FILES INCLUDED:
- Essential website files only
- Production-ready configuration template
- Optimized .htaccess for InfinityFree
- Database schema for import
- 
For detailed instructions, see the full deployment guide.
"@

$instructionsContent | Out-File -FilePath (Join-Path $tempPath "DEPLOYMENT_README.txt") -Encoding UTF8
Write-Host "  ✓ Created deployment instructions" -ForegroundColor Green

# Create connection test file
$testContent = @'
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

$testContent | Out-File -FilePath (Join-Path $tempPath "test_connection.php") -Encoding UTF8
Write-Host "  ✓ Created connection test file" -ForegroundColor Green

Write-Host ""
Write-Host "Creating ZIP file..." -ForegroundColor Yellow

# Create ZIP file
try {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempPath, $zipPath)
    Write-Host "  ✓ ZIP file created successfully" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Error creating ZIP file: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Clean up temporary directory
Remove-Item $tempPath -Recurse -Force
Write-Host "  ✓ Cleaned up temporary files" -ForegroundColor Green

# Get ZIP file size
$zipSize = (Get-Item $zipPath).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)

Write-Host ""
Write-Host "=== Deployment ZIP Created Successfully! ===" -ForegroundColor Green
Write-Host ""
Write-Host "ZIP File: $zipFileName" -ForegroundColor Cyan
Write-Host "Size: $zipSizeMB MB" -ForegroundColor Cyan
Write-Host "Location: $zipPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Extract ZIP file to InfinityFree htdocs folder" -ForegroundColor White
Write-Host "2. Follow instructions in DEPLOYMENT_README.txt" -ForegroundColor White
Write-Host "3. Update config_production.php with your details" -ForegroundColor White
Write-Host "4. Rename config_production.php to config.php" -ForegroundColor White
Write-Host ""
Write-Host "Ready for deployment! 🚀" -ForegroundColor Green
