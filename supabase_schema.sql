-- ============================================================
-- AutoPulse - Supabase PostgreSQL Database Schema & Seed Data
-- 100% Free Cloud Database Setup for AutoPulse
-- Instructions: Run this script inside the Supabase SQL Editor.
-- Safe to run multiple times — uses IF NOT EXISTS and DROP IF EXISTS
-- ============================================================

-- 1. Brands Table
CREATE TABLE IF NOT EXISTS public.brands (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    logo_url TEXT,
    country VARCHAR(100) DEFAULT 'India',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS public.categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 3. Cars Table
CREATE TABLE IF NOT EXISTS public.cars (
    id SERIAL PRIMARY KEY,
    brand_id INTEGER REFERENCES public.brands(id) ON DELETE SET NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    brand_name VARCHAR(100),
    body_type VARCHAR(50) NOT NULL,
    fuel_type VARCHAR(50) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    price_min NUMERIC(6,2) NOT NULL,
    price_max NUMERIC(6,2) NOT NULL,
    price_label VARCHAR(100) DEFAULT 'Ex-showroom price',
    status VARCHAR(50) DEFAULT 'Available',
    seating_capacity INT DEFAULT 5,
    engine_displacement VARCHAR(100),
    power VARCHAR(100),
    torque VARCHAR(100),
    mileage VARCHAR(100),
    safety_rating VARCHAR(100),
    featured_image TEXT NOT NULL,
    gallery_images TEXT[] DEFAULT '{}',
    overview TEXT,
    pros TEXT[] DEFAULT '{}',
    cons TEXT[] DEFAULT '{}',
    is_featured INT DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 4. News Articles Table
CREATE TABLE IF NOT EXISTS public.news_articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    subtitle TEXT,
    content TEXT NOT NULL,
    image TEXT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_role VARCHAR(100) DEFAULT 'Automotive Journalist',
    author_avatar TEXT,
    category VARCHAR(50) DEFAULT 'Car News',
    model_tag VARCHAR(100),
    views_count INT DEFAULT 1000,
    is_hero INT DEFAULT 0,
    is_trending INT DEFAULT 0,
    published_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 5. Reviews Table
CREATE TABLE IF NOT EXISTS public.reviews (
    id SERIAL PRIMARY KEY,
    car_id INTEGER REFERENCES public.cars(id) ON DELETE CASCADE,
    car_name VARCHAR(150),
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(150),
    rating NUMERIC(2,1) NOT NULL,
    title VARCHAR(255) NOT NULL,
    review_text TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 6. Comments Table
CREATE TABLE IF NOT EXISTS public.comments (
    id SERIAL PRIMARY KEY,
    article_id INTEGER REFERENCES public.news_articles(id) ON DELETE CASCADE,
    article_title VARCHAR(255),
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    comment_text TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 7. Chatbot Knowledge Rules Table
CREATE TABLE IF NOT EXISTS public.chatbot_responses (
    id SERIAL PRIMARY KEY,
    keyword_triggers TEXT NOT NULL,
    response_text TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- ============================================================
-- ROW LEVEL SECURITY (RLS) POLICIES
-- DROP IF EXISTS first so this script is always safe to re-run
-- ============================================================

ALTER TABLE public.brands ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.cars ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.news_articles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reviews ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.chatbot_responses ENABLE ROW LEVEL SECURITY;

-- ── Drop all existing policies first (safe to run even if they don't exist) ──
DROP POLICY IF EXISTS "Allow public read access on brands" ON public.brands;
DROP POLICY IF EXISTS "Allow public read access on categories" ON public.categories;
DROP POLICY IF EXISTS "Allow public read access on cars" ON public.cars;
DROP POLICY IF EXISTS "Allow public read access on news_articles" ON public.news_articles;
DROP POLICY IF EXISTS "Allow public read access on reviews" ON public.reviews;
DROP POLICY IF EXISTS "Allow public read access on comments" ON public.comments;
DROP POLICY IF EXISTS "Allow public read access on chatbot_responses" ON public.chatbot_responses;
DROP POLICY IF EXISTS "Allow public insert on reviews" ON public.reviews;
DROP POLICY IF EXISTS "Allow public insert on comments" ON public.comments;

-- ── Recreate Read Policies (Public anonymous access) ──
CREATE POLICY "Allow public read access on brands"
    ON public.brands FOR SELECT USING (true);

CREATE POLICY "Allow public read access on categories"
    ON public.categories FOR SELECT USING (true);

CREATE POLICY "Allow public read access on cars"
    ON public.cars FOR SELECT USING (true);

CREATE POLICY "Allow public read access on news_articles"
    ON public.news_articles FOR SELECT USING (true);

CREATE POLICY "Allow public read access on reviews"
    ON public.reviews FOR SELECT USING (status = 'approved');

CREATE POLICY "Allow public read access on comments"
    ON public.comments FOR SELECT USING (status = 'approved');

CREATE POLICY "Allow public read access on chatbot_responses"
    ON public.chatbot_responses FOR SELECT USING (true);

-- ── Recreate Insert Policies (Allow users to submit reviews and comments) ──
CREATE POLICY "Allow public insert on reviews"
    ON public.reviews FOR INSERT WITH CHECK (true);

CREATE POLICY "Allow public insert on comments"
    ON public.comments FOR INSERT WITH CHECK (true);

-- ============================================================
-- SEED DATA
-- ON CONFLICT DO NOTHING = safe to re-run, won't duplicate rows
-- ============================================================

-- Seed Brands
INSERT INTO public.brands (id, name, slug, country) VALUES
(1, 'Tata Motors', 'tata-motors', 'India'),
(2, 'Mahindra', 'mahindra', 'India'),
(3, 'Hyundai', 'hyundai', 'South Korea'),
(4, 'Maruti Suzuki', 'maruti-suzuki', 'India'),
(5, 'BMW', 'bmw', 'Germany')
ON CONFLICT (id) DO NOTHING;

-- Seed Cars (Using Real PNG images)
INSERT INTO public.cars (id, brand_id, name, slug, brand_name, body_type, fuel_type, transmission, price_min, price_max, status, seating_capacity, engine_displacement, power, torque, mileage, safety_rating, featured_image, gallery_images, overview, pros, cons, is_featured) VALUES
(1, 1, 'Tata Nexon Facelift', 'tata-nexon-facelift', 'Tata Motors', 'SUV', 'Petrol', 'Automatic', 8.00, 15.50, 'Trending', 5, '1199 cc', '118 bhp', '170 Nm', '17.4 kmpl', '5 Star (BNCAP / GNCAP)', 'assets/images/cars/nexon-real.png', ARRAY['assets/images/cars/nexon-real.png'], 'The Tata Nexon facelift elevates the compact SUV segment with an aggressive futuristic front fascia, bi-LED projector headlamps, a 10.25-inch floating touchscreen, and uncompromising 5-star crash safety build.', ARRAY['Top-class 5-star safety rating', 'Striking concept-car styling', 'Punchy turbo petrol and diesel options', 'Feature-loaded cabin with 360-degree camera'], ARRAY['Infotainment software can have occasional lag', 'Firm low-speed ride quality', 'Rear seat headroom average for very tall passengers'], 1),
(2, 2, 'Mahindra XUV700', 'mahindra-xuv700', 'Mahindra', 'SUV', 'Diesel', 'Automatic', 13.99, 26.99, 'Trending', 7, '2198 cc', '182 bhp', '450 Nm', '16.5 kmpl', '5 Star (GNCAP)', 'assets/images/cars/xuv700-real.png', ARRAY['assets/images/cars/xuv700-real.png'], 'Mahindra XUV700 sets the benchmark in full-size mid-segment SUVs, offering dual 10.25-inch superscreens, Level-2 ADAS driver assistance, optional AWD, and blistering powertrain performance.', ARRAY['Blistering 2.0L Turbo Petrol & 2.2L Diesel engines', 'Segment-first Level 2 ADAS suite', 'Comfortable 3-row layout and Sony 3D sound system', 'High speed stability and composure'], ARRAY['Long waiting periods for top variants', 'Third row best suited for children', 'Boot space with all 3 rows up is limited'], 1),
(3, 3, 'Hyundai Creta', 'hyundai-creta', 'Hyundai', 'SUV', 'Petrol', 'Automatic', 11.00, 20.15, 'Trending', 5, '1497 cc', '158 bhp', '253 Nm', '18.4 kmpl', '3 Star (GNCAP)', 'assets/images/cars/creta-real.png', ARRAY['assets/images/cars/creta-real.png'], 'The Hyundai Creta is Indias undisputed king of mid-size SUVs, featuring bold parametric jewel grille, panoramic sunroof, ventilated front seats, dual-zone climate control, and refined driving dynamics.', ARRAY['Silky smooth refined petrol & diesel engine choices', 'Plush premium cabin ergonomics', 'Panoramic sunroof and ventilated seats', 'High resale value and widespread service network'], ARRAY['Safety score behind Tata/Mahindra rivals', 'No manual option on the top turbo-petrol spec', 'Ride can feel soft at aggressive cornering speeds'], 1),
(4, 4, 'Maruti Suzuki Swift', 'maruti-suzuki-swift', 'Maruti Suzuki', 'Hatchback', 'Petrol', 'Manual', 6.49, 9.64, 'Available', 5, '1197 cc', '80 bhp', '111.7 Nm', '25.75 kmpl', 'Standard 6 Airbags', 'assets/images/cars/swift-real.png', ARRAY['assets/images/cars/swift-real.png'], 'The 4th generation Maruti Suzuki Swift brings the brand new Z-series 3-cylinder engine delivering astounding real-world fuel economy exceeding 25 kmpl, standard 6 airbags, and nimble city handling.', ARRAY['Phenomenal fuel efficiency (25+ kmpl)', 'Nimble dimensions perfect for city traffic', '6 Airbags standard across all trims', 'Peppy low-end throttle response'], ARRAY['Three-cylinder engine has slight idle vibration', 'Rear seat knee room is modest', 'Interior plastics feel utilitarian in places'], 0),
(5, 5, 'BMW 3 Series Gran Limousine', 'bmw-3-series-gran-limousine', 'BMW', 'Sedan', 'Petrol', 'Automatic', 60.60, 62.00, 'Available', 5, '1998 cc', '255 bhp', '400 Nm', '15.3 kmpl', '5 Star (Euro NCAP)', 'assets/images/cars/bmw3-real.png', ARRAY['assets/images/cars/bmw3-real.png'], 'The BMW 3 Series Gran Limousine combines the legendary rear-wheel-drive dynamics of BMW with an extended 110mm wheelbase for unmatched rear-seat lounge luxury and class-leading curved display cockpit.', ARRAY['Effortless 255 bhp TwinPower Turbo engine', 'First-class rear seat legroom and headrest cushions', 'Curved iDrive 8 display with wireless CarPlay', 'Peerless rear-wheel-drive balance and steering feel'], ARRAY['Low ground clearance requires caution on tall breakers', 'Premium pricing with no spare wheel well', 'Heftier road footprint than standard 3 Series'], 0),
(6, 1, 'Tata Curvv EV', 'tata-curvv-ev', 'Tata Motors', 'EV', 'Electric', 'Automatic', 17.49, 21.99, 'Upcoming', 5, 'Electric Motor', '165 bhp', '215 Nm', '585 km (ARAI)', '5 Star (BNCAP)', 'assets/images/cars/curvv-real.png', ARRAY['assets/images/cars/curvv-real.png'], 'The Tata Curvv EV introduces the Coupe-SUV body style to the mass market with striking aerodynamic sloping roofline, 55 kWh battery pack, ultra-fast 70 kW DC charging, and Level-2 ADAS suite.', ARRAY['Stunning Coupe SUV silhouette and road presence', 'Claimed 585 km range with 55 kWh battery pack', 'Powered gesture tailgate and massive 500L boot', 'Vehicle-to-Load (V2L) and Vehicle-to-Vehicle (V2V) charging'], ARRAY['Coupe roofline slightly compromises rear rearward visibility', 'Higher variant price approaches larger EV options', 'Piano black interior finishes prone to fingerprints'], 1)
ON CONFLICT (id) DO NOTHING;

-- Seed News Articles (Real PNG images)
INSERT INTO public.news_articles (id, title, slug, subtitle, content, image, author_name, author_role, author_avatar, category, model_tag, views_count, is_hero, is_trending) VALUES
(1, 'Tata Curvv EV Launched In India: Price Starts At Rs 17.49 Lakh', 'tata-curvv-ev-launched-india-price', 'Tata Motors enters the coupe-SUV segment with aggressive pricing, 585km range, and 5-star BNCAP safety rating.', 'Tata Motors has officially launched its highly anticipated Curvv EV coupe-SUV in the Indian market with prices starting from Rs 17.49 lakh for the 45kWh battery variant and topping out at Rs 21.99 lakh for the 55kWh long-range model (ex-showroom).', 'assets/images/news/curvv-launch.png', 'Hormazd Sorabjee', 'Editor-in-Chief', 'assets/images/avatars/hormazd.svg', 'Car News', 'Tata Curvv', 184500, 1, 1),
(2, 'Mahindra Thar Roxx 5-Door First Drive Review: The Ultimate Everyday Off-Roader', 'mahindra-thar-roxx-5-door-first-drive-review', 'Mahindra stretches the iconic Thar formula into a genuinely practical 5-door family SUV with stellar ride comfort.', 'The Thar Roxx is perhaps the most significant Indian SUV launch of this decade. While the 3-door Thar captured hearts with its rugged swagger, it was often constrained to a weekend toy. The Roxx addresses every single criticism with flying colors.', 'assets/images/news/thar-roxx.png', 'Shapur Kotwal', 'Deputy Editor', 'assets/images/avatars/shapur.svg', 'Car News', 'Mahindra Thar Roxx', 142300, 0, 1),
(3, '2025 Hyundai Creta N Line Review: Hot Hatch Spirit In An SUV Body', 'hyundai-creta-n-line-review', 'Firmer suspension, throatier exhaust, and razor-sharp steering turn the everyday Creta into a drivers delight.', 'Hyundais N Line sub-brand has created a loyal following in India with the i20 N Line and Venue N Line. Now, the flagship Creta gets the full N Line performance makeover.', 'assets/images/cars/creta-real.png', 'Gavin DSouza', 'Road Test Editor', 'assets/images/avatars/gavin.svg', 'Car News', 'Hyundai Creta', 98200, 0, 1)
ON CONFLICT (id) DO NOTHING;

-- Seed Reviews
INSERT INTO public.reviews (id, car_id, car_name, author_name, rating, title, review_text, status) VALUES
(1, 1, 'Tata Nexon Facelift', 'Rahul Sharma', 4.5, 'Absolute tank with futuristic tech!', 'I have driven my Tata Nexon Fearless Plus for 8,500 km. The 5-star crash safety and high bonnet give immense confidence on Indian highways.', 'approved'),
(2, 2, 'Mahindra XUV700', 'Vikramaditya', 5.0, 'Best long-distance cruiser under 30 Lakhs', 'Took the XUV700 AX7L AWD on a 2,500 km road trip through Rajasthan. The diesel engine has torque for days, ADAS worked like magic.', 'approved'),
(3, 3, 'Hyundai Creta', 'Pooja Nair', 4.0, 'Super refined and comfortable city family SUV', 'The Hyundai Creta automatic is effortless in Bangalore traffic. The panoramic roof makes the cabin feel twice as spacious.', 'approved')
ON CONFLICT (id) DO NOTHING;
