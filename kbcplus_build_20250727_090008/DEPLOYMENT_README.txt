KBC Plus - Deployment Instructions
==================================

QUICK START:
1. Upload all files to your web server's htdocs/public_html folder
2. Create MySQL database in your hosting control panel
3. Import database_import/schema.sql using phpMyAdmin
4. Optionally import database_import/sample_data.sql for test data
5. Rename backend/config_production.php to backend/config.php
6. Edit backend/config.php with your database details
7. Replace 'yourdomain.infinityfreeapp.com' with your actual domain

IMPORTANT:
- Update database credentials in backend/config.php
- Replace domain placeholders in config files
- Enable SSL if available on your hosting
- Delete test_connection.php after testing
- The logs directory is protected with .htaccess

FILES INCLUDED:
- Essential website files only (no dev/test files)
- Production-ready configuration template
- Database schema and sample data for import
- Protected logs directory
- Connection test file for verification

FOLDER STRUCTURE:
├── assets/              # CSS, JS, and other static assets
├── backend/             # PHP backend files and APIs
│   ├── api/            # API endpoints
│   └── config_production.php # Rename to config.php and edit
├── database_import/     # Database files to import
│   ├── schema.sql      # Main database structure
│   └── sample_data.sql # Sample content (optional)
├── logs/               # Protected error log directory
├── index.html          # Main website homepage
├── admin.html          # Admin panel
├── login.html          # Login page
├── admin.js            # Admin functionality
├── .htaccess           # Web server configuration
└── test_connection.php # Delete after testing

HOSTING REQUIREMENTS:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- mod_rewrite enabled (for .htaccess)
- At least 50MB storage space

POST-DEPLOYMENT:
1. Test the connection using test_connection.php
2. Log into admin panel to verify functionality
3. Delete test_connection.php for security
4. Monitor logs/error.log for any issues

For support, refer to the original project documentation.
