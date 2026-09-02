<?php
/**
 * AutoPulse - Admin: Moderate Reviews
 */

require_once __DIR__ . '/includes/admin_auth.php';

// Handle delete
if (isset($_GET['delete'])) {
    $del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $del->execute([(int)$_GET['delete']]);
    header('Location: reviews.php?msg=Review+deleted');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $up = $pdo->prepare("UPDATE reviews SET status = IF(status='approved','pending','approved') WHERE id = ?");
    $up->execute([(int)$_GET['toggle_status']]);
    header('Location: reviews.php?msg=Status+updated');
    exit;
}

$stmt = $pdo->query("SELECT r.*, c.name AS car_name FROM reviews r JOIN cars c ON r.car_id = c.id ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Reviews - AutoPulse Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="admin-logo">AUTO<span>PULSE</span> CMS</div>
        <ul class="admin-nav-list">
            <li class="admin-nav-item"><a href="index.php">&#9881; Dashboard</a></li>
            <li class="admin-nav-item"><a href="cars.php">&#128663; Manage Cars</a></li>
            <li class="admin-nav-item"><a href="news.php">&#128240; Manage News</a></li>
            <li class="admin-nav-item active"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 24px;">Moderate Car Reviews</h1>

        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px;">
            <table class="admin-table">
                <thead>
                    <tr><th>Car</th><th>Reviewer</th><th>Rating</th><th>Title &amp; Comment</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['car_name']) ?></strong></td>
                            <td><?= htmlspecialchars($r['author_name']) ?></td>
                            <td><strong style="color: #ff9800;">★ <?= $r['rating'] ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($r['title']) ?></strong>
                                <p style="font-size: 12px; color: #4b5563; margin-top: 2px;"><?= htmlspecialchars(substr($r['review_text'], 0, 80)) ?>...</p>
                            </td>
                            <td>
                                <span class="badge-tag" style="background: <?= $r['status']==='approved' ? '#16a34a' : '#ea580c' ?>;">
                                    <?= htmlspecialchars($r['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="reviews.php?toggle_status=<?= $r['id'] ?>" style="color: #2563eb; font-weight: 700; margin-right: 8px;">Toggle</a>
                                <a href="reviews.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete review?');" style="color: #dc2626; font-weight: 700;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
