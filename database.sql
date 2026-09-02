-- AutoPulse Database Schema and Seed Data
-- Compatible with MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS `autopulse_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autopulse_db`;

-- Drop tables if exists in reverse relation order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `news_articles`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `chatbot_responses`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT 'assets/images/avatars/default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Brands Table
CREATE TABLE `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `origin` VARCHAR(50) DEFAULT 'Global',
  `logo` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `type` ENUM('car', 'news') DEFAULT 'car'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cars Table
CREATE TABLE `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `brand_id` INT NOT NULL,
  `category_id` INT DEFAULT NULL,
  `body_type` ENUM('SUV', 'Sedan', 'Hatchback', 'EV', 'Luxury', 'MUV') NOT NULL,
  `fuel_type` ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG') NOT NULL,
  `transmission` ENUM('Manual', 'Automatic', 'AMT', 'DCT') NOT NULL,
  `price_min` DECIMAL(10, 2) NOT NULL, -- in Lakhs (e.g. 7.99)
  `price_max` DECIMAL(10, 2) NOT NULL, -- in Lakhs (e.g. 15.49)
  `price_label` VARCHAR(50) DEFAULT 'Ex-showroom price',
  `status` ENUM('Available', 'Upcoming', 'Trending') DEFAULT 'Available',
  `seating_capacity` INT DEFAULT 5,
  `engine_displacement` VARCHAR(50) DEFAULT '1199 cc',
  `power` VARCHAR(50) DEFAULT '118 bhp',
  `torque` VARCHAR(50) DEFAULT '170 Nm',
  `mileage` VARCHAR(50) DEFAULT '17.0 kmpl',
  `safety_rating` VARCHAR(20) DEFAULT '5 Star (GNCAP)',
  `featured_image` VARCHAR(255) NOT NULL,
  `gallery_images` TEXT DEFAULT NULL, -- comma-separated list
  `overview` TEXT NOT NULL,
  `pros` TEXT DEFAULT NULL, -- Pipe-separated list
  `cons` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News Articles Table
CREATE TABLE `news_articles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `author_name` VARCHAR(100) NOT NULL,
  `author_role` VARCHAR(100) DEFAULT 'Automotive Journalist',
  `author_avatar` VARCHAR(255) DEFAULT 'assets/images/avatars/author1.jpg',
  `category` VARCHAR(50) DEFAULT 'Car News', -- Car News, Bike News, Motorsport, Industry, EV
  `model_tag` VARCHAR(100) DEFAULT NULL,    -- e.g. 'Nexon', 'Creta', 'Curvv'
  `views_count` INT DEFAULT 1200,
  `is_hero` TINYINT(1) DEFAULT 0,
  `is_trending` TINYINT(1) DEFAULT 0,
  `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews Table
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `car_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `author_name` VARCHAR(100) NOT NULL,
  `rating` DECIMAL(2, 1) NOT NULL, -- e.g. 4.5
  `title` VARCHAR(200) NOT NULL,
  `review_text` TEXT NOT NULL,
  `status` ENUM('approved', 'pending') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments Table
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `article_id` INT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_email` VARCHAR(150) NOT NULL,
  `comment_text` TEXT NOT NULL,
  `status` ENUM('approved', 'pending') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`article_id`) REFERENCES `news_articles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist Table
CREATE TABLE `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_car` (`user_id`, `car_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chatbot Responses Table (Rules & Offline FAQs)
CREATE TABLE `chatbot_responses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `trigger_keywords` VARCHAR(255) NOT NULL,
  `response_text` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Users: Admin password is 'admin123' (hashed via bcrypt)
-- User demo password is 'user123'
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`) VALUES
(1, 'AutoPulse Admin', 'admin@autopulse.com', '$2y$10$t3Jmsv2mYgT2qB.hRz1h..nFekR3X86v1qGg3sFm7hK7fU7k6Wb8i', 'admin', 'assets/images/avatars/admin.svg'),
(2, 'Rahul Sharma', 'rahul@example.com', '$2y$10$K7XG9wO8c9p4w3g4n3aJfeZ0k7oE0Kk9i0B4QhP3rL2vM5nB4Vq6C', 'user', 'assets/images/avatars/user1.svg');

