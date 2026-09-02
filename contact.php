<?php
/**
 * AutoPulse - Contact & Helpline
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'contact';
$page_title = 'Contact AutoPulse Editorial & Road Test Team';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = 'Thank you for reaching out! Our editorial team will get back to you shortly.';
}

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 900px; margin: 30px auto 60px auto;">
        
        <div class="section-header">
            <h1 class="section-title">Contact <span class="accent">Us</span></h1>
            <span class="meta-text">Reach Out to Editorial, Road Tests &amp; Support</span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 32px;">
            <!-- Contact Info -->
            <div style="background: #fff; padding: 32px; border: 1px solid var(--border-color); border-radius: 4px;">
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Editorial Headquarters</h3>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
                    AutoPulse Media Towers<br>
                    Plot 14, Okhla Industrial Area, Phase-III<br>
                    New Delhi - 110020, India
                </p>

                <div style="margin-bottom: 16px;">
                    <strong style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); display: block;">Helpline (Mon - Sat)</strong>
                    <span style="font-size: 16px; font-weight: 800; color: var(--primary-red);">+91 11 4567 8900</span>
                </div>

                <div style="margin-bottom: 16px;">
                    <strong style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); display: block;">Email Inquiries</strong>
                    <span style="font-size: 14px; color: var(--text-dark); font-weight: 700;">contact@autopulse.com</span>
                </div>

                <div>
                    <strong style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); display: block;">Road Test Tips</strong>
                    <span style="font-size: 14px; color: var(--text-dark); font-weight: 700;">editor@autopulse.com</span>
                </div>
            </div>

            <!-- Contact Form -->
            <div style="background: #fff; padding: 32px; border: 1px solid var(--border-color); border-radius: 4px;">
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Send Us a Message</h3>
                
                <?php if (!empty($msg)): ?>
                    <div style="padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 4px; margin-bottom: 16px; font-size: 14px;">
                        <?= $msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Your Name</label>
                        <input type="text" name="name" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Email Address</label>
                        <input type="email" name="email" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Subject / Car Model</label>
                        <input type="text" name="subject" placeholder="e.g. Question about Nexon vs Creta" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Message</label>
                        <textarea name="message" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" required></textarea>
                    </div>

                    <button type="submit" class="btn-card-action" style="width: 100%; padding: 12px; background: var(--primary-red); cursor: pointer;">
                        Submit Inquiry
                    </button>
                </form>
            </div>
        </div>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
