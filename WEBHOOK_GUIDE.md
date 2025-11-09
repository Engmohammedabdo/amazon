# 🔗 دليل Webhook API - PYRASTORE

دليل شامل لاستخدام Webhook API لإضافة المنتجات تلقائياً من n8n أو أي أداة automation أخرى.

## 📡 نظرة عامة

Webhook API يسمح لك بإضافة منتجات جديدة إلى الموقع تلقائياً عبر HTTP POST requests. مثالي للتكامل مع:

- ✅ n8n (أداة automation)
- ✅ Zapier
- ✅ Make (Integromat)
- ✅ أي سكريبت Python/Node.js
- ✅ Postman للاختبار

## 🔑 الحصول على API Key

1. سجل الدخول للوحة التحكم: `/admin/login.php`
2. اذهب إلى: **الإعدادات → Webhook API**
3. انسخ المفتاح الحالي أو ولّد مفتاح جديد
4. احفظ المفتاح في مكان آمن

⚠️ **تحذير**: لا تشارك API Key مع أحد!

## 📍 Endpoints

### 1. إضافة منتج جديد

```http
POST /api/webhook.php
```

**Headers المطلوبة:**

```http
X-API-Key: your-api-key-here
Content-Type: application/json
```

**Body (JSON):**

```json
{
  "title": "عنوان المنتج (مطلوب)",
  "description": "وصف تفصيلي للمنتج",
  "imageUrl": "https://example.com/image.jpg",
  "price": 149.99,
  "originalPrice": 299.99,
  "category": "electronics",
  "affiliateLink": "https://www.amazon.ae/dp/B08XYZ (مطلوب)",
  "videoUrl": "https://drive.google.com/file/d/XXX",
  "videoOrientation": "landscape",
  "additionalImages": [
    "https://example.com/image2.jpg",
    "https://example.com/image3.jpg"
  ]
}
```

**الحقول المطلوبة:**

| الحقل | النوع | الوصف |
|------|------|-------|
| `title` | string | عنوان المنتج (مطلوب) |
| `affiliateLink` | string | رابط الأفلييت من أمازون (مطلوب) |

**الحقول الاختيارية:**

| الحقل | النوع | القيمة الافتراضية | الوصف |
|------|------|------------------|-------|
| `description` | string | '' | وصف المنتج |
| `imageUrl` | string | placeholder | رابط الصورة الرئيسية |
| `price` | float | 0 | السعر الحالي |
| `originalPrice` | float | null | السعر قبل الخصم |
| `category` | string | 'other' | الفئة (انظر الجدول أدناه) |
| `videoUrl` | string | null | رابط فيديو YouTube أو Google Drive |
| `videoOrientation` | string | 'landscape' | `landscape` أو `portrait` |
| `additionalImages` | array | [] | روابط صور إضافية |

**الفئات المتاحة:**

| القيمة | الاسم بالعربية |
|-------|----------------|
| `electronics` | إلكترونيات |
| `fashion` | أزياء |
| `home` | منزل ومطبخ |
| `sports` | رياضة |
| `beauty` | جمال وعناية |
| `books` | كتب |
| `toys` | ألعاب |
| `other` | منتجات أخرى |

**استجابة ناجحة (201):**

```json
{
  "success": true,
  "message": "تم إضافة المنتج بنجاح",
  "product_id": 123
}
```

**استجابة خطأ (400):**

```json
{
  "success": false,
  "message": "الحقول المطلوبة: title, affiliateLink"
}
```

**استجابة خطأ (401):**

```json
{
  "success": false,
  "message": "API Key غير صحيح"
}
```

### 2. فحص حالة الـ API

```http
GET /api/webhook.php?action=health
```

**لا يحتاج API Key**

**استجابة:**

```json
{
  "success": true,
  "message": "Webhook API يعمل بنجاح",
  "timestamp": "2025-11-09 15:30:45",
  "version": "1.0.0"
}
```

### 3. عرض التوثيق

```http
GET /api/webhook.php?action=docs
```

