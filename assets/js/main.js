/**
 * AutoPulse - Main Application Scripts
 * Manages City Selector, Mobile Navigation, Search Bar, and Wishlist toggling.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. City / Location Selector Persistence
    const citySelect = document.getElementById('userCitySelect');
    if (citySelect) {
        const savedCity = localStorage.getItem('autopulse_city');
        if (savedCity) {
            citySelect.value = savedCity;
        }
        citySelect.addEventListener('change', (e) => {
            localStorage.setItem('autopulse_city', e.target.value);
        });
    }

    // 2. Mobile Navigation Toggle
    const mobileToggle = document.getElementById('mobileNavToggle');
    const horizontalNav = document.getElementById('horizontalNav');
    if (mobileToggle && horizontalNav) {
        mobileToggle.addEventListener('click', () => {
            horizontalNav.classList.toggle('show');
        });
    }

    // 3. Search Bar Toggle
    const searchBtn = document.getElementById('topSearchBtn');
    const searchPanel = document.getElementById('searchOverlayPanel');
    if (searchBtn && searchPanel) {
        searchBtn.addEventListener('click', () => {
            searchPanel.classList.toggle('open');
            if (searchPanel.classList.contains('open')) {
                const input = searchPanel.querySelector('input');
                if (input) input.focus();
            }
        });
    }

    // 4. Wishlist Toggle (AJAX + LocalStorage fallback)
    document.addEventListener('click', (e) => {
        const wishlistBtn = e.target.closest('.car-wishlist-btn');
        if (!wishlistBtn) return;

        e.preventDefault();
        const carId = wishlistBtn.dataset.carId;
        if (!carId) return;

        // Toggle visual state
        wishlistBtn.classList.toggle('active');
        const isActive = wishlistBtn.classList.contains('active');
        wishlistBtn.innerHTML = isActive ? '&#9829;' : '&#9825;';

        // Check if user is logged in via server session or use client storage
        fetch('api/toggle-wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ car_id: carId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'redirect') {
                window.location.href = 'login.php?ref=' + encodeURIComponent(window.location.pathname);
            } else if (data.status === 'success') {
                updateWishlistCounter(data.count);
            }
        })
        .catch(() => {
            // Client-side fallback for Vercel/Static mode
            let localWishlist = JSON.parse(localStorage.getItem('autopulse_wishlist') || '[]');
            if (isActive) {
                if (!localWishlist.includes(carId)) localWishlist.push(carId);
            } else {
                localWishlist = localWishlist.filter(id => id !== carId);
            }
            localStorage.setItem('autopulse_wishlist', JSON.stringify(localWishlist));
            updateWishlistCounter(localWishlist.length);
        });
    });

    function updateWishlistCounter(count) {
        const badge = document.getElementById('wishlistCountBadge');
        if (badge) {
            badge.textContent = count;
        }
    }
});
