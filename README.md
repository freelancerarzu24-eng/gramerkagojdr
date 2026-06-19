# News Portal Installation Guide

This is a professional custom News Portal Website built with PHP 8.3+ and MySQL.

## Prerequisites
- PHP 8.3 or higher
- MySQL / MariaDB
- Web Server (Apache/Nginx)

## Installation Steps
1. Upload all files to your web server root.
2. Ensure the following directories are writable:
   - `assets/uploads/news/`
   - `assets/uploads/ads/`
   - `assets/uploads/gallery/`
   - `config/`
3. Navigate to `http://your-domain.com/install/` in your browser.
4. Follow the instructions to set up your database and Super Admin account.
5. Once installation is complete, the `install.lock` file will prevent further access to the installer.

## Security Features
- PDO Prepared Statements for SQL Injection protection.
- Strict File Upload validation (extension and MIME type) to prevent RCE.
- CSRF Protection on all admin forms.
- Password Hashing with `password_hash()`.
- XSS prevention via `htmlspecialchars()`.

## Admin Panel & Login Guide
Access the dashboard at `http://your-domain.com/admin/`.

### How to Login:
1. **Via Installation Wizard:**
   - During the installation process at `http://your-domain.com/install/`, you will be asked to create a Super Admin account.
   - Use those credentials to log in at the `/admin/` URL.

2. **Via Sample Data Seeder (Development only):**
   - If you ran `php seeder.php`, the default credentials are:
     - **Username:** `admin`
     - **Password:** `admin123`
   - *Note: Please change these credentials immediately after logging in for security.*

3. **Session Security:**
   - The admin panel uses secure sessions and CSRF protection. If your session expires, you will be redirected to the login page.

## Features
- Multi-language support (English/Bangla).
- AJAX "Load More" for news listings.
- JSON-LD Schema for SEO.
- Breaking News Ticker.
- Dynamic Advertisement system.
- Photo and Video galleries.
- Live TV embedding.
