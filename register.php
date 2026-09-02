<?php
/**
 * AutoPulse - User Registration Page
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $ins->execute([$name, $email, $hash]);

            // Auto-login
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'user';

            header('Location: index.php');
            exit;
        }
    }
}

$page_title = 'Create AutoPulse Account';
include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 480px; margin: 50px auto 70px auto;">
        <div style="background: #fff; padding: 36px; border: 1px solid var(--border-color); border-radius: 6px; box-shadow: var(--shadow-sm);">
            
            <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px; text-transform: uppercase;">
                Create <span style="color: var(--primary-red);">Account</span>
            </h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
                Join the AutoPulse community to save cars, rate vehicles, and comment.
            </p>

            <?php if (!empty($error)): ?>
                <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 20px; font-size: 13.5px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Password (Min. 6 chars)</label>
                    <input type="password" name="password" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Confirm Password</label>
                    <input type="password" name="confirm_password" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                </div>

                <button type="submit" class="btn-card-action" style="width: 100%; padding: 14px; background: var(--primary-red); font-size: 14px; cursor: pointer;">
                    Register Now
                </button>
            </form>

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-light); text-align: center; font-size: 13px;">
                Already have an account? <a href="login.php" style="color: var(--primary-red); font-weight: 700;">Sign In</a>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
