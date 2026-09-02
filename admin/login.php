<?php
/**
 * AutoPulse - Admin Login Page
 */

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter admin credentials.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'admin';

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid admin credentials. Access denied.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoPulse - Editorial &amp; Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div style="background: #ffffff; width: 100%; max-width: 440px; padding: 36px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 24px;">
            <a href="../index.php" style="font-size: 26px; font-weight: 900; text-decoration: none; color: #1a1a1a;">
                AUTO<span style="color: #d90000;">PULSE</span>
            </a>
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #666; letter-spacing: 1px; margin-top: 4px;">
                Editorial Content Management System
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 20px; font-size: 13.5px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; color: #374151;">Admin Email</label>
                <input type="email" name="email" value="admin@autopulse.com" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" required>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; color: #374151;">Master Password</label>
                <input type="password" name="password" value="admin123" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" required>
                <small style="display: block; margin-top: 4px; color: #6b7280;">Default: <code>admin@autopulse.com</code> / <code>admin123</code></small>
            </div>

            <button type="submit" class="btn-card-action" style="width: 100%; padding: 14px; background: #d90000; font-size: 14px; cursor: pointer;">
                Enter Admin Portal
            </button>
        </form>

        <div style="margin-top: 24px; text-align: center; font-size: 12px; color: #6b7280;">
            <a href="../index.php" style="color: #374151; text-decoration: underline;">&larr; Back to Public Portal</a>
        </div>
    </div>

</body>
</html>
