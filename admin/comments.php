<?php
/**
 * AutoPulse - Admin: Moderate Comments
 */

require_once __DIR__ . '/includes/admin_auth.php';

// Handle delete
if (isset($_GET['delete'])) {
    $del = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $del->execute([(int)$_GET['delete']]);
    header('Location: comments.php?msg=Comment+deleted');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $up = $pdo->prepare("UPDATE comments SET status = IF(status='approved','pending','approved') WHERE id = ?");
    $up->execute([(int)$_GET['toggle_status']]);
    header('Location: comments.php?msg=Status+updated');
    exit;
}

$stmt = $pdo->query("SELECT c.*, a.title AS article_title FROM comments c JOIN news_articles a ON c.article_id = a.id ORDER BY c.created_at DESC");
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Comments - AutoPulse Admin</title>
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
            <li class="admin-nav-item"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item active"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 24px;">Moderate Article Comments</h1>

        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px;">
            <table class="admin-table">
                <thead>
                    <tr><th>Article</th><th>User</th><th>Comment</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(substr($c['article_title'], 0, 40)) ?>...</strong></td>
                            <td>
                                <div><?= htmlspecialchars($c['user_name']) ?></div>
                                <small style="color: #6b7280;"><?= htmlspecialchars($c['user_email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($c['comment_text']) ?></td>
                            <td>
                                <span class="badge-tag" style="background: <?= $c['status']==='approved' ? '#16a34a' : '#ea580c' ?>;">
                                    <?= htmlspecialchars($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="comments.php?toggle_status=<?= $c['id'] ?>" style="color: #2563eb; font-weight: 700; margin-right: 8px;">Toggle</a>
                                <a href="comments.php?delete=<?= $c['id'] ?>" onclick="return confirm('Delete comment?');" style="color: #dc2626; font-weight: 700;">Delete</a>
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
