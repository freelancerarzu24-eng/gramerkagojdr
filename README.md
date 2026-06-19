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

## Admin Panel
Access the dashboard at `http://your-domain.com/admin/`.

## Features
- Multi-language support (English/Bangla).
- AJAX "Load More" for news listings.
- JSON-LD Schema for SEO.
- Breaking News Ticker.
- Dynamic Advertisement system.
- Photo and Video galleries.
- Live TV embedding.
