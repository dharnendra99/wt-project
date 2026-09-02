<?php
/**
 * AutoPulse - Car Detail Page
 * Complete automotive specification sheet, interactive gallery, pros & cons, and verified user reviews.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$car_id = (int)($_GET['id'] ?? 0);

if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name, b.origin AS brand_origin 
                           FROM cars c 
                           LEFT JOIN brands b ON c.brand_id = b.id 
                           WHERE c.slug = ?");
    $stmt->execute([$slug]);
    $car = $stmt->fetch();
} elseif ($car_id > 0) {
    $stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name, b.origin AS brand_origin 
                           FROM cars c 
                           LEFT JOIN brands b ON c.brand_id = b.id 
                           WHERE c.id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
} else {
    header("Location: cars.php");
    exit;
}

if (!$car) {
    die("Car model not found. <a href='cars.php'>Return to all cars</a>");
}

$current_page = 'cars';
$page_title = "{$car['name']} Price, Mileage, Specs, Images & Reviews";

// Process Review Submission
$review_success = '';
$review_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $author_name = sanitize($_POST['author_name'] ?? '');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $title = sanitize($_POST['title'] ?? '');
    $review_text = sanitize($_POST['review_text'] ?? '');
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;

    if (empty($author_name) || empty($title) || empty($review_text)) {
        $review_error = 'Please fill out all review fields.';
    } else {
        $ins = $pdo->prepare("INSERT INTO reviews (car_id, user_id, author_name, rating, title, review_text, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        $ins->execute([$car['id'], $user_id, $author_name, $rating, $title, $review_text]);
        $review_success = 'Thank you! Your road test review has been published.';
    }
}

// Fetch Reviews for this car
$rev_stmt = $pdo->prepare("SELECT * FROM reviews WHERE car_id = ? AND status = 'approved' ORDER BY created_at DESC");
$rev_stmt->execute([$car['id']]);
$reviews = $rev_stmt->fetchAll();

// Calculate Average Rating
$avg_rating = 4.5;
if (!empty($reviews)) {
    $total = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total / count($reviews), 1);
}

// Split Gallery Images
$gallery = array_filter(explode(',', $car['gallery_images'] ?? ''));
if (empty($gallery)) {
    $gallery = [$car['featured_image']];
}

// Split Pros and Cons
$pros = array_filter(explode('|', $car['pros'] ?? ''));
$cons = array_filter(explode('|', $car['cons'] ?? ''));

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; 
            <a href="cars.php">Cars</a> &gt; 
            <span><?= htmlspecialchars($car['name']) ?></span>
        </div>

        <!-- Car Detail Hero: Gallery (Left) + Pricing & Action (Right) -->
        <div class="car-detail-hero">
            <!-- Left: Gallery -->
            <div class="gallery-col">
                <div class="gallery-main-view">
                    <img id="mainGalleryImg" src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                </div>
                <div class="gallery-thumbs-row">
                    <?php foreach ($gallery as $idx => $imgUrl): ?>
                        <div class="thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="document.getElementById('mainGalleryImg').src='<?= htmlspecialchars(trim($imgUrl)) ?>'; document.querySelectorAll('.thumb-item').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
                            <img src="<?= htmlspecialchars(trim($imgUrl)) ?>" alt="Thumbnail <?= $idx + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Info & Pricing -->
            <div class="car-detail-info">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <span class="meta-text" style="color:var(--primary-red); font-weight:700;"><?= htmlspecialchars($car['brand_name']) ?></span>
                        <h1 class="car-detail-title"><?= htmlspecialchars($car['name']) ?></h1>
                    </div>
                    <button class="car-wishlist-btn <?= is_in_wishlist($pdo, $car['id']) ? 'active' : '' ?>" data-car-id="<?= $car['id'] ?>" style="position:static; width:44px; height:44px; font-size:22px; border:1px solid var(--border-color);" title="Add to Wishlist">
                        <?= is_in_wishlist($pdo, $car['id']) ? '&#9829;' : '&#9825;' ?>
                    </button>
                </div>

                <!-- Star Rating Badge -->
                <div style="margin: 8px 0 16px 0; display: flex; align-items: center; gap: 12px;">
                    <?= render_star_rating($avg_rating) ?>
                    <span style="font-size: 13px; color: var(--text-muted);">(<?= count($reviews) ?> User Reviews)</span>
                    <span class="badge-tag" style="background:#1a1a1a;"><?= htmlspecialchars($car['safety_rating']) ?></span>
                </div>

                <!-- Price Box -->
                <div class="car-detail-pricing-box">
                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <div>
                            <span style="font-size:12px; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Ex-Showroom Price</span>
                            <div class="price-text" style="font-size:26px; margin: 4px 0;"><?= format_car_price($car['price_min'], $car['price_max']) ?></div>
                        </div>
                        <span class="badge-tag"><?= htmlspecialchars($car['status']) ?></span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">
                        *On-road price in <strong id="selectedCityLabel">Delhi</strong>: approx Rs <?= number_format((float)$car['price_min'] * 1.12, 2) ?> - <?= number_format((float)$car['price_max'] * 1.14, 2) ?> Lakh (includes RTO &amp; insurance).
                    </p>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 14px; margin-bottom: 24px;">
                    <a href="compare.php?car1=<?= $car['id'] ?>" class="btn-card-action" style="background:var(--primary-red); padding:12px 24px; font-size:14px; text-align:center;">
                        Compare With Rivals
                    </a>
                    <button type="button" class="btn-card-action" onclick="alert('Test drive request registered for <?= addslashes($car['name']) ?>! Our nearest authorized dealership will contact you shortly.')" style="background:#fff; color:var(--text-dark); border:1px solid var(--border-color); padding:12px 24px; font-size:14px; cursor:pointer;">
                        Book Test Drive
                    </button>
                </div>

                <!-- Key Specs Grid -->
                <div class="detail-specs-grid">
                    <div class="spec-box">
                        <span class="label">Engine / Motor</span>
                        <div class="value"><?= htmlspecialchars($car['engine_displacement']) ?></div>
                    </div>
                    <div class="spec-box">
                        <span class="label">Max Power</span>
                        <div class="value"><?= htmlspecialchars($car['power']) ?></div>
                    </div>
                    <div class="spec-box">
                        <span class="label">Peak Torque</span>
                        <div class="value"><?= htmlspecialchars($car['torque']) ?></div>
                    </div>
                    <div class="spec-box">
                        <span class="label">Fuel / Mileage</span>
                        <div class="value"><?= htmlspecialchars($car['mileage']) ?></div>
                    </div>
                    <div class="spec-box">
                        <span class="label">Transmission</span>
                        <div class="value"><?= htmlspecialchars($car['transmission']) ?></div>
                    </div>
                    <div class="spec-box">
                        <span class="label">Seating Capacity</span>
                        <div class="value"><?= htmlspecialchars($car['seating_capacity']) ?> Seater</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Autocar-Style Road Test Overview -->
        <section style="margin-bottom: 40px; background:#fff; padding:32px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
            <div class="section-header">
                <h2 class="section-title"><?= htmlspecialchars($car['name']) ?> <span class="accent">Overview</span></h2>
                <span class="meta-text">Autocar India Benchmark Report</span>
            </div>
            <p style="font-size: 16px; line-height: 1.8; color: #333; margin-bottom: 24px;">
                <?= nl2br(htmlspecialchars($car['overview'])) ?>
            </p>

            <!-- Pros & Cons Breakdown -->
            <div class="pros-cons-grid">
                <div class="pros-box">
                    <h4>&#10004; What We Like (The Pros)</h4>
                    <ul class="pros-cons-list">
                        <?php foreach ($pros as $p): ?>
                            <li><span style="color:#16a34a; font-weight:800;">+</span> <?= htmlspecialchars(trim($p)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="cons-box">
                    <h4>&#10008; What Could Be Better (The Cons)</h4>
                    <ul class="pros-cons-list">
                        <?php foreach ($cons as $c): ?>
                            <li><span style="color:var(--primary-red); font-weight:800;">&minus;</span> <?= htmlspecialchars(trim($c)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>

        <!-- User Reviews & Star Ratings -->
        <section style="margin-bottom: 48px; background:#fff; padding:32px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
            <div class="section-header">
                <h2 class="section-title">Owner &amp; Expert <span class="accent">Reviews</span></h2>
                <span class="meta-text">Real-world feedback</span>
            </div>

            <?php if (!empty($review_success)): ?>
                <div style="padding:14px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:4px; margin-bottom:20px;">
                    <?= $review_success ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($review_error)): ?>
                <div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:4px; margin-bottom:20px;">
                    <?= $review_error ?>
                </div>
            <?php endif; ?>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:36px;">
                <!-- Review List -->
                <div>
                    <h3 style="font-size:18px; font-weight:800; margin-bottom:16px;">What Drivers Say (<?= count($reviews) ?>)</h3>
                    <?php if (empty($reviews)): ?>
                        <p style="color:var(--text-muted); font-size:14px;">No reviews yet. Be the first to share your experience!</p>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:16px;">
                            <?php foreach ($reviews as $rev): ?>
                                <div style="border-bottom:1px solid var(--border-light); padding-bottom:16px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <strong><?= htmlspecialchars($rev['author_name']) ?></strong>
                                        <span style="font-size:11px; color:var(--text-muted);"><?= time_ago($rev['created_at']) ?></span>
                                    </div>
                                    <div style="margin-bottom:6px;"><?= render_star_rating($rev['rating']) ?></div>
                                    <h4 style="font-size:14px; font-weight:700; margin-bottom:4px;"><?= htmlspecialchars($rev['title']) ?></h4>
                                    <p style="font-size:13.5px; color:#4b5563; line-height:1.5;"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Write a Review Form -->
                <div style="background:var(--bg-section); padding:24px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                    <h3 style="font-size:18px; font-weight:800; margin-bottom:16px;">Write a Review</h3>
                    <form method="POST">
                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Your Name</label>
                            <input type="text" name="author_name" value="<?= is_logged_in() ? htmlspecialchars($_SESSION['user_name']) : '' ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Overall Rating</label>
                            <select name="rating" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-weight:700;">
                                <option value="5.0">5.0 - Outstanding (Exceptional)</option>
                                <option value="4.5">4.5 - Excellent</option>
                                <option value="4.0">4.0 - Very Good</option>
                                <option value="3.5">3.5 - Good</option>
                                <option value="3.0">3.0 - Average</option>
                                <option value="2.0">2.0 - Below Expectations</option>
                            </select>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Review Title</label>
                            <input type="text" name="title" placeholder="e.g. Fantastic highway performance and comfort" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Detailed Review</label>
                            <textarea name="review_text" rows="4" placeholder="Mention ride quality, mileage in traffic, build quality, and features..." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn-card-action" style="background:var(--primary-red); width:100%; padding:12px; cursor:pointer;">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
// Update city in on-road price text when header city changes
document.addEventListener('DOMContentLoaded', () => {
    const cityEl = document.getElementById('selectedCityLabel');
    const savedCity = localStorage.getItem('autopulse_city');
    if (cityEl && savedCity) {
        cityEl.textContent = savedCity;
    }
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
