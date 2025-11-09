<?php
/**
 * Webhook API - استقبال المنتجات من n8n
 * POST /api/webhook.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// ==================== Health Check ====================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'health') {
        sendJsonResponse([
            'success' => true,
            'message' => 'Webhook API يعمل بنجاح',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    } elseif ($_GET['action'] === 'docs') {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Webhook API Documentation - PYRASTORE</title>
            <style>
                body { font-family: 'Cairo', sans-serif; padding: 2rem; max-width: 900px; margin: 0 auto; line-height: 1.6; }
                h1 { color: #FF6B35; }
                code { background: #f4f4f4; padding: 0.2rem 0.5rem; border-radius: 3px; }
                pre { background: #f4f4f4; padding: 1rem; border-radius: 5px; overflow-x: auto; direction: ltr; text-align: left; }
                .endpoint { background: #e7f5ff; padding: 1rem; margin: 1rem 0; border-radius: 5px; border-right: 4px solid #1c7ed6; }
                .method { background: #FF6B35; color: white; padding: 0.2rem 0.5rem; border-radius: 3px; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>📡 Webhook API - دليل الاستخدام</h1>

            <div class="endpoint">
                <h2><span class="method">POST</span> /api/webhook.php</h2>
                <p><strong>الوصف:</strong> إضافة منتج جديد</p>

                <h3>Headers المطلوبة:</h3>
                <pre>X-API-Key: YOUR_API_KEY_HERE
Content-Type: application/json</pre>

                <h3>Body (JSON):</h3>
                <pre>{
  "title": "اسم المنتج",
  "description": "وصف المنتج",
  "imageUrl": "https://example.com/image.jpg",
  "price": 149.99,
  "originalPrice": 299.99,
  "category": "electronics",
  "affiliateLink": "https://www.amazon.ae/dp/...",
  "videoUrl": "https://drive.google.com/file/d/...",
  "videoOrientation": "landscape",
  "additionalImages": [
    "https://example.com/image2.jpg",
    "https://example.com/image3.jpg"
  ]
}</pre>

                <h3>الحقول المطلوبة:</h3>
                <ul>
                    <li><code>title</code> - عنوان المنتج (مطلوب)</li>
                    <li><code>affiliateLink</code> - رابط الأفلييت (مطلوب)</li>
                </ul>

                <h3>الحقول الاختيارية:</h3>
                <ul>
                    <li><code>description</code> - وصف المنتج</li>
                    <li><code>imageUrl</code> - رابط الصورة الرئيسية</li>
                    <li><code>price</code> - السعر الحالي</li>
                    <li><code>originalPrice</code> - السعر الأصلي (قبل الخصم)</li>
                    <li><code>category</code> - الفئة: electronics, fashion, home, sports, beauty, books, toys, other</li>
                    <li><code>videoUrl</code> - رابط فيديو من Google Drive أو YouTube</li>
                    <li><code>videoOrientation</code> - اتجاه الفيديو: portrait أو landscape</li>
                    <li><code>additionalImages</code> - مصفوفة من روابط الصور الإضافية</li>
                </ul>

                <h3>الاستجابة الناجحة:</h3>
                <pre>{
  "success": true,
  "message": "تم إضافة المنتج بنجاح",
  "product_id": 123
}</pre>
            </div>

            <div class="endpoint">
                <h2><span class="method" style="background: #10B981;">GET</span> /api/webhook.php?action=health</h2>
                <p><strong>الوصف:</strong> فحص حالة الـ API</p>
            </div>

            <h2>🔐 الحصول على API Key</h2>
            <p>يمكنك الحصول على API Key أو إنشاء واحد جديد من لوحة التحكم في صفحة الإعدادات:</p>
            <p><a href="/admin/settings.php">لوحة التحكم → الإعدادات → Webhook API</a></p>

            <h2>💡 مثال cURL</h2>
            <pre>curl -X POST https://yoursite.com/api/webhook.php \
  -H "X-API-Key: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "سماعات لاسلكية",
    "description": "سماعات بلوتوث بجودة عالية",
    "imageUrl": "https://example.com/headphones.jpg",
    "price": 149.99,
    "originalPrice": 299.99,
    "category": "electronics",
    "affiliateLink": "https://www.amazon.ae/dp/B08XYZ"
  }'</pre>

            <h2>⚠️ رموز الأخطاء</h2>
            <ul>
                <li><code>401</code> - API Key غير صحيح</li>
                <li><code>400</code> - بيانات غير صحيحة أو ناقصة</li>
                <li><code>405</code> - طريقة HTTP غير مسموحة</li>
                <li><code>500</code> - خطأ في السيرفر</li>
            </ul>
        </body>
        </html>
        <?php
        exit();
    }
}

// ==================== Add Product ====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // التحقق من API Key
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if (empty($apiKey)) {
        sendJsonResponse(['success' => false, 'message' => 'API Key مطلوب'], 401);
    }

    if (!verifyApiKey($apiKey)) {
        sendJsonResponse(['success' => false, 'message' => 'API Key غير صحيح'], 401);
    }

    // قراءة البيانات
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        sendJsonResponse(['success' => false, 'message' => 'بيانات JSON غير صحيحة'], 400);
    }

    // التحقق من الحقول المطلوبة
    if (empty($input['title']) || empty($input['affiliateLink'])) {
        sendJsonResponse(['success' => false, 'message' => 'الحقول المطلوبة: title, affiliateLink'], 400);
    }

    $db = getDB();

    // تحضير البيانات
    $title = $input['title'];
    $description = $input['description'] ?? '';
    $imageUrl = $input['imageUrl'] ?? 'https://via.placeholder.com/400';
    $price = floatval($input['price'] ?? 0);
    $originalPrice = isset($input['originalPrice']) ? floatval($input['originalPrice']) : null;
    $category = $input['category'] ?? 'other';
    $affiliateLink = $input['affiliateLink'];
    $videoUrl = $input['videoUrl'] ?? null;
    $videoOrientation = $input['videoOrientation'] ?? 'landscape';

    // حساب نسبة الخصم
    $discountPercentage = null;
    if ($originalPrice && $price > 0) {
        $discountPercentage = calculateDiscount($originalPrice, $price);
    }

    // إدراج المنتج
    $stmt = $db->prepare("INSERT INTO products (title, description, image_url, price, original_price,
                         discount_percentage, category, affiliate_link, video_url, video_orientation)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $title,
        $description,
        $imageUrl,
        $price,
        $originalPrice,
        $discountPercentage,
        $category,
        $affiliateLink,
        $videoUrl,
        $videoOrientation
    ]);

    $productId = $db->lastInsertId();

    // إضافة الصور الإضافية
    $imagesAdded = 0;
    if (!empty($input['additionalImages']) && is_array($input['additionalImages'])) {
        $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)");
        foreach ($input['additionalImages'] as $index => $imageUrl) {
            // Validate URL before inserting
            if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                try {
                    $imgStmt->execute([$productId, $imageUrl, $index + 1]);
                    $imagesAdded++;
                } catch (PDOException $e) {
                    error_log("Failed to insert additional image for product $productId: " . $e->getMessage());
                }
            } else {
                error_log("Invalid image URL skipped for product $productId: " . var_export($imageUrl, true));
            }
        }
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'تم إضافة المنتج بنجاح',
        'product_id' => $productId,
        'additional_images_added' => $imagesAdded
    ], 201);

} catch (PDOException $e) {
    error_log("Webhook API Database Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'خطأ في قاعدة البيانات'], 500);
} catch (Exception $e) {
    error_log("Webhook API Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'حدث خطأ غير متوقع'], 500);
}
?>
