<?php
/**
 * إدارة المنتجات
 */

$pageTitle = 'إدارة المنتجات';
include '_header.php';

$db = getDB();
$message = '';
$error = '';

// معالجة الإجراءات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $originalPrice = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;

        // Validation للـ Category
        $allowedCategories = ['electronics', 'fashion', 'home', 'sports', 'beauty', 'books', 'toys', 'other'];
        $category = in_array($_POST['category'] ?? '', $allowedCategories) ? $_POST['category'] : 'other';

        $affiliateLink = trim($_POST['affiliate_link'] ?? '');
        $videoUrl = trim($_POST['video_url'] ?? '');
        $videoOrientation = $_POST['video_orientation'] ?? 'landscape';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // معالجة الصور الإضافية
        $additionalImages = trim($_POST['additional_images'] ?? '');
        $additionalImagesArray = [];
        if (!empty($additionalImages)) {
            // فصل الروابط (كل رابط في سطر جديد)
            $lines = explode("\n", $additionalImages);
            foreach ($lines as $line) {
                $url = trim($line);
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $additionalImagesArray[] = $url;
                }
            }
        }

        $discountPercentage = $originalPrice ? calculateDiscount($originalPrice, $price) : null;

        try {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO products (title, description, image_url, price, original_price, discount_percentage, category, affiliate_link, video_url, video_orientation, is_active)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $imageUrl, $price, $originalPrice, $discountPercentage, $category, $affiliateLink, $videoUrl, $videoOrientation, $isActive]);
                $productId = $db->lastInsertId();

                // إضافة الصور الإضافية
                if (!empty($additionalImagesArray)) {
                    $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)");
                    foreach ($additionalImagesArray as $order => $imgUrl) {
                        $imgStmt->execute([$productId, $imgUrl, $order]);
                    }
                }

                $message = 'تم إضافة المنتج بنجاح';
            } else {
                $stmt = $db->prepare("UPDATE products SET title = ?, description = ?, image_url = ?, price = ?, original_price = ?, discount_percentage = ?, category = ?, affiliate_link = ?, video_url = ?, video_orientation = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $description, $imageUrl, $price, $originalPrice, $discountPercentage, $category, $affiliateLink, $videoUrl, $videoOrientation, $isActive, $id]);

                // حذف الصور الإضافية القديمة وإضافة الجديدة
                $db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
                if (!empty($additionalImagesArray)) {
                    $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)");
                    foreach ($additionalImagesArray as $order => $imgUrl) {
                        $imgStmt->execute([$id, $imgUrl, $order]);
                    }
                }

                $message = 'تم تحديث المنتج بنجاح';
            }
        } catch (Exception $e) {
            $error = 'حدث خطأ: ' . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'تم حذف المنتج بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ: ' . $e->getMessage();
        }
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $db->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'تم تغيير حالة المنتج';
        } catch (Exception $e) {
            $error = 'حدث خطأ: ' . $e->getMessage();
        }
    } elseif ($action === 'bulk_delete') {
        $ids = $_POST['product_ids'] ?? [];
        if (!empty($ids) && is_array($ids)) {
            $deletedCount = 0;
            try {
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                foreach ($ids as $id) {
                    $id = intval($id);
                    if ($id > 0) {
                        $stmt->execute([$id]);
                        $deletedCount++;
                    }
                }
                $message = "تم حذف $deletedCount منتج بنجاح";
            } catch (Exception $e) {
                $error = 'حدث خطأ: ' . $e->getMessage();
            }
        } else {
            $error = 'لم يتم تحديد أي منتجات للحذف';
        }
    }
}

