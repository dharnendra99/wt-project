<?php
/**
 * AutoPulse - Homepage
 * Follows Autocar India layout and design hierarchy:
 * 1. Top bar with city selector & search (header.php)
 * 2. Horizontal navigation with active red underline (header.php)
 * 3. Hero / Featured Carousel (hero slider)
 * 4. Suggested / Latest News Feed (with left thumbnails)
 * 5. News by Models pill chips
 * 6. Numbered Trending News (1, 2, 3, 4 with 135K+ badges)
 * 7. Editors horizontal scrollable avatar list
 * 8. Explore quick-links row
 * 9. Trending, Upcoming & Latest Cars card grid with bold red prices
 * 10. Multi-column dark footer with floating rule-based chatbot (footer.php)
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'home';
$page_title = 'Latest Car News, Road Tests, Reviews & Prices in India';

// 1. Fetch Hero / Featured News Articles (slider)
$hero_stmt = $pdo->query("SELECT * FROM news_articles ORDER BY is_hero DESC, published_at DESC LIMIT 3");
$hero_articles = $hero_stmt->fetchAll();

// 2. Fetch Latest / Suggested News Articles (excluding the primary hero)
$hero_ids = array_column($hero_articles, 'id');
$placeholders = implode(',', array_fill(0, count($hero_ids), '?'));
$latest_stmt = $pdo->prepare("SELECT * FROM news_articles WHERE id NOT IN ($placeholders) ORDER BY published_at DESC LIMIT 4");
$latest_stmt->execute($hero_ids);
$latest_news = $latest_stmt->fetchAll();

// If not enough latest news, fallback query
if (empty($latest_news)) {
    $latest_stmt = $pdo->query("SELECT * FROM news_articles ORDER BY published_at DESC LIMIT 4");
    $latest_news = $latest_stmt->fetchAll();
}

// 3. Fetch Distinct Model Tags for "News by models" section
$tags_stmt = $pdo->query("SELECT DISTINCT model_tag FROM news_articles WHERE model_tag IS NOT NULL LIMIT 8");
$model_tags = $tags_stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Fetch Trending News (Ordered by views_count)
$trending_stmt = $pdo->query("SELECT * FROM news_articles ORDER BY views_count DESC LIMIT 4");
$trending_news = $trending_stmt->fetchAll();

// 5. Editors list
$editors = [
    ['name' => 'Hormazd Sorabjee', 'role' => 'Editor-in-Chief', 'avatar' => 'assets/images/avatars/hormazd.svg'],
    ['name' => 'Shapur Kotwal', 'role' => 'Deputy Editor', 'avatar' => 'assets/images/avatars/shapur.svg'],
    ['name' => 'Gavin D\'Souza', 'role' => 'Road Test Editor', 'avatar' => 'assets/images/avatars/gavin.svg'],
    ['name' => 'Sergius Barretto', 'role' => 'Managing Editor', 'avatar' => 'assets/images/avatars/sergius.svg'],
    ['name' => 'Rishaad Mody', 'role' => 'Two-Wheeler Editor', 'avatar' => 'assets/images/avatars/rishaad.svg']
];

// 6. Fetch Trending Cars
$trend_cars_stmt = $pdo->query("SELECT c.*, b.name AS brand_name 
                                FROM cars c 
                                LEFT JOIN brands b ON c.brand_id = b.id 
                                WHERE c.status = 'Trending' 
                                ORDER BY c.id ASC LIMIT 3");
$trending_cars = $trend_cars_stmt->fetchAll();

// 7. Fetch Upcoming Cars
$up_cars_stmt = $pdo->query("SELECT c.*, b.name AS brand_name 
                             FROM cars c 
                             LEFT JOIN brands b ON c.brand_id = b.id 
                             WHERE c.status = 'Upcoming' OR c.body_type = 'EV' 
                             ORDER BY c.id DESC LIMIT 3");
$upcoming_cars = $up_cars_stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- ============================================================
             3. Hero / Featured Article Section (Carousel Slider)
             ============================================================ -->
        <section class="hero-slider-section">
            <div class="hero-slider-wrapper">
                <div class="slider-track" id="heroSliderTrack">
                    <?php foreach ($hero_articles as $idx => $hero): ?>
                        <div class="slider-slide <?= $idx === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($hero['image']) ?>" alt="<?= htmlspecialchars($hero['title']) ?>">
                            <div class="slider-overlay">
                                <span class="slider-tag"><?= htmlspecialchars($hero['category']) ?></span>
                                <h1 class="slider-headline">
                                    <a href="news-detail.php?slug=<?= urlencode($hero['slug']) ?>">
                                        <?= htmlspecialchars($hero['title']) ?>
                                    </a>
                                </h1>
                                <div class="slider-meta">
                                    <div class="author-info">
                                        <img src="<?= htmlspecialchars($hero['author_avatar']) ?>" alt="<?= htmlspecialchars($hero['author_name']) ?>" class="author-avatar-sm">
                                        <span><?= htmlspecialchars($hero['author_name']) ?></span>
                                    </div>
                                    <span>•</span>
                                    <span><?= time_ago($hero['published_at']) ?></span>
                                    <span>•</span>
                                    <span><?= format_views($hero['views_count']) ?> Reads</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Controls -->
                <div class="slider-controls">
                    <button class="slider-btn" id="sliderPrevBtn" aria-label="Previous Slide">&larr;</button>
                    <button class="slider-btn" id="sliderNextBtn" aria-label="Next Slide">&rarr;</button>
                </div>
                <div class="slider-dots" id="sliderDots"></div>
            </div>
        </section>

        <!-- ============================================================
             5. "News by models" section (Pill / Chip tags)
             ============================================================ -->
        <section class="model-tags-section">
            <div class="model-tags-header">
                <span style="color: var(--primary-red); font-size: 16px;">&#9881;</span>
                <span>News By Models</span>
            </div>
            <div class="model-tags-scroll">
                <a href="news.php" class="model-chip active">All Models</a>
                <?php foreach ($model_tags as $tag): ?>
                    <a href="news.php?model=<?= urlencode($tag) ?>" class="model-chip"><?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
                <a href="news.php?model=Nexon" class="model-chip">Tata Nexon</a>
                <a href="news.php?model=Creta" class="model-chip">Hyundai Creta</a>
                <a href="news.php?model=XUV700" class="model-chip">Mahindra XUV700</a>
                <a href="news.php?model=Swift" class="model-chip">Maruti Swift</a>
                <a href="news.php?model=Thar" class="model-chip">Mahindra Thar Roxx</a>
            </div>
        </section>

        <!-- ============================================================
             4 & 6. Latest News (Left) + Trending News (Right)
             ============================================================ -->
        <div class="news-layout-row">
            <!-- Left Column: Suggested / Latest News Feed -->
            <div class="latest-news-column">
                <div class="section-header">
                    <h2 class="section-title">Latest <span class="accent">News</span></h2>
                    <a href="news.php" class="section-link">View All News &rarr;</a>
                </div>

                <div class="latest-news-list">
                    <?php foreach ($latest_news as $news): ?>
                        <article class="news-card-horizontal">
                            <div class="news-thumbnail-wrap">
                                <a href="news-detail.php?slug=<?= urlencode($news['slug']) ?>">
                                    <img src="<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
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
                                    <p class="news-snippet">
                                        <?= htmlspecialchars($news['subtitle'] ?? substr(strip_tags($news['content']), 0, 120) . '...') ?>
                                    </p>
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

                <div class="view-more-btn-wrap">
                    <a href="news.php" class="btn-view-more">View More Automotive News</a>
                </div>
            </div>

            <!-- Right Column: Trending News (Numbered 1, 2, 3, 4 with badges) -->
            <aside class="trending-sidebar-column">
                <div class="trending-sidebar-card">
                    <h3 class="trending-sidebar-title">
                        <span>Trending <span style="color:var(--primary-red);">Stories</span></span>
                        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Last 24h</span>
                    </h3>

                    <div class="trending-list">
                        <?php foreach ($trending_news as $index => $item): ?>
                            <div class="trending-item">
                                <div class="trending-rank">0<?= $index + 1 ?></div>
                                <div class="trending-details">
                                    <h4 class="trending-title">
                                        <a href="news-detail.php?slug=<?= urlencode($item['slug']) ?>">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </h4>
                                    <div class="trending-meta">
                                        <span class="view-count-badge"><?= format_views($item['views_count']) ?></span>
                                        <span><?= time_ago($item['published_at']) ?></span>
                                    </div>
                                </div>
                                <div class="trending-thumb">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ============================================================
             7. "Editors" Section (Horizontal scrollable circular avatars)
             ============================================================ -->
        <section class="editors-section">
            <div class="section-header">
                <h2 class="section-title">Our <span class="accent">Editors</span></h2>
                <span class="meta-text">Automotive Experts &amp; Road Testers</span>
            </div>
            <div class="editors-scroll-row">
                <?php foreach ($editors as $editor): ?>
                    <div class="editor-card" onclick="window.location.href='news.php?author=<?= urlencode($editor['name']) ?>'">
                        <div class="editor-avatar-wrap">
                            <img src="<?= $editor['avatar'] ?>" alt="<?= $editor['name'] ?>">
                        </div>
                        <h4 class="editor-name"><?= $editor['name'] ?></h4>
                        <span class="editor-role"><?= $editor['role'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================================================
             8. "Explore" Quick-links Row
             ============================================================ -->
        <section class="explore-section">
            <div class="section-header">
                <h2 class="section-title">Explore <span class="accent">AutoPulse</span></h2>
            </div>
            <div class="explore-grid">
                <a href="cars.php" class="explore-card">
                    <span class="explore-icon">&#128663;</span>
                    <span class="explore-label">Explore Cars</span>
                </a>
                <a href="news.php?cat=Bike+News" class="explore-card">
                    <span class="explore-icon">&#127949;</span>
                    <span class="explore-label">Explore Bikes</span>
                </a>
                <a href="compare.php" class="explore-card">
                    <span class="explore-icon">&#9878;</span>
                    <span class="explore-label">Expert's Advice</span>
                </a>
                <a href="reviews.php" class="explore-card">
                    <span class="explore-icon">&#9733;</span>
                    <span class="explore-label">Reviews</span>
                </a>
                <a href="news.php" class="explore-card">
                    <span class="explore-icon">&#128240;</span>
                    <span class="explore-label">Articles &amp; Blogs</span>
                </a>
            </div>
        </section>

        <!-- ============================================================
             9. "Trending Cars" Grid Section
             ============================================================ -->
        <section class="cars-section">
            <div class="section-header">
                <h2 class="section-title">Trending <span class="accent">Cars</span></h2>
                <a href="cars.php?status=Trending" class="section-link">View All Trending &rarr;</a>
            </div>

            <div class="cars-grid">
                <?php foreach ($trending_cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-media">
                            <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" loading="lazy">
                            <span class="car-badge-status trending">Trending</span>
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
            </div>
        </section>

        <!-- ============================================================
             9. "Upcoming Cars" Grid Section
             ============================================================ -->
        <section style="margin-bottom: 48px;">
            <div class="section-header">
                <h2 class="section-title">Upcoming &amp; EV <span class="accent">Launches</span></h2>
                <a href="cars.php?status=Upcoming" class="section-link">View All Launches &rarr;</a>
            </div>

            <div class="cars-grid">
                <?php foreach ($upcoming_cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-media">
                            <img src="<?= htmlspecialchars($car['featured_image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" loading="lazy">
                            <span class="car-badge-status"><?= htmlspecialchars($car['status']) ?></span>
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
                                <span><?= htmlspecialchars($car['power']) ?></span> •
                                <span><?= htmlspecialchars($car['mileage']) ?></span>
                            </div>
                            <div class="car-card-footer">
                                <div class="car-price-block">
                                    <span class="price-text"><?= format_car_price($car['price_min'], $car['price_max']) ?></span>
                                    <span class="price-label">Expected Ex-showroom</span>
                                </div>
                                <a href="car-detail.php?slug=<?= urlencode($car['slug']) ?>" class="btn-card-action">View Specs</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
