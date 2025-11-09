<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();

// Get date range
$period = isset($_GET['period']) ? intval($_GET['period']) : 7;

// Get overview stats
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_clicks,
        COUNT(DISTINCT session_id) as unique_visitors,
        SUM(CASE WHEN click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
        SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks
    FROM click_tracking
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
");
$stmt->execute([':period' => $period]);
$stats = $stmt->fetch();

$conversionRate = $stats['product_clicks'] > 0
    ? round(($stats['purchase_clicks'] / $stats['product_clicks']) * 100, 2)
    : 0;

$ctr = $stats['unique_visitors'] > 0
    ? round(($stats['product_clicks'] / $stats['unique_visitors']) * 100, 2)
    : 0;

// Get top products
$topProducts = $db->prepare("
    SELECT
        p.id,
        p.title_en,
        p.title_ar,
        COUNT(DISTINCT ct.session_id) as unique_views,
        SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
        SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks,
        ROUND(
            (SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) * 100.0 /
            NULLIF(SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END), 0)),
            2
        ) as conversion_rate
    FROM products p
    LEFT JOIN click_tracking ct ON p.id = ct.product_id
        AND ct.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    WHERE p.is_active = 1
    GROUP BY p.id
    HAVING product_clicks > 0
    ORDER BY purchase_clicks DESC
    LIMIT 10
");
$topProducts->execute([':period' => $period]);
$topProductsList = $topProducts->fetchAll();

// Get traffic sources
$trafficSources = $db->prepare("
    SELECT
        COALESCE(utm_source, 'Direct') as source,
        COALESCE(utm_medium, 'None') as medium,
        COUNT(DISTINCT session_id) as visitors,
        SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as conversions
    FROM click_tracking
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    GROUP BY utm_source, utm_medium
    ORDER BY visitors DESC
    LIMIT 10
");
$trafficSources->execute([':period' => $period]);
$trafficList = $trafficSources->fetchAll();

// Get device stats
$deviceStats = $db->prepare("
    SELECT
        device_type,
        COUNT(DISTINCT session_id) as visitors,
        SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as conversions
    FROM click_tracking
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    GROUP BY device_type
    ORDER BY visitors DESC
");
$deviceStats->execute([':period' => $period]);
$deviceList = $deviceStats->fetchAll();

$pageTitle = 'Analytics & Reports';
include 'header.php';
?>

<style>
.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.period-selector select {
    padding: 10px 15px;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
}

.chart-container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}

.chart-container h3 {
    margin-bottom: 20px;
    color: var(--dark);
}

.progress-bar {
    background: var(--light);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transition: width 0.3s;
}
</style>

<div class="analytics-header">
    <h1>📊 Analytics & Reports</h1>

    <div class="period-selector">
        <select onchange="window.location.href='?period=' + this.value">
            <option value="7" <?= $period == 7 ? 'selected' : '' ?>>Last 7 Days</option>
            <option value="30" <?= $period == 30 ? 'selected' : '' ?>>Last 30 Days</option>
            <option value="90" <?= $period == 90 ? 'selected' : '' ?>>Last 90 Days</option>
        </select>
    </div>
</div>

<!-- Overview Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['unique_visitors']) ?></div>
            <div class="stat-label">Unique Visitors</div>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">🖱️</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['product_clicks']) ?></div>
            <div class="stat-label">Product Clicks</div>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">🛒</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($stats['purchase_clicks']) ?></div>
            <div class="stat-label">Purchase Clicks</div>
        </div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon">📈</div>
        <div class="stat-info">
            <div class="stat-value"><?= $conversionRate ?>%</div>
            <div class="stat-label">Conversion Rate</div>
        </div>
    </div>
</div>

<div class="content-grid">
    <!-- Top Products -->
    <div class="card" style="grid-column: 1 / -1;">
        <h2>🔥 Top Performing Products</h2>
        <?php if (empty($topProductsList)): ?>
            <p style="text-align: center; padding: 40px; color: #999;">No data yet</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Views</th>
                    <th>Product Clicks</th>
                    <th>Purchase Clicks</th>
                    <th>Conversion Rate</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProductsList as $product): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($product['title_en']) ?></strong><br>
                        <small style="color: #999;"><?= htmlspecialchars($product['title_ar']) ?></small>
                    </td>
                    <td><?= number_format($product['unique_views']) ?></td>
                    <td><?= number_format($product['product_clicks']) ?></td>
                    <td><?= number_format($product['purchase_clicks']) ?></td>
                    <td>
                        <span class="badge <?= ($product['conversion_rate'] ?? 0) > 5 ? 'success' : 'secondary' ?>">
                            <?= number_format($product['conversion_rate'] ?? 0, 2) ?>%
                        </span>
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min(100, ($product['conversion_rate'] ?? 0) * 10) ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="content-grid">
    <!-- Traffic Sources -->
    <div class="chart-container">
        <h3>📍 Traffic Sources</h3>
        <?php if (empty($trafficList)): ?>
            <p style="text-align: center; padding: 20px; color: #999;">No traffic data</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Medium</th>
                    <th>Visitors</th>
                    <th>Conversions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trafficList as $traffic): ?>
                <tr>
                    <td><?= htmlspecialchars($traffic['source']) ?></td>
                    <td><?= htmlspecialchars($traffic['medium']) ?></td>
                    <td><?= number_format($traffic['visitors']) ?></td>
                    <td><?= number_format($traffic['conversions']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Device Stats -->
    <div class="chart-container">
        <h3>📱 Device Breakdown</h3>
        <?php if (empty($deviceList)): ?>
            <p style="text-align: center; padding: 20px; color: #999;">No device data</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Visitors</th>
                    <th>Conversions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceList as $device): ?>
                <tr>
                    <td>
                        <?php
                        $icon = $device['device_type'] === 'mobile' ? '📱' : ($device['device_type'] === 'tablet' ? '📲' : '💻');
                        echo $icon . ' ' . ucfirst($device['device_type']);
                        ?>
                    </td>
                    <td><?= number_format($device['visitors']) ?></td>
                    <td><?= number_format($device['conversions']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
