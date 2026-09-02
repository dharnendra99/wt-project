<?php
/**
 * AutoPulse - About Us & Editorial Philosophy
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'about';
$page_title = 'About AutoPulse - Automotive Journalism Benchmark';
include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 900px; margin: 30px auto 60px auto;">
        
        <div class="section-header">
            <h1 class="section-title">About <span class="accent">AutoPulse</span></h1>
            <span class="meta-text">Our Story &amp; Testing Heritage</span>
        </div>

        <div style="background: #fff; padding: 36px; border: 1px solid var(--border-color); border-radius: 4px; line-height: 1.8; font-size: 16px;">
            <p style="margin-bottom: 20px;">
                Inspired by the benchmark standards of Indian automotive journalism pioneered by Autocar India, <strong>AutoPulse</strong> was established to provide the country’s car and motorcycle enthusiasts with exhaustive, scientific, and uncompromising road tests.
            </p>

            <h3 style="font-size: 20px; font-weight: 800; text-transform: uppercase; margin: 28px 0 12px 0; color: var(--primary-red);">
                Our Road Testing Rigor
            </h3>
            <p style="margin-bottom: 20px;">
                Every car featured on AutoPulse undergoes a rigorous testing protocol: GPS-verified acceleration runs, real-world city and highway fuel efficiency loops, sound level decibel testing at cruising speeds, and braking distance measurements from 100 to 0 km/h.
            </p>

            <h3 style="font-size: 20px; font-weight: 800; text-transform: uppercase; margin: 28px 0 12px 0; color: var(--primary-red);">
                Unbiased Verdicts &amp; Safety First
            </h3>
            <p style="margin-bottom: 20px;">
                We champion vehicle safety ratings. We prominently feature verified Bharat NCAP and Global NCAP crash test results, electronic stability control availability, and airbag distributions across variant lineups to help buyers make informed, safe choices.
            </p>

            <div style="margin-top: 32px; padding: 24px; background: var(--bg-section); border-left: 4px solid var(--primary-red); border-radius: 4px;">
                <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 8px;">College Mini Project Submission</h4>
                <p style="font-size: 14px; color: var(--text-muted); margin: 0;">
                    AutoPulse is developed with pure PHP (PDO prepared statements), vanilla JavaScript, MySQL, and modular CSS with zero external API dependencies. Built for full local offline evaluation and seamless Vercel frontend deployment.
                </p>
            </div>
        </div>

    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
