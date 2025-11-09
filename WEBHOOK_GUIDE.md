# 🔗 Webhook API Complete Guide - PYRASTORE

Comprehensive guide for integrating Amazon products with PYRASTORE using Webhook API and n8n automation.

---

## 📡 Overview

The Webhook API allows you to automatically add products to your website via HTTP POST requests. Perfect for:

- ✅ **n8n** (Recommended - automation platform)
- ✅ **Zapier** / **Make** (Integromat)
- ✅ **Python/Node.js** scripts
- ✅ **Postman** for testing

---

## 🔑 Getting Your API Key

1. Login to admin panel: `/admin/login.php`
2. Go to: **Settings → Webhook API**
3. Copy existing key or generate new one
4. Store securely (never share publicly!)

⚠️ **Security Warning**: Treat API Key like a password!

---

## 📍 API Endpoints

### Endpoint URL

```
POST https://yourdomain.com/api/webhook.php
```

### Health Check

```
GET https://yourdomain.com/api/webhook.php?action=health
```

### Documentation

```
GET https://yourdomain.com/api/webhook.php?action=docs
```

---

## 🗺️ Field Mapping: Amazon → Webhook → Website

This table shows how to map Amazon scraper fields to webhook fields and where they appear on the website:

| Amazon Field | Webhook Field | Type | Required | Website Display Location |
|--------------|---------------|------|----------|-------------------------|
| `product_title` / `title` | `title` | string | ✅ Yes | `<h1 class="product-detail-title">` |
| `product_url` / `amazon_link` | `affiliateLink` | string | ✅ Yes | Buy button href |
| `product_description` / `about_product` | `description` | string | No | `.product-description` section |
| `product_price` / `current_price` | `price` | float | No | `.current-price` (large, primary color) |
| `product_original_price` / `was_price` | `originalPrice` | float | No | `.original-price` (strikethrough) |
| `product_category` | `category` | string | No | `.category-badge` |
| `image_link1` / `main_image` | `imageUrl` | string | No | `#mainImage` (main gallery image) |
| `image_link2`, `image_link3`, ... | `additionalImages[0]`, `[1]`, ... | array | No | `.thumbnail-item` (gallery thumbnails) |
| `video_url` | `videoUrl` | string | No | Video iframe (if exists) |
| N/A | `videoOrientation` | string | No | Video display style |

### 🎯 Where Fields Appear on Product Page

```
Product Page Layout:
┌─────────────────────────────────────┐
│ Gallery (imageUrl + additionalImages)│
│ [Main Image] [Thumbnail] [Thumbnail] │
├─────────────────────────────────────┤
│ <h1>{title}</h1>                    │ ← product-detail-title
│                                     │
│ ⚡ Original: $299 {originalPrice}  │ ← original-price (strikethrough)
│ 💰 Now: $149 {price}               │ ← current-price (bold, large)
│ 🏷️ Save 50% (auto-calculated)     │ ← discount-badge
│                                     │
│ [🛒 Buy Now] → {affiliateLink}     │ ← buy-now-btn
│                                     │
│ 📝 Description:                     │
│ {description}                       │ ← product-description
│                                     │
│ 🎥 Video (if videoUrl exists)       │
│ [▶️ Video Player]                   │
│                                     │
│ ⭐ Reviews (separate table)         │
│ 👥 Similar Products                 │
└─────────────────────────────────────┘
```

---

## 📋 Complete Field Reference

### ✅ Required Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `title` | string | Product name/title | "Wireless Bluetooth Headphones 5.0" |
| `affiliateLink` | string | Amazon affiliate URL | "https://www.amazon.ae/dp/B08XYZ123?tag=yourstore" |

### 📝 Optional Fields

| Field | Type | Default | Description | Validation |
|-------|------|---------|-------------|-----------|
| `description` | string | `""` | Full product description | Max 65,535 chars (TEXT) |
| `imageUrl` | string | Placeholder | Main product image URL | Must be valid URL |
| `price` | float | `0` | Current price (AED) | Positive number |
| `originalPrice` | float | `null` | Original price before discount | Must be ≥ price |
| `category` | string | `"other"` | Product category (see below) | Must match enum values |
| `videoUrl` | string | `null` | YouTube or Google Drive URL | Valid URL |
| `videoOrientation` | string | `"landscape"` | Video aspect ratio | `landscape` or `portrait` |
| `additionalImages` | array | `[]` | Additional product images (URLs) | Array of valid URLs |