يعرض صفحة HTML بالتوثيق الكامل.

## 💡 أمثلة عملية

### مثال 1: cURL

```bash
curl -X POST https://yoursite.com/api/webhook.php \
  -H "X-API-Key: pyrastore-webhook-2025" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "سماعات لاسلكية بلوتوث 5.0",
    "description": "سماعات عالية الجودة مع خاصية إلغاء الضوضاء، بطارية تدوم 30 ساعة",
    "imageUrl": "https://m.media-amazon.com/images/I/61vFO3XcneL._AC_SL1500_.jpg",
    "price": 149.99,
    "originalPrice": 299.99,
    "category": "electronics",
    "affiliateLink": "https://www.amazon.ae/dp/B08XYZ123"
  }'
```

### مثال 2: Python

```python
import requests
import json

url = "https://yoursite.com/api/webhook.php"
headers = {
    "X-API-Key": "pyrastore-webhook-2025",
    "Content-Type": "application/json"
}

data = {
    "title": "ساعة ذكية رياضية",
    "description": "ساعة ذكية مع مراقبة معدل ضربات القلب",
    "imageUrl": "https://example.com/watch.jpg",
    "price": 199.99,
    "originalPrice": 399.99,
    "category": "electronics",
    "affiliateLink": "https://www.amazon.ae/dp/B08ABC456",
    "additionalImages": [
        "https://example.com/watch2.jpg",
        "https://example.com/watch3.jpg"
    ]
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

### مثال 3: JavaScript (Node.js)

```javascript
const axios = require('axios');

const url = 'https://yoursite.com/api/webhook.php';
const headers = {
    'X-API-Key': 'pyrastore-webhook-2025',
    'Content-Type': 'application/json'
};

const data = {
    title: 'حقيبة ظهر عصرية',
    description: 'حقيبة ظهر مقاومة للماء مع منفذ USB',
    imageUrl: 'https://example.com/backpack.jpg',
    price: 89.99,
    originalPrice: 179.99,
    category: 'fashion',
    affiliateLink: 'https://www.amazon.ae/dp/B08DEF789'
};

axios.post(url, data, { headers })
    .then(response => console.log(response.data))
    .catch(error => console.error(error.response.data));
```

### مثال 4: n8n Workflow

```json
{
  "nodes": [
    {
      "name": "HTTP Request",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "method": "POST",
        "url": "https://yoursite.com/api/webhook.php",
        "authentication": "headerAuth",
        "headerAuth": "pyrastoreApi",
        "options": {},
        "bodyParametersJson": "={\"title\": \"{{$json[\"product_title\"]}}\", \"affiliateLink\": \"{{$json[\"amazon_url\"]}}\", \"price\": {{$json[\"price\"]}}, \"category\": \"electronics\"}"
      }
    }
  ]
}
```

في n8n:
1. أضف **HTTP Request** node
2. **Method**: POST
3. **URL**: `https://yoursite.com/api/webhook.php`
4. **Headers**: أضف `X-API-Key` مع قيمة API Key
5. **Body**: اختر JSON
6. املأ البيانات من الـ nodes السابقة

## 🎥 دعم الفيديوهات

### Google Drive

```json
{
  "videoUrl": "https://drive.google.com/file/d/1abc123xyz/view",
  "videoOrientation": "landscape"
}
```

سيتم تحويل الرابط تلقائياً إلى:
```
https://drive.google.com/file/d/1abc123xyz/preview
```

### YouTube

```json
{
  "videoUrl": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "videoOrientation": "landscape"
}
```

سيتم تحويل الرابط تلقائياً إلى embed format.

## 🖼️ الصور الإضافية

```json
{
  "imageUrl": "https://example.com/main-image.jpg",
  "additionalImages": [
    "https://example.com/image-2.jpg",
    "https://example.com/image-3.jpg",
    "https://example.com/image-4.jpg"
  ]
}
```

- الصورة الرئيسية: `imageUrl`
- الصور الإضافية: `additionalImages` (array)
- يتم عرض جميع الصور في معرض الصور بصفحة المنتج

