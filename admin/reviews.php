<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'approve') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("UPDATE reviews SET is_approved = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: reviews.php?success=Review approved');
            exit;
        } elseif ($_POST['action'] === 'unapprove') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("UPDATE reviews SET is_approved = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: reviews.php?success=Review unapproved');
            exit;
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: reviews.php?success=Review deleted');
            exit;
        } elseif ($_POST['action'] === 'verify') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("UPDATE reviews SET is_verified = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: reviews.php?success=Review marked as verified');
            exit;
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query
$where = '1=1';
if ($filter === 'pending') {
    $where = 'r.is_approved = 0';
} elseif ($filter === 'approved') {
    $where = 'r.is_approved = 1';
} elseif ($filter === 'verified') {
    $where = 'r.is_verified = 1';
}

// Get reviews
$stmt = $db->query("
    SELECT
        r.*,
        p.title_en as product_title,
        p.title_ar as product_title_ar
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    WHERE $where
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

// Get counts
$counts = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified
    FROM reviews
")->fetch();

$pageTitle = 'Reviews Management';
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

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    background: white;
    padding: 5px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.filter-tab {
    padding: 10px 20px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    color: #666;
}

.filter-tab.active {
    background: var(--primary);
    color: white;
}

.filter-tab:hover {
    background: rgba(102, 126, 234, 0.1);
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: var(--shadow);
    border-left: 4px solid #e0e0e0;
}

.review-card.approved {
    border-left-color: #10b981;
}

.review-card.pending {
    border-left-color: #f59e0b;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.review-info {
    flex: 1;
}

.review-product {
    font-size: 14px;
    color: #999;
    margin-bottom: 5px;
}

.review-customer {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 5px;
}

.review-date {
    font-size: 13px;
    color: #666;
}

.review-meta {
    display: flex;
    gap: 10px;
}

.review-rating {
    display: flex;
    gap: 3px;
    margin-bottom: 15px;
}

.star {
    font-size: 18px;
    color: #fbbf24;
}

.star.empty {
    color: #d1d5db;
}

.review-text {
    font-size: 15px;
    line-height: 1.7;
    color: #555;
    margin-bottom: 15px;
}

.review-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--border);
}

.btn-sm {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-approve {
    background: #10b981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
}

.btn-unapprove {
    background: #f59e0b;
    color: white;
}

.btn-verify {
    background: var(--info);
    color: white;
}

.btn-delete {
    background: var(--danger);
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state h3 {
    color: var(--dark);
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
}
</style>

<?php if (isset($_GET['success'])): ?>
<div class="success-message">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<div class="reviews-header">
    <h1>⭐ Reviews Management</h1>
</div>

<div class="filter-tabs">
    <a href="reviews.php?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
        All (<?= $counts['total'] ?>)
    </a>
    <a href="reviews.php?filter=pending" class="filter-tab <?= $filter === 'pending' ? 'active' : '' ?>">
        Pending (<?= $counts['pending'] ?>)
    </a>
    <a href="reviews.php?filter=approved" class="filter-tab <?= $filter === 'approved' ? 'active' : '' ?>">
        Approved (<?= $counts['approved'] ?>)
    </a>
    <a href="reviews.php?filter=verified" class="filter-tab <?= $filter === 'verified' ? 'active' : '' ?>">
        Verified (<?= $counts['verified'] ?>)
    </a>
</div>

<div style="margin-top: 30px;">
    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">⭐</div>
            <h3>No reviews found</h3>
            <p>There are no reviews matching your filter criteria.</p>
        </div>
    <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card <?= $review['is_approved'] ? 'approved' : 'pending' ?>">
                    <div class="review-header">
                        <div class="review-info">
                            <div class="review-product">
                                📦 <?= htmlspecialchars($review['product_title']) ?>
                            </div>
                            <div class="review-customer">
                                <?= htmlspecialchars($review['customer_name']) ?>
                                <?php if ($review['is_verified']): ?>
                                    <span style="color: #10b981; font-size: 14px;">✓ Verified</span>
                                <?php endif; ?>
                            </div>
                            <div class="review-date">
                                <?= date('F j, Y - g:i A', strtotime($review['created_at'])) ?>
                            </div>
                        </div>

                        <div class="review-meta">
                            <?php if ($review['is_approved']): ?>
                                <span class="badge success">✓ Approved</span>
                            <?php else: ?>
                                <span class="badge warning">⏳ Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $review['rating'] ? '' : 'empty' ?>">
                                <?= $i <= $review['rating'] ? '⭐' : '☆' ?>
                            </span>
                        <?php endfor; ?>
                    </div>

                    <div class="review-text">
                        <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                    </div>

                    <div class="review-actions">
                        <?php if (!$review['is_approved']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn-sm btn-approve">✓ Approve</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="unapprove">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn-sm btn-unapprove">✗ Unapprove</button>
                            </form>
                        <?php endif; ?>

                        <?php if (!$review['is_verified']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="verify">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn-sm btn-verify">✓ Mark as Verified</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $review['id'] ?>">
                            <button type="submit" class="btn-sm btn-delete">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
