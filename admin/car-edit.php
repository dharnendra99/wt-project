<?php
/**
 * AutoPulse - Admin: Edit Car Model
 */

require_once __DIR__ . '/includes/admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: cars.php?error=Car+not+found');
    exit;
}

$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $body_type = $_POST['body_type'] ?? 'SUV';
    $fuel_type = $_POST['fuel_type'] ?? 'Petrol';
    $transmission = $_POST['transmission'] ?? 'Automatic';
    $price_min = (float)($_POST['price_min'] ?? 0);
    $price_max = (float)($_POST['price_max'] ?? 0);
    $status = $_POST['status'] ?? 'Available';
    $engine_displacement = sanitize($_POST['engine_displacement'] ?? '');
    $power = sanitize($_POST['power'] ?? '');
    $mileage = sanitize($_POST['mileage'] ?? '');
    $safety_rating = sanitize($_POST['safety_rating'] ?? '');
    $overview = sanitize($_POST['overview'] ?? '');
    $pros = sanitize($_POST['pros'] ?? '');
    $cons = sanitize($_POST['cons'] ?? '');
    $featured_image = $car['featured_image'];

    // Handle File Upload if provided
    if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/cars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileInfo = pathinfo($_FILES['featured_image_file']['name']);
        $ext = strtolower($fileInfo['extension']);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
            $newFileName = $car['slug'] . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $uploadDir . $newFileName)) {
                $featured_image = 'assets/images/cars/' . $newFileName;
            }
        }
    }

    if (empty($name) || empty($brand_id) || $price_min <= 0) {
        $error = 'Please fill out all required fields.';
    } else {
        $sql = "UPDATE cars SET name = ?, brand_id = ?, body_type = ?, fuel_type = ?, transmission = ?, price_min = ?, price_max = ?, status = ?, engine_displacement = ?, power = ?, mileage = ?, safety_rating = ?, overview = ?, pros = ?, cons = ?, featured_image = ? WHERE id = ?";
        $up = $pdo->prepare($sql);
        $up->execute([$name, $brand_id, $body_type, $fuel_type, $transmission, $price_min, $price_max, $status, $engine_displacement, $power, $mileage, $safety_rating, $overview, $pros, $cons, $featured_image, $id]);

        header('Location: cars.php?msg=Car+updated+successfully');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= htmlspecialchars($car['name']) ?> - AutoPulse Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="admin-logo">AUTO<span>PULSE</span> CMS</div>
        <ul class="admin-nav-list">
            <li class="admin-nav-item"><a href="index.php">&#9881; Dashboard</a></li>
            <li class="admin-nav-item active"><a href="cars.php">&#128663; Manage Cars</a></li>
            <li class="admin-nav-item"><a href="news.php">&#128240; Manage News</a></li>
            <li class="admin-nav-item"><a href="reviews.php">&#9733; Moderate Reviews</a></li>
            <li class="admin-nav-item"><a href="comments.php">&#128172; Moderate Comments</a></li>
            <li class="admin-nav-item"><a href="../index.php" target="_blank">&#127760; View Public Site</a></li>
            <li class="admin-nav-item" style="margin-top: 40px;"><a href="logout.php" style="color: #f87171;">&#10148; Sign Out</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 900;">Edit <?= htmlspecialchars($car['name']) ?></h1>
                <p style="font-size: 13px; color: #6b7280;">Modify technical details, pricing, and status.</p>
            </div>
            <a href="cars.php" class="btn-card-action" style="background: #374151;">&larr; Back to Cars</a>
        </div>

        <?php if (!empty($error)): ?>
            <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="background: #fff; padding: 32px; border: 1px solid var(--border-color); border-radius: 4px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Model Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($car['name']) ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Brand *</label>
                    <select name="brand_id" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $b['id'] == $car['brand_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Body Type</label>
                    <select name="body_type" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <?php foreach (['SUV', 'Sedan', 'Hatchback', 'EV', 'Luxury', 'MUV'] as $bt): ?>
                            <option value="<?= $bt ?>" <?= $car['body_type'] === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Fuel Type</label>
                    <select name="fuel_type" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <?php foreach (['Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG'] as $ft): ?>
                            <option value="<?= $ft ?>" <?= $car['fuel_type'] === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Transmission</label>
                    <select name="transmission" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <?php foreach (['Automatic', 'Manual', 'AMT', 'DCT'] as $tr): ?>
                            <option value="<?= $tr ?>" <?= $car['transmission'] === $tr ? 'selected' : '' ?>><?= $tr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Min Price (Lakhs)</label>
                    <input type="number" step="0.01" name="price_min" value="<?= $car['price_min'] ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Max Price (Lakhs)</label>
                    <input type="number" step="0.01" name="price_max" value="<?= $car['price_max'] ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Status</label>
                    <select name="status" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="Available" <?= $car['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Upcoming" <?= $car['status'] === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="Trending" <?= $car['status'] === 'Trending' ? 'selected' : '' ?>>Trending</option>
                    </select>
                </div>
            </div>

            <!-- Image Upload & Preview -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Featured Image</label>
                <div style="display: flex; gap: 14px; align-items: center;">
                    <img src="../<?= htmlspecialchars($car['featured_image']) ?>" alt="" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                    <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.svg,.webp" style="flex:1; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
            </div>

            <!-- Overview -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Overview</label>
                <textarea name="overview" rows="4" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;"><?= htmlspecialchars($car['overview']) ?></textarea>
            </div>

            <button type="submit" class="btn-card-action" style="background: var(--primary-red); padding: 14px 28px; font-size: 14px; cursor: pointer;">
                Update Car Model
            </button>
        </form>
    </main>
</div>

</body>
</html>
