<?php
/**
 * AutoPulse - REST API: News
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db_connect.php';

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM news_articles WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch() ?: []);
        exit;
    }

    $stmt = $pdo->query("SELECT * FROM news_articles ORDER BY published_at DESC");
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) {
    echo file_get_contents(__DIR__ . '/../data/news.json');
}
