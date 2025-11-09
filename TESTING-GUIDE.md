# 🧪 PyraStore - Testing & Verification Guide

Complete guide to test all features and verify installation.

## 🚀 Quick Test

Visit this URL to run automated tests:
```
https://events.pyramedia.info/test-site.php
```

This will check:
- ✅ All required files
- ✅ Database connection
- ✅ Tables and data
- ✅ UTF-8 encoding
- ✅ Permissions
- ✅ PHP extensions

---

## 📋 Manual Testing Checklist

### 1. Homepage Tests

**URL:** `https://events.pyramedia.info/`

- [ ] Page loads successfully
- [ ] Hero section displays correctly
- [ ] Categories show with Arabic names (not ???)
- [ ] Categories show emoji icons correctly
- [ ] Search bar works
- [ ] Language switcher (AR/EN) works
- [ ] Products grid displays
- [ ] Filter by category works
- [ ] Price filter works
- [ ] Discount filter works
- [ ] Sort options work
- [ ] Pagination works

### 2. Product Details Tests

**URL:** `https://events.pyramedia.info/product.php?id=1`

- [ ] Product page loads
- [ ] Product images display
- [ ] Image gallery works (click thumbnails)
- [ ] Product title shows in Arabic/English
- [ ] Price and discount display correctly
- [ ] Rating stars display
- [ ] Reviews section shows
- [ ] Similar products display
- [ ] "Buy Now" button works
- [ ] Social share buttons work

### 3. Admin Panel Tests

**URL:** `https://events.pyramedia.info/admin/login.php`

#### Login
- [ ] Login page loads
- [ ] Can login with credentials
- [ ] Wrong credentials show error
- [ ] Session timeout works

#### Dashboard
**URL:** `/admin/index.php`
- [ ] Dashboard displays
- [ ] Stats cards show correct numbers
- [ ] Top products table displays
- [ ] Quick actions work

#### Products Management
**URL:** `/admin/products.php`
- [ ] Products list displays
- [ ] Can add new product
- [ ] Can edit product
- [ ] Can delete product
- [ ] Image URLs work
- [ ] Bilingual fields work

#### Categories Management
**URL:** `/admin/categories.php`
- [ ] Categories list displays with icons
- [ ] Can add new category
- [ ] Can edit category
- [ ] Icon picker works
- [ ] Color picker works
- [ ] Cannot delete category with products

#### Analytics
**URL:** `/admin/analytics.php`
- [ ] Analytics page loads
- [ ] Overview stats display
- [ ] Top products table shows
- [ ] Traffic sources display
- [ ] Device stats show
- [ ] Date filter works

#### Reviews Management
**URL:** `/admin/reviews.php`
- [ ] Reviews list displays
- [ ] Filter tabs work (All/Pending/Approved)
- [ ] Can approve review
- [ ] Can unapprove review
- [ ] Can verify review
- [ ] Can delete review

#### Settings
**URL:** `/admin/settings.php`
- [ ] Settings page loads
- [ ] Can update site name
- [ ] Can update affiliate ID
- [ ] Can add tracking pixels
- [ ] Changes save successfully

### 4. API Tests

#### Categories API
**URL:** `/api/categories.php`
```bash
curl https://events.pyramedia.info/api/categories.php
```
- [ ] Returns JSON
- [ ] Arabic names show correctly
- [ ] Product counts are correct

#### Products API
**URL:** `/api/products.php`
```bash
curl https://events.pyramedia.info/api/products.php
```
- [ ] Returns JSON
- [ ] Pagination works
- [ ] Filters work (?category=electronics)
- [ ] Search works (?search=phone)

#### Tracking API
**URL:** `/api/tracking.php`
```bash
curl -X POST https://events.pyramedia.info/api/tracking.php \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"click_type":"product_view","session_id":"test123"}'
```
- [ ] Accepts POST requests
- [ ] Saves tracking data
- [ ] Returns success response

### 5. Tracking & Analytics

#### Client-Side Tracking
- [ ] Session ID generated in localStorage
- [ ] Product views tracked automatically
- [ ] Product clicks tracked
- [ ] Purchase clicks tracked
- [ ] UTM parameters captured

#### Pixels (if configured)
- [ ] Google Analytics loads
- [ ] Meta Pixel loads
- [ ] TikTok Pixel loads
- [ ] Events fire correctly

### 6. SEO & Performance

