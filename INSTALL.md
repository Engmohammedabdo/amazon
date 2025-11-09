# 🚀 PyraStore - Quick Installation Guide

## Method 1: Automatic Installation (Recommended) ⚡

### Super Easy - 3 Steps Only!

1. **Upload all files** to your hosting (via FTP or File Manager)
   ```
   Upload everything to: public_html/
   ```

2. **Open the installer** in your browser:
   ```
   https://yourdomain.com/install.php
   ```

3. **Follow the wizard** - Just fill in the forms:
   - Step 1: System check (automatic)
   - Step 2: Database details (from cPanel)
   - Step 3: Site & admin settings
   - Step 4: Done! 🎉

4. **Delete install.php** after successful installation for security

### What You Need:
- MySQL database name
- MySQL username
- MySQL password
- Your Amazon Affiliate ID

That's it! The installer will:
- ✅ Check system requirements
- ✅ Create database tables
- ✅ Import all data
- ✅ Configure everything
- ✅ Create admin account
- ✅ Set up affiliate links

---

## Method 2: Manual Installation 🛠️

If you prefer to install manually, follow these steps:

### Step 1: Create Database
1. Login to cPanel
2. Go to MySQL Databases
3. Create database: `pyrastore_db`
4. Create user with strong password
5. Add user to database with ALL PRIVILEGES

### Step 2: Import Database
1. Open phpMyAdmin
2. Select your database
3. Click Import
4. Choose `database.sql`
5. Click Go

### Step 3: Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'pyrastore_db');
```

### Step 4: Configure Site
Edit `config/config.php`:
```php
define('SITE_URL', 'https://yourdomain.com');
```

### Step 5: Set Permissions
```bash
chmod 755 -R .
chmod 777 -R assets/uploads
```

### Step 6: Change Admin Password
1. Go to phpMyAdmin
2. Find `admin_users` table
3. Update password:
```sql
UPDATE admin_users
SET password = 'your_bcrypt_hash_here'
WHERE username = 'admin';
```

Generate hash in PHP:
```php
<?php echo password_hash('your_new_password', PASSWORD_DEFAULT); ?>
```

---

## 🔐 Default Login Credentials

**URL:** `https://yourdomain.com/admin`
- **Username:** admin
- **Password:** admin123

⚠️ **CHANGE IMMEDIATELY AFTER FIRST LOGIN!**

---

## ⚙️ Post-Installation Setup

### 1. Admin Panel Configuration
Login to admin panel and go to Settings:

- **Site Information**
  - Site name (Arabic & English)
  - Contact email
  - WhatsApp number

- **Amazon Affiliate**
  - Your Affiliate ID (e.g., pyrastore-21)
  - Amazon domain (amazon.ae)

- **Tracking Pixels**
  - Google Analytics ID
  - Meta Pixel ID
  - TikTok Pixel ID

### 2. Add Your First Product
1. Go to Admin → Products
2. Click "Add New Product"
3. Fill in all details (Arabic & English)
4. Add product images (URLs)
5. Set price and discount
6. Click Save

### 3. Test Everything
- Visit your homepage
- Click on a product
- Check if affiliate links work
- Test the tracking in Analytics

---

## 🐛 Common Issues

### "Database Connection Error"
- Check credentials in `config/database.php`
- Verify database exists in cPanel
- Ensure user has correct privileges

### "Cannot write to directory"
- Set correct permissions: `chmod 777 assets/uploads`
- Check file ownership

### "Install.php not found"
- Make sure you uploaded ALL files
- Check file paths

### "Page not found"
- Check .htaccess file exists
- Verify mod_rewrite is enabled

---

## 📞 Need Help?

1. Check README.md for detailed documentation
2. Review troubleshooting section
3. Verify all files were uploaded correctly
4. Check PHP version (7.4+ required)
5. Ensure all required PHP extensions are installed

---

## ✅ Installation Checklist

Before you start:
- [ ] PHP 7.4 or higher
- [ ] MySQL 5.7 or higher
- [ ] PDO extension enabled
- [ ] Writable uploads directory
- [ ] .htaccess file uploaded
- [ ] Database created in cPanel
- [ ] Amazon Affiliate ID ready

After installation:
- [ ] Delete install.php
- [ ] Change admin password
- [ ] Configure affiliate ID
- [ ] Add tracking pixels
- [ ] Test affiliate links
- [ ] Add first products
- [ ] Enable SSL certificate

---

## 🎉 You're Ready!

Once installed, you can:
- Add unlimited products
- Track conversions
- View detailed analytics
- Customize everything
- Start earning commissions!

**Happy Selling! 🚀💰**

For full documentation, see README.md
