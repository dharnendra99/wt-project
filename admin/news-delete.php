<?php
/**
 * AutoPulse - Admin: Delete Article
 */

require_once __DIR__ . '/includes/admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $del = $pdo->prepare("DELETE FROM news_articles WHERE id = ?");
    $del->execute([$id]);
}

header('Location: news.php?msg=Article+deleted+successfully');
exit;
