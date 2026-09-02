<?php
/**
 * AutoPulse - Common Helper Functions
 * Clean, well-commented helper functions for date formatting, price display, user auth, and sanitization.
 */

/**
 * Sanitize string input to prevent XSS attacks
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Convert MySQL DATETIME to human-friendly "time ago" string
 * (e.g., "2 hours ago", "3 days ago") like Autocar India
 */
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('d M Y', $time);
    }
}

/**
 * Format Indian Rupee car price range
 * Example: 8.00 and 15.50 -> "Rs 8.00 - 15.50 Lakh"
 * Example: 60.60 and 62.00 -> "Rs 60.60 - 62.00 Lakh"
 */
function format_car_price($min, $max, $label = 'Ex-showroom price') {
    $min_f = number_format((float)$min, 2);
    $max_f = number_format((float)$max, 2);
    
    if ($min == $max || empty($max) || $max == 0) {
        return "Rs {$min_f} Lakh*";
    }
    return "Rs {$min_f} - {$max_f} Lakh*";
}

/**
 * Check if a regular user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if logged-in user is an administrator
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current logged in user data
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Check if a car is in user's wishlist
 */
function is_in_wishlist($pdo, $car_id) {
    if (!is_logged_in()) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$_SESSION['user_id'], $car_id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Generate visual star rating HTML (1 to 5 stars)
 */
function render_star_rating($rating) {
    $rating = (float)$rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;

    $html = '<div class="star-rating" title="' . number_format($rating, 1) . ' out of 5 stars">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<span class="star full">&#9733;</span>';
    }
    if ($half) {
        $html .= '<span class="star half">&#9733;</span>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<span class="star empty">&#9734;</span>';
    }
    $html .= ' <span class="rating-number">' . number_format($rating, 1) . '</span></div>';
    return $html;
}

/**
 * Format view count in Autocar India style (e.g., 184500 -> "184K+")
 */
function format_views($count) {
    $count = (int)$count;
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M+';
    } elseif ($count >= 1000) {
        return round($count / 1000) . 'K+';
    }
    return (string)$count;
}