### 📦 Categories

| Value | Arabic Display | Icon |
|-------|---------------|------|
| `electronics` | إلكترونيات | 📱 |
| `fashion` | أزياء | 👔 |
| `home` | منزل ومطبخ | 🏠 |
| `sports` | رياضة | ⚽ |
| `beauty` | جمال وعناية | 💄 |
| `books` | كتب | 📚 |
| `toys` | ألعاب | 🧸 |
| `other` | أخرى | 🛍️ |

### 🎬 Video URL Formats

**YouTube:**
- Input: `https://www.youtube.com/watch?v=VIDEO_ID`
- Input: `https://youtu.be/VIDEO_ID`
- Auto-converts to: `https://www.youtube.com/embed/VIDEO_ID`

**Google Drive:**
- Input: `https://drive.google.com/file/d/FILE_ID/view`
- Auto-converts to: `https://drive.google.com/file/d/FILE_ID/preview`

---

## 💡 Complete Examples

### Example 1: Minimal Required Fields Only

```json
{
  "title": "Wireless Earbuds with Noise Cancellation",
  "affiliateLink": "https://www.amazon.ae/dp/B08ABC123?tag=pyrastore"
}
```

**Result:** Product created with title and buy button. All other fields use defaults.

---

### Example 2: Complete Product with All Fields

```json
{
  "title": "Sony WH-1000XM5 Wireless Headphones - Black",
  "description": "Industry-leading noise cancellation with Auto NC Optimizer. Crystal clear hands-free calling. Up to 30 hours battery life. Multipoint connection allows you to switch between devices.",
  "imageUrl": "https://m.media-amazon.com/images/I/61vFO3XcneL._AC_SL1500_.jpg",
  "price": 1299.00,
  "originalPrice": 1699.00,
  "category": "electronics",
  "affiliateLink": "https://www.amazon.ae/dp/B0BZ1B45TV?tag=pyrastore",
  "videoUrl": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "videoOrientation": "landscape",
  "additionalImages": [
    "https://m.media-amazon.com/images/I/51wf+hpTI0L._AC_SL1500_.jpg",
    "https://m.media-amazon.com/images/I/61MKlJUlijL._AC_SL1500_.jpg",
    "https://m.media-amazon.com/images/I/61hJ+GxVqYL._AC_SL1500_.jpg"
  ]
}
```

**Result:**
- ✅ Full image gallery (4 images total)
- ✅ Price with 24% discount badge
- ✅ Complete description
- ✅ Video player
- ✅ Auto-calculated savings (400 AED)

---

### Example 3: Product with Multiple Images

```json
{
  "title": "Smart Watch with Heart Rate Monitor",
  "affiliateLink": "https://www.amazon.ae/dp/B08DEF456",
  "imageUrl": "https://example.com/watch-main.jpg",
  "additionalImages": [
    "https://example.com/watch-side.jpg",
    "https://example.com/watch-back.jpg",
    "https://example.com/watch-strap.jpg",
    "https://example.com/watch-box.jpg"
  ],
  "price": 299.99,
  "category": "electronics"
}
```

**Result:** Gallery with 5 clickable images (1 main + 4 additional)

---

### Example 4: Product with Discount

```json
{
  "title": "Premium Yoga Mat - Extra Thick",
  "affiliateLink": "https://www.amazon.ae/dp/B08GHI789",
  "price": 89.99,
  "originalPrice": 179.99,
  "category": "sports"
}
```

**Result:**
- Original price shown with strikethrough
- Current price prominent
- Auto-calculated: **50% discount** badge
- Auto-calculated: **Save 90 AED**

---

## 🔧 cURL Examples

### Basic Request