-- Brands
INSERT INTO `brands` (`id`, `name`, `slug`, `origin`) VALUES
(1, 'Tata Motors', 'tata-motors', 'India'),
(2, 'Mahindra', 'mahindra', 'India'),
(3, 'Hyundai', 'hyundai', 'South Korea'),
(4, 'Maruti Suzuki', 'maruti-suzuki', 'India / Japan'),
(5, 'BMW', 'bmw', 'Germany'),
(6, 'Toyota', 'toyota', 'Japan');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `type`) VALUES
(1, 'Compact SUV', 'compact-suv', 'car'),
(2, 'Mid-Size SUV', 'mid-size-suv', 'car'),
(3, 'Premium Sedan', 'premium-sedan', 'car'),
(4, 'Hatchback', 'hatchback', 'car'),
(5, 'Electric Vehicle', 'electric-vehicle', 'car'),
(6, 'Car News', 'car-news', 'news'),
(7, 'Bike News', 'bike-news', 'news'),
(8, 'Motorsport', 'motorsport', 'news'),
(9, 'Industry', 'industry', 'news');

-- Cars (Prices in Lakhs INR)
INSERT INTO `cars` (`id`, `name`, `slug`, `brand_id`, `category_id`, `body_type`, `fuel_type`, `transmission`, `price_min`, `price_max`, `price_label`, `status`, `seating_capacity`, `engine_displacement`, `power`, `torque`, `mileage`, `safety_rating`, `featured_image`, `gallery_images`, `overview`, `pros`, `cons`, `is_featured`) VALUES
(1, 'Tata Nexon Facelift', 'tata-nexon-facelift', 1, 1, 'SUV', 'Petrol', 'Automatic', 8.00, 15.50, 'Ex-showroom price', 'Trending', 5, '1199 cc', '118 bhp', '170 Nm', '17.4 kmpl', '5 Star (BNCAP / GNCAP)', 'assets/images/cars/nexon.svg', 'assets/images/cars/nexon.svg,assets/images/cars/nexon-side.svg,assets/images/cars/nexon-interior.svg', 'The Tata Nexon facelift elevates the compact SUV segment with an aggressive futuristic front fascia, bi-LED projector headlamps, a 10.25-inch floating touchscreen, and uncompromising 5-star crash safety build.', 'Top-class 5-star safety rating|Striking concept-car styling|Punchy turbo petrol and diesel options|Feature-loaded cabin with 360-degree camera', 'Infotainment software can have occasional lag|Firm low-speed ride quality|Rear seat headroom average for very tall passengers', 1),

(2, 'Mahindra XUV700', 'mahindra-xuv700', 2, 2, 'SUV', 'Diesel', 'Automatic', 13.99, 26.99, 'Ex-showroom price', 'Trending', 7, '2198 cc', '182 bhp', '450 Nm', '16.5 kmpl', '5 Star (GNCAP)', 'assets/images/cars/xuv700.svg', 'assets/images/cars/xuv700.svg,assets/images/cars/xuv700-side.svg,assets/images/cars/xuv700-interior.svg', 'Mahindra XUV700 sets the benchmark in full-size mid-segment SUVs, offering dual 10.25-inch superscreens, Level-2 ADAS driver assistance, optional AWD, and blistering powertrain performance.', 'Blistering 2.0L Turbo Petrol & 2.2L Diesel engines|Segment-first Level 2 ADAS suite|Comfortable 3-row layout and Sony 3D sound system|High speed stability and composure', 'Long waiting periods for top variants|Third row best suited for children|Boot space with all 3 rows up is limited', 1),

