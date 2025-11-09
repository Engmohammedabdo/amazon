<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();

// Get quick stats
$totalProducts = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch()['count'];
$totalClicks = $db->query("SELECT COUNT(*) as count FROM click_tracking WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['count'];
$totalPurchaseClicks = $db->query("SELECT COUNT(*) as count FROM click_tracking WHERE click_type = 'purchase_click' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['count'];
$totalViews = $db->query("SELECT COUNT(DISTINCT session_id) as count FROM click_tracking WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['count'];

// Get top products
$topProducts = $db->query("
    SELECT
        p.id,
        p.title_en,
        p.title_ar,
        SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks,
        SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks
    FROM products p
    LEFT JOIN click_tracking ct ON p.id = ct.product_id
        AND ct.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    WHERE p.is_active = 1
    GROUP BY p.id
    HAVING product_clicks > 0
    ORDER BY purchase_clicks DESC, product_clicks DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
include 'header.php';
?>

<div class="dashboard">
    <h1>📊 Dashboard Overview</h1>
    <p class="subtitle">Last 7 days performance</p>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($totalProducts) ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($totalViews) ?></div>
                <div class="stat-label">Unique Visitors</div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon">🖱️</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($totalClicks) ?></div>
                <div class="stat-label">Total Clicks</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon">🛒</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($totalPurchaseClicks) ?></div>
                <div class="stat-label">Purchase Clicks</div>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <h2>🔥 Top Performing Products</h2>
            <?php if (empty($topProducts)): ?>
                <p style="text-align: center; padding: 40px; color: #999;">No data yet</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Product Clicks</th>
                            <th>Purchase Clicks</th>
                            <th>Conversion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $product): ?>
                            <?php
                            $conversionRate = $product['product_clicks'] > 0
                                ? round(($product['purchase_clicks'] / $product['product_clicks']) * 100, 2)
                                : 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($product['title_en']) ?></strong><br>
                                    <small style="color: #999;"><?= htmlspecialchars($product['title_ar']) ?></small>
                                </td>
                                <td><?= number_format($product['product_clicks']) ?></td>
                                <td><?= number_format($product['purchase_clicks']) ?></td>
                                <td>
                                    <span class="badge <?= $conversionRate > 5 ? 'success' : ($conversionRate > 2 ? 'warning' : 'secondary') ?>">
                                        <?= $conversionRate ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>⚡ Quick Actions</h2>
            <div class="quick-actions">
                <a href="products.php?action=add" class="action-btn blue">
                    <span class="action-icon">➕</span>
                    <span class="action-text">Add New Product</span>
                </a>
                <a href="products.php" class="action-btn green">
                    <span class="action-icon">📦</span>
                    <span class="action-text">Manage Products</span>
                </a>
                <a href="analytics.php" class="action-btn purple">
                    <span class="action-icon">📊</span>
                    <span class="action-text">View Analytics</span>
                </a>
                <a href="settings.php" class="action-btn orange">
                    <span class="action-icon">⚙️</span>
                    <span class="action-text">Settings</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
