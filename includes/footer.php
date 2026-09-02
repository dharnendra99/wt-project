<?php
/**
 * AutoPulse - Shared Footer Component
 * Autocar India-inspired multi-column dark footer with integrated rule-based floating chatbot.
 */
?>
<footer class="site-footer">
    <div class="container">
        <!-- Multi-column link groups -->
        <div class="footer-top-grid">
            <div class="footer-brand">
                <h3>AUTO<span>PULSE</span></h3>
                <p>India's leading authority on automotive journalism, road tests, verified car pricing, and unbiased reviews since 2024.</p>
                <div class="social-links-row">
                    <a href="#" class="social-btn" aria-label="Facebook">fb</a>
                    <a href="#" class="social-btn" aria-label="X / Twitter">&#120143;</a>
                    <a href="#" class="social-btn" aria-label="YouTube">yt</a>
                    <a href="#" class="social-btn" aria-label="Instagram">ig</a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Cars</h4>
                <ul class="footer-links">
                    <li><a href="cars.php?body_type=SUV">SUV Cars</a></li>
                    <li><a href="cars.php?body_type=Sedan">Sedan Cars</a></li>
                    <li><a href="cars.php?body_type=Hatchback">Hatchbacks</a></li>
                    <li><a href="cars.php?fuel_type=Electric">Electric Cars (EV)</a></li>
                    <li><a href="cars.php?status=Upcoming">Upcoming Cars</a></li>
                    <li><a href="compare.php">Compare Cars</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Bikes</h4>
                <ul class="footer-links">
                    <li><a href="news.php?cat=Bike+News">Bike News</a></li>
                    <li><a href="news.php?cat=Bike+News">Superbikes</a></li>
                    <li><a href="news.php?cat=Bike+News">Electric Scooters</a></li>
                    <li><a href="news.php?cat=Bike+News">Commuter Bikes</a></li>
                    <li><a href="news.php?cat=Motorsport">MotoGP Reports</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Editorial</h4>
                <ul class="footer-links">
                    <li><a href="news.php">Latest Auto News</a></li>
                    <li><a href="reviews.php">Road Test Reviews</a></li>
                    <li><a href="news.php?cat=Motorsport">Motorsport &amp; F1</a></li>
                    <li><a href="news.php?cat=Industry">Industry Insights</a></li>
                    <li><a href="search.php?q=Safety">Crash Test Ratings</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>AutoPulse</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Editorial Team</a></li>
                    <li><a href="contact.php">Contact &amp; Helpline</a></li>
                    <li><a href="admin/login.php">Editor &amp; Admin Portal</a></li>
                    <li><a href="wishlist.php">Saved Garage</a></li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> AutoPulse India. Inspired by Autocar India design &amp; structure. College Mini Project.</p>
            <p>Built with plain PHP, MySQL &amp; Vanilla JavaScript. 100% Offline Compatible.</p>
        </div>
    </div>
</footer>

<!-- ==========================================================================
     Floating Rule-Based Chatbot Assistant (Offline FAQ & Dynamic Pricing)
     ========================================================================== -->
<div class="chatbot-widget-container" id="chatbotContainer">
    <!-- Toggle Floating Button -->
    <button class="chatbot-toggle-btn" id="chatbotToggleBtn" aria-label="Open AutoPulse Assistant">
        <span class="chat-bubble-icon">&#128172;</span>
        <span class="chat-close-icon">&#10005;</span>
    </button>

    <!-- Chat Drawer Window -->
    <div class="chatbot-drawer" id="chatbotDrawer">
        <!-- Red Header Bar -->
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-header-avatar">&#9881;</div>
                <div class="chatbot-header-text">
                    <h4>AutoPulse Assistant</h4>
                    <p>Instant Car Prices, Specs &amp; FAQ</p>
                </div>
            </div>
            <button id="chatbotCloseBtn" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">&#10005;</button>
        </div>

        <!-- Messages Body -->
        <div class="chatbot-messages-body" id="chatbotMessagesBody">
            <div class="chat-message bot">
                <div class="chat-bubble">
                    Hello! I am your <strong>AutoPulse Rule Assistant</strong>. Ask me anything about car prices, specs, mileage, upcoming launches, or test drives!
                </div>
            </div>
            <div class="chat-suggestions-row">
                <span class="chat-chip" data-prompt="Price of Nexon">Price of Nexon</span>
                <span class="chat-chip" data-prompt="Price of Creta">Price of Creta</span>
                <span class="chat-chip" data-prompt="Compare Nexon and Creta">Compare Cars</span>
                <span class="chat-chip" data-prompt="Upcoming cars">Upcoming cars</span>
            </div>
        </div>

        <!-- Input Row -->
        <div class="chat-input-row">
            <input type="text" id="chatInputField" class="chat-input-field" placeholder="Ask e.g. 'Price of Nexon'..." autocomplete="off">
            <button id="chatSendBtn" class="chat-send-btn" aria-label="Send Message">&#10148;</button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/main.js"></script>
<script src="assets/js/slider.js"></script>
<script src="assets/js/chatbot.js"></script>

</body>
</html>
