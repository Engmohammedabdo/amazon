<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - PyraStore Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>🛍️ PyraStore</h1>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>

                <a href="products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text">Products</span>
                </a>

                <a href="analytics.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Analytics</span>
                </a>

                <a href="categories.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                    <span class="nav-icon">🏷️</span>
                    <span class="nav-text">Categories</span>
                </a>

                <a href="reviews.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
                    <span class="nav-icon">⭐</span>
                    <span class="nav-text">Reviews</span>
                </a>

                <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>

                <a href="../index.php" class="nav-item" target="_blank">
                    <span class="nav-icon">🌐</span>
                    <span class="nav-text">View Site</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">👤</div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_username']) ?></div>
                        <a href="logout.php" class="logout-link">Logout</a>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
                    <h2><?= $pageTitle ?? 'Dashboard' ?></h2>
                </div>
                <div class="topbar-right">
                    <span class="datetime" id="datetime"></span>
                </div>
            </div>

            <div class="content">
