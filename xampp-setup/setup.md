# XAMPP Setup Guide for School Management System

This guide will help you set up the School Management System with PHP and MySQL using XAMPP.

## Prerequisites

1. **XAMPP** - Download and install from https://www.apachefriends.org/
2. **Text Editor** - VS Code, Sublime Text, or any code editor
3. **Web Browser** - Chrome, Firefox, or any modern browser

## Step 1: Install XAMPP

1. Download XAMPP for your operating system
2. Run the installer with default settings
3. Start XAMPP Control Panel
4. Start **Apache** and **MySQL** services

## Step 2: Configure Database

1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Click on **New** in the left sidebar
3. Enter database name: `school_system`
4. Click **Create**
5. Select the `school_system` database
6. Click on **Import** tab
7. Choose the file: `database/schema.sql` from your project
8. Click **Go** to import the database schema

## Step 3: Deploy Project Files

1. Navigate to your XAMPP installation directory (usually `C:/xampp/`)
2. Go to `htdocs/` folder
3. Create a new folder: `school-management`
4. Copy all project files to `C:/xampp/htdocs/school-management/`

## Step 4: Configure Apache Virtual Host (Optional)

For a cleaner URL, you can set up a virtual host:

1. Open `C:/xampp/apache/conf/extra/httpd-vhosts.conf`
2. Add the following at the end:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/school-management/public"
    ServerName school-management.local
    ServerAlias www.school-management.local
    
    <Directory "C:/xampp/htdocs/school-management/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Open `C:/xampp/apache/conf/extra/httpd-vhosts.conf`
4. Uncomment the line: `NameVirtualHost *:80`
5. Restart Apache from XAMPP Control Panel
6. Add `127.0.0.1 school-management.local` to your hosts file (`C:/Windows/System32/drivers/etc/hosts`)

## Step 5: Access the Application

### Without Virtual Host
Open your browser and go to: `http://localhost/school-management/`

### With Virtual Host
Open your browser and go to: `http://school-management.local/`

## Step 6: Test the System

### Default Login Credentials
- **Instructor**: Username: `admin`, Password: `password123`
- **Student**: Username: `student`, Password: `password123`

### Test Features
1. **Registration**: Create new student/instructor accounts
2. **Login**: Test authentication with both roles
3. **Room Management** (Instructor only):
   - Add new rooms
   - Edit existing rooms
   - Change room status
   - Delete rooms
4. **Room Viewing** (Both roles):
   - View room list
   - Filter by status
   - Search rooms

## Step 7: Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Problem**: "Database connection failed"
**Solution**:
- Ensure MySQL service is running in XAMPP
- Check if database `school_system` exists in phpMyAdmin
- Verify database credentials in `config/database.php`

#### 2. 404 Not Found Error
**Problem**: Page not found
**Solution**:
- Check if files are in correct directory (`htdocs/school-management/`)
- Ensure Apache service is running
- Check URL spelling

#### 3. Permission Denied
**Problem**: Access denied
**Solution**:
- Check folder permissions
- Ensure Apache has read access to project files

#### 4. Session Issues
**Problem**: Login not working
**Solution**:
- Check if PHP session support is enabled
- Clear browser cookies and cache
- Restart Apache service

#### 5. CORS Issues
**Problem**: API calls not working
**Solution**:
- Ensure API endpoints are accessible
- Check browser console for errors
- Verify API URLs are correct

## Step 8: Security Considerations

### For Development
- Use default XAMPP settings
- Keep error reporting enabled
- Use test credentials

### For Production
- Change default passwords
- Disable error reporting
- Use HTTPS
- Set proper file permissions
- Regularly update XAMPP components

## Step 9: Customization

### Change Database Credentials
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'school_system';
private $username = 'your_username';
private $password = 'your_password';
```

### Change Session Timeout
Edit `includes/session.php`:
```php
$timeout = 30 * 60; // Change 30 to desired minutes
```

### Customize Styling
Edit `styles.css` or create new CSS files in the `public/` directory.

## Step 10: Backup and Maintenance

### Database Backup
1. Open phpMyAdmin
2. Select `school_system` database
3. Click **Export**
4. Choose format and options
5. Click **Go**

### File Backup
Regularly backup the entire project folder:
- `C:/xampp/htdocs/school-management/`

## Additional Resources

- **XAMPP Documentation**: https://www.apachefriends.org/docs/
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Apache Documentation**: https://httpd.apache.org/docs/

## Support

If you encounter issues:
1. Check XAMPP error logs
2. Check Apache error logs
3. Check PHP error logs
4. Review browser console for JavaScript errors
5. Verify all file permissions

---

**Setup Complete!** Your School Management System should now be running on XAMPP with PHP and MySQL.
