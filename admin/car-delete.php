<?php
/**
 * AutoPulse - Admin: Delete Car
 */

require_once __DIR__ . '/includes/admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $del = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $del->execute([$id]);
}

header('Location: cars.php?msg=Car+deleted+successfully');
exit;
