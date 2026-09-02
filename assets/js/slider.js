/**
 * AutoPulse - Hero Carousel / Image Slider
 * Smooth slide transitions, pagination dots, next/prev navigation, and auto-play.
 */

document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('heroSliderTrack');
    if (!track) return;

    const slides = Array.from(track.children);
    const prevBtn = document.getElementById('sliderPrevBtn');
    const nextBtn = document.getElementById('sliderNextBtn');
    const dotsContainer = document.getElementById('sliderDots');

    if (slides.length <= 1) return;

    let currentIndex = 0;
    let autoPlayTimer = null;

    // Create indicator dots
    if (dotsContainer) {
        dotsContainer.innerHTML = '';
        slides.forEach((_, idx) => {
            const dot = document.createElement('div');
            dot.classList.add('slider-dot');
            if (idx === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(idx));
            dotsContainer.appendChild(dot);
        });
    }

    function updateSlide() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        slides.forEach((slide, idx) => {
            if (idx === currentIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });

        if (dotsContainer) {
            const dots = dotsContainer.querySelectorAll('.slider-dot');
            dots.forEach((dot, idx) => {
                dot.classList.toggle('active', idx === currentIndex);
            });
        }
    }

    function goToSlide(index) {
        currentIndex = (index + slides.length) % slides.length;
        updateSlide();
        resetTimer();
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);

    function startTimer() {
        autoPlayTimer = setInterval(nextSlide, 5000);
    }

    function resetTimer() {
        clearInterval(autoPlayTimer);
        startTimer();
    }

    // Pause on hover
    const wrapper = document.querySelector('.hero-slider-wrapper');
    if (wrapper) {
        wrapper.addEventListener('mouseenter', () => clearInterval(autoPlayTimer));
        wrapper.addEventListener('mouseleave', startTimer);
    }

    startTimer();
    updateSlide();
});
