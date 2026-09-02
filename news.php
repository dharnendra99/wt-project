<?php
/**
 * AutoPulse - News Articles Hub
 * Filter news by category (Car News, Bike News, Motorsport, Industry) or by car model.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$category = $_GET['cat'] ?? '';
$model = $_GET['model'] ?? '';
$author = $_GET['author'] ?? '';

$where = ["1=1"];
$params = [];

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    if ($category === 'Bike News') $current_page = 'bikenews';
    elseif ($category === 'Motorsport') $current_page = 'motorsport';
    else $current_page = 'news';
    $page_title = "{$category} - Latest Updates, Spy Shots & Launches";
} elseif (!empty($model)) {
    $where[] = "model_tag LIKE ?";
    $params[] = "%{$model}%";
    $current_page = 'news';
    $page_title = "{$model} News, Reviews & Updates";
} elseif (!empty($author)) {
    $where[] = "author_name = ?";
    $params[] = $author;
    $current_page = 'news';
    $page_title = "Articles by {$author}";
} else {
    $current_page = 'news';
    $page_title = 'Latest Automotive News India - Car & Bike Launches';
}

$whereClause = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT * FROM news_articles WHERE {$whereClause} ORDER BY published_at DESC");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Trending sidebar
$trend_stmt = $pdo->query("SELECT * FROM news_articles ORDER BY views_count DESC LIMIT 4");
$trending_news = $trend_stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; 
            <a href="news.php">News</a>
            <?php if (!empty($category)): ?> &gt; <span><?= htmlspecialchars($category) ?></span><?php endif; ?>
            <?php if (!empty($model)): ?> &gt; <span><?= htmlspecialchars($model) ?></span><?php endif; ?>
        </div>

        <div class="section-header">
            <h1 class="section-title">
                <?= !empty($category) ? htmlspecialchars($category) : (!empty($model) ? htmlspecialchars($model) . ' News' : 'Automotive <span class="accent">News</span>') ?>
            </h1>
            <span class="meta-text"><?= count($articles) ?> Articles Found</span>
        </div>

        <div class="news-layout-row">
            <!-- Left Column: Articles Grid -->
            <div class="latest-news-column">
                <div class="latest-news-list">
                    <?php if (empty($articles)): ?>
                        <p style="padding:40px; text-align:center; color:var(--text-muted);">No articles found matching your criteria.</p>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <article class="news-card-horizontal">
                                <div class="news-thumbnail-wrap">
                                    <a href="news-detail.php?slug=<?= urlencode($article['slug']) ?>">
                                        <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="news-content-col">
                                    <div>
                                        <span class="news-card-tag"><?= htmlspecialchars($article['category']) ?></span>
                                        <h2 class="news-headline" style="font-size: 18px;">
                                            <a href="news-detail.php?slug=<?= urlencode($article['slug']) ?>">
                                                <?= htmlspecialchars($article['title']) ?>
                                            </a>
                                        </h2>
                                        <p class="news-snippet">
                                            <?= htmlspecialchars($article['subtitle'] ?? substr(strip_tags($article['content']), 0, 140) . '...') ?>
                                        </p>
                                    </div>
                                    <div class="news-card-meta">
                                        <span><?= htmlspecialchars($article['author_name']) ?></span>
                                        <span>•</span>
                                        <span><?= time_ago($article['published_at']) ?></span>
                                        <span>•</span>
                                        <span><?= format_views($article['views_count']) ?> Reads</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Trending News -->
            <aside class="trending-sidebar-column">
                <div class="trending-sidebar-card">
                    <h3 class="trending-sidebar-title">
                        <span>Trending <span style="color:var(--primary-red);">Stories</span></span>
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

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