(3, 'Hyundai Creta', 'hyundai-creta', 3, 2, 'SUV', 'Petrol', 'Automatic', 11.00, 20.15, 'Ex-showroom price', 'Trending', 5, '1497 cc', '158 bhp', '253 Nm', '18.4 kmpl', '3 Star (GNCAP)', 'assets/images/cars/creta.svg', 'assets/images/cars/creta.svg,assets/images/cars/creta-side.svg,assets/images/cars/creta-interior.svg', 'The Hyundai Creta is India\'s undisputed king of mid-size SUVs, featuring bold parametric jewel grille, panoramic sunroof, ventilated front seats, dual-zone climate control, and refined driving dynamics.', 'Silky smooth refined petrol & diesel engine choices|Plush premium cabin ergonomics|Panoramic sunroof and ventilated seats|High resale value and widespread service network', 'Safety score behind Tata/Mahindra rivals|No manual option on the top turbo-petrol spec|Ride can feel soft at aggressive cornering speeds', 1),

(4, 'Maruti Suzuki Swift', 'maruti-suzuki-swift', 4, 4, 'Hatchback', 'Petrol', 'Manual', 6.49, 9.64, 'Ex-showroom price', 'Available', 5, '1197 cc', '80 bhp', '111.7 Nm', '25.75 kmpl', 'Standard 6 Airbags', 'assets/images/cars/swift.svg', 'assets/images/cars/swift.svg,assets/images/cars/swift-side.svg,assets/images/cars/swift-interior.svg', 'The 4th generation Maruti Suzuki Swift brings the brand new Z-series 3-cylinder engine delivering astounding real-world fuel economy exceeding 25 kmpl, standard 6 airbags, and nimble city handling.', 'Phenomenal fuel efficiency (25+ kmpl)|Nimble dimensions perfect for city traffic|6 Airbags standard across all trims|Peppy low-end throttle response', 'Three-cylinder engine has slight idle vibration|Rear seat knee room is modest|Interior plastics feel utilitarian in places', 0),

(5, 'BMW 3 Series Gran Limousine', 'bmw-3-series-gran-limousine', 5, 3, 'Sedan', 'Petrol', 'Automatic', 60.60, 62.00, 'Ex-showroom price', 'Available', 5, '1998 cc', '255 bhp', '400 Nm', '15.3 kmpl', '5 Star (Euro NCAP)', 'assets/images/cars/bmw-3.svg', 'assets/images/cars/bmw-3.svg,assets/images/cars/bmw-3-side.svg,assets/images/cars/bmw-3-interior.svg', 'The BMW 3 Series Gran Limousine combines the legendary rear-wheel-drive dynamics of BMW with an extended 110mm wheelbase for unmatched rear-seat lounge luxury and class-leading curved display cockpit.', 'Effortless 255 bhp TwinPower Turbo engine|First-class rear seat legroom and headrest cushions|Curved iDrive 8 display with wireless CarPlay|Peerless rear-wheel-drive balance and steering feel', 'Low ground clearance requires caution on tall breakers|Premium pricing with no spare wheel well|Heftier road footprint than standard 3 Series', 0),

(6, 'Tata Curvv EV', 'tata-curvv-ev', 1, 5, 'EV', 'Electric', 'Automatic', 17.49, 21.99, 'Ex-showroom price', 'Upcoming', 5, 'Electric Motor', '165 bhp', '215 Nm', '585 km (ARAI)', '5 Star (BNCAP)', 'assets/images/cars/curvv.svg', 'assets/images/cars/curvv.svg,assets/images/cars/curvv-side.svg,assets/images/cars/curvv-interior.svg', 'The Tata Curvv EV introduces the Coupe-SUV body style to the mass market with striking aerodynamic sloping roofline, 55 kWh battery pack, ultra-fast 70 kW DC charging, and Level-2 ADAS suite.', 'Stunning Coupe SUV silhouette and road presence|Claimed 585 km range with 55 kWh battery pack|Powered gesture tailgate and massive 500L boot|Vehicle-to-Load (V2L) and Vehicle-to-Vehicle (V2V) charging', 'Coupe roofline slightly compromises rear rearward visibility|Higher variant price approaches larger EV options|Piano black interior finishes prone to fingerprints', 1);

