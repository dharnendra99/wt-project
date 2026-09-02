<?php
/**
 * AutoPulse - Admin: Edit News Article
 */

require_once __DIR__ . '/includes/admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM news_articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: news.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subtitle = sanitize($_POST['subtitle'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'Car News';
    $author_name = sanitize($_POST['author_name'] ?? '');
    $model_tag = sanitize($_POST['model_tag'] ?? '');
    $is_hero = isset($_POST['is_hero']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;
    $image = $article['image'];

    if (isset($_FILES['news_image_file']) && $_FILES['news_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/news/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileInfo = pathinfo($_FILES['news_image_file']['name']);
        $ext = strtolower($fileInfo['extension']);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
            $newFileName = $article['slug'] . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['news_image_file']['tmp_name'], $uploadDir . $newFileName)) {
                $image = 'assets/images/news/' . $newFileName;
            }
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Headline and content are required.';
    } else {
        $up = $pdo->prepare("UPDATE news_articles SET title = ?, subtitle = ?, content = ?, category = ?, author_name = ?, model_tag = ?, is_hero = ?, is_trending = ?, image = ? WHERE id = ?");
        $up->execute([$title, $subtitle, $content, $category, $author_name, $model_tag, $is_hero, $is_trending, $image, $id]);

        header('Location: news.php?msg=Article+updated+successfully');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - AutoPulse Admin</title>
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
                <h1 style="font-size: 24px; font-weight: 900;">Edit Article</h1>
            </div>
            <a href="news.php" class="btn-card-action" style="background: #374151;">&larr; Back to News</a>
        </div>

        <form method="POST" enctype="multipart/form-data" style="background: #fff; padding: 32px; border: 1px solid var(--border-color); border-radius: 4px;">
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Headline *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-weight:700;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Subtitle</label>
                <input type="text" name="subtitle" value="<?= htmlspecialchars($article['subtitle'] ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Category</label>
                    <select name="category" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <?php foreach (['Car News', 'Bike News', 'Motorsport', 'Industry'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= $article['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Author</label>
                    <input type="text" name="author_name" value="<?= htmlspecialchars($article['author_name']) ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Model Tag</label>
                    <input type="text" name="model_tag" value="<?= htmlspecialchars($article['model_tag'] ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Change Image</label>
                <input type="file" name="news_image_file" accept=".jpg,.jpeg,.png,.svg,.webp" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Content *</label>
                <textarea name="content" rows="10" style="width:100%; padding:12px; border:1px solid var(--border-color); border-radius:4px; font-size:15px; line-height:1.6;" required><?= htmlspecialchars($article['content']) ?></textarea>
            </div>

            <button type="submit" class="btn-card-action" style="background: var(--primary-red); padding: 14px 28px; font-size: 14px; cursor: pointer;">
                Update Article
            </button>
        </form>
    </main>
</div>

</body>
</html>
