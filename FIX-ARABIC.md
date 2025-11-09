# 🔧 إصلاح مشكلة النص العربي (علامات الاستفهام)

## المشكلة
أسماء التصنيفات العربية تظهر كعلامات استفهام `???` بدلاً من النص العربي.

## السبب
مشكلة في encoding قاعدة البيانات - لم يتم ضبط UTF-8 بشكل صحيح.

## ✅ الحل السريع (خطوة واحدة فقط!)

### افتح هذا الرابط في المتصفح:
```
https://events.pyramedia.info/fix-encoding.php
```

**هذا السكريبت سيقوم بـ:**
1. ✓ تحويل قاعدة البيانات إلى UTF-8
2. ✓ تحويل جميع الجداول إلى UTF-8
3. ✓ إعادة إدخال أسماء التصنيفات الصحيحة
4. ✓ التحقق من النتيجة

### بعد تشغيل السكريبت:
1. انتظر حتى ترى رسالة "✅ ALL DONE!"
2. افتح الموقع وتحقق من التصنيفات
3. **احذف ملف fix-encoding.php** من السيرفر (للأمان)

---

## 🔍 إذا لم يعمل الحل التلقائي

### الحل اليدوي - عبر phpMyAdmin:

#### Step 1: تحويل قاعدة البيانات
```sql
ALTER DATABASE pyrastore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 2: تحويل الجداول
```sql
ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE products CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE settings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 3: إعادة إدخال البيانات الصحيحة
```sql
UPDATE categories SET name_ar = 'إلكترونيات', icon = '📱' WHERE slug = 'electronics';
UPDATE categories SET name_ar = 'أزياء', icon = '👕' WHERE slug = 'fashion';
UPDATE categories SET name_ar = 'المنزل والمطبخ', icon = '🏠' WHERE slug = 'home-kitchen';
UPDATE categories SET name_ar = 'الجمال والعناية', icon = '💄' WHERE slug = 'beauty-care';
UPDATE categories SET name_ar = 'رياضة ولياقة', icon = '⚽' WHERE slug = 'sports-fitness';
UPDATE categories SET name_ar = 'ألعاب وهدايا', icon = '🎁' WHERE slug = 'toys-gifts';
UPDATE categories SET name_ar = 'كتب وقرطاسية', icon = '📚' WHERE slug = 'books-stationery';
UPDATE categories SET name_ar = 'سيارات وإكسسوارات', icon = '🚗' WHERE slug = 'automotive';
```

---

## 🔒 للتأكد من عدم تكرار المشكلة

### في ملف config/database.php
تأكد من وجود هذا السطر في الـ PDO options:
```php
PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
```

### في جميع صفحات HTML/PHP
تأكد من وجود:
```html
<meta charset="UTF-8">
```

### في جميع ملفات API
تأكد من وجود:
```php
header('Content-Type: application/json; charset=utf-8');
```

---

## ✅ التحقق من النجاح

بعد تطبيق الإصلاح:

1. افتح الصفحة الرئيسية
2. يجب أن ترى التصنيفات بالعربي:
   - 📱 إلكترونيات
   - 👕 أزياء
   - 🏠 المنزل والمطبخ
   - 💄 الجمال والعناية
   - ⚽ رياضة ولياقة
   - 🎁 ألعاب وهدايا
   - 📚 كتب وقرطاسية
   - 🚗 سيارات وإكسسوارات

---

## 🆘 إذا استمرت المشكلة

جرب هذه الخطوات:

1. **Clear Browser Cache**: Ctrl+Shift+Delete
2. **تحقق من encoding الصفحة**: في المتصفح، انقر بزر الماوس الأيمن → View Page Source → تحقق من `<meta charset="UTF-8">`
3. **تحقق من phpMyAdmin**: افتح جدول categories، يجب أن ترى النص العربي صحيحاً
4. **تحقق من collation**: في phpMyAdmin → Structure → يجب أن يكون `utf8mb4_unicode_ci`

---

## 📞 Need More Help?

إذا لم تحل المشكلة:
1. أرسل screenshot من phpMyAdmin (جدول categories)
2. أرسل screenshot من الصفحة الرئيسية
3. جرب افتح الموقع في Incognito/Private mode

---

**Good Luck! 🚀**
