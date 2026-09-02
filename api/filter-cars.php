<?php
/**
 * AutoPulse - AJAX Car Filter Endpoint
 * Returns filtered car cards as JSON HTML response without page reloads.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

$where = ["1=1"];
$params = [];

// Filter by Brands
if (!empty($_GET['brand'])) {
    $brands = (array)$_GET['brand'];
    $brandPlaceholders = implode(',', array_fill(0, count($brands), '?'));
    $where[] = "b.slug IN ($brandPlaceholders)";
    foreach ($brands as $b) $params[] = $b;
}

// Filter by Body Types
if (!empty($_GET['body_type'])) {
    $bodyTypes = (array)$_GET['body_type'];
    $bodyPlaceholders = implode(',', array_fill(0, count($bodyTypes), '?'));
    $where[] = "c.body_type IN ($bodyPlaceholders)";
    foreach ($bodyTypes as $bt) $params[] = $bt;
}

// Filter by Fuel Types
if (!empty($_GET['fuel_type'])) {
    $fuelTypes = (array)$_GET['fuel_type'];
    $fuelPlaceholders = implode(',', array_fill(0, count($fuelTypes), '?'));
    $where[] = "c.fuel_type IN ($fuelPlaceholders)";
    foreach ($fuelTypes as $ft) $params[] = $ft;
}

// Filter by Status
if (!empty($_GET['status'])) {
    $where[] = "c.status = ?";
    $params[] = $_GET['status'];
}

// Filter by Price Range
if (!empty($_GET['price_bracket'])) {
    $bracket = $_GET['price_bracket'];
    if ($bracket === 'under_10') {
        $where[] = "c.price_min < 10.00";
    } elseif ($bracket === '10_to_20') {
        $where[] = "c.price_min >= 10.00 AND c.price_min <= 20.00";
    } elseif ($bracket === '20_to_50') {
        $where[] = "c.price_min >= 20.00 AND c.price_min <= 50.00";
    } elseif ($bracket === 'above_50') {
        $where[] = "c.price_min > 50.00";
    }
}

// Order By
$orderBy = "c.price_min ASC";
if (!empty($_GET['sort'])) {
    if ($_GET['sort'] === 'price_desc') $orderBy = "c.price_max DESC";
    if ($_GET['sort'] === 'name') $orderBy = "c.name ASC";
    if ($_GET['sort'] === 'newest') $orderBy = "c.id DESC";
}

$whereClause = implode(' AND ', $where);
$sql = "SELECT c.*, b.name AS brand_name 
        FROM cars c 
        LEFT JOIN brands b ON c.brand_id = b.id 
        WHERE {$whereClause} 
        ORDER BY {$orderBy}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

ob_start();
if (empty($cars)): ?>
    <div style="grid-column: 1/-1; text-align: center; padding: 48px; background: #fff; border: 1px solid var(--border-color); border-radius: 4px;">
        <h3 style="font-size: 20px; color: var(--text-dark); margin-bottom: 8px;">No matching cars found</h3>
        <p style="color: var(--text-muted);">Try adjusting or clearing your filters to see more results.</p>
    </div>
<?php else:
    foreach ($cars as $car): ?>
        <div class="car-card">
            <div class="car-card-media">
                <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" loading="lazy">
                <span class="car-badge-status <?= strtolower($car['status']) ?>"><?= htmlspecialchars($car['status']) ?></span>
                <button class="car-wishlist-btn <?= is_in_wishlist($pdo, $car['id']) ? 'active' : '' ?>" data-car-id="<?= $car['id'] ?>" title="Save to Wishlist">
                    <?= is_in_wishlist($pdo, $car['id']) ? '&#9829;' : '&#9825;' ?>
                </button>
            </div>
            <div class="car-card-body">
                <span class="car-card-brand"><?= htmlspecialchars($car['brand_name'] ?? 'Automobile') ?></span>
                <h3 class="car-card-title">
                    <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>"><?= htmlspecialchars($car['name']) ?></a>
                </h3>
                <div class="car-card-specs">
                    <span><?= htmlspecialchars($car['fuel_type']) ?></span> •
                    <span><?= htmlspecialchars($car['transmission']) ?></span> •
                    <span><?= htmlspecialchars($car['mileage']) ?></span>
                </div>
                <div class="car-card-footer">
                    <div class="car-price-block">
                        <span class="price-text"><?= format_car_price($car['price_min'], $car['price_max']) ?></span>
                        <span class="price-label"><?= htmlspecialchars($car['price_label']) ?></span>
                    </div>
                    <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>" class="btn-card-action">Explore</a>
                </div>
            </div>
        </div>
    <?php endforeach;
endif;
$html = ob_get_clean();

echo json_encode([
    'count' => count($cars),
    'html'  => $html
]);
