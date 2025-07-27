# KBC Plus - InfinityFree Deployment Preparation Script
# This PowerShell script helps prepare your project for deployment

Write-Host "=== KBC Plus - InfinityFree Deployment Preparation ===" -ForegroundColor Green
Write-Host ""

# Get current directory
$projectPath = Get-Location
Write-Host "Project Path: $projectPath" -ForegroundColor Yellow

# Create deployment folder
$deploymentPath = Join-Path $projectPath "deployment_ready"
if (Test-Path $deploymentPath) {
    Write-Host "Removing existing deployment folder..." -ForegroundColor Yellow
    Remove-Item $deploymentPath -Recurse -Force
}

Write-Host "Creating deployment folder..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $deploymentPath | Out-Null

# Files and folders to include in deployment
$filesToInclude = @(
    "assets",
    "backend",
    "index.html",
    "admin.html",
    "login.html",
    "admin.js"
)

# Files to specifically exclude (development/test files)
$filesToExclude = @(
    "*test*",
    "*debug*",
    "*check*",
    "*fix*",
    "*setup*",
    "*create*",
    "*examine*",
    "*import*",
    "*.sql",
    "*.log",
    "*local*",
    "*xampp*"
)

Write-Host "Copying essential files..." -ForegroundColor Yellow

# Copy essential files and folders
foreach ($item in $filesToInclude) {
    $sourcePath = Join-Path $projectPath $item
    if (Test-Path $sourcePath) {
        $destPath = Join-Path $deploymentPath $item
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

# Copy database schema (for manual import)
$databasePath = Join-Path $deploymentPath "database_import"
New-Item -ItemType Directory -Path $databasePath | Out-Null
$schemaPath = Join-Path $projectPath "database\schema.sql"
if (Test-Path $schemaPath) {
    Copy-Item $schemaPath (Join-Path $databasePath "schema.sql")
    Write-Host "  ✓ Copied database schema" -ForegroundColor Green
}

# Copy sample data
$sampleDataPath = Join-Path $projectPath "database\sample_data.sql"
if (Test-Path $sampleDataPath) {
    Copy-Item $sampleDataPath (Join-Path $databasePath "sample_data.sql")
    Write-Host "  ✓ Copied sample data" -ForegroundColor Green
}

# Copy production configuration
$prodConfigPath = Join-Path $projectPath "backend\config_production.php"
if (Test-Path $prodConfigPath) {
    Copy-Item $prodConfigPath (Join-Path $deploymentPath "backend\config_production.php")
    Write-Host "  ✓ Copied production config template" -ForegroundColor Green
}

# Copy optimized .htaccess
$htaccessPath = Join-Path $projectPath ".htaccess_infinityfree"
if (Test-Path $htaccessPath) {
    Copy-Item $htaccessPath (Join-Path $deploymentPath ".htaccess")
    Write-Host "  ✓ Copied optimized .htaccess" -ForegroundColor Green
}

# Create logs directory
$logsPath = Join-Path $deploymentPath "logs"
New-Item -ItemType Directory -Path $logsPath | Out-Null
New-Item -ItemType File -Path (Join-Path $logsPath "error.log") | Out-Null
New-Item -ItemType File -Path (Join-Path $logsPath ".htaccess") -Value "Order allow,deny`nDeny from all" | Out-Null
Write-Host "  ✓ Created logs directory with protection" -ForegroundColor Green

# Remove development files from backend if they exist
$backendPath = Join-Path $deploymentPath "backend"
if (Test-Path $backendPath) {
    Get-ChildItem $backendPath -File | Where-Object { 
        $_.Name -match "(test_|debug_|check_|fix_|setup_|create_|examine_|import_)" 
    } | Remove-Item -Force
    Write-Host "  ✓ Removed development files from backend" -ForegroundColor Green
}

# Create deployment instructions
$instructionsPath = Join-Path $deploymentPath "DEPLOYMENT_INSTRUCTIONS.txt"
$instructions = @"
KBC Plus - InfinityFree Deployment Instructions
==============================================

BEFORE UPLOADING:
1. Update backend/config_production.php with your InfinityFree database details
2. Replace 'yourdomain.infinityfreeapp.com' with your actual domain in:
   - backend/config_production.php
   - .htaccess
3. Set up your InfinityFree database and import database_import/schema.sql

UPLOAD PROCESS:
1. Upload all files to your InfinityFree htdocs directory
2. Rename config_production.php to config.php in the backend folder
3. Set proper file permissions (PHP files: 644, Directories: 755)
4. Test your website

DATABASE SETUP:
1. Create MySQL database in InfinityFree control panel
2. Import database_import/schema.sql using phpMyAdmin
3. Optionally import sample_data.sql for test data
4. Update config.php with your database credentials

POST-DEPLOYMENT:
1. Enable SSL certificate in InfinityFree control panel
2. Uncomment HTTPS redirect rules in .htaccess
3. Test all functionality
4. Set up regular backups

For detailed instructions, see INFINITYFREE_DEPLOYMENT_GUIDE.md
"@

$instructions | Out-File -FilePath $instructionsPath -Encoding UTF8
Write-Host "  ✓ Created deployment instructions" -ForegroundColor Green

# Create a simple PHP test file
$testPhpPath = Join-Path $deploymentPath "test_connection.php"
$testPhpContent = @"
<?php
// Simple connection test - DELETE after successful deployment
require_once 'backend/config.php';

echo "<h2>KBC Plus - Connection Test</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

try {
    // Test database connection
    $stmt = `$pdo->query("SELECT 1 as test");
    $result = `$stmt->fetch();
    if (`$result['test'] == 1) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
    }
} catch (Exception `$e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . `$e->getMessage() . "</p>";
}

echo "<p><strong>Remember to delete this file after testing!</strong></p>";
?>
"@

$testPhpContent | Out-File -FilePath $testPhpPath -Encoding UTF8
Write-Host "  ✓ Created connection test file" -ForegroundColor Green

Write-Host ""
Write-Host "=== Deployment Preparation Complete! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Review files in the 'deployment_ready' folder" -ForegroundColor White
Write-Host "2. Update config_production.php with your InfinityFree details" -ForegroundColor White
Write-Host "3. Follow the instructions in DEPLOYMENT_INSTRUCTIONS.txt" -ForegroundColor White
Write-Host "4. Read INFINITYFREE_DEPLOYMENT_GUIDE.md for detailed guidance" -ForegroundColor White
Write-Host ""
Write-Host "Deployment folder created at: $deploymentPath" -ForegroundColor Cyan

# Show folder contents
Write-Host ""
Write-Host "Deployment folder contents:" -ForegroundColor Yellow
Get-ChildItem $deploymentPath -Recurse -Directory | ForEach-Object {
    Write-Host "📁 $($_.FullName.Replace($deploymentPath + '\', ''))" -ForegroundColor Cyan
}
Get-ChildItem $deploymentPath -File | ForEach-Object {
    Write-Host "📄 $($_.Name)" -ForegroundColor White
}

Write-Host ""
Write-Host "Ready for deployment! 🚀" -ForegroundColor Green
