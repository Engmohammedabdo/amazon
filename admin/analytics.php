<?php
/**
 * صفحة الإحصائيات والتحليلات
 */

$pageTitle = 'الإحصائيات والتحليلات';
include '_header.php';

try {
    $db = getDB();

    // Filter by date
    $period = $_GET['period'] ?? 'week';
    $dateFilter = match($period) {
        'today' => "DATE(a.created_at) = CURDATE()",
        'week' => "a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
        'month' => "a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        default => "1=1"
    };

    // الإحصائيات العامة
    $totalEvents = $db->query("SELECT COUNT(*) FROM analytics_events a WHERE $dateFilter")->fetchColumn();
    $pageViews = $db->query("SELECT COUNT(*) FROM analytics_events a WHERE a.event_type = 'page_view' AND $dateFilter")->fetchColumn();
    $productClicks = $db->query("SELECT COUNT(*) FROM analytics_events a WHERE a.event_type = 'product_click' AND $dateFilter")->fetchColumn();
    $purchaseClicks = $db->query("SELECT COUNT(*) FROM analytics_events a WHERE a.event_type = 'purchase_button_click' AND $dateFilter")->fetchColumn();

    // أكثر المنتجات نقراً
    $topProducts = $db->query("
        SELECT p.*, COUNT(a.id) as clicks
        FROM products p
        LEFT JOIN analytics_events a ON p.id = a.product_id AND a.event_type = 'purchase_button_click' AND $dateFilter
        GROUP BY p.id
        ORDER BY clicks DESC
        LIMIT 10
    ")->fetchAll();

    // معدل التحويل لكل منتج
    $conversionRates = [];
    foreach ($topProducts as $product) {
        // استخدام prepared statement لتجنب SQL injection
        $viewsQuery = "SELECT COUNT(*) FROM analytics_events a WHERE a.product_id = ? AND a.event_type = 'product_click' AND $dateFilter";
        $stmt = $db->prepare($viewsQuery);
        $stmt->execute([$product['id']]);
        $views = $stmt->fetchColumn();

        $clicks = $product['clicks'];
        $rate = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
        $conversionRates[$product['id']] = ['views' => $views, 'clicks' => $clicks, 'rate' => $rate];
    }

} catch (Exception $e) {
    error_log("Analytics Page Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">حدث خطأ أثناء تحميل الإحصائيات: ' . htmlspecialchars($e->getMessage()) . '</div>';

    // تعيين قيم افتراضية
    $totalEvents = $pageViews = $productClicks = $purchaseClicks = 0;
    $topProducts = [];
    $conversionRates = [];
    $period = 'week';
}
?>

<div class="page-header">
    <h1>📈 الإحصائيات والتحليلات</h1>
    <p>تحليل أداء الموقع والمنتجات</p>
</div>

<!-- Period Filter -->
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; gap: 0.5rem;">
        <a href="?period=today" class="btn <?php echo $period === 'today' ? 'btn-primary' : ''; ?>" style="<?php echo $period !== 'today' ? 'background: #6B7280; color: white;' : ''; ?>">اليوم</a>
        <a href="?period=week" class="btn <?php echo $period === 'week' ? 'btn-primary' : ''; ?>" style="<?php echo $period !== 'week' ? 'background: #6B7280; color: white;' : ''; ?>">آخر 7 أيام</a>
        <a href="?period=month" class="btn <?php echo $period === 'month' ? 'btn-primary' : ''; ?>" style="<?php echo $period !== 'month' ? 'background: #6B7280; color: white;' : ''; ?>">آخر 30 يوم</a>
        <a href="?period=all" class="btn <?php echo $period === 'all' ? 'btn-primary' : ''; ?>" style="<?php echo $period !== 'all' ? 'background: #6B7280; color: white;' : ''; ?>">الكل</a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">إجمالي الأحداث</span>
            <span class="stat-icon">📊</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalEvents); ?></div>
    </div>

    <div class="stat-card info">
        <div class="stat-header">
            <span class="stat-title">مشاهدات الصفحة</span>
            <span class="stat-icon">👁️</span>
        </div>
        <div class="stat-value"><?php echo number_format($pageViews); ?></div>
    </div>

    <div class="stat-card warning">
        <div class="stat-header">
            <span class="stat-title">نقرات المنتجات</span>
            <span class="stat-icon">🖱️</span>
        </div>
        <div class="stat-value"><?php echo number_format($productClicks); ?></div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">نقرات الشراء</span>
            <span class="stat-icon">🛒</span>
        </div>
        <div class="stat-value"><?php echo number_format($purchaseClicks); ?></div>
    </div>
</div>

<!-- Top Products -->
<div class="card">
    <div class="card-header">
        <h2>أكثر 10 منتجات نقراً</h2>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>الصورة</th>
                    <th>المنتج</th>
                    <th>المشاهدات</th>
                    <th>النقرات</th>
                    <th>معدل التحويل</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; ?>
                <?php foreach ($topProducts as $p): ?>
                    <?php $stats = $conversionRates[$p['id']]; ?>
                    <tr>
                        <td><strong><?php echo $rank++; ?></strong></td>
                        <td><img src="<?php echo clean($p['image_url']); ?>" class="product-thumb"></td>
                        <td><?php echo clean(truncateText($p['title'], 50)); ?></td>
                        <td><?php echo number_format($stats['views']); ?></td>
                        <td><?php echo number_format($stats['clicks']); ?></td>
                        <td>
                            <span class="badge badge-success"><?php echo $stats['rate']; ?>%</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '_footer.php'; ?>
