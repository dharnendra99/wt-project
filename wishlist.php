<?php
/**
 * AutoPulse - User Wishlist / Saved Garage
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php?ref=wishlist.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Handle direct item removal
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND car_id = ?");
    $del->execute([$user_id, $remove_id]);
    header('Location: wishlist.php');
    exit;
}

// Fetch user's wishlist cars
$stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name 
                       FROM wishlist w 
                       JOIN cars c ON w.car_id = c.id 
                       LEFT JOIN brands b ON c.brand_id = b.id 
                       WHERE w.user_id = ? 
                       ORDER BY w.created_at DESC");
$stmt->execute([$user_id]);
$saved_cars = $stmt->fetchAll();

$current_page = 'wishlist';
$page_title = 'My Saved Cars & Wishlist';

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; <span>My Wishlist</span>
        </div>

        <div class="section-header">
            <h1 class="section-title">My Saved <span class="accent">Garage</span></h1>
            <span class="meta-text"><?= count($saved_cars) ?> Cars Bookmarked</span>
        </div>

        <?php if (empty($saved_cars)): ?>
            <div style="padding: 60px; background: #fff; border: 1px solid var(--border-color); text-align: center; border-radius: 4px; margin-bottom: 50px;">
                <div style="font-size: 44px; color: var(--border-color); margin-bottom: 12px;">&#9825;</div>
                <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">Your saved garage is empty</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">Browse our car listings and tap the heart icon on any car to bookmark it for later comparison.</p>
                <a href="cars.php" class="btn-card-action" style="background: var(--primary-red); padding: 12px 24px;">Browse Cars Now</a>
            </div>
        <?php else: ?>
            <div class="cars-grid" style="margin-bottom: 48px;">
                <?php foreach ($saved_cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-media">
                            <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                            <span class="car-badge-status <?= strtolower($car['status']) ?>"><?= htmlspecialchars($car['status']) ?></span>
                            <a href="wishlist.php?remove=<?= $car['id'] ?>" class="car-wishlist-btn active" title="Remove from Wishlist" style="text-decoration: none;">
                                &#10005;
                            </a>
                        </div>
                        <div class="car-card-body">
                            <span class="car-card-brand"><?= htmlspecialchars($car['brand_name'] ?? 'Automobile') ?></span>
                            <h3 class="car-card-title">
                                <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>"><?= htmlspecialchars($car['name']) ?></a>
                            </h3>
                            <div class="car-card-specs">
                                <span><?= htmlspecialchars($car['fuel_type']) ?></span> •
                                <span><?= htmlspecialchars($car['mileage']) ?></span>
                            </div>
                            <div class="car-card-footer">
                                <div class="car-price-block">
                                    <span class="price-text"><?= format_car_price($car['price_min'], $car['price_max']) ?></span>
                                    <span class="price-label"><?= htmlspecialchars($car['price_label']) ?></span>
                                </div>
                                <div style="display:flex; gap:6px;">
                                    <a href="compare.php?car1=<?= $car['id'] ?>" class="btn-card-action" style="background:#475569; font-size:11px; padding:8px 10px;">Compare</a>
                                    <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>" class="btn-card-action" style="font-size:11px; padding:8px 12px;">Specs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
