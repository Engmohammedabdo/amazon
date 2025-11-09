<?php
/**
 * لوحة التحكم الرئيسية - Dashboard
 */

$pageTitle = 'لوحة التحكم الرئيسية';
include '_header.php';

try {
    $db = getDB();

    // إحصائيات عامة
    $totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $totalViews = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'page_view'")->fetchColumn();
    $totalClicks = $db->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'purchase_button_click'")->fetchColumn();

    // حساب معدل التحويل
    $ctr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;

    // أحدث المنتجات
    $latestProducts = $db->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll();

    // النقرات آخر 7 أيام
    $clicksLast7Days = $db->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM analytics_events
        WHERE event_type = 'purchase_button_click'
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}
?>

<div class="page-header">
    <h1>📊 لوحة التحكم</h1>
    <p>نظرة عامة على أداء الموقع</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">إجمالي المنتجات</span>
            <span class="stat-icon">📦</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalProducts); ?></div>
    </div>

    <div class="stat-card info">
        <div class="stat-header">
            <span class="stat-title">إجمالي الزيارات</span>
            <span class="stat-icon">👁️</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalViews); ?></div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <span class="stat-title">إجمالي النقرات</span>
            <span class="stat-icon">🖱️</span>
        </div>
        <div class="stat-value"><?php echo number_format($totalClicks); ?></div>
    </div>

    <div class="stat-card warning">
        <div class="stat-header">
            <span class="stat-title">معدل التحويل</span>
            <span class="stat-icon">📈</span>
        </div>
        <div class="stat-value"><?php echo $ctr; ?>%</div>
    </div>
</div>

<!-- Latest Products -->
<div class="card">
    <div class="card-header">
        <h2>أحدث المنتجات</h2>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>الفئة</th>
                    <th>السعر</th>
                    <th>تاريخ الإضافة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($latestProducts) > 0): ?>
                    <?php foreach ($latestProducts as $product): ?>
                    <tr>
                        <td>
                            <img src="<?php echo clean($product['image_url']); ?>" alt="صورة" class="product-thumb">
                        </td>
                        <td><?php echo clean(truncateText($product['title'], 50)); ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo getCategoryIcon($product['category']); ?> <?php echo getCategoryNameAr($product['category']); ?>
                            </span>
                        </td>
                        <td><?php echo formatPrice($product['price']); ?> درهم</td>
                        <td><?php echo formatDateArabic($product['created_at']); ?></td>
                        <td>
                            <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" target="_blank">عرض</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                            لا توجد منتجات حتى الآن
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Clicks Chart -->
<?php if (count($clicksLast7Days) > 0): ?>
<div class="card">
    <div class="card-header">
        <h2>📊 النقرات - آخر 7 أيام</h2>
    </div>

    <div style="padding: 1rem;">
        <canvas id="clicksChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('clicksChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($clicksLast7Days, 'date')); ?>,
            datasets: [{
                label: 'النقرات',
                data: <?php echo json_encode(array_column($clicksLast7Days, 'count')); ?>,
                borderColor: '#FF6B35',
                backgroundColor: 'rgba(255, 107, 53, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

<?php include '_footer.php'; ?>