```bash
curl -X POST https://yourdomain.com/api/webhook.php \
  -H "X-API-Key: pyrastore-YOUR-KEY-HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Wireless Earbuds - Premium Sound",
    "affiliateLink": "https://www.amazon.ae/dp/B08XYZ123"
  }'
```

### Full Product with All Fields

```bash
curl -X POST https://yourdomain.com/api/webhook.php \
  -H "X-API-Key: pyrastore-YOUR-KEY-HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Professional Gaming Mouse RGB",
    "description": "16000 DPI optical sensor, 7 programmable buttons, RGB lighting with 16.8 million colors, ergonomic design for extended gaming sessions",
    "imageUrl": "https://m.media-amazon.com/images/I/61ABC123.jpg",
    "price": 149.99,
    "originalPrice": 299.99,
    "category": "electronics",
    "affiliateLink": "https://www.amazon.ae/dp/B08MOUSE99?tag=pyrastore",
    "additionalImages": [
      "https://m.media-amazon.com/images/I/61ABC124.jpg",
      "https://m.media-amazon.com/images/I/61ABC125.jpg"
    ]
  }'
```

---

## 🤖 n8n Integration Guide

### Step-by-Step Setup

#### 1. Create HTTP Request Node

In your n8n workflow:
1. Add **HTTP Request** node
2. Configure as shown below

#### 2. Node Configuration

```
Method: POST
URL: https://yourdomain.com/api/webhook.php

Authentication: Generic Credential Type
Credential Type: Header Auth

Header Name: X-API-Key
Header Value: pyrastore-YOUR-KEY-HERE

Body Content Type: JSON

Body Parameters (JSON):
```

#### 3. JSON Body Mapping (n8n Expression Mode)

```json
{
  "title": "={{ $json.product_title }}",
  "description": "={{ $json.product_description || $json.about_product }}",
  "imageUrl": "={{ $json.image_link1 || $json.main_image }}",
  "price": "={{ $json.product_price }}",
  "originalPrice": "={{ $json.product_original_price }}",
  "category": "electronics",
  "affiliateLink": "={{ $json.product_url }}",
  "additionalImages": "={{ [$json.image_link2, $json.image_link3].filter(img => img) }}"
}
```

#### 4. Field Mapping from Amazon Scraper

If you're scraping Amazon, map these fields:

| Amazon Scraper Output | n8n Expression | Webhook Field |
|----------------------|----------------|---------------|
| Product title | `{{ $json.product_title }}` | `title` |
| Product URL | `{{ $json.product_url }}` | `affiliateLink` |
| Current price | `{{ $json.product_price }}` | `price` |
| Original price | `{{ $json.product_original_price }}` | `originalPrice` |
| Main image | `{{ $json.image_link1 }}` | `imageUrl` |
| Image 2 | `{{ $json.image_link2 }}` | `additionalImages[0]` |
| Image 3 | `{{ $json.image_link3 }}` | `additionalImages[1]` |
| Description | `{{ $json.about_product }}` | `description` |

#### 5. Example n8n Workflow JSON

```json
{
  "nodes": [
    {
      "parameters": {
        "method": "POST",
        "url": "https://yourdomain.com/api/webhook.php",
        "authentication": "genericCredentialType",
        "genericAuthType": "headerAuth",
        "sendHeaders": true,
        "headerParameters": {
          "parameters": [
            {
              "name": "X-API-Key",
              "value": "pyrastore-YOUR-KEY"
            }
          ]
        },
        "sendBody": true,
        "bodyParameters": {
          "parameters": []
        },
        "specifyBody": "json",
        "jsonBody": "={{ {\n  \"title\": $json.product_title,\n  \"affiliateLink\": $json.product_url,\n  \"price\": $json.product_price,\n  \"originalPrice\": $json.product_original_price,\n  \"imageUrl\": $json.image_link1,\n  \"additionalImages\": [$json.image_link2, $json.image_link3].filter(img => img),\n  \"category\": \"electronics\"\n} }}",
        "options": {}
      },
      "name": "Add Product to PyraStore",
      "type": "n8n-nodes-base.httpRequest",
      "position": [800, 300]
    }
  ]
}
```

