<?php
/**
 * AutoPulse - Car Listings Page
 * Features sidebar filtering (Brand, Body Type, Fuel, Price Range), sort options, and instant AJAX updates.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'cars';
$page_title = 'Find New Cars in India - Prices, Specs & Offers';

// Fetch all brands for filter
$brands_stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$all_brands = $brands_stmt->fetchAll();

// Fetch unique body types and fuel types
$body_types = ['SUV', 'Sedan', 'Hatchback', 'EV', 'Luxury', 'MUV'];
$fuel_types = ['Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG'];

// Initial cars query
$where = ["1=1"];
$params = [];

if (!empty($_GET['status'])) {
    $where[] = "c.status = ?";
    $params[] = $_GET['status'];
    if ($_GET['status'] === 'Upcoming') {
        $current_page = 'upcoming';
        $page_title = 'Upcoming Cars in India 2024-2025 - Expected Prices & Launch Dates';
    }
}

if (!empty($_GET['body_type'])) {
    $where[] = "c.body_type = ?";
    $params[] = $_GET['body_type'];
}

if (!empty($_GET['fuel_type'])) {
    $where[] = "c.fuel_type = ?";
    $params[] = $_GET['fuel_type'];
}

$whereClause = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name 
                       FROM cars c 
                       LEFT JOIN brands b ON c.brand_id = b.id 
                       WHERE {$whereClause} 
                       ORDER BY c.price_min ASC");
$stmt->execute($params);
$cars = $stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">
        
        <!-- Breadcrumb & Header -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; <span>New Cars</span>
        </div>

        <div class="section-header" style="margin-bottom: 16px;">
            <h1 class="section-title">Explore New <span class="accent">Cars</span></h1>
            <span class="meta-text"><span id="resultCount"><?= count($cars) ?></span> Models Found</span>
        </div>

        <div class="listing-layout">
            <!-- Sidebar Filters -->
            <aside class="filter-sidebar">
                <div class="filter-header">
                    <h3>Filters</h3>
                    <button type="button" id="resetFiltersBtn" class="btn-reset-filters">Clear All</button>
                </div>

                <form id="carFilterForm">
                    <!-- Brand Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">Brand</h4>
                        <div class="filter-options-list">
                            <?php foreach ($all_brands as $brand): ?>
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="brand[]" value="<?= htmlspecialchars($brand['slug']) ?>">
                                    <span><?= htmlspecialchars($brand['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Body Type Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">Body Type</h4>
                        <div class="filter-options-list">
                            <?php foreach ($body_types as $bt): ?>
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="body_type[]" value="<?= $bt ?>" <?= (isset($_GET['body_type']) && $_GET['body_type'] === $bt) ? 'checked' : '' ?>>
                                    <span><?= $bt ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Fuel Type Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">Fuel Type</h4>
                        <div class="filter-options-list">
                            <?php foreach ($fuel_types as $ft): ?>
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="fuel_type[]" value="<?= $ft ?>" <?= (isset($_GET['fuel_type']) && $_GET['fuel_type'] === $ft) ? 'checked' : '' ?>>
                                    <span><?= $ft ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Bracket Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">Budget</h4>
                        <div class="filter-options-list">
                            <label class="custom-checkbox">
                                <input type="radio" name="price_bracket" value="" checked>
                                <span>All Price Brackets</span>
                            </label>
                            <label class="custom-checkbox">
                                <input type="radio" name="price_bracket" value="under_10">
                                <span>Under Rs 10 Lakh</span>
                            </label>
                            <label class="custom-checkbox">
                                <input type="radio" name="price_bracket" value="10_to_20">
                                <span>Rs 10 - 20 Lakh</span>
                            </label>
                            <label class="custom-checkbox">
                                <input type="radio" name="price_bracket" value="20_to_50">
                                <span>Rs 20 - 50 Lakh</span>
                            </label>
                            <label class="custom-checkbox">
                                <input type="radio" name="price_bracket" value="above_50">
                                <span>Above Rs 50 Lakh (Luxury)</span>
                            </label>
                        </div>
                    </div>
                </form>
            </aside>

            <!-- Cars Result Column -->
            <section class="listing-results">
                <!-- Sort Bar -->
                <div class="active-filters-bar">
                    <span style="font-size: 13px; font-weight: 700; text-transform: uppercase;">Showing Available &amp; Upcoming Models</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="carSortSelect" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Sort by:</label>
                        <select id="carSortSelect" name="sort" form="carFilterForm" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 13px;">
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="name">Name: A to Z</option>
                            <option value="newest">Latest Launches</option>
                        </select>
                    </div>
                </div>

                <!-- Cars Card Grid -->
                <div class="cars-grid" id="carsResultGrid">
                    <?php if (empty($cars)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 48px; background: #fff; border: 1px solid var(--border-color); border-radius: 4px;">
                            <h3 style="font-size: 20px; color: var(--text-dark); margin-bottom: 8px;">No matching cars found</h3>
                            <p style="color: var(--text-muted);">Try adjusting or clearing your filters to see more results.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cars as $car): ?>
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

    </div>
</main>

<script src="assets/js/ajax-filter.js"></script>
<?php include_once __DIR__ . '/includes/footer.php'; ?>
