# 🚀 دليل النشر - PYRASTORE

دليل خطوة بخطوة لنشر الموقع على السيرفر.

## 📋 معلومات السيرفر

- **الموقع**: https://events.pyramedia.info/
- **المجلد**: `public_html/events`
- **قاعدة البيانات**: `pyramed1_db`

## 🔧 الخطوة 1: تجهيز السيرفر

### 1.1 الدخول إلى cPanel
افتح cPanel الخاص بك

### 1.2 إنشاء/التحقق من قاعدة البيانات
قاعدة البيانات موجودة بالفعل:
- اسم القاعدة: `pyramed1_db`
- المستخدم: `pyramed1_db`
- الكلمة: `Engmidoz@2020`

## 📦 الخطوة 2: سحب الملفات من GitHub عبر SSH

### 2.1 الاتصال بـ SSH

في cPanel، افتح **Terminal** أو استخدم SSH client:

```bash
ssh your-username@your-server.com
```

### 2.2 الانتقال للمجلد المطلوب

```bash
cd public_html
```

### 2.3 إنشاء مجلد events (إذا لم يكن موجوداً)

```bash
mkdir -p events
cd events
```

### 2.4 استنساخ المشروع من GitHub

```bash
# استنساخ المشروع
git clone -b claude/build-amazon-affiliate-site-011CUwcbGe29Tbe2dQCRwTdS https://github.com/Engmohammedabdo/amazon.git temp_clone

# نقل الملفات من المجلد المؤقت
mv temp_clone/* .
mv temp_clone/.* . 2>/dev/null || true

# حذف المجلد المؤقت
rm -rf temp_clone
```

أو بطريقة أبسط (إذا كان المجلد فارغاً):

```bash
# استنساخ مباشرة
git clone -b claude/build-amazon-affiliate-site-011CUwcbGe29Tbe2dQCRwTdS https://github.com/Engmohammedabdo/amazon.git .
```

### 2.5 إنشاء ملف config.php

```bash
# إنشاء ملف الإعدادات
cat > includes/config.php << 'EOF'
<?php
/**
 * ملف الإعدادات - PYRASTORE
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'pyramed1_db');
define('DB_USER', 'pyramed1_db');
define('DB_PASS', 'Engmidoz@2020');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الموقع
define('SITE_URL', 'https://events.pyramedia.info');
define('SITE_NAME', 'PYRASTORE');
define('SITE_TAGLINE', 'UAE PICKS');

// إعدادات الأمان
define('SESSION_LIFETIME', 7200);

// المنطقة الزمنية
date_default_timezone_set('Asia/Dubai');

// عرض الأخطاء
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
?>
EOF
```

### 2.6 ضبط الصلاحيات

```bash
# صلاحيات الملفات
find . -type f -exec chmod 644 {} \;

# صلاحيات المجلدات
find . -type d -exec chmod 755 {} \;

# حماية config.php
chmod 600 includes/config.php
```

## 🗄️ الخطوة 3: تثبيت قاعدة البيانات

### الطريقة 1: عبر المتصفح (الأسهل)

1. افتح المتصفح واذهب إلى:
   ```
   https://events.pyramedia.info/install.php
   ```

2. أدخل البيانات التالية:
   - **خادم قاعدة البيانات**: localhost
   - **اسم قاعدة البيانات**: pyramed1_db
   - **اسم المستخدم**: pyramed1_db
   - **كلمة المرور**: Engmidoz@2020
   - **رابط الموقع**: https://events.pyramedia.info

3. اضغط "تثبيت الموقع"

4. بعد التثبيت الناجح، **احذف ملف install.php فوراً**:
   ```bash
   rm install.php
   ```

### الطريقة 2: عبر phpMyAdmin

1. افتح phpMyAdmin من cPanel
2. اختر قاعدة البيانات `pyramed1_db`
3. اذهب إلى تبويب "Import"
4. ارفع ملف `DATABASE_SCHEMA.sql`
5. اضغط "Go"

## ✅ الخطوة 4: التحقق من التثبيت

### 4.1 اختبار الموقع

افتح المتصفح واذهب إلى:
```
https://events.pyramedia.info/
```

يجب أن ترى الصفحة الرئيسية مع 5 منتجات تجريبية.

### 4.2 اختبار لوحة التحكم

```
https://events.pyramedia.info/admin/login.php
```

بيانات الدخول الافتراضية:
- **المستخدم**: admin
- **الكلمة**: admin123

⚠️ **مهم جداً**: غيّر كلمة المرور فوراً!

### 4.3 اختبار Webhook API

```bash
# Health Check
curl https://events.pyramedia.info/api/webhook.php?action=health
```

يجب أن ترى:
```json
{
  "success": true,
  "message": "Webhook API يعمل بنجاح",
  "timestamp": "..."
}
```

## 🔒 الخطوة 5: الأمان

### 5.1 حذف ملف التثبيت

```bash
rm install.php
```

### 5.2 تغيير كلمة المرور

1. سجل الدخول للوحة التحكم
2. اذهب إلى الإعدادات
3. غيّر كلمة المرور

### 5.3 تغيير API Key

1. اذهب إلى: الإعدادات → Webhook API
2. ولّد مفتاح جديد
3. احفظ المفتاح في مكان آمن

### 5.4 إنشاء ملف .htaccess للحماية

```bash
cat > .htaccess << 'EOF'
# PYRASTORE - Security Configuration

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(sql|log|env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect includes folder
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/events/includes/.*
    RewriteRule ^(.*)$ - [F,L]
</IfModule>

# Enable Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
EOF
```

## 🔄 تحديث الموقع لاحقاً

إذا تم تحديث الكود في GitHub:

```bash
# الانتقال لمجلد الموقع
cd ~/public_html/events

# سحب آخر التحديثات
git pull origin claude/build-amazon-affiliate-site-011CUwcbGe29Tbe2dQCRwTdS

# ملاحظة: ملف config.php محمي ولن يتأثر
```

## 🐛 استكشاف الأخطاء

### المشكلة: "خطأ في الاتصال بقاعدة البيانات"

**الحل**:
```bash
# تحقق من ملف config.php
cat includes/config.php

# تأكد من البيانات صحيحة
```

### المشكلة: "500 Internal Server Error"

**الحل**:
```bash
# تحقق من error log
tail -f ~/public_html/error_log

# أو من cPanel → Error Log
```

### المشكلة: الموقع يعرض كود PHP

**الحل**:
- تأكد من أن PHP مفعّل على السيرفر
- تحقق من إصدار PHP (يجب أن يكون 8.0+)
- في cPanel → Select PHP Version

### المشكلة: CSS/JS لا يعمل

**الحل**:
```bash
# تحقق من الصلاحيات
ls -la assets/css/
ls -la assets/js/

# إصلاح الصلاحيات
chmod 644 assets/css/*
chmod 644 assets/js/*
```

## 📞 الدعم

إذا واجهت أي مشكلة:

1. تحقق من `error_log` في cPanel
2. راجع ملف `README.md`
3. راجع `WEBHOOK_GUIDE.md` لمشاكل الـ API

## ✨ الخطوات التالية

بعد التثبيت الناجح:

1. ✅ حذف install.php
2. ✅ تغيير كلمة مرور الأدمن
3. ✅ تغيير API Key
4. ✅ إضافة منتجاتك من لوحة التحكم
5. ✅ تفعيل أدوات التتبع (GA, Meta, TikTok)
6. ✅ ربط n8n مع Webhook API
7. ✅ البدء في التسويق!

---

**🎉 مبروك! موقعك الآن جاهز للعمل!**