#### 6. Handle Response

Add an **IF** node after HTTP Request to check success:

```
Condition: {{ $json.success }} equals true

✅ TRUE → Log success / Send notification
❌ FALSE → Handle error / Retry logic
```

---

## 📤 API Responses

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "تم إضافة المنتج بنجاح",
  "product_id": 42,
  "additional_images_added": 3
}
```

**Fields:**
- `success`: Always `true` on success
- `message`: Success message in Arabic
- `product_id`: Database ID of created product (use for tracking)
- `additional_images_added`: Number of additional images successfully saved

### Error Responses

#### 400 - Missing Required Fields

```json
{
  "success": false,
  "message": "الحقول المطلوبة: title, affiliateLink"
}
```

**Fix:** Ensure `title` and `affiliateLink` are in your request.

---

#### 401 - Unauthorized (Invalid API Key)

```json
{
  "success": false,
  "message": "API Key غير صحيح"
}
```

**Fix:**
- Check header name is exactly `X-API-Key` (case-sensitive)
- Verify API key is correct (copy from admin panel)
- Ensure no extra spaces in key

---

#### 405 - Method Not Allowed

```json
{
  "success": false,
  "message": "Method not allowed"
}
```

**Fix:** Use `POST` method, not GET.

---

#### 500 - Server Error

```json
{
  "success": false,
  "message": "خطأ في قاعدة البيانات"
}
```

**Fix:**
- Check server error logs
- Verify database connection
- Ensure `config.php` exists

---

## 🧪 Testing Guide

### 1. Quick Health Check

Test if API is responding:

```bash
curl https://yourdomain.com/api/webhook.php?action=health
```

Expected response:
```json
{
  "success": true,
  "message": "Webhook API يعمل بنجاح",
  "timestamp": "2025-11-09 15:30:45",
  "version": "1.0.0"
}
```

---

### 2. Test with Postman

1. **Create New Request**
   - Method: `POST`
   - URL: `https://yourdomain.com/api/webhook.php`

2. **Headers Tab:**
   ```
   X-API-Key: your-api-key-here
   Content-Type: application/json
   ```

3. **Body Tab:**
   - Select: **raw**
   - Type: **JSON**
   - Content:
   ```json
   {
     "title": "Test Product",
     "affiliateLink": "https://amazon.ae/test"
   }
   ```

4. **Click Send**

5. **Expected Result:** 201 status code with product_id

---

### 3. Test with Python

```python
import requests

url = "https://yourdomain.com/api/webhook.php"
headers = {
    "X-API-Key": "pyrastore-YOUR-KEY",
    "Content-Type": "application/json"
}
data = {
    "title": "Test Product from Python",
    "affiliateLink": "https://amazon.ae/test",
    "price": 99.99
}

response = requests.post(url, headers=headers, json=data)
print(f"Status: {response.status_code}")
print(f"Response: {response.json()}")

if response.status_code == 201:
    product_id = response.json()['product_id']
    print(f"✅ Product created with ID: {product_id}")
else:
    print(f"❌ Error: {response.json()['message']}")
```

---

## 🔍 Troubleshooting

### Problem: 401 Unauthorized

**Symptoms:** Response says "API Key غير صحيح"

**Solutions:**
1. ✅ Check header name is `X-API-Key` (exact case)
2. ✅ Get fresh API key from admin panel
3. ✅ Remove any spaces from key
4. ✅ Test with curl first:
   ```bash
   curl -v https://yourdomain.com/api/webhook.php \
     -H "X-API-Key: your-key" \
     -H "Content-Type: application/json" \
     -d '{"title":"test","affiliateLink":"https://test.com"}'
   ```

---

### Problem: 400 Missing Fields

**Symptoms:** Response says "الحقول المطلوبة: title, affiliateLink"

**Solutions:**
1. ✅ Ensure JSON body includes both `title` and `affiliateLink`
2. ✅ Check field names are spelled correctly (case-sensitive)
3. ✅ Verify Content-Type header is `application/json`
4. ✅ Test JSON validity: https://jsonlint.com

---

### Problem: Images Not Showing

