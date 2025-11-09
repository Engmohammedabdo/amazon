# 🛍️ PyraStore - Amazon Affiliate UAE

A modern, feature-rich Amazon Affiliate store built specifically for the UAE market with advanced tracking, analytics, and bilingual support (Arabic/English).

## ✨ Features

### 🎯 Core Features
- **Bilingual Support**: Full Arabic and English support with RTL layout
- **Advanced Product Management**: Complete CRUD operations for products
- **Smart Filtering**: Filter by category, price, discount, and search
- **Product Details**: Rich product pages with image galleries and reviews
- **Click Tracking**: Comprehensive tracking of user interactions
- **Analytics Dashboard**: Detailed insights into product performance and conversions

### 📊 Tracking & Analytics
- **Session Tracking**: Unique visitor identification
- **Click Events**: Track product views, clicks, and purchase clicks
- **Conversion Tracking**: Monitor conversion rates by product
- **UTM Support**: Track campaign performance (source, medium, campaign)
- **Device Analytics**: Desktop, mobile, and tablet breakdown
- **Traffic Sources**: Understand where your visitors come from

### 🔌 Integrations
- **Google Analytics 4**: Full GA4 integration
- **Meta Pixel**: Facebook/Instagram ad tracking
- **TikTok Pixel**: TikTok ad performance tracking
- **Amazon Associates**: Automated affiliate link generation

### 🎨 Modern UI/UX
- **Responsive Design**: Perfect on all devices
- **Modern Gradient Design**: Eye-catching colors and animations
- **Category Icons**: Visual category representation
- **Hero Section**: Engaging landing page
- **Product Cards**: Beautiful product displays with hover effects
- **Social Sharing**: Share products on Facebook, Twitter, WhatsApp

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx with mod_rewrite enabled
- Bluehost or any shared hosting with PHP/MySQL support

### Installation

#### ⚡ Method 1: Automatic Installation (RECOMMENDED)

**Super Easy - Just 3 Steps!**

1. **Upload all files** to your hosting directory
   ```bash
   Upload to: public_html/
   ```

2. **Open the installer** in your browser:
   ```
   https://yourdomain.com/install.php
   ```

3. **Follow the wizard**:
   - Step 1: System check ✓ (automatic)
   - Step 2: Enter database details
   - Step 3: Configure site & admin
   - Step 4: Done! 🎉

4. **Delete install.php** after installation for security

**That's it!** The installer will automatically:
- Check system requirements
- Create database tables
- Import sample data
- Configure everything
- Set up admin account

For detailed instructions, see [INSTALL.md](INSTALL.md)

---

#### 🛠️ Method 2: Manual Installation

If you prefer manual installation:

#### 1. Upload Files
Upload all files to your Bluehost public_html directory (or subdomain folder).

```bash
# If using FTP, upload the entire directory structure
# Maintain the folder structure as is
```

#### 2. Create Database
1. Log in to Bluehost cPanel
2. Go to MySQL Databases
3. Create a new database: `pyrastore_db`
4. Create a MySQL user with a strong password
5. Add the user to the database with ALL PRIVILEGES

#### 3. Import Database
1. Go to phpMyAdmin in cPanel
2. Select your database
3. Click "Import"
4. Choose `database.sql` file
5. Click "Go"

#### 4. Configure Database Connection
Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');    // Your MySQL username
define('DB_PASS', 'your_password');    // Your MySQL password
define('DB_NAME', 'pyrastore_db');     // Your database name
```

#### 5. Update Site URL
Edit `config/config.php`:

```php
define('SITE_URL', 'https://yourdomain.com'); // Your actual domain
```

#### 6. Set File Permissions
```bash
chmod 755 -R /path/to/your/site
chmod 777 -R assets/uploads
```

## 🎛️ Admin Panel

### Access
- URL: `https://yourdomain.com/admin`
- Default Username: `admin`
- Default Password: `admin123`

