<?php
/**
 * AutoPulse - Compare Cars Tool
 * Side-by-side comparison of 2 or 3 cars across price, dimensions, powertrains, and safety ratings.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'compare';
$page_title = 'Compare Cars in India - Price, Specs, Mileage & Features';

// Fetch all cars for selection dropdowns
$stmt = $pdo->query("SELECT c.id, c.name, c.price_min, c.price_max, b.name AS brand_name 
                     FROM cars c 
                     LEFT JOIN brands b ON c.brand_id = b.id 
                     ORDER BY c.name ASC");
$all_cars = $stmt->fetchAll();

// Get selected car IDs from query parameters or default to first 2 cars
$car1_id = (int)($_GET['car1'] ?? ($all_cars[0]['id'] ?? 1));
$car2_id = (int)($_GET['car2'] ?? ($all_cars[1]['id'] ?? 2));
$car3_id = !empty($_GET['car3']) ? (int)$_GET['car3'] : null;

$selected_ids = array_filter([$car1_id, $car2_id, $car3_id]);
$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));

$compStmt = $pdo->prepare("SELECT c.*, b.name AS brand_name 
                           FROM cars c 
                           LEFT JOIN brands b ON c.brand_id = b.id 
                           WHERE c.id IN ($placeholders)");
$compStmt->execute($selected_ids);
$compared_cars_raw = $compStmt->fetchAll();

// Re-order according to selected_ids order
$compared_cars = [];
foreach ($selected_ids as $id) {
    foreach ($compared_cars_raw as $car) {
        if ($car['id'] == $id) {
            $compared_cars[] = $car;
            break;
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; <span>Compare Cars</span>
        </div>

        <div class="section-header">
            <h1 class="section-title">Car <span class="accent">Comparison</span></h1>
            <span class="meta-text">Side-by-side technical evaluation</span>
        </div>

        <!-- Car Selectors Bar -->
        <form method="GET" action="compare.php" class="compare-selector-bar">
            <!-- Slot 1 -->
            <div class="compare-slot">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:8px;">Car 1</label>
                <select name="car1" onchange="this.form.submit()">
                    <?php foreach ($all_cars as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $car1_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Slot 2 -->
            <div class="compare-slot">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:8px;">Car 2</label>
                <select name="car2" onchange="this.form.submit()">
                    <?php foreach ($all_cars as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $car2_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Slot 3 (Optional) -->
            <div class="compare-slot">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:8px;">Car 3 (Optional)</label>
                <select name="car3" onchange="this.form.submit()">
                    <option value="">-- Add 3rd Car --</option>
                    <?php foreach ($all_cars as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $car3_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- Spec Comparison Table -->
        <table class="compare-matrix-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Criteria / Spec</th>
                    <?php foreach ($compared_cars as $car): ?>
                        <th style="width: <?= 75 / count($compared_cars) ?>%; text-align: center;">
                            <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" style="height: 120px; object-fit: cover; margin: 0 auto 10px auto; border-radius: 4px;">
                            <div style="font-size: 16px; font-weight: 800; color: var(--text-dark); margin-bottom: 4px;">
                                <?= htmlspecialchars($car['name']) ?>
                            </div>
                            <div class="price-text" style="font-size: 16px;">
                                <?= format_car_price($car['price_min'], $car['price_max']) ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <!-- Price Section -->
                <tr class="compare-category-row">
                    <td colspan="<?= count($compared_cars) + 1 ?>">Pricing &amp; Value</td>
                </tr>
                <tr>
                    <td><strong>Ex-Showroom Price</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center; font-weight: 700; color: var(--primary-red);">
                            <?= format_car_price($car['price_min'], $car['price_max']) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><span class="badge-tag"><?= htmlspecialchars($car['status']) ?></span></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Engine & Transmission -->
                <tr class="compare-category-row">
                    <td colspan="<?= count($compared_cars) + 1 ?>">Engine &amp; Performance</td>
                </tr>
                <tr>
                    <td><strong>Displacement / Motor</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['engine_displacement']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Max Power</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center; font-weight: 700;"><?= htmlspecialchars($car['power']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Peak Torque</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['torque']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Fuel Type</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['fuel_type']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Fuel Efficiency / Range</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center; font-weight: 800; color: #16a34a;"><?= htmlspecialchars($car['mileage']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Transmission</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['transmission']) ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Dimensions & Safety -->
                <tr class="compare-category-row">
                    <td colspan="<?= count($compared_cars) + 1 ?>">Cabin &amp; Crash Safety</td>
                </tr>
                <tr>
                    <td><strong>Body Type</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['body_type']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Seating Capacity</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;"><?= htmlspecialchars($car['seating_capacity']) ?> Seater</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>Safety Crash Test Rating</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center; font-weight: 700; color: #1e3a8a;"><?= htmlspecialchars($car['safety_rating']) ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Full Page Link -->
                <tr>
                    <td><strong>Full Road Test</strong></td>
                    <?php foreach ($compared_cars as $car): ?>
                        <td style="text-align: center;">
                            <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>" class="btn-card-action" style="padding: 6px 14px; font-size: 11px;">
                                View Details &rarr;
                            </a>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
