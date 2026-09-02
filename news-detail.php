<?php
/**
 * AutoPulse - News Detail Reader
 * Rich article page with author byline, time ago timestamp, reader comment section, and related stories.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$article_id = (int)($_GET['id'] ?? 0);

if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT * FROM news_articles WHERE slug = ?");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
} elseif ($article_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news_articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
} else {
    header('Location: news.php');
    exit;
}

if (!$article) {
    die("Article not found. <a href='news.php'>Back to news</a>");
}

// Increment view count
$upView = $pdo->prepare("UPDATE news_articles SET views_count = views_count + 1 WHERE id = ?");
$upView->execute([$article['id']]);

$current_page = 'news';
$page_title = $article['title'];

// Process Comment Submission
$comment_success = '';
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $user_name = sanitize($_POST['user_name'] ?? '');
    $user_email = sanitize($_POST['user_email'] ?? '');
    $comment_text = sanitize($_POST['comment_text'] ?? '');

    if (empty($user_name) || empty($user_email) || empty($comment_text)) {
        $comment_error = 'Please fill out all comment fields.';
    } else {
        $ins = $pdo->prepare("INSERT INTO comments (article_id, user_name, user_email, comment_text, status) VALUES (?, ?, ?, ?, 'approved')");
        $ins->execute([$article['id'], $user_name, $user_email, $comment_text]);
        $comment_success = 'Your comment has been posted successfully!';
    }
}

// Fetch approved comments
$c_stmt = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? AND status = 'approved' ORDER BY created_at DESC");
$c_stmt->execute([$article['id']]);
$comments = $c_stmt->fetchAll();

// Related stories
$rel_stmt = $pdo->prepare("SELECT * FROM news_articles WHERE id != ? AND category = ? ORDER BY published_at DESC LIMIT 3");
$rel_stmt->execute([$article['id'], $article['category']]);
$related_news = $rel_stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container">

        <!-- Breadcrumbs -->
        <div style="margin: 20px 0 10px 0; font-size: 13px; color: var(--text-muted);">
            <a href="index.php">Home</a> &gt; 
            <a href="news.php">News</a> &gt; 
            <a href="news.php?cat=<?= urlencode($article['category']) ?>"><?= htmlspecialchars($article['category']) ?></a>
        </div>

        <article style="max-width: 860px; margin: 0 auto 48px auto; background:#fff; padding: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
            
            <span class="badge-tag" style="margin-bottom: 12px;"><?= htmlspecialchars($article['category']) ?></span>
            
            <h1 style="font-size: 32px; font-weight: 900; line-height: 1.25; margin-bottom: 12px;">
                <?= htmlspecialchars($article['title']) ?>
            </h1>

            <?php if (!empty($article['subtitle'])): ?>
                <p style="font-size: 18px; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px;">
                    <?= htmlspecialchars($article['subtitle']) ?>
                </p>
            <?php endif; ?>

            <!-- Author Byline -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?= htmlspecialchars($article['author_avatar']) ?>" alt="<?= htmlspecialchars($article['author_name']) ?>" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid var(--primary-red);">
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-dark);"><?= htmlspecialchars($article['author_name']) ?></div>
                        <span class="meta-text"><?= htmlspecialchars($article['author_role']) ?></span>
                    </div>
                </div>
                <div class="meta-text" style="text-align: right;">
                    <div><?= date('F j, Y', strtotime($article['published_at'])) ?></div>
                    <div style="color: var(--primary-red);"><?= format_views($article['views_count']) ?> Reads</div>
                </div>
            </div>

            <!-- Featured Image -->
            <div style="margin-bottom: 28px; border-radius: var(--radius-sm); overflow: hidden; max-height: 440px; background: #000;">
                <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <!-- Content Body -->
            <div style="font-size: 16px; line-height: 1.85; color: #262626; margin-bottom: 32px;">
                <?= nl2br(htmlspecialchars($article['content'])) ?>
            </div>

            <!-- Model Tag -->
            <?php if (!empty($article['model_tag'])): ?>
                <div style="padding: 16px 0; border-top: 1px solid var(--border-light); margin-bottom: 32px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 13px; font-weight: 700; text-transform: uppercase;">Tagged Model:</span>
                    <a href="news.php?model=<?= urlencode($article['model_tag']) ?>" class="model-chip" style="font-size: 12px;">
                        <?= htmlspecialchars($article['model_tag']) ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Reader Comments Section -->
            <section style="border-top: 2px solid var(--border-color); padding-top: 32px;">
                <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 20px;">
                    Discussion &amp; Comments (<?= count($comments) ?>)
                </h3>

                <?php if (!empty($comment_success)): ?>
                    <div style="padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 4px; margin-bottom: 16px;">
                        <?= $comment_success ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($comment_error)): ?>
                    <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 16px;">
                        <?= $comment_error ?>
                    </div>
                <?php endif; ?>

                <!-- Comment Form -->
                <form method="POST" style="background: var(--bg-section); padding: 20px; border-radius: 4px; margin-bottom: 28px; border: 1px solid var(--border-color);">
                    <h4 style="font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px;">Leave a Comment</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <input type="text" name="user_name" placeholder="Your Name" value="<?= is_logged_in() ? htmlspecialchars($_SESSION['user_name']) : '' ?>" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required>
                        <input type="email" name="user_email" placeholder="Your Email" value="<?= is_logged_in() ? htmlspecialchars($_SESSION['user_email']) : '' ?>" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required>
                    </div>
                    <textarea name="comment_text" rows="3" placeholder="Share your thoughts on this road test or car launch..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; margin-bottom: 12px;" required></textarea>
                    <button type="submit" name="submit_comment" class="btn-card-action" style="background: var(--primary-red); cursor: pointer;">
                        Post Comment
                    </button>
                </form>

                <!-- Comments List -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php if (empty($comments)): ?>
                        <p style="color: var(--text-muted); font-size: 14px;">No comments yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comm): ?>
                            <div style="padding: 14px; background: #fafafa; border-radius: 4px; border: 1px solid var(--border-light);">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <strong><?= htmlspecialchars($comm['user_name']) ?></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);"><?= time_ago($comm['created_at']) ?></span>
                                </div>
                                <p style="font-size: 14px; color: #374151;"><?= nl2br(htmlspecialchars($comm['comment_text'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </article>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
