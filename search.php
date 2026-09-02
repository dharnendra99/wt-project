<?php
/**
 * AutoPulse - Universal Search Results Page
 * Searches across cars, news articles, and owner reviews.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');
$current_page = 'search';
$page_title = !empty($query) ? "Search results for '{$query}'" : 'Search AutoPulse';

$matching_cars = [];
$matching_news = [];

if (!empty($query)) {
    $param = "%{$query}%";

    // 1. Search Cars
    $carStmt = $pdo->prepare("SELECT c.*, b.name AS brand_name 
                              FROM cars c 
                              LEFT JOIN brands b ON c.brand_id = b.id 
                              WHERE c.name LIKE ? OR b.name LIKE ? OR c.body_type LIKE ? OR c.fuel_type LIKE ?");
    $carStmt->execute([$param, $param, $param, $param]);
    $matching_cars = $carStmt->fetchAll();

    // 2. Search News
    $newsStmt = $pdo->prepare("SELECT * FROM news_articles 
                               WHERE title LIKE ? OR subtitle LIKE ? OR content LIKE ? OR model_tag LIKE ? 
                               ORDER BY published_at DESC");
    $newsStmt->execute([$param, $param, $param, $param]);
    $matching_news = $newsStmt->fetchAll();
}

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; <span>Search</span>
        </div>

        <div class="section-header">
            <h1 class="section-title">
                Search Results for <span class="accent">"<?= htmlspecialchars($query) ?>"</span>
            </h1>
            <span class="meta-text"><?= count($matching_cars) + count($matching_news) ?> Matches Found</span>
        </div>

        <!-- Search input box to refine -->
        <form method="GET" action="search.php" style="margin-bottom: 32px; display: flex; gap: 10px; max-width: 600px;">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" class="search-input" placeholder="Search cars, news, models..." required>
            <button type="submit" class="search-submit-btn">Search</button>
        </form>

        <?php if (empty($matching_cars) && empty($matching_news)): ?>
            <div style="padding: 48px; background: #fff; border: 1px solid var(--border-color); text-align: center; border-radius: 4px; margin-bottom: 40px;">
                <h3 style="font-size: 20px; margin-bottom: 8px;">No matching results found</h3>
                <p style="color: var(--text-muted);">Try searching for popular car models like "Nexon", "Creta", "Thar", or topics like "Electric" or "Safety".</p>
            </div>
        <?php endif; ?>

        <!-- Matching Cars -->
        <?php if (!empty($matching_cars)): ?>
            <section style="margin-bottom: 40px;">
                <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; margin-bottom: 16px; border-left: 4px solid var(--primary-red); padding-left: 10px;">
                    Matching Cars (<?= count($matching_cars) ?>)
                </h2>
                <div class="cars-grid">
                    <?php foreach ($matching_cars as $car): ?>
                        <div class="car-card">
                            <div class="car-card-media">
                                <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                                <span class="car-badge-status <?= strtolower($car['status']) ?>"><?= htmlspecialchars($car['status']) ?></span>
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
                                    <span class="price-text"><?= format_car_price($car['price_min'], $car['price_max']) ?></span>
                                    <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>" class="btn-card-action">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Matching News Articles -->
        <?php if (!empty($matching_news)): ?>
            <section style="margin-bottom: 48px;">
                <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; margin-bottom: 16px; border-left: 4px solid var(--primary-red); padding-left: 10px;">
                    Matching News &amp; Articles (<?= count($matching_news) ?>)
                </h2>
                <div class="latest-news-list">
                    <?php foreach ($matching_news as $news): ?>
                        <article class="news-card-horizontal">
                            <div class="news-thumbnail-wrap">
                                <a href="news-detail.php?slug=<?= urlencode($news['slug']) ?>">
                                    <img src="<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
                                </a>
                            </div>
                            <div class="news-content-col">
                                <div>
                                    <span class="news-card-tag"><?= htmlspecialchars($news['category']) ?></span>
                                    <h3 class="news-headline">
                                        <a href="news-detail.php?slug=<?= urlencode($news['slug']) ?>">
                                            <?= htmlspecialchars($news['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="news-snippet"><?= htmlspecialchars($news['subtitle'] ?? substr(strip_tags($news['content']), 0, 140) . '...') ?></p>
                                </div>
                                <div class="news-card-meta">
                                    <span><?= htmlspecialchars($news['author_name']) ?></span>
                                    <span>•</span>
                                    <span><?= time_ago($news['published_at']) ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