- [ ] robots.txt exists and is correct
- [ ] Meta tags are present
- [ ] Page titles are correct
- [ ] Arabic/English content separates properly
- [ ] Images have alt tags
- [ ] Site is mobile responsive

### 7. Security Tests

- [ ] install.php is deleted (or locked)
- [ ] Admin area requires login
- [ ] SQL injection protected (PDO)
- [ ] XSS protection works
- [ ] Passwords are hashed
- [ ] Session timeout works

---

## 🔍 Troubleshooting Common Issues

### Issue: Categories show ???

**Solution:**
```
Visit: https://events.pyramedia.info/fix-encoding.php
Wait for "ALL DONE!" message
Delete fix-encoding.php
```

### Issue: Database connection error

**Check:**
1. config/database.php has correct credentials
2. Database exists in cPanel
3. User has correct privileges

**Fix:**
```php
// In config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'pyramed1_db');
define('DB_PASS', 'Engmidoz@2020');
define('DB_NAME', 'pyramed1_db');
```

### Issue: Admin login doesn't work

**Reset password:**
```sql
-- Via phpMyAdmin
UPDATE admin_users
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
```
Password will be: `admin123`

### Issue: Products not displaying

**Check:**
1. Database has products
2. Products are marked as active
3. JavaScript console for errors
4. API returns data

**Add test product:**
```sql
INSERT INTO products (product_id, title_ar, title_en, category, price, amazon_url, affiliate_link, is_active)
VALUES ('TEST001', 'منتج تجريبي', 'Test Product', 'electronics', 99.99,
        'https://amazon.ae/dp/TEST001', 'https://amazon.ae/dp/TEST001?tag=pyrastore-21', 1);
```

### Issue: Tracking not working

**Verify:**
1. JavaScript loads without errors
2. Session ID exists in localStorage
3. API endpoint is accessible
4. Database table click_tracking exists

---

## 📊 Expected Test Results

### Database Tables

| Table | Expected Rows |
|-------|---------------|
| categories | 8 |
| settings | 15+ |
| admin_users | 1+ |
| products | 0+ |
| reviews | 0+ |
| click_tracking | 0+ |
| product_images | 0+ |

### File Checklist

```
✓ index.php
✓ product.php
✓ 404.php
✓ robots.txt
✓ install.php (should be deleted after install)
✓ test-site.php (for testing only)
✓ fix-encoding.php (run once, then delete)
✓ config/config.php
✓ config/database.php
✓ admin/index.php
✓ admin/login.php
✓ admin/products.php
✓ admin/categories.php
✓ admin/analytics.php
✓ admin/reviews.php
✓ admin/settings.php
✓ api/products.php
✓ api/categories.php
✓ api/tracking.php
✓ api/analytics.php
✓ assets/css/style.css
✓ assets/css/admin.css
✓ assets/css/product.css
✓ assets/js/main.js
✓ assets/js/tracking.js
```

---

## 🎯 Performance Benchmarks

### Page Load Times (Target)
- Homepage: < 2 seconds
- Product Page: < 1.5 seconds
- Admin Pages: < 1 second
- API Responses: < 500ms

### Browser Support
- ✅ Chrome/Edge (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ✅ Mobile Browsers

### Device Support
- ✅ Desktop (1920x1080+)
- ✅ Laptop (1366x768+)
- ✅ Tablet (768x1024)
- ✅ Mobile (375x667+)

---

## ✅ Final Verification

Before going live, verify:

1. **Security**
   - [ ] delete install.php
   - [ ] delete test-site.php
   - [ ] delete fix-encoding.php
   - [ ] Change default admin password
   - [ ] Enable SSL (HTTPS)

2. **Configuration**
   - [ ] Set correct SITE_URL
   - [ ] Add Amazon Affiliate ID
   - [ ] Add Google Analytics
   - [ ] Add Meta Pixel
   - [ ] Add TikTok Pixel

3. **Content**
   - [ ] Add real products
   - [ ] Add product images
   - [ ] Test affiliate links
   - [ ] Verify prices are correct

4. **Backup**
   - [ ] Backup database
   - [ ] Backup files
   - [ ] Document passwords

---

## 📞 Support

If issues persist:
1. Check error logs in cPanel
2. Review database in phpMyAdmin
3. Use browser developer tools (F12)
4. Check PHP version (7.4+ required)

---

**Happy Testing! 🚀**
