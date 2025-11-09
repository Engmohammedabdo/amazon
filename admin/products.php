<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            // Process add/edit
            $productId = $_POST['product_id'] ?? null;
            $titleAr = sanitizeInput($_POST['title_ar']);
            $titleEn = sanitizeInput($_POST['title_en']);
            $descriptionAr = sanitizeInput($_POST['description_ar']);
            $descriptionEn = sanitizeInput($_POST['description_en']);
            $category = sanitizeInput($_POST['category']);
            $price = floatval($_POST['price']);
            $originalPrice = floatval($_POST['original_price'] ?? $price);
            $amazonUrl = sanitizeInput($_POST['amazon_url']);
            $rating = floatval($_POST['rating'] ?? 0);
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

            // Extract Amazon product ID
            preg_match('/\/dp\/([A-Z0-9]+)/', $amazonUrl, $matches);
            $asin = $matches[1] ?? 'PROD_' . time();

            $discount = calculateDiscount($originalPrice, $price);
            $affiliateLink = buildAffiliateLink($amazonUrl);

            if ($productId) {
                // Update existing product
                $stmt = $db->prepare("
                    UPDATE products SET
                        title_ar = :title_ar,
                        title_en = :title_en,
                        description_ar = :description_ar,
                        description_en = :description_en,
                        category = :category,
                        price = :price,
                        original_price = :original_price,
                        discount_percentage = :discount,
                        amazon_url = :amazon_url,
                        affiliate_link = :affiliate_link,
                        rating = :rating,
                        is_featured = :is_featured
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':title_ar' => $titleAr,
                    ':title_en' => $titleEn,
                    ':description_ar' => $descriptionAr,
                    ':description_en' => $descriptionEn,
                    ':category' => $category,
                    ':price' => $price,
                    ':original_price' => $originalPrice,
                    ':discount' => $discount,
                    ':amazon_url' => $amazonUrl,
                    ':affiliate_link' => $affiliateLink,
                    ':rating' => $rating,
                    ':is_featured' => $isFeatured,
                    ':id' => $productId
                ]);

                $message = 'Product updated successfully!';
            } else {
                // Insert new product
                $stmt = $db->prepare("
                    INSERT INTO products (
                        product_id, title_ar, title_en, description_ar, description_en,
                        category, price, original_price, discount_percentage,
                        amazon_url, affiliate_link, rating, is_featured
                    ) VALUES (
                        :asin, :title_ar, :title_en, :description_ar, :description_en,
                        :category, :price, :original_price, :discount,
                        :amazon_url, :affiliate_link, :rating, :is_featured
                    )
                ");

                $stmt->execute([
                    ':asin' => $asin,
                    ':title_ar' => $titleAr,
                    ':title_en' => $titleEn,
                    ':description_ar' => $descriptionAr,
                    ':description_en' => $descriptionEn,
                    ':category' => $category,
                    ':price' => $price,
                    ':original_price' => $originalPrice,
                    ':discount' => $discount,
                    ':amazon_url' => $amazonUrl,
                    ':affiliate_link' => $affiliateLink,
                    ':rating' => $rating,
                    ':is_featured' => $isFeatured
                ]);

                $newProductId = $db->lastInsertId();

                // Handle image URLs
                if (!empty($_POST['image_urls'])) {
                    $imageUrls = explode("\n", $_POST['image_urls']);
                    foreach ($imageUrls as $index => $url) {
                        $url = trim($url);
                        if (!empty($url)) {
                            $stmt = $db->prepare("
                                INSERT INTO product_images (product_id, image_url, is_primary, display_order)
                                VALUES (:product_id, :image_url, :is_primary, :display_order)
                            ");
                            $stmt->execute([
                                ':product_id' => $newProductId,
                                ':image_url' => $url,
                                ':is_primary' => $index === 0 ? 1 : 0,
                                ':display_order' => $index
                            ]);
                        }
                    }
                }

                $message = 'Product added successfully!';
            }

            header('Location: products.php?success=' . urlencode($message));
            exit;
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);

            header('Location: products.php?success=Product deleted successfully');
            exit;
        }
    }
}

// Get products list
$stmt = $db->query("
    SELECT
        p.*,
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as images_count
    FROM products p
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

$pageTitle = 'Products Management';
include 'header.php';
?>

<style>
.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 600;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.products-header h1 {
    font-size: 28px;
}

.btn-add {
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
}

.products-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.product-img-cell {
    width: 80px;
}

.product-img-cell img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.btn-edit,
.btn-delete {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.3s;
}

.btn-edit {
    background: var(--info);
    color: white;
}

.btn-delete {
    background: var(--danger);
    color: white;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    font-size: 24px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
}

.modal-close:hover {
    color: var(--danger);
}
</style>

<?php if (isset($_GET['success'])): ?>
<div class="success-message">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<div class="products-header">
    <h1>📦 Products Management</h1>
    <button class="btn-add" onclick="showAddModal()">➕ Add New Product</button>
</div>

<div class="products-table">
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title (EN)</th>
                <th>Title (AR)</th>
                <th>Category</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td class="product-img-cell">
                    <img src="<?= htmlspecialchars($product['primary_image'] ?? '/assets/images/placeholder.png') ?>" alt="">
                </td>
                <td><?= htmlspecialchars($product['title_en']) ?></td>
                <td><?= htmlspecialchars($product['title_ar']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= formatPrice($product['price']) ?></td>
                <td>
                    <?php if ($product['discount_percentage'] > 0): ?>
                        <span class="badge success"><?= $product['discount_percentage'] ?>%</span>
                    <?php else: ?>
                        <span class="badge secondary">No Discount</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($product['is_featured']): ?>
                        <span class="badge warning">⭐ Featured</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="product-actions">
                        <button class="btn-edit" onclick="editProduct(<?= $product['id'] ?>)">Edit</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Product</h2>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="product_id" id="productId" value="">

            <div class="form-grid">
                <div class="form-group">
                    <label>Title (English) *</label>
                    <input type="text" name="title_en" id="titleEn" required>
                </div>

                <div class="form-group">
                    <label>Title (Arabic) *</label>
                    <input type="text" name="title_ar" id="titleAr" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description (English)</label>
                <textarea name="description_en" id="descriptionEn" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Description (Arabic)</label>
                <textarea name="description_ar" id="descriptionAr" rows="3"></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" id="category" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['slug'] ?>"><?= $cat['name_en'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Rating (0-5)</label>
                    <input type="number" name="rating" id="rating" min="0" max="5" step="0.1" value="0">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Current Price (AED) *</label>
                    <input type="number" name="price" id="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Original Price (AED)</label>
                    <input type="number" name="original_price" id="originalPrice" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label>Amazon Product URL *</label>
                <input type="url" name="amazon_url" id="amazonUrl" required placeholder="https://www.amazon.ae/dp/B0XXXXXX">
            </div>

            <div class="form-group">
                <label>Image URLs (one per line)</label>
                <textarea name="image_urls" id="imageUrls" rows="4" placeholder="https://example.com/image1.jpg
https://example.com/image2.jpg"></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_featured" id="isFeatured">
                    Mark as Featured Product
                </label>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Product</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('productModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productId').value = '';
    document.querySelector('form').reset();
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
}

function editProduct(id) {
    // In a real app, you'd fetch product data via AJAX
    alert('Edit functionality - implement AJAX call to fetch product data');
}
</script>

<?php include 'footer.php'; ?>
