<?php
/**
 * AutoPulse - REST API: Cars
 * Returns car data in JSON format for the AngularJS frontend.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $slug = $_GET['slug'] ?? '';

    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name, b.slug AS brand_slug 
                               FROM cars c 
                               LEFT JOIN brands b ON c.brand_id = b.id 
                               WHERE c.id = ?");
        $stmt->execute([$id]);
        $car = $stmt->fetch();
        if ($car) {
            $car['gallery_images'] = array_filter(explode(',', $car['gallery_images'] ?? ''));
            $car['pros'] = array_filter(explode('|', $car['pros'] ?? ''));
            $car['cons'] = array_filter(explode('|', $car['cons'] ?? ''));
        }
        echo json_encode($car ?: []);
        exit;
    }

    if (!empty($slug)) {
        $stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name, b.slug AS brand_slug 
                               FROM cars c 
                               LEFT JOIN brands b ON c.brand_id = b.id 
                               WHERE c.slug = ?");
        $stmt->execute([$slug]);
        $car = $stmt->fetch();
        if ($car) {
            $car['gallery_images'] = array_filter(explode(',', $car['gallery_images'] ?? ''));
            $car['pros'] = array_filter(explode('|', $car['pros'] ?? ''));
            $car['cons'] = array_filter(explode('|', $car['cons'] ?? ''));
        }
        echo json_encode($car ?: []);
        exit;
    }

    $stmt = $pdo->query("SELECT c.*, b.name AS brand_name, b.slug AS brand_slug 
                         FROM cars c 
                         LEFT JOIN brands b ON c.brand_id = b.id 
                         ORDER BY c.price_min ASC");
    $cars = $stmt->fetchAll();

    foreach ($cars as &$car) {
        $car['gallery_images'] = array_filter(explode(',', $car['gallery_images'] ?? ''));
        $car['pros'] = array_filter(explode('|', $car['pros'] ?? ''));
        $car['cons'] = array_filter(explode('|', $car['cons'] ?? ''));
    }

    echo json_encode($cars);
} catch (Exception $e) {
    // If database unavailable, fall back to JSON file
    $json = file_get_contents(__DIR__ . '/../data/cars.json');
    echo $json;
}