// جلب المنتجات
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// جلب منتج للتعديل
$editProduct = null;
$editProductImages = [];
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();

    // جلب الصور الإضافية
    if ($editProduct) {
        $imgStmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY display_order");
        $imgStmt->execute([$editId]);
        $editProductImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

<div class="page-header">
    <h1>📦 إدارة المنتجات</h1>
    <p>إضافة وتعديل وحذف المنتجات</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo clean($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo clean($error); ?></div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h2><?php echo $editProduct ? '✏️ تعديل منتج' : '➕ إضافة منتج جديد'; ?></h2>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
        <?php if ($editProduct): ?>
            <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label>عنوان المنتج *</label>
                <input type="text" name="title" class="form-control" required value="<?php echo clean($editProduct['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>رابط الصورة الرئيسية *</label>
                <input type="url" name="image_url" class="form-control" required value="<?php echo clean($editProduct['image_url'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>السعر الحالي (درهم) *</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $editProduct['price'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label>السعر الأصلي (اختياري)</label>
                <input type="number" step="0.01" name="original_price" class="form-control" value="<?php echo $editProduct['original_price'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label>الفئة *</label>
                <select name="category" class="form-control" required>
                    <option value="electronics" <?php echo ($editProduct['category'] ?? '') === 'electronics' ? 'selected' : ''; ?>>📱 إلكترونيات</option>
                    <option value="fashion" <?php echo ($editProduct['category'] ?? '') === 'fashion' ? 'selected' : ''; ?>>👔 أزياء</option>
                    <option value="home" <?php echo ($editProduct['category'] ?? '') === 'home' ? 'selected' : ''; ?>>🏠 منزل ومطبخ</option>
                    <option value="sports" <?php echo ($editProduct['category'] ?? '') === 'sports' ? 'selected' : ''; ?>>⚽ رياضة</option>
                    <option value="beauty" <?php echo ($editProduct['category'] ?? '') === 'beauty' ? 'selected' : ''; ?>>💄 جمال وعناية</option>
                    <option value="books" <?php echo ($editProduct['category'] ?? '') === 'books' ? 'selected' : ''; ?>>📚 كتب</option>
                    <option value="toys" <?php echo ($editProduct['category'] ?? '') === 'toys' ? 'selected' : ''; ?>>🧸 ألعاب</option>
                    <option value="other" <?php echo ($editProduct['category'] ?? '') === 'other' ? 'selected' : ''; ?>>🛍️ أخرى</option>
                </select>
            </div>

            <div class="form-group">
                <label>رابط الأفلييت *</label>
                <input type="url" name="affiliate_link" class="form-control" required value="<?php echo clean($editProduct['affiliate_link'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>رابط الفيديو (اختياري)</label>
                <input type="url" name="video_url" class="form-control" value="<?php echo clean($editProduct['video_url'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>اتجاه الفيديو</label>
                <select name="video_orientation" class="form-control">
                    <option value="landscape" <?php echo ($editProduct['video_orientation'] ?? '') === 'landscape' ? 'selected' : ''; ?>>عرضي</option>
                    <option value="portrait" <?php echo ($editProduct['video_orientation'] ?? '') === 'portrait' ? 'selected' : ''; ?>>عمودي</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>الوصف *</label>
            <textarea name="description" class="form-control" required rows="4"><?php echo clean($editProduct['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label>صور إضافية (اختياري)</label>
            <small style="display: block; color: #6B7280; margin-bottom: 0.5rem;">
                📸 أدخل رابط كل صورة في سطر جديد. هذه الصور ستظهر في معرض الصور بصفحة المنتج.
            </small>
            <textarea name="additional_images" class="form-control" rows="5" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"><?php echo !empty($editProductImages) ? implode("\n", $editProductImages) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?php echo ($editProduct['is_active'] ?? 1) ? 'checked' : ''; ?>>
                تفعيل المنتج
            </label>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo $editProduct ? '💾 حفظ التعديلات' : '➕ إضافة المنتج'; ?>
        </button>

        <?php if ($editProduct): ?>
            <a href="/admin/products.php" class="btn" style="background: #6B7280; color: white; margin-right: 0.5rem;">إلغاء</a>
        <?php endif; ?>
    </form>
</div>

<!-- Search & Filter -->
<div class="card">
    <form method="GET" action="" style="display: flex; gap: 1rem; align-items: end;">
        <div class="form-group" style="flex: 1; margin: 0;">
            <label>البحث</label>
            <input type="text" name="search" class="form-control" placeholder="ابحث عن منتج..." value="<?php echo clean($search); ?>">
        </div>

        <div class="form-group" style="width: 200px; margin: 0;">
            <label>الفئة</label>
            <select name="category" class="form-control">
                <option value="">جميع الفئات</option>
                <option value="electronics" <?php echo $category === 'electronics' ? 'selected' : ''; ?>>إلكترونيات</option>
                <option value="fashion" <?php echo $category === 'fashion' ? 'selected' : ''; ?>>أزياء</option>
                <option value="home" <?php echo $category === 'home' ? 'selected' : ''; ?>>منزل ومطبخ</option>
                <option value="sports" <?php echo $category === 'sports' ? 'selected' : ''; ?>>رياضة</option>
                <option value="beauty" <?php echo $category === 'beauty' ? 'selected' : ''; ?>>جمال وعناية</option>
                <option value="books" <?php echo $category === 'books' ? 'selected' : ''; ?>>كتب</option>
                <option value="toys" <?php echo $category === 'toys' ? 'selected' : ''; ?>>ألعاب</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">🔍 بحث</button>
        <a href="/admin/products.php" class="btn" style="background: #6B7280; color: white;">مسح</a>
    </form>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkActionsBar" class="card" style="display: none; background: #FFF3CD; border: 2px solid #FFC107; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span id="selectedCount" style="font-weight: 600; color: #856404;">
                تم تحديد <span id="countNumber">0</span> منتج
            </span>
            <button type="button" class="btn btn-sm" style="background: #3B82F6; color: white;" onclick="selectAllProducts()">
                ✓ تحديد الكل
            </button>
            <button type="button" class="btn btn-sm" style="background: #6B7280; color: white;" onclick="deselectAllProducts()">
                ✗ إلغاء التحديد
            </button>
        </div>
        <div>
            <form method="POST" id="bulkDeleteForm" style="display: inline;" onsubmit="return confirmBulkDelete()">
                <input type="hidden" name="action" value="bulk_delete">
                <div id="bulkDeleteIds"></div>
                <button type="submit" class="btn btn-danger">
                    🗑️ حذف المحدد
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Products List -->
<div class="card">
    <div class="card-header">
        <h2>قائمة المنتجات (<?php echo count($products); ?>)</h2>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="selectAllCheckbox" onclick="toggleAllCheckboxes(this)" title="تحديد/إلغاء تحديد الكل">
                    </th>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>الفئة</th>
                    <th>السعر</th>
                    <th>الخصم</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="product-checkbox" value="<?php echo $p['id']; ?>" onchange="updateBulkActions()">
                    </td>
                    <td><img src="<?php echo clean($p['image_url']); ?>" class="product-thumb"></td>
                    <td><?php echo clean(truncateText($p['title'], 50)); ?></td>
                    <td><span class="badge badge-info"><?php echo getCategoryNameAr($p['category']); ?></span></td>
                    <td><?php echo formatPrice($p['price']); ?> درهم</td>
                    <td><?php echo $p['discount_percentage'] ? $p['discount_percentage'] . '%' : '-'; ?></td>
                    <td>
                        <?php if ($p['is_active']): ?>
                            <span class="badge badge-success">مفعّل</span>
                        <?php else: ?>
                            <span class="badge badge-danger">معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary" target="_blank">👁️</a>
                        <a href="?edit=<?php echo $p['id']; ?>" class="btn btn-sm" style="background: #3B82F6; color: white;">✏️</a>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('تغيير حالة المنتج؟')">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-warning">🔄</button>
                        </form>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// تحديث شريط الإجراءات الجماعية
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkedBoxes.length;

    // إظهار/إخفاء شريط الإجراءات
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    if (count > 0) {
        bulkActionsBar.style.display = 'block';
    } else {
        bulkActionsBar.style.display = 'none';
    }

    // تحديث العدد
    document.getElementById('countNumber').textContent = count;

    // تحديث checkboxات التحديد الكلي
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (count === checkboxes.length && count > 0) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else if (count > 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    // تحديث الـ hidden inputs للنموذج
    updateHiddenInputs(checkedBoxes);
}

// تحديث المدخلات المخفية بمعرفات المنتجات المحددة
function updateHiddenInputs(checkedBoxes) {
    const container = document.getElementById('bulkDeleteIds');
    container.innerHTML = '';

    checkedBoxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'product_ids[]';
        input.value = checkbox.value;
        container.appendChild(input);
    });
}

// تبديل جميع الـ checkboxes
function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActions();
}

// تحديد جميع المنتجات
function selectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateBulkActions();
}

// إلغاء تحديد جميع المنتجات
function deselectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// تأكيد الحذف الجماعي
function confirmBulkDelete() {
    const count = document.querySelectorAll('.product-checkbox:checked').length;

    if (count === 0) {
        alert('يرجى تحديد منتج واحد على الأقل للحذف');
        return false;
    }

    const message = `هل أنت متأكد من حذف ${count} منتج؟\n\nهذا الإجراء لا يمكن التراجع عنه!`;
    return confirm(message);
}

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>

<?php include '_footer.php'; ?>
