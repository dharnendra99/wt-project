<?php
/**
 * AutoPulse - Admin: Add News Article
 */

require_once __DIR__ . '/includes/admin_auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subtitle = sanitize($_POST['subtitle'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $author_name = sanitize($_POST['author_name'] ?? 'AutoPulse Staff');
    $author_role = sanitize($_POST['author_role'] ?? 'Automotive Journalist');
    $category = $_POST['category'] ?? 'Car News';
    $model_tag = sanitize($_POST['model_tag'] ?? '');
    $is_hero = isset($_POST['is_hero']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $check = $pdo->prepare("SELECT COUNT(*) FROM news_articles WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetchColumn() > 0) $slug .= '-' . time();

    $image = 'assets/images/news/curvv-launch.svg';

    if (isset($_FILES['news_image_file']) && $_FILES['news_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/news/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileInfo = pathinfo($_FILES['news_image_file']['name']);
        $ext = strtolower($fileInfo['extension']);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
            $newFileName = $slug . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['news_image_file']['tmp_name'], $uploadDir . $newFileName)) {
                $image = 'assets/images/news/' . $newFileName;
            }
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Headline and article content are required.';
    } else {
        $ins = $pdo->prepare("INSERT INTO news_articles (title, slug, subtitle, content, image, author_name, author_role, category, model_tag, is_hero, is_trending) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$title, $slug, $subtitle, $content, $image, $author_name, $author_role, $category, $model_tag, $is_hero, $is_trending]);

        header('Location: news.php?msg=Article+published+successfully');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Article - AutoPulse Admin</title>
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
                <h1 style="font-size: 24px; font-weight: 900;">Write New Article</h1>
                <p style="font-size: 13px; color: #6b7280;">Draft and publish road tests, scoop stories, and news.</p>
            </div>
            <a href="news.php" class="btn-card-action" style="background: #374151;">&larr; Back to News</a>
        </div>

        <?php if (!empty($error)): ?>
            <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="background: #fff; padding: 32px; border: 1px solid var(--border-color); border-radius: 4px;">
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Article Headline *</label>
                <input type="text" name="title" placeholder="e.g. 2025 Tata Sierra EV Spotted Testing in Ladakh" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:16px; font-weight:700;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Subtitle / Standfirst</label>
                <input type="text" name="subtitle" placeholder="Short introductory sentence displayed under headline" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Category</label>
                    <select name="category" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="Car News">Car News</option>
                        <option value="Bike News">Bike News</option>
                        <option value="Motorsport">Motorsport</option>
                        <option value="Industry">Industry</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Author Name</label>
                    <input type="text" name="author_name" value="Hormazd Sorabjee" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Model Tag (Optional)</label>
                    <input type="text" name="model_tag" placeholder="e.g. Tata Sierra" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Lead Image (Upload)</label>
                <input type="file" name="news_image_file" accept=".jpg,.jpeg,.png,.svg,.webp" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Article Body *</label>
                <textarea name="content" rows="10" placeholder="Write the full journalistic story here..." style="width:100%; padding:12px; border:1px solid var(--border-color); border-radius:4px; font-size:15px; line-height:1.6;" required></textarea>
            </div>

            <div style="display: flex; gap: 24px; margin-bottom: 24px;">
                <label class="custom-checkbox">
                    <input type="checkbox" name="is_hero" value="1">
                    <span>Feature on Homepage Hero Slider</span>
                </label>
                <label class="custom-checkbox">
                    <input type="checkbox" name="is_trending" value="1" checked>
                    <span>Highlight in Trending List</span>
                </label>
            </div>

            <button type="submit" class="btn-card-action" style="background: var(--primary-red); padding: 14px 28px; font-size: 14px; cursor: pointer;">
                Publish Story
            </button>
        </form>
    </main>
</div>

</body>
</html>