-- News Articles
INSERT INTO `news_articles` (`id`, `title`, `slug`, `subtitle`, `content`, `image`, `author_name`, `author_role`, `author_avatar`, `category`, `model_tag`, `views_count`, `is_hero`, `is_trending`, `published_at`) VALUES
(1, 'Tata Curvv EV Launched In India: Price Starts At Rs 17.49 Lakh', 'tata-curvv-ev-launched-india-price', 'Tata Motors enters the coupe-SUV segment with aggressive pricing, 585km range, and 5-star BNCAP safety rating.', 'Tata Motors has officially launched its highly anticipated Curvv EV coupe-SUV in the Indian market with prices starting from Rs 17.49 lakh for the 45kWh battery variant and topping out at Rs 21.99 lakh for the 55kWh long-range model (ex-showroom). The Curvv EV is based on Tata’s acti.ev architecture and becomes the first mass-market coupe SUV in India.\r\n\r\nOn the exterior, the Curvv EV sports full-width connected LED daytime running lights, a blanked-off grille with an illuminated Tata logo, flush door handles, and 18-inch aerodynamic alloy wheels. The sloping roofline seamlessly flows into the high-set rear tailgate.\r\n\r\nInside, the cabin features a four-spoke steering wheel with an illuminated logo, a 12.3-inch touchscreen infotainment system with wireless Apple CarPlay and Android Auto, a 10.25-inch digital driver display, a 9-speaker JBL sound system, ventilated front seats, and a panoramic glass roof.\r\n\r\nCharging speeds are brisk: with a 70kW DC fast charger, the Curvv EV can juice up from 10 to 80 percent in just 40 minutes, adding roughly 150 km of range in 15 minutes of charging.', 'assets/images/news/curvv-launch.svg', 'Hormazd Sorabjee', 'Editor-in-Chief', 'assets/images/avatars/hormazd.svg', 'Car News', 'Tata Curvv', 184500, 1, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),

(2, 'Mahindra Thar Roxx 5-Door First Drive Review: The Ultimate Everyday Off-Roader', 'mahindra-thar-roxx-5-door-first-drive-review', 'Mahindra stretches the iconic Thar formula into a genuinely practical 5-door family SUV with stellar ride comfort.', 'The Thar Roxx is perhaps the most significant Indian SUV launch of this decade. While the 3-door Thar captured hearts with its rugged swagger, it was often constrained to a weekend toy due to difficult rear passenger ingress and negligible luggage room. The Roxx addresses every single criticism with flying colors.\r\n\r\nBuilt on a modified Scorpio-N platform, the Thar Roxx gains an elongated 2,850 mm wheelbase. The interior is a massive leap forward: soft-touch white leatherette dashboard elements, dual 10.25-inch screens, Level-2 ADAS, panoramic Skyroof, and a spacious 644-litre boot.\r\n\r\nUnder the hood, you get the choice between the 2.0-litre mStallion turbo petrol producing up to 177 bhp and the 2.2-litre mHawk diesel churning out up to 175 bhp and 370 Nm of torque. Ride quality over broken tarmac is remarkably plush thanks to frequency-dependent damping.', 'assets/images/news/thar-roxx.svg', 'Shapur Kotwal', 'Deputy Editor', 'assets/images/avatars/shapur.svg', 'Car News', 'Mahindra Thar Roxx', 142300, 0, 1, DATE_SUB(NOW(), INTERVAL 5 HOUR)),

(3, '2025 Hyundai Creta N Line Review: Hot Hatch Spirit In An SUV Body', 'hyundai-creta-n-line-review', 'Firmer suspension, throatier exhaust, and razor-sharp steering turn the everyday Creta into a driver’s delight.', 'Hyundai’s N Line sub-brand has created a loyal following in India with the i20 N Line and Venue N Line. Now, the flagship Creta gets the full N Line performance makeover, featuring mechanical stiffening of the dampers, a 30 percent tighter steering calibration, and a twin-tip exhaust that emits a distinct, sporty burble.\r\n\r\nPower comes exclusively from the 1.5-litre turbo-petrol motor producing 160 hp and 253 Nm of torque, mated to either a crisp 6-speed manual gearbox with metal pedals or a lightning-quick 7-speed dual-clutch transmission (DCT).\r\n\r\nVisual upgrades include thunder blue matte paint, aggressive red pinstripe skirts, dark chrome radiator grille, and 18-inch diamond-cut alloys with red brake calipers.', 'assets/images/news/creta-nline.svg', 'Gavin D\'Souza', 'Road Test Editor', 'assets/images/avatars/gavin.svg', 'Car News', 'Hyundai Creta', 98200, 0, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),

