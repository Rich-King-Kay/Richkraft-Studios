# Installation & Deployment Guide
## Deslin Montessori School Management System

---

## Table of Contents
1. [System Requirements](#system-requirements)
2. [XAMPP Installation](#xampp-installation)
3. [Project Setup](#project-setup)
4. [Database Configuration](#database-configuration)
5. [Application Configuration](#application-configuration)
6. [Verification & Testing](#verification--testing)
7. [Troubleshooting](#troubleshooting)
8. [Production Deployment](#production-deployment)

---

## System Requirements

### Minimum Requirements
- **Operating System**: Windows 7+, macOS 10.10+, Linux (Ubuntu 16.04+)
- **RAM**: 2GB minimum (4GB recommended)
- **Disk Space**: 500MB free space
- **Internet**: For initial setup only

### Software Requirements
- **XAMPP**: 7.4+ or equivalent (Apache 2.4+, PHP 7.4+, MySQL 5.7+)
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Text Editor**: Any editor for configuration (VS Code recommended)

### PHP Extensions Required
- PDO (PHP Data Objects)
- MySQLi
- GD (for image handling)
- JSON
- Session
- Filter

### Apache Modules
- mod_rewrite (for URL rewriting)
- mod_php

---

## XAMPP Installation

### Windows Installation

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org
   - Download XAMPP for Windows (PHP 7.4 or higher)
   - Choose the installer version

2. **Install XAMPP**
   - Run the installer
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Choose installation directory (default: C:\xampp)
   - Complete the installation

3. **Verify Installation**
   - Open XAMPP Control Panel
   - Start Apache and MySQL
   - Open browser: http://localhost
   - You should see XAMPP dashboard

4. **Enable mod_rewrite**
   - In XAMPP Control Panel, click "Config" for Apache
   - Open `httpd.conf`
   - Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Remove the `#` to uncomment
   - Save and restart Apache

### macOS Installation

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org
   - Download XAMPP for macOS

2. **Install XAMPP**
   - Mount the .dmg file
   - Run the installer
   - Choose installation location
   - Installation path: `/Applications/XAMPP`

3. **Start Services**
   - Open XAMPP from Applications folder
   - Start Apache and MySQL
   - Verify at: http://localhost

4. **Enable mod_rewrite**
   - Open Terminal
   - Edit httpd.conf:
     ```bash
     sudo nano /Applications/XAMPP/etc/httpd.conf
     ```
   - Find and uncomment: `LoadModule rewrite_module`
   - Restart Apache

### Linux Installation (Ubuntu)

1. **Update System**
   ```bash
   sudo apt-get update
   sudo apt-get upgrade
   ```

2. **Install XAMPP**
   ```bash
   # Download XAMPP
   wget https://www.apachefriends.org/xampp-files/7.4.27/xampp-linux-x64-7.4.27-0-installer.run
   
   # Make executable
   chmod +x xampp-linux-x64-7.4.27-0-installer.run
   
   # Run installer
   sudo ./xampp-linux-x64-7.4.27-0-installer.run
   ```

3. **Start Services**
   ```bash
   sudo /opt/lampp/lampp start
   ```

4. **Verify Installation**
   - Open browser: http://localhost

---

## Project Setup

### Step 1: Clone/Download Project

**Using Git**:
```bash
git clone https://github.com/Rich-King-Kay/Richkraft-Studios.git
cd Richkraft-Studios
git checkout deslin-montessori-school
```

**Manual Download**:
- Download ZIP from GitHub
- Extract to your desired location

### Step 2: Place in XAMPP htdocs

**Windows**:
```
C:\xampp\htdocs\Richkraft-Studios\deslin-montessori-school
```

**macOS**:
```
/Applications/XAMPP/htdocs/Richkraft-Studios/deslin-montessori-school
```

**Linux**:
```
/opt/lampp/htdocs/Richkraft-Studios/deslin-montessori-school
```

### Step 3: Create Required Directories

```bash
cd deslin-montessori-school
mkdir -p public/uploads/photos
mkdir -p public/uploads/logo
mkdir -p public/uploads/reports
mkdir -p logs
```

### Step 4: Set File Permissions

**Windows** (skip if using NTFS):
- Right-click folders > Properties > Security
- Grant write permissions to IIS_IUSRS

**macOS/Linux**:
```bash
chmod 755 public/uploads
chmod 755 public/uploads/photos
chmod 755 public/uploads/logo
chmod 755 public/uploads/reports
chmod 755 logs
chmod 644 config/config.php
```

---

## Database Configuration

### Step 1: Create Database

1. **Open phpMyAdmin**
   - Navigate to: http://localhost/phpmyadmin
   - Login with default credentials (username: root, password: blank)

2. **Create New Database**
   - Click "New" button
   - Database name: `deslin_montessori_school`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

### Step 2: Import Database Schema

1. **Select Database**
   - Click on `deslin_montessori_school`

2. **Import SQL File**
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"
   - Wait for import to complete

3. **Verify Tables**
   - You should see 14 tables created
   - Click each table to verify structure

### Step 3: Create Default Admin User

1. **Open SQL Terminal**
   - In phpMyAdmin, click "SQL" tab

2. **Run Query**
   ```sql
   INSERT INTO users (
       username, 
       email, 
       password_hash, 
       full_name, 
       role, 
       is_active
   ) VALUES (
       'admin',
       'admin@deslinmontessori.edu',
       '$2y$12$1234567890123456789012',
       'Administrator',
       'admin',
       1
   );
   ```

   *Note: Generate proper hash using this method:*
   - Create a test file `hash.php` with:
   ```php
   <?php
   echo password_hash('Admin@12345', PASSWORD_BCRYPT, ['cost' => 12]);
   ?>
   ```
   - Run it and copy the hash
   - Use the hash in the INSERT query

---

## Application Configuration

### Step 1: Edit Configuration File

1. **Open `config/config.php`**
   - Use your text editor
   - Update the following:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Your MySQL password
define('DB_NAME', 'deslin_montessori_school');

// Application URL
define('APP_URL', 'http://localhost/Richkraft-Studios/deslin-montessori-school');

// School Information
define('SCHOOL_NAME', 'Deslin Montessori School');
define('SCHOOL_EMAIL', 'info@deslinmontessori.edu');

// Set APP_ENV to 'development' for testing
define('APP_ENV', 'development');
```

2. **Save the file**

### Step 2: Configure Apache Virtual Host (Optional)

For easier access without the full path:

**Windows (httpd-vhosts.conf)**:
```apache
<VirtualHost *:80>
    ServerName deslin.local
    DocumentRoot "C:/xampp/htdocs/Richkraft-Studios/deslin-montessori-school/public"
    <Directory "C:/xampp/htdocs/Richkraft-Studios/deslin-montessori-school/public">
        AllowOverride All
        Allow from all
    </Directory>
</VirtualHost>
```

Add to your hosts file:
```
127.0.0.1 deslin.local
```

---

## Verification & Testing

### Step 1: Test Database Connection

1. **Create test file**: `public/test-connection.php`
```php
<?php
require_once '../config/config.php';
require_once '../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Database connected successfully!<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
```

2. **Access**: http://localhost/Richkraft-Studios/deslin-montessori-school/public/test-connection.php

### Step 2: Test Login System

1. **Access Login Page**
   - Navigate to: http://localhost/Richkraft-Studios/deslin-montessori-school/public/login.php

2. **Enter Credentials**
   - Username: `admin`
   - Password: `Admin@12345`
   - Click "Login"

3. **Verify Dashboard Access**
   - Should redirect to dashboard
   - Logo and navigation should load properly

### Step 3: Test File Uploads

1. **Navigate to Student Management**
   - Add a new student
   - Upload a passport photo
   - Verify file saves to `public/uploads/photos/`

### Step 4: Test All Modules

- [ ] Login/Logout
- [ ] Dashboard loads
- [ ] Student management (add/edit/delete)
- [ ] Teacher management
- [ ] Class management
- [ ] Attendance marking
- [ ] Grade entry
- [ ] Report card generation
- [ ] Settings configuration

---

## Troubleshooting

### Common Issues & Solutions

#### 1. Database Connection Error
**Error**: "Could not connect to MySQL"

**Solution**:
- [ ] Verify MySQL is running in XAMPP Control Panel
- [ ] Check DB credentials in `config/config.php`
- [ ] Verify database exists: `deslin_montessori_school`
- [ ] Check MySQL user permissions
- [ ] Try connecting via phpMyAdmin

#### 2. Blank Page on Login
**Error**: Blank/white page

**Solution**:
- [ ] Enable error display: Set `APP_ENV` to 'development'
- [ ] Check `logs/error.log`
- [ ] Verify all required PHP extensions are loaded
- [ ] Check file permissions
- [ ] Clear browser cache (Ctrl+Shift+Delete)

#### 3. File Upload Not Working
**Error**: Upload fails or files not saving

**Solution**:
- [ ] Verify upload directories exist
- [ ] Check permissions: `chmod 755 public/uploads/`
- [ ] Check `php.ini` settings:
  - `upload_max_filesize = 5M`
  - `post_max_size = 5M`
- [ ] Restart Apache after changing php.ini

#### 4. 404 Error on Pages
**Error**: "Page not found" or 404 error

**Solution**:
- [ ] Verify mod_rewrite is enabled
- [ ] Check `.htaccess` file permissions
- [ ] Verify file exists in expected location
- [ ] Clear browser cache
- [ ] Restart Apache

#### 5. Session Issues
**Error**: "Session expired" or logged out unexpectedly

**Solution**:
- [ ] Check `php.ini` session settings
- [ ] Verify session save path exists
- [ ] Increase session timeout in `config/config.php`
- [ ] Clear browser cookies
- [ ] Check server time is correct

#### 6. Logo/Images Not Displaying
**Error**: Broken image icons

**Solution**:
- [ ] Verify image file exists
- [ ] Check image path in HTML
- [ ] Verify file permissions
- [ ] Check image file format (JPEG, PNG)
- [ ] Try different browser

#### 7. CSS/JavaScript Not Loading
**Error**: Unstyled page or JS not working

**Solution**:
- [ ] Check browser console for 404 errors
- [ ] Verify asset paths are correct
- [ ] Check `.htaccess` rules
- [ ] Verify files exist in `public/assets/`
- [ ] Clear browser cache

---

## Production Deployment

### Pre-Deployment Checklist

- [ ] Set `APP_ENV` to 'production' in `config.php`
- [ ] Disable error display
- [ ] Enable error logging
- [ ] Update database credentials
- [ ] Change default admin password
- [ ] Update school information
- [ ] Upload official school logo
- [ ] Configure email settings
- [ ] Set up SSL certificate (HTTPS)
- [ ] Backup database
- [ ] Test all functionality

### Deployment Steps

1. **Prepare Server**
   - Install PHP 7.4+, MySQL 5.7+, Apache 2.4+
   - Enable mod_rewrite
   - Configure firewall
   - Set up SSL/TLS certificate

2. **Deploy Files**
   - Upload project to server
   - Set proper file permissions
   - Create upload directories
   - Create database and import schema

3. **Configure Application**
   - Update `config/config.php` with server details
   - Configure database connection
   - Update `APP_URL`
   - Set secure session configuration

4. **Security Configuration**
   - Configure SSL/TLS
   - Set up regular backups
   - Configure firewall rules
   - Set up monitoring
   - Configure automated logs rotation

5. **Test Deployment**
   - Verify all functionality
   - Check SSL certificate
   - Test file uploads
   - Test email notifications
   - Run security scan

### Backup Strategy

**Daily Backups**:
```bash
# Backup database
mysqldump -u root -p deslin_montessori_school > backups/db_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf backups/files_$(date +%Y%m%d_%H%M%S).tar.gz public/uploads/
```

**Store backups off-server** for security.

---

## Support

For technical support:
- Email: support@richkraftstudios.com
- Documentation: See README.md
- Code Comments: Check inline documentation

---

**Document Version**: 1.0
**Last Updated**: June 25, 2026
