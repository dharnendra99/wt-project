<?php
/**
 * AutoPulse - User Login Page
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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            $ref = $_GET['ref'] ?? 'index.php';
            header("Location: {$ref}");
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

$page_title = 'User Sign In';
include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 480px; margin: 50px auto 70px auto;">
        <div style="background: #fff; padding: 36px; border: 1px solid var(--border-color); border-radius: 6px; box-shadow: var(--shadow-sm);">
            
            <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px; text-transform: uppercase;">
                Sign <span style="color: var(--primary-red);">In</span>
            </h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
                Access your AutoPulse saved cars, road test reviews, and bookmarks.
            </p>

            <?php if (!empty($error)): ?>
                <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; margin-bottom: 20px; font-size: 13.5px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'rahul@example.com') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Password</label>
                    <input type="password" name="password" value="user123" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 14px;" required>
                    <small style="display:block; margin-top:4px; color:var(--text-muted);">Demo credentials pre-filled: <code>rahul@example.com</code> / <code>user123</code></small>
                </div>

                <button type="submit" class="btn-card-action" style="width: 100%; padding: 14px; background: var(--primary-red); font-size: 14px; cursor: pointer;">
                    Sign In to AutoPulse
                </button>
            </form>

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-light); text-align: center; font-size: 13px;">
                Don't have an account? <a href="register.php" style="color: var(--primary-red); font-weight: 700;">Create Account</a>
            </div>

            <div style="margin-top: 12px; text-align: center; font-size: 12px; color: var(--text-muted);">
                Are you an editor? <a href="admin/login.php" style="text-decoration: underline;">Admin Portal Login</a>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
