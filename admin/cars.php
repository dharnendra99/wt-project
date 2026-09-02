<?php
/**
 * AutoPulse - Admin: Cars List
 */

require_once __DIR__ . '/includes/admin_auth.php';

$stmt = $pdo->query("SELECT c.*, b.name AS brand_name 
                     FROM cars c 
                     LEFT JOIN brands b ON c.brand_id = b.id 
                     ORDER BY c.id DESC");
$cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - AutoPulse Admin</title>
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
            <li class="admin-nav-item"><a href="index.php">&#9881; Dashboard</a></li>
            <li class="admin-nav-item active"><a href="cars.php">&#128663; Manage Cars</a></li>
            <li class="admin-nav-item"><a href="news.php">&#128240; Manage News</a></li>
            <li class="admin-nav-item"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 900;">Manage Car Models</h1>
                <p style="font-size: 13px; color: #6b7280;"><?= count($cars) ?> cars currently in the catalog</p>
            </div>
            <a href="car-add.php" class="btn-card-action" style="background: var(--primary-red); padding: 10px 20px;">+ Add New Car</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div style="padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px; overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Model Name</th>
                        <th>Brand</th>
                        <th>Body / Fuel</th>
                        <th>Price Range</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td style="width: 70px;">
                                <img src="../<?= htmlspecialchars($car['featured_image']) ?>" alt="" style="width: 60px; height: 38px; object-fit: cover; border-radius: 2px;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($car['name']) ?></strong>
                                <div style="font-size: 11px; color: #6b7280;">slug: <?= htmlspecialchars($car['slug']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($car['brand_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($car['body_type']) ?> • <?= htmlspecialchars($car['fuel_type']) ?></td>
                            <td style="font-weight: 700; color: var(--primary-red);">
                                <?= format_car_price($car['price_min'], $car['price_max']) ?>
                            </td>
                            <td><span class="badge-tag" style="font-size: 10px;"><?= htmlspecialchars($car['status']) ?></span></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="car-edit.php?id=<?= $car['id'] ?>" style="color: #2563eb; font-weight: 700;">Edit</a>
                                    <span>|</span>
                                    <a href="car-delete.php?id=<?= $car['id'] ?>" onclick="return confirm('Are you sure you want to delete this car model?');" style="color: #dc2626; font-weight: 700;">Delete</a>
                                    <span>|</span>
                                    <a href="../car-detail.php?slug=<?= urlencode($car['slug']) ?>" target="_blank" style="color: #4b5563;">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
