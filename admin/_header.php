<?php
/**
 * Header المشترك للوحة التحكم
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'لوحة التحكم'; ?> - PYRASTORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>PYRASTORE</h2>
                <p>لوحة التحكم</p>
            </div>

            <nav class="sidebar-nav">
                <a href="/admin/index.php" class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <span>📊</span>
                    <span>لوحة التحكم</span>
                </a>
                <a href="/admin/products.php" class="nav-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
                    <span>📦</span>
                    <span>المنتجات</span>
                </a>
                <a href="/admin/analytics.php" class="nav-link <?php echo $currentPage === 'analytics' ? 'active' : ''; ?>">
                    <span>📈</span>
                    <span>الإحصائيات</span>
                </a>
                <a href="/admin/settings.php" class="nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <span>⚙️</span>
                    <span>الإعدادات</span>
                </a>
                <a href="/" class="nav-link" target="_blank">
                    <span>🌐</span>
                    <span>عرض الموقع</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <p style="color: #999; margin-bottom: 0.5rem; font-size: 0.85rem;">
                    مرحباً، <?php echo clean($_SESSION['admin_username']); ?>
                </p>
                <form method="POST" action="/admin/logout.php">
                    <button type="submit" class="btn-logout">🚪 تسجيل الخروج</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