(4, 'Next-Gen Maruti Dzire Spotted Testing: Sunroof And 6 Airbags Confirmed', 'next-gen-maruti-dzire-spotted-sunroof-confirmed', 'India\'s bestselling compact sedan gets ready for a massive overhaul with segment-first features.', 'Fresh spy images of the upcoming 2025 Maruti Suzuki Dzire have emerged, revealing several groundbreaking additions for the sub-4-metre compact sedan segment. For the first time ever, the Dzire will be equipped with a single-pane electric sunroof, matching competitor Hyundai Aura.\r\n\r\nUnlike previous generations where the Dzire shared identical sheet metal with the Swift from the B-pillar forward, the new Dzire will sport a distinct, more upright chrome hexagonal front grille and sleeker horizontal LED headlights to emphasize executive appeal.\r\n\r\nUnder the hood will sit the new Z12E 1.2-litre 3-cylinder petrol engine paired with 5-speed manual and AMT gearboxes, boasting an ARAI-certified fuel efficiency expected to top 26 kmpl.', 'assets/images/news/dzire-spy.svg', 'Sergius Barretto', 'Managing Editor', 'assets/images/avatars/sergius.svg', 'Car News', 'Maruti Dzire', 76400, 0, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),

(5, 'Ducati Panigale V4 2025 Unveiled: 216hp Superbike Gets Double-Sided Swingarm', 'ducati-panigale-v4-2025-unveiled', 'The Italian marquee revamps its flagship superbike with aerodynamics inspired by MotoGP and revolutionary Brembo Hypure brakes.', 'Ducati has taken the wraps off the 2025 Panigale V4 and V4 S. Marking a radical design departure, Ducati has ditched the iconic single-sided swingarm in favor of a hollow symmetrical double-sided unit that reduces lateral stiffness by 37 percent for superior cornering grip.\r\n\r\nThe 1,103cc Desmosedici Stradale V4 engine now complies with Euro 5+ emissions while still generating a staggering 216 horsepower at 13,500 rpm. Combined with an Akrapovič racing exhaust, output climbs to an astonishing 228 hp.', 'assets/images/news/ducati-v4.svg', 'Rishaad Mody', 'Two-Wheeler Editor', 'assets/images/avatars/rishaad.svg', 'Bike News', 'Ducati Panigale', 54100, 0, 0, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Reviews
INSERT INTO `reviews` (`id`, `car_id`, `user_id`, `author_name`, `rating`, `title`, `review_text`, `status`, `created_at`) VALUES
(1, 1, 2, 'Rahul Sharma', 4.5, 'Absolute tank with futuristic tech!', 'I have driven my Tata Nexon Fearless Plus for 8,500 km. The 5-star crash safety and high bonnet give immense confidence on Indian highways. The ventilated seats are a lifesaver in Delhi summers. Only complaint is the occasional touchscreen reboot.', 'approved', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(2, 2, 1, 'Vikramaditya', 5.0, 'Best long-distance cruiser under 30 Lakhs', 'Took the XUV700 AX7L AWD on a 2,500 km road trip through Rajasthan. The diesel engine has torque for days, ADAS lane keep assist worked like magic on the expressway, and the Sony audio system is pure concert hall quality.', 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 3, 2, 'Pooja Nair', 4.0, 'Super refined and comfortable city family SUV', 'The Hyundai Creta automatic is effortless in Bangalore traffic. The panoramic roof makes the cabin feel twice as spacious. Mileage in bumper-to-bumper traffic is around 11 kmpl, but goes up to 17 kmpl on open highway stretches.', 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Comments
INSERT INTO `comments` (`id`, `article_id`, `user_name`, `user_email`, `comment_text`, `status`, `created_at`) VALUES
(1, 1, 'Karan Malhotra', 'karan@gmail.com', 'The Rs 17.49L introductory price is brilliant! With the 55 kWh battery this will give genuine 400+ km real-world range.', 'approved', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 1, 'Amit Sengupta', 'amit@yahoo.com', 'Tata is genuinely leading the EV revolution in India. That coupe rear profile looks like a BMW X4 from certain angles.', 'approved', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, 2, 'Siddharth R.', 'sid@outlook.com', 'Finally a 5-door Thar that my parents can comfortably get into without rock climbing! Ordering the AX7L diesel AT tomorrow.', 'approved', DATE_SUB(NOW(), INTERVAL 2 HOUR));

-- Sample Wishlist
INSERT INTO `wishlist` (`user_id`, `car_id`) VALUES
(2, 1),
(2, 3);

-- Chatbot Offline Knowledge Base & FAQ Triggers
INSERT INTO `chatbot_responses` (`id`, `trigger_keywords`, `response_text`, `category`) VALUES
(1, 'hello,hi,hey,namaste,greetings', 'Hello! Welcome to AutoPulse Assistant. How can I help you today? You can ask about car prices, compare models, read latest automotive news, or check safety ratings.', 'greeting'),
(2, 'compare,comparo,versus,vs', 'You can compare up to 3 cars head-to-head on our Compare page! Just head to the "Compare" link in the navigation or type "compare [Car A] and [Car B]".', 'comparison'),
(3, 'test drive,book test drive,appointment', 'To book a test drive, visit the detail page of any car on AutoPulse, click on the "Book Test Drive" button, or contact your nearest authorized dealership directly.', 'sales'),
(4, 'safety,crash test,bncap,gncap,rating', 'Safety is top priority! On AutoPulse, cars like Tata Nexon, Mahindra XUV700, and Tata Curvv EV feature verified 5-Star BNCAP/GNCAP crash safety ratings. Check our individual car detail pages for full safety spec sheets.', 'safety'),
(5, 'electric,ev,charging,battery', 'We cover all the latest EVs! Tata Curvv EV has just launched with prices starting at Rs 17.49 Lakh and a 585 km ARAI range. You can filter for Electric vehicles in our "All Cars" section.', 'ev'),
(6, 'contact,support,help,phone,email', 'You can reach the AutoPulse editorial & support team at contact@autopulse.com or call our helpline at +91 11 4567 8900 (Mon-Sat, 9AM to 6PM).', 'support'),
(7, 'upcoming cars,new launches,future cars', 'Upcoming cars include the Tata Curvv EV, Next-Gen Maruti Suzuki Dzire, Hyundai Creta EV, and Mahindra Thar Roxx. Visit our "Upcoming Cars" tab to explore photos, expected prices, and launch dates!', 'upcoming'),
(8, 'mileage,fuel efficiency,average,kmpl', 'For maximum fuel efficiency, the new 2024 Maruti Suzuki Swift leads the hatchback segment with over 25.75 kmpl! You can filter cars by mileage on our Car Listings page.', 'mileage'),
(9, 'sedan,best sedan', 'Looking for a sedan? Check out the BMW 3 Series Gran Limousine for luxury rear-seat comfort, or the upcoming next-gen Maruti Dzire for an economical family commuter.', 'body_type'),
(10, 'suv,best suv', 'Top SUV picks in India right now include Tata Nexon (compact SUV), Hyundai Creta (mid-size), and Mahindra XUV700 (full-size 7-seater). Check our homepage for in-depth reviews of all three!', 'body_type'),
(11, 'loan,emi,finance,calculator', 'Looking to finance your dream car? Most Indian banks offer automotive loans covering 85-90% of on-road prices with interest rates ranging from 8.5% to 10.5%. Calculate your EMI easily on our car detail pages.', 'finance'),
(12, 'autocar,autopulse,about', 'AutoPulse is India\'s premier automotive news and car comparison portal inspired by Autocar India, providing unbiased road tests, real-world mileage data, and breaking auto news.', 'about');
