<?php
/**
 * AutoPulse - REST API: Reviews
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db_connect.php';

try {
    $stmt = $pdo->query("SELECT r.*, c.name AS car_name FROM reviews r JOIN cars c ON r.car_id = c.id WHERE r.status = 'approved' ORDER BY r.created_at DESC");
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) {
    echo file_get_contents(__DIR__ . '/../data/reviews.json');
}
