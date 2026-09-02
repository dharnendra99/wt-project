<?php
/**
 * AutoPulse - Reviews & Road Tests Hub
 * Comprehensive collection of expert & owner ratings with star scores and pros/cons.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'reviews';
$page_title = 'Car Reviews & Star Ratings in India - Expert Road Tests';

// Fetch all approved reviews joined with cars and brands
$stmt = $pdo->query("SELECT r.*, c.name AS car_name, c.slug AS car_slug, c.featured_image AS car_image, b.name AS brand_name 
                     FROM reviews r 
                     JOIN cars c ON r.car_id = c.id 
                     LEFT JOIN brands b ON c.brand_id = b.id 
                     WHERE r.status = 'approved' 
                     ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();

// Fetch cars with their average review ratings
$cars_rev_stmt = $pdo->query("SELECT c.id, c.name, c.slug, c.featured_image, c.price_min, c.price_max, 
                              AVG(r.rating) AS avg_rating, COUNT(r.id) AS total_reviews 
                              FROM cars c 
                              LEFT JOIN reviews r ON c.id = r.car_id AND r.status = 'approved' 
                              GROUP BY c.id 
                              ORDER BY avg_rating DESC");
$cars_with_ratings = $cars_rev_stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; <span>Car Reviews</span>
        </div>

        <div class="section-header">
            <h1 class="section-title">Car <span class="accent">Reviews</span> &amp; Road Tests</h1>
            <span class="meta-text">Real-World Mileage, Comfort &amp; Performance</span>
        </div>

        <!-- Car Model Ratings Grid -->
        <div style="margin-bottom: 40px;">
            <h2 style="font-size: 18px; font-weight: 800; text-transform: uppercase; margin-bottom: 18px;">
                Ratings By Popular Car Models
            </h2>
            <div class="cars-grid">
                <?php foreach ($cars_with_ratings as $c): ?>
                    <div class="car-card">
                        <div class="car-card-media">
                            <img src="<?= htmlspecialchars($c['featured_image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                        </div>
                        <div class="car-card-body">
                            <h3 class="car-card-title">
                                <a href="car-detail.php?slug=<?= urlencode($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></a>
                            </h3>
                            <div style="margin: 6px 0 12px 0;">
                                <?= render_star_rating($c['avg_rating'] ? round($c['avg_rating'], 1) : 4.5) ?>
                                <span style="font-size: 12px; color: var(--text-muted);">(<?= $c['total_reviews'] ?> reviews)</span>
                            </div>
                            <div class="car-card-footer">
                                <span class="price-text" style="font-size: 15px;">
                                    <?= format_car_price($c['price_min'], $c['price_max']) ?>
                                </span>
                                <a href="car-detail.php?slug=<?= urlencode($c['slug']) ?>" class="btn-card-action">Read Tests</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Latest Owner Reviews Feed -->
        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px; padding: 28px; margin-bottom: 48px;">
            <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--primary-red);">
                Latest Verified Owner &amp; Tester Reviews
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: var(--bg-section); padding: 20px; border-radius: 4px; border: 1px solid var(--border-light); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--primary-red); text-transform: uppercase;">
                                    <?= htmlspecialchars($rev['car_name']) ?>
                                </span>
                                <span style="font-size: 11px; color: var(--text-muted);"><?= time_ago($rev['created_at']) ?></span>
                            </div>
                            <div style="margin-bottom: 8px;"><?= render_star_rating($rev['rating']) ?></div>
                            <h4 style="font-size: 15px; font-weight: 800; margin-bottom: 8px; color: var(--text-dark);">
                                <?= htmlspecialchars($rev['title']) ?>
                            </h4>
                            <p style="font-size: 13.5px; color: #4b5563; line-height: 1.5; margin-bottom: 14px;">
                                "<?= htmlspecialchars($rev['review_text']) ?>"
                            </p>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-dark); border-top: 1px solid var(--border-color); padding-top: 10px;">
                            By <?= htmlspecialchars($rev['author_name']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
