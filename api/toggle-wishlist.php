<?php
/**
 * AutoPulse - AJAX Wishlist Toggle Endpoint
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'redirect', 'message' => 'Please login to save cars to your wishlist.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$car_id = (int)($input['car_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if (!$car_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid car ID']);
    exit;
}

try {
    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$user_id, $car_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Remove
        $del = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        $del->execute([$existing['id']]);
        $action = 'removed';
    } else {
        // Add
        $ins = $pdo->prepare("INSERT INTO wishlist (user_id, car_id) VALUES (?, ?)");
        $ins->execute([$user_id, $car_id]);
        $action = 'added';
    }

    // Get updated count
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $cntStmt->execute([$user_id]);
    $count = (int)$cntStmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'count'  => $count
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
