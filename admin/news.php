<?php
/**
 * AutoPulse - Admin: Manage News Articles
 */

require_once __DIR__ . '/includes/admin_auth.php';

$stmt = $pdo->query("SELECT * FROM news_articles ORDER BY published_at DESC");
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - AutoPulse Admin</title>
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
            <li class="admin-nav-item active"><a href="news.php">&#128240; Manage News</a></li>
            <li class="admin-nav-item"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 900;">Manage News &amp; Articles</h1>
                <p style="font-size: 13px; color: #6b7280;"><?= count($articles) ?> stories published</p>
            </div>
            <a href="news-add.php" class="btn-card-action" style="background: var(--primary-red); padding: 10px 20px;">+ Write New Article</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div style="padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 4px; overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Headline</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $art): ?>
                        <tr>
                            <td style="width: 70px;">
                                <img src="../<?= htmlspecialchars($art['image']) ?>" alt="" style="width: 60px; height: 38px; object-fit: cover; border-radius: 2px;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars(substr($art['title'], 0, 48)) ?>...</strong>
                                <?php if ($art['is_hero']): ?>
                                    <span class="badge-tag" style="font-size: 9px; margin-left: 6px;">Hero</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge-outline"><?= htmlspecialchars($art['category']) ?></span></td>
                            <td><?= htmlspecialchars($art['author_name']) ?></td>
                            <td><?= format_views($art['views_count']) ?></td>
                            <td style="font-size: 12px; color: #6b7280;"><?= date('M j, Y', strtotime($art['published_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="news-edit.php?id=<?= $art['id'] ?>" style="color: #2563eb; font-weight: 700;">Edit</a>
                                    <span>|</span>
                                    <a href="news-delete.php?id=<?= $art['id'] ?>" onclick="return confirm('Delete this article?');" style="color: #dc2626; font-weight: 700;">Delete</a>
                                    <span>|</span>
                                    <a href="../news-detail.php?slug=<?= urlencode($art['slug']) ?>" target="_blank" style="color: #4b5563;">View</a>
                                </div>
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