**Symptoms:** Product created but images don't display

**Solutions:**
1. ✅ Check image URLs are publicly accessible
2. ✅ Verify URLs use HTTPS (not HTTP)
3. ✅ Test URL in browser - should load image directly
4. ✅ Check response: `additional_images_added` should be > 0
5. ✅ Look for errors in server logs

**Debug:**
```bash
# Test if URL is valid
curl -I "https://your-image-url.jpg"

# Should return: HTTP/1.1 200 OK
```

---

### Problem: Discount Not Showing

**Symptoms:** Product created but no discount badge

**Solutions:**
1. ✅ Ensure `originalPrice` is provided
2. ✅ Verify `originalPrice` > `price`
3. ✅ Both must be numbers (not strings)
4. ✅ Example:
   ```json
   {
     "price": 149.99,
     "originalPrice": 299.99  ← Must be higher
   }
   ```

---

## 📊 Advanced Use Cases

### Bulk Product Import from CSV

```python
import csv
import requests
import time

API_URL = "https://yourdomain.com/api/webhook.php"
API_KEY = "pyrastore-YOUR-KEY"

def add_product(row):
    headers = {
        "X-API-Key": API_KEY,
        "Content-Type": "application/json"
    }

    data = {
        "title": row['title'],
        "affiliateLink": row['amazon_url'],
        "price": float(row['price']),
        "originalPrice": float(row['original_price']) if row['original_price'] else None,
        "imageUrl": row['image_url'],
        "category": row['category'],
        "description": row['description']
    }

    response = requests.post(API_URL, headers=headers, json=data)
    return response

# Read CSV file
with open('products.csv', 'r', encoding='utf-8') as file:
    reader = csv.DictReader(file)

    for row in reader:
        print(f"Adding: {row['title']}")
        response = add_product(row)

        if response.status_code == 201:
            product_id = response.json()['product_id']
            print(f"  ✅ Success! ID: {product_id}")
        else:
            print(f"  ❌ Error: {response.json()['message']}")

        time.sleep(1)  # Rate limiting - 1 second between requests
```

**CSV Format:**
```csv
title,amazon_url,price,original_price,image_url,category,description
"Wireless Mouse","https://amazon.ae/dp/123",49.99,99.99,"https://img.jpg","electronics","Great mouse"
```

---

### Scheduled Auto-Import with Python

```python
import schedule
import time
import requests

def fetch_and_import_products():
    # Your product scraping logic here
    products = scrape_amazon_deals()

    for product in products:
        # Add to PyraStore via webhook
        response = requests.post(
            "https://yourdomain.com/api/webhook.php",
            headers={"X-API-Key": "your-key", "Content-Type": "application/json"},
            json=product
        )

        if response.status_code == 201:
            print(f"✅ Added: {product['title']}")
        else:
            print(f"❌ Failed: {product['title']}")

# Run every 6 hours
schedule.every(6).hours.do(fetch_and_import_products)

print("🤖 Auto-import bot started...")
while True:
    schedule.run_pending()
    time.sleep(60)
```

---

## 📱 Mobile App Integration

### React Native Example

```javascript
const addProduct = async (productData) => {
  try {
    const response = await fetch('https://yourdomain.com/api/webhook.php', {
      method: 'POST',
      headers: {
        'X-API-Key': 'pyrastore-YOUR-KEY',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(productData)
    });

    const result = await response.json();

    if (response.status === 201) {
      console.log('✅ Product added:', result.product_id);
      return result.product_id;
    } else {
      console.error('❌ Error:', result.message);
      return null;
    }
  } catch (error) {
    console.error('Network error:', error);
    return null;
  }
};

// Usage
const newProduct = {
  title: 'Smart Watch Pro',
  affiliateLink: 'https://amazon.ae/dp/ABC123',
  price: 299.99,
  category: 'electronics'
};

addProduct(newProduct);
```

---

## 💰 Auto-Calculate Savings

The webhook automatically calculates:

1. **Discount Percentage:**
   ```
   discount = ((originalPrice - price) / originalPrice) * 100
   ```
   Example: (299.99 - 149.99) / 299.99 = 50%

