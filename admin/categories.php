<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Add new category
            $nameAr = $_POST['name_ar'];
            $nameEn = $_POST['name_en'];
            $slug = $_POST['slug'];
            $icon = $_POST['icon'];
            $color = $_POST['color'];
            $displayOrder = intval($_POST['display_order']);

            $stmt = $db->prepare("
                INSERT INTO categories (name_ar, name_en, slug, icon, color, display_order)
                VALUES (:name_ar, :name_en, :slug, :icon, :color, :display_order)
            ");

            $stmt->execute([
                ':name_ar' => $nameAr,
                ':name_en' => $nameEn,
                ':slug' => $slug,
                ':icon' => $icon,
                ':color' => $color,
                ':display_order' => $displayOrder
            ]);

            header('Location: categories.php?success=Category added successfully');
            exit;

        } elseif ($_POST['action'] === 'edit') {
            // Edit category
            $id = intval($_POST['id']);
            $nameAr = $_POST['name_ar'];
            $nameEn = $_POST['name_en'];
            $slug = $_POST['slug'];
            $icon = $_POST['icon'];
            $color = $_POST['color'];
            $displayOrder = intval($_POST['display_order']);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $stmt = $db->prepare("
                UPDATE categories SET
                    name_ar = :name_ar,
                    name_en = :name_en,
                    slug = :slug,
                    icon = :icon,
                    color = :color,
                    display_order = :display_order,
                    is_active = :is_active
                WHERE id = :id
            ");

            $stmt->execute([
                ':name_ar' => $nameAr,
                ':name_en' => $nameEn,
                ':slug' => $slug,
                ':icon' => $icon,
                ':color' => $color,
                ':display_order' => $displayOrder,
                ':is_active' => $isActive,
                ':id' => $id
            ]);

            header('Location: categories.php?success=Category updated successfully');
            exit;

        } elseif ($_POST['action'] === 'delete') {
            // Delete category
            $id = intval($_POST['id']);

            // Check if category has products
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category = (SELECT slug FROM categories WHERE id = :id)");
            $stmt->execute([':id' => $id]);
            $count = $stmt->fetch()['count'];

            if ($count > 0) {
                header('Location: categories.php?error=Cannot delete category with products');
                exit;
            }

            $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $id]);

            header('Location: categories.php?success=Category deleted successfully');
            exit;
        }
    }
}

// Get all categories
$stmt = $db->query("
    SELECT
        c.*,
        COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.slug = p.category AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.name_en ASC
");
$categories = $stmt->fetchAll();

$pageTitle = 'Categories Management';
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

.error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 600;
}

.categories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.category-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: var(--shadow);
    transition: all 0.3s;
    border-left: 4px solid;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.category-icon {
    font-size: 36px;
}

.category-actions {
    display: flex;
    gap: 5px;
}

.btn-icon {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
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

.category-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 5px;
}

.category-name-ar {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.category-slug {
    font-size: 12px;
    color: #999;
    background: var(--light);
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    margin-bottom: 10px;
}

.category-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid var(--border);
}

.product-count {
    font-size: 13px;
    color: #666;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
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
    max-width: 500px;
    width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
}

.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 2px solid var(--border);
}
</style>

<?php if (isset($_GET['success'])): ?>
<div class="success-message">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="error-message">
    ❌ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<div class="categories-header">
    <h1>🏷️ Categories Management</h1>
    <button class="btn btn-primary" onclick="showAddModal()">➕ Add New Category</button>
</div>

<div class="categories-grid">
    <?php foreach ($categories as $category): ?>
    <div class="category-card" style="border-left-color: <?= htmlspecialchars($category['color']) ?>">
        <div class="category-header">
            <div class="category-icon"><?= htmlspecialchars($category['icon']) ?></div>
            <div class="category-actions">
                <button class="btn-icon btn-edit" onclick='editCategory(<?= json_encode($category, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This will delete the category.')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                    <button type="submit" class="btn-icon btn-delete" <?= $category['product_count'] > 0 ? 'disabled title="Cannot delete category with products"' : '' ?>>🗑️</button>
                </form>
            </div>
        </div>

        <div class="category-name"><?= htmlspecialchars($category['name_en']) ?></div>
        <div class="category-name-ar"><?= htmlspecialchars($category['name_ar']) ?></div>
        <div class="category-slug">slug: <?= htmlspecialchars($category['slug']) ?></div>

        <div class="category-stats">
            <div class="product-count">📦 <?= $category['product_count'] ?> products</div>
            <div class="status-badge <?= $category['is_active'] ? 'active' : 'inactive' ?>">
                <?= $category['is_active'] ? '✓ Active' : '✗ Inactive' ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Category</h2>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="categoryId" value="">

            <div class="form-group">
                <label>Name (English) *</label>
                <input type="text" name="name_en" id="nameEn" required>
            </div>

            <div class="form-group">
                <label>Name (Arabic) *</label>
                <input type="text" name="name_ar" id="nameAr" required>
            </div>

            <div class="form-group">
                <label>Slug *</label>
                <input type="text" name="slug" id="slug" required placeholder="electronics">
                <small>URL-friendly name (lowercase, no spaces)</small>
            </div>

            <div class="form-group">
                <label>Icon (Emoji)</label>
                <input type="text" name="icon" id="icon" placeholder="📱" maxlength="2">
                <small>Use emoji picker or paste emoji</small>
            </div>

            <div class="form-group">
                <label>Color</label>
                <div class="color-picker-wrapper">
                    <input type="color" name="color" id="color" value="#FF9900">
                    <div class="color-preview" id="colorPreview"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" id="displayOrder" value="0" min="0">
                <small>Lower numbers appear first</small>
            </div>

            <div class="form-group" id="activeCheckboxGroup" style="display: none;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="isActive" checked>
                    Active
                </label>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Category</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Update color preview
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('colorPreview').style.backgroundColor = this.value;
});

// Auto-generate slug from English name
document.getElementById('nameEn').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
});

function showAddModal() {
    document.getElementById('categoryModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('formAction').value = 'add';
    document.getElementById('categoryId').value = '';
    document.getElementById('activeCheckboxGroup').style.display = 'none';
    document.querySelector('form').reset();
    document.getElementById('colorPreview').style.backgroundColor = '#FF9900';
}

function editCategory(category) {
    document.getElementById('categoryModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('nameEn').value = category.name_en;
    document.getElementById('nameAr').value = category.name_ar;
    document.getElementById('slug').value = category.slug;
    document.getElementById('icon').value = category.icon;
    document.getElementById('color').value = category.color;
    document.getElementById('displayOrder').value = category.display_order;
    document.getElementById('isActive').checked = category.is_active == 1;
    document.getElementById('activeCheckboxGroup').style.display = 'block';
    document.getElementById('colorPreview').style.backgroundColor = category.color;
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'footer.php'; ?>
