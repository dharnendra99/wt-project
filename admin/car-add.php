<?php
/**
 * AutoPulse - Admin: Add New Car
 */

require_once __DIR__ . '/includes/admin_auth.php';

$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $body_type = $_POST['body_type'] ?? 'SUV';
    $fuel_type = $_POST['fuel_type'] ?? 'Petrol';
    $transmission = $_POST['transmission'] ?? 'Automatic';
    $price_min = (float)($_POST['price_min'] ?? 0);
    $price_max = (float)($_POST['price_max'] ?? 0);
    $price_label = sanitize($_POST['price_label'] ?? 'Ex-showroom price');
    $status = $_POST['status'] ?? 'Available';
    $seating_capacity = (int)($_POST['seating_capacity'] ?? 5);
    $engine_displacement = sanitize($_POST['engine_displacement'] ?? '');
    $power = sanitize($_POST['power'] ?? '');
    $torque = sanitize($_POST['torque'] ?? '');
    $mileage = sanitize($_POST['mileage'] ?? '');
    $safety_rating = sanitize($_POST['safety_rating'] ?? '5 Star (BNCAP)');
    $overview = sanitize($_POST['overview'] ?? '');
    $pros = sanitize($_POST['pros'] ?? '');
    $cons = sanitize($_POST['cons'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Generate unique slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE slug = ?");
    $stmtCheck->execute([$slug]);
    if ($stmtCheck->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    // Default image
    $featured_image = 'assets/images/cars/nexon.svg';

    // Handle File Upload if provided
    if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/cars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileInfo = pathinfo($_FILES['featured_image_file']['name']);
        $ext = strtolower($fileInfo['extension']);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
            $newFileName = $slug . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $uploadDir . $newFileName)) {
                $featured_image = 'assets/images/cars/' . $newFileName;
            }
        }
    }

    if (empty($name) || empty($brand_id) || $price_min <= 0) {
        $error = 'Please fill out all required fields (Name, Brand, Min Price).';
    } else {
        $sql = "INSERT INTO cars (name, slug, brand_id, body_type, fuel_type, transmission, price_min, price_max, price_label, status, seating_capacity, engine_displacement, power, torque, mileage, safety_rating, featured_image, gallery_images, overview, pros, cons, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, $slug, $brand_id, $body_type, $fuel_type, $transmission, $price_min, $price_max, $price_label, $status, $seating_capacity, $engine_displacement, $power, $torque, $mileage, $safety_rating, $featured_image, $featured_image, $overview, $pros, $cons, $is_featured
        ]);

        header('Location: cars.php?msg=Car+added+successfully');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Car Model - AutoPulse Admin</title>
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
                <h1 style="font-size: 24px; font-weight: 900;">Add New Car Model</h1>
                <p style="font-size: 13px; color: #6b7280;">Add a new vehicle with technical specifications, pricing, and image upload.</p>
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
                    <input type="text" name="name" placeholder="e.g. Tata Sierra EV" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Brand *</label>
                    <select name="brand_id" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Body Type</label>
                    <select name="body_type" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="SUV">SUV</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="EV">EV (Electric)</option>
                        <option value="Luxury">Luxury</option>
                        <option value="MUV">MUV</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Fuel Type</label>
                    <select name="fuel_type" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                        <option value="CNG">CNG</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Transmission</label>
                    <select name="transmission" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                        <option value="AMT">AMT</option>
                        <option value="DCT">DCT</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Min Price (Lakhs INR) *</label>
                    <input type="number" step="0.01" name="price_min" placeholder="e.g. 10.50" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Max Price (Lakhs INR)</label>
                    <input type="number" step="0.01" name="price_max" placeholder="e.g. 18.20" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Status</label>
                    <select name="status" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                        <option value="Available">Available</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Trending">Trending</option>
                    </select>
                </div>
            </div>

            <!-- Specs Row -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Engine / Motor</label>
                    <input type="text" name="engine_displacement" placeholder="e.g. 1498 cc" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Power</label>
                    <input type="text" name="power" placeholder="e.g. 148 bhp" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Mileage</label>
                    <input type="text" name="mileage" placeholder="e.g. 18.5 kmpl" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Crash Safety</label>
                    <input type="text" name="safety_rating" placeholder="5 Star (BNCAP)" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
                </div>
            </div>

            <!-- Image Upload -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Featured Car Image (JPG, PNG, SVG)</label>
                <input type="file" name="featured_image_file" accept=".jpg,.jpeg,.png,.svg,.webp" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;">
            </div>

            <!-- Overview -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Road Test Overview &amp; Review</label>
                <textarea name="overview" rows="4" placeholder="Autocar India road test summary..." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;"></textarea>
            </div>

            <!-- Pros & Cons -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Pros (Separate with pipe |)</label>
                    <textarea name="pros" rows="2" placeholder="5-star safety|Refined engine|Punchy low-end" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;"></textarea>
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Cons (Separate with pipe |)</label>
                    <textarea name="cons" rows="2" placeholder="Stiff ride|No diesel option|Tight boot" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px;"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-card-action" style="background: var(--primary-red); padding: 14px 28px; font-size: 14px; cursor: pointer;">
                Save Car Model
            </button>
        </form>
    </main>
</div>

</body>
</html>