2. **Savings Amount:**
   ```
   savings = originalPrice - price
   ```
   Example: 299.99 - 149.99 = 150 AED

Both values are displayed automatically on the product page if you provide `originalPrice`.

---

## 🎓 Best Practices

### 1. ✅ Always Validate Data Before Sending

```python
def validate_product(data):
    # Required fields
    if not data.get('title'):
        return False, "Title is required"
    if not data.get('affiliateLink'):
        return False, "Affiliate link is required"

    # Price validation
    if data.get('originalPrice') and data.get('price'):
        if data['originalPrice'] <= data['price']:
            return False, "Original price must be higher than current price"

    # URL validation
    if data.get('imageUrl'):
        if not data['imageUrl'].startswith('http'):
            return False, "Image URL must start with http/https"

    return True, "Valid"
```

---

### 2. ✅ Handle Errors Gracefully

```python
def add_product_safe(data):
    try:
        response = requests.post(API_URL, headers=headers, json=data, timeout=10)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.Timeout:
        print("⏱️ Request timeout - server too slow")
    except requests.exceptions.HTTPError as e:
        print(f"❌ HTTP Error: {e.response.status_code}")
        print(f"Message: {e.response.json()['message']}")
    except Exception as e:
        print(f"💥 Unexpected error: {e}")

    return None
```

---

### 3. ✅ Rate Limiting

Don't spam the API - add delays:

```python
import time

for product in products:
    add_product(product)
    time.sleep(2)  # 2 seconds delay between requests
```

---

### 4. ✅ Log Product IDs

Keep track of what you've added:

```python
import json

added_products = {}

response = add_product(product_data)
if response and response['success']:
    product_id = response['product_id']
    added_products[product_data['title']] = product_id

    # Save to file
    with open('added_products.json', 'w') as f:
        json.dump(added_products, f, indent=2)
```

---

### 5. ✅ Use Environment Variables for API Key

Never hardcode API keys:

```python
import os
from dotenv import load_dotenv

load_dotenv()
API_KEY = os.getenv('PYRASTORE_API_KEY')
```

**.env file:**
```
PYRASTORE_API_KEY=pyrastore-your-secret-key
```

---

## 🔐 Security Checklist

- ✅ Use HTTPS only (never HTTP)
- ✅ Store API key in environment variables
- ✅ Never commit API key to Git
- ✅ Regenerate API key if leaked
- ✅ Use rate limiting in your scripts
- ✅ Validate all data before sending
- ✅ Monitor for unusual activity

---

## 📞 Support & Help

### 1. Check Built-in Documentation

```
https://yourdomain.com/api/webhook.php?action=docs
```

### 2. Test API Health

```
https://yourdomain.com/api/webhook.php?action=health
```

### 3. Review Error Logs

- cPanel → Error Logs
- Check: `/api/webhook.php` errors

### 4. Common Issues Checklist

- [ ] API Key is correct and active
- [ ] Using POST method (not GET)
- [ ] Header name is `X-API-Key` (exact case)
- [ ] Content-Type is `application/json`
- [ ] JSON is valid (test at jsonlint.com)
- [ ] Required fields `title` and `affiliateLink` are present
- [ ] Image URLs are publicly accessible

---

## 🎉 Quick Start Checklist

- [ ] Get API key from admin panel
- [ ] Test health check endpoint
- [ ] Test minimal request with cURL
- [ ] Verify product appears on website
- [ ] Test with image URLs
- [ ] Test with discount (originalPrice)
- [ ] Test with additional images array
- [ ] Set up n8n workflow
- [ ] Add error handling
- [ ] Monitor success rate

---

## 📚 Related Documentation

- [README.md](README.md) - Project overview
- [DEPLOY.md](DEPLOY.md) - Deployment guide
- [PROJECT_AUDIT_REPORT.md](PROJECT_AUDIT_REPORT.md) - Technical audit

---

**Version:** 2.0
**Last Updated:** 2025-11-09
**Status:** ✅ Production Ready

---

🎉 **You're now ready to automate product imports to PYRASTORE!**
