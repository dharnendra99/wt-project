<?php
/**
 * AutoPulse - Shared Header Component
 * Autocar India-inspired layout with Top Bar, Location Selector, Search, and Horizontal Navigation with Red Active Underline.
 */

if (!isset($current_page)) {
    $current_page = 'home';
}

$user = current_user();

// Calculate wishlist count for logged-in user
$wishlist_count = 0;
if (is_logged_in()) {
    $wl_stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $wl_stmt->execute([$_SESSION['user_id']]);
    $wishlist_count = (int)$wl_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | AutoPulse' : 'AutoPulse - India\'s Premier Car News, Reviews & Buyer Guide' ?></title>
    <meta name="description" content="AutoPulse delivers breaking Indian automotive news, in-depth road test reviews, verified prices, specifications, and car comparisons.">
    
    <!-- Custom Stylesheets (Local, offline compatible) -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>

<header class="site-header">
    <div class="container">
        <!-- 1. Top Bar: Logo on Left, Location & Search on Right -->
        <div class="top-bar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle navigation">&#9776;</button>
                <a href="index.php" class="brand-logo">
                    AUTO<span class="turbo-dot">PULSE</span>
                    <span class="logo-tag">INDIA</span>
                </a>
            </div>

            <div class="top-bar-right">
                <!-- Location / City Selector -->
                <div class="location-selector" title="Choose your city for localized on-road pricing">
                    <span style="color: var(--primary-red);">&#9873;</span>
                    <select id="userCitySelect" aria-label="Select City">
                        <option value="Delhi">Delhi</option>
                        <option value="Mumbai">Mumbai</option>
                        <option value="Bengaluru">Bengaluru</option>
                        <option value="Chennai">Chennai</option>
                        <option value="Hyderabad">Hyderabad</option>
                        <option value="Kolkata">Kolkata</option>
                        <option value="Pune">Pune</option>
                        <option value="Ahmedabad">Ahmedabad</option>
                    </select>
                </div>

                <!-- Search Button -->
                <button class="top-search-btn" id="topSearchBtn" title="Search cars, news, and reviews">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>

                <!-- User Wishlist & Account Links -->
                <div class="user-nav-actions">
                    <a href="wishlist.php" class="btn-wishlist-badge" title="My Wishlist">
                        <span style="font-size: 16px; color: var(--primary-red);">&#9829;</span>
                        <span class="wishlist-count" id="wishlistCountBadge"><?= $wishlist_count ?></span>
                    </a>

                    <?php if ($user): ?>
                        <span style="font-size: 13px; color: var(--text-muted); display: none; md:inline;">Hi, <strong><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></strong></span>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin/index.php" style="background:#111827; color:#fff; font-size:11px; text-transform:uppercase; padding:4px 8px; border-radius:3px;">Admin</a>
                        <?php endif; ?>
                        <a href="logout.php" style="color: var(--text-muted); font-size: 12px;">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Search Bar Dropdown Overlay -->
        <div class="search-overlay-panel" id="searchOverlayPanel">
            <form action="search.php" method="GET" class="search-form-wrap">
                <input type="text" name="q" class="search-input" placeholder="Search cars, upcoming models, news, or reviews..." required>
                <button type="submit" class="search-submit-btn">Search</button>
            </form>
        </div>

        <!-- 2. Horizontal Nav below logo with Red Underline on active tab -->
        <nav class="horizontal-nav" id="horizontalNav">
            <ul class="nav-links-list">
                <li class="nav-item <?= $current_page === 'news' || $current_page === 'home' ? 'active' : '' ?>">
                    <a href="news.php?cat=Car+News">Car News</a>
                </li>
                <li class="nav-item <?= $current_page === 'bikenews' ? 'active' : '' ?>">
                    <a href="news.php?cat=Bike+News">Bike News</a>
                </li>
                <li class="nav-item <?= $current_page === 'motorsport' ? 'active' : '' ?>">
                    <a href="news.php?cat=Motorsport">Motorsport</a>
                </li>
                <li class="nav-item <?= $current_page === 'reviews' ? 'active' : '' ?>">
                    <a href="reviews.php">Reviews</a>
                </li>
                <li class="nav-item <?= $current_page === 'cars' ? 'active' : '' ?>">
                    <a href="cars.php">All Cars</a>
                </li>
                <li class="nav-item <?= $current_page === 'upcoming' ? 'active' : '' ?>">
                    <a href="cars.php?status=Upcoming">Upcoming Cars</a>
                </li>
                <li class="nav-item <?= $current_page === 'compare' ? 'active' : '' ?>">
                    <a href="compare.php" class="nav-cta-compare">Compare Cars</a>
                </li>
            </ul>
        </nav>
    </div>
</header>
