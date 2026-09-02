/**
 * AutoPulse - AJAX Car Filtering Engine
 * Filters cars seamlessly by Brand, Body Type, Fuel, and Price Range without page reloads.
 * Supports dual mode: PHP API endpoint locally, or static JSON fallback when deployed on Vercel!
 */

document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('carFilterForm');
    const carsGrid = document.getElementById('carsResultGrid');
    const carsCount = document.getElementById('resultCount');
    const resetBtn = document.getElementById('resetFiltersBtn');

    if (!filterForm || !carsGrid) return;

    // Listen to changes on any filter input
    filterForm.addEventListener('change', () => {
        applyFilters();
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            filterForm.reset();
            applyFilters();
        });
    }

    function applyFilters() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }

        // Show loading state
        carsGrid.style.opacity = '0.5';

        // Try PHP backend API first
        fetch('api/filter-cars.php?' + params.toString())
            .then(res => {
                if (!res.ok) throw new Error('PHP API not available');
                return res.json();
            })
            .then(data => {
                carsGrid.innerHTML = data.html;
                if (carsCount) carsCount.textContent = data.count;
                carsGrid.style.opacity = '1';
            })
            .catch(() => {
                // Client-side fallback for static/Vercel deployment
                filterClientSide(formData);
            });
    }

    // Static fallback for Vercel
    function filterClientSide(formData) {
        fetch('frontend/data/cars.json')
            .catch(() => fetch('data/cars.json'))
            .then(res => res.json())
            .then(allCars => {
                const brands = formData.getAll('brand[]');
                const bodyTypes = formData.getAll('body_type[]');
                const fuelTypes = formData.getAll('fuel_type[]');
                const maxPrice = formData.get('max_price');

                let filtered = allCars.filter(car => {
                    if (brands.length > 0 && !brands.includes(car.brand_slug)) return false;
                    if (bodyTypes.length > 0 && !bodyTypes.includes(car.body_type)) return false;
                    if (fuelTypes.length > 0 && !fuelTypes.includes(car.fuel_type)) return false;
                    if (maxPrice && parseFloat(car.price_min) > parseFloat(maxPrice)) return false;
                    return true;
                });

                if (carsCount) carsCount.textContent = filtered.length;

                if (filtered.length === 0) {
                    carsGrid.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 48px; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px;">
                            <h3 style="font-size: 20px; color: #1a1a1a; margin-bottom: 8px;">No matching cars found</h3>
                            <p style="color: #666;">Try adjusting your filters or price range to explore more models.</p>
                        </div>
                    `;
                } else {
                    carsGrid.innerHTML = filtered.map(car => `
                        <div class="car-card">
                            <div class="car-card-media">
                                <img src="${car.featured_image}" alt="${car.name}" loading="lazy">
                                <span class="car-badge-status ${car.status.toLowerCase()}">${car.status}</span>
                                <button class="car-wishlist-btn" data-car-id="${car.id}">&#9825;</button>
                            </div>
                            <div class="car-card-body">
                                <span class="car-card-brand">${car.brand_name || 'Automobile'}</span>
                                <h3 class="car-card-title">
                                    <a href="car-detail.php?slug=${car.slug}">${car.name}</a>
                                </h3>
                                <div class="car-card-specs">
                                    <span>${car.fuel_type}</span> •
                                    <span>${car.transmission}</span> •
                                    <span>${car.mileage}</span>
                                </div>
                                <div class="car-card-footer">
                                    <div class="car-price-block">
                                        <span class="price-text">Rs ${car.price_min} - ${car.price_max} Lakh*</span>
                                        <span class="price-label">Ex-showroom price</span>
                                    </div>
                                    <a href="car-detail.php?slug=${car.slug}" class="btn-card-action">Explore</a>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
                carsGrid.style.opacity = '1';
            })
            .catch(err => {
                console.error('Error loading fallback cars data', err);
                carsGrid.style.opacity = '1';
            });
    }
});