## 💰 حساب الخصم التلقائي

إذا قدمت `originalPrice` و `price`:

```json
{
  "price": 149.99,
  "originalPrice": 299.99
}
```

سيتم حساب:
- **نسبة الخصم**: 50%
- **قيمة التوفير**: 150 درهم

تلقائياً وعرضها على الموقع!

## ⚠️ رموز الأخطاء

| الكود | المعنى | الحل |
|------|--------|-----|
| 200 | نجاح | تمت العملية بنجاح |
| 201 | تم الإنشاء | تم إضافة المنتج بنجاح |
| 400 | بيانات خاطئة | تحقق من البيانات المرسلة |
| 401 | غير مصرح | API Key خاطئ أو مفقود |
| 405 | طريقة غير مسموحة | استخدم POST فقط |
| 500 | خطأ في السيرفر | تحقق من logs |

## 🔍 استكشاف الأخطاء

### خطأ 401: API Key غير صحيح

```bash
# تحقق من Header
curl -v https://yoursite.com/api/webhook.php \
  -H "X-API-Key: your-key"
```

تأكد من:
- ✅ كتابة `X-API-Key` بنفس الحروف الكبيرة/الصغيرة
- ✅ نسخ المفتاح كاملاً بدون مسافات
- ✅ المفتاح صحيح من لوحة التحكم

### خطأ 400: بيانات ناقصة

تحقق من إرسال الحقول المطلوبة:
```json
{
  "title": "مطلوب",
  "affiliateLink": "مطلوب"
}
```

### خطأ 500: خطأ في السيرفر

- تحقق من error logs في cPanel
- تأكد من صلاحيات قاعدة البيانات
- تأكد من وجود ملف `config.php`

## 🧪 الاختبار

### استخدام Postman

1. افتح Postman
2. أنشئ POST request جديد
3. **URL**: `https://yoursite.com/api/webhook.php`
4. **Headers**:
   - `X-API-Key`: your-key-here
   - `Content-Type`: application/json
5. **Body** → raw → JSON:
```json
{
  "title": "منتج تجريبي",
  "affiliateLink": "https://amazon.ae/test"
}
```
6. اضغط Send

### Health Check السريع

```bash
curl https://yoursite.com/api/webhook.php?action=health
```

يجب أن ترى:
```json
{
  "success": true,
  "message": "Webhook API يعمل بنجاح"
}
```

## 📊 أفضل الممارسات

1. **احفظ API Key بأمان**: لا تشاركه أو تنشره
2. **استخدم HTTPS**: للاتصال الآمن
3. **تحقق من البيانات**: قبل الإرسال
4. **معالجة الأخطاء**: في السكريبت الخاص بك
5. **تتبع النجاح**: احفظ product_id المُرجع

## 🎓 سيناريوهات متقدمة

### سكريبت منتظم لإضافة منتجات

```python
# scraper.py
import requests
import schedule
import time

def add_product(title, link, price):
    url = "https://yoursite.com/api/webhook.php"
    headers = {
        "X-API-Key": "your-api-key",
        "Content-Type": "application/json"
    }
    data = {
        "title": title,
        "affiliateLink": link,
        "price": price,
        "category": "electronics"
    }

    response = requests.post(url, headers=headers, json=data)
    if response.status_code == 201:
        print(f"✅ تم إضافة: {title}")
    else:
        print(f"❌ خطأ: {response.json()}")

# تشغيل كل ساعة
schedule.every().hour.do(lambda: add_product(
    "منتج جديد",
    "https://amazon.ae/...",
    99.99
))

while True:
    schedule.run_pending()
    time.sleep(60)
```

## 🆘 الحصول على المساعدة

إذا واجهت أي مشكلة:

1. راجع error logs
2. جرب Health Check endpoint
3. تحقق من التوثيق المدمج: `/api/webhook.php?action=docs`
4. تأكد من صحة API Key

---

**🎉 الآن أنت جاهز لاستخدام Webhook API!**

للمزيد من المعلومات، راجع [README.md](README.md)