**⚠️ IMPORTANT**: Change the default password immediately after first login!

### Change Admin Password

1. Log in to phpMyAdmin
2. Find the `admin_users` table
3. Run this SQL to create a new password:

```sql
-- Replace 'your_new_password' with your desired password
UPDATE admin_users
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
```

Or use this PHP script to generate a new password hash:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

### Admin Features

#### 📦 Products Management
- Add/Edit/Delete products
- Upload multiple product images
- Set pricing and discounts
- Mark products as featured
- Manage product categories
- Bilingual product information

#### 📊 Analytics
- View traffic statistics
- Monitor conversion rates
- Track top-performing products
- Analyze traffic sources
- Device and browser breakdown
- Custom date ranges (7, 30, 90 days)

#### ⚙️ Settings
- Configure site information
- Set Amazon Affiliate ID
- Add tracking pixels (GA4, Meta, TikTok)
- Customize display options
- Manage reviews settings

## 🔧 Configuration

### Amazon Affiliate Setup

1. Sign up for [Amazon Associates Program UAE](https://affiliate.amazon.ae/)
2. Get your Associate ID (e.g., `pyrastore-21`)
3. Add it in Admin → Settings → Amazon Affiliate ID

### Google Analytics 4

1. Create a GA4 property at [Google Analytics](https://analytics.google.com/)
2. Get your Measurement ID (format: `G-XXXXXXXXXX`)
3. Add it in Admin → Settings → Google Analytics ID

### Meta Pixel

1. Create a pixel at [Facebook Business](https://business.facebook.com/)
2. Get your Pixel ID (15-16 digits)
3. Add it in Admin → Settings → Meta Pixel ID

### TikTok Pixel

1. Create a pixel in [TikTok Ads Manager](https://ads.tiktok.com/)
2. Get your Pixel ID
3. Add it in Admin → Settings → TikTok Pixel ID

## 📁 File Structure

```
pyrastore/
├── admin/                  # Admin panel
│   ├── analytics.php      # Analytics dashboard
│   ├── footer.php         # Admin footer
│   ├── header.php         # Admin header
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Logout handler
│   ├── products.php       # Products management
│   └── settings.php       # Settings page
├── api/                    # REST API endpoints
│   ├── analytics.php      # Analytics API
│   ├── categories.php     # Categories API
│   ├── products.php       # Products API
│   └── tracking.php       # Tracking API
├── assets/                 # Static assets
│   ├── css/
│   │   ├── admin.css      # Admin panel styles
│   │   ├── product.css    # Product page styles
│   │   └── style.css      # Main site styles
│   ├── js/
│   │   ├── main.js        # Main JavaScript
│   │   └── tracking.js    # Tracking system
│   ├── images/            # Site images
│   └── uploads/           # User uploads
├── config/                 # Configuration
│   ├── config.php         # Main config
│   └── database.php       # Database config
├── database.sql           # Database schema
├── index.php              # Homepage
├── product.php            # Product details page
└── README.md              # This file
```

## 🎨 Customization

### Adding Categories

Categories are pre-loaded in the database. To add more:

```sql
INSERT INTO categories (name_ar, name_en, slug, icon, color, display_order)
VALUES ('اسم الفئة', 'Category Name', 'category-slug', '🎯', '#FF5722', 9);
```

### Changing Colors

Edit `assets/css/style.css`:

```css
:root {
    --primary: #FF9900;        /* Amazon orange */
    --secondary: #232F3E;      /* Amazon dark blue */
    --success: #10b981;
    --danger: #ef4444;
}
```

### Adding Products

Via Admin Panel:
1. Go to Admin → Products
2. Click "Add New Product"
3. Fill in all details:
   - Title (Arabic & English)
   - Description (Arabic & English)
   - Category
   - Price & Original Price
   - Amazon Product URL
   - Image URLs (one per line)
4. Click "Save Product"

### Product Image URLs

You can use images from:
- Amazon CDN (recommended)
- Your own hosting
- Free image hosts (Imgur, etc.)

Format for multiple images:
```
https://example.com/image1.jpg
https://example.com/image2.jpg
https://example.com/image3.jpg
```

## 📈 Marketing Tips

### 1. SEO Optimization
- Use descriptive product titles
- Fill in product descriptions
- Use proper categories
- Add alt text to images

### 2. Social Media
- Share products on Facebook, Instagram
- Use TikTok for viral marketing
- Create engaging content around products

### 3. Paid Advertising
- Use UTM parameters in ad links:
  - `?utm_source=facebook&utm_medium=cpc&utm_campaign=summer_sale`
- Monitor performance in Analytics
- Track ROI by campaign

### 4. Content Marketing
- Write blog posts about products
- Create comparison guides
- Make video reviews

## 🔒 Security

### Important Security Steps

1. **Change Admin Password** immediately
2. **Restrict admin directory**:
   ```apache
   # Add to .htaccess in /admin
   <Limit GET POST>
       order deny,allow
       deny from all
       allow from YOUR_IP_ADDRESS
   </Limit>
   ```

3. **Update PHP**:
   - Set `display_errors = 0` in production
   - Enable `error_log`

4. **Database Security**:
   - Use strong MySQL password
   - Limit MySQL user privileges
   - Regular backups

5. **File Permissions**:
   ```bash
   # Recommended permissions
   Directories: 755
   PHP files: 644
   Uploads: 777 (but restricted by .htaccess)
   ```

## 🐛 Troubleshooting

### Database Connection Error
**Error**: "Connection failed: Access denied"
**Solution**:
- Check database credentials in `config/database.php`
- Verify database exists
- Ensure user has privileges

### Images Not Displaying
**Error**: Broken image icons
**Solution**:
- Check image URLs are valid
- Ensure `uploads` folder has write permissions (777)
- Verify image paths are absolute URLs

### Products Not Loading
**Error**: Empty products list
**Solution**:
- Check if database has products
- Verify API endpoints are accessible
- Check JavaScript console for errors

### Tracking Not Working
**Error**: No analytics data
**Solution**:
- Verify tracking pixels are configured
- Check JavaScript console for errors
- Test with browser developer tools

### Admin Login Issues
**Error**: Invalid credentials
**Solution**:
- Use default credentials: `admin` / `admin123`
- Reset password via phpMyAdmin
- Clear browser cache and cookies

## 🆘 Support

### Common Issues

**Q: How do I get Amazon Affiliate links?**
A: Join Amazon Associates UAE, get your tracking ID, add it in Settings.

**Q: Can I change the language?**
A: Yes, the site supports Arabic and English. Users can switch via the language toggle.

**Q: How do I add more products?**
A: Use the Admin panel → Products → Add New Product

**Q: Where can I see my earnings?**
A: Earnings are tracked in your Amazon Associates dashboard, not in this app.

**Q: Can I customize the design?**
A: Yes, edit the CSS files in `assets/css/`

## 📝 Database Schema

### Tables

1. **products**: Main product information
2. **product_images**: Product image URLs
3. **categories**: Product categories
4. **click_tracking**: User interaction tracking
5. **reviews**: Customer reviews
6. **settings**: Site configuration
7. **admin_users**: Admin accounts

## 🔄 Updates

### Keeping Your Site Updated

1. Backup database regularly
2. Keep PHP updated
3. Monitor for security patches
4. Update tracking pixels as needed

## 📜 License

This project is provided as-is for educational and commercial use.

## 🙏 Credits

- Built with PHP, MySQL, JavaScript
- Icons: Emoji (native)
- Fonts: Google Fonts (Cairo, Poppins)
- Tracking: Google Analytics, Meta Pixel, TikTok Pixel

## 📞 Contact

For setup help or customization:
- Email: info@pyrastore.com

---

**Made with ❤️ for Amazon Affiliates in the UAE**

**Happy Selling! 🚀💰**
