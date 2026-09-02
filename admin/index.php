<?php
/**
 * AutoPulse - Admin Dashboard
 */

require_once __DIR__ . '/includes/admin_auth.php';

// Fetch summary metrics
$cars_count = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$news_count = $pdo->query("SELECT COUNT(*) FROM news_articles")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$reviews_count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$comments_count = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Fetch recent cars
$recent_cars = $pdo->query("SELECT c.*, b.name AS brand_name FROM cars c LEFT JOIN brands b ON c.brand_id = b.id ORDER BY c.id DESC LIMIT 5")->fetchAll();

// Fetch recent news
$recent_news = $pdo->query("SELECT * FROM news_articles ORDER BY published_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoPulse Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="admin-wrap">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            AUTO<span>PULSE</span> CMS
        </div>
        <ul class="admin-nav-list">
            <li class="admin-nav-item active"><a href="index.php">&#9881; Dashboard</a></li>
            <li class="admin-nav-item"><a href="cars.php">&#128663; Manage Cars</a></li>
            <li class="admin-nav-item"><a href="news.php">&#128240; Manage News</a></li>
            <li class="admin-nav-item"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 900; color: #111827;">Editorial Control Center</h1>
                <p style="font-size: 13px; color: #6b7280;">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="car-add.php" class="btn-card-action" style="background: var(--primary-red); padding: 10px 18px;">+ Add Car</a>
                <a href="news-add.php" class="btn-card-action" style="background: #111827; padding: 10px 18px;">+ New Article</a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="admin-stat-grid">
            <div class="stat-card">
                <span class="meta-text">Total Car Database</span>
                <div class="num"><?= $cars_count ?></div>
            </div>
            <div class="stat-card">
                <span class="meta-text">Published Articles</span>
                <div class="num"><?= $news_count ?></div>
            </div>
            <div class="stat-card">
                <span class="meta-text">User Community</span>
                <div class="num"><?= $users_count ?></div>
            </div>
            <div class="stat-card">
                <span class="meta-text">Reviews &amp; Comments</span>
                <div class="num"><?= (int)$reviews_count + (int)$comments_count ?></div>
            </div>
        </div>

        <!-- Quick Actions & Recent Tables -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Recent Cars -->
            <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3 style="font-size: 16px; font-weight: 800; text-transform: uppercase;">Recent Car Models</h3>
                    <a href="cars.php" style="font-size: 12px; color: var(--primary-red); font-weight: 700;">View All &rarr;</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Fuel</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cars as $c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['fuel_type']) ?></td>
                                <td><?= format_car_price($c['price_min'], $c['price_max']) ?></td>
                                <td><a href="car-edit.php?id=<?= $c['id'] ?>" style="color: var(--primary-red); font-weight: 700;">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Articles -->
            <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3 style="font-size: 16px; font-weight: 800; text-transform: uppercase;">Recent Articles</h3>
                    <a href="news.php" style="font-size: 12px; color: var(--primary-red); font-weight: 700;">View All &rarr;</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_news as $n): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars(substr($n['title'], 0, 32)) ?>...</strong></td>
                                <td><span class="badge-tag" style="font-size: 9px;"><?= htmlspecialchars($n['category']) ?></span></td>
                                <td style="font-size: 11px; color: #6b7280;"><?= date('M j', strtotime($n['published_at'])) ?></td>
                                <td><a href="news-edit.php?id=<?= $n['id'] ?>" style="color: var(--primary-red); font-weight: 700;">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
