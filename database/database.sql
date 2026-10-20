-- ============================================================================
-- Peniel Properties Real Estate Website Database Schema
-- Compatible with MySQL 5.7+, MariaDB 10.3+, and phpMyAdmin
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `peniel_properties` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `peniel_properties`;

-- ----------------------------------------------------------------------------
-- Table: admins
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'admin',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: properties
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reference_no` VARCHAR(30) NOT NULL UNIQUE,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `description` MEDIUMTEXT NOT NULL,
  `category` ENUM('sale', 'rent') NOT NULL DEFAULT 'sale',
  `property_type` ENUM('house', 'apartment', 'townhouse', 'commercial', 'land') NOT NULL DEFAULT 'house',
  `price` DECIMAL(14,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `rental_frequency` ENUM('month', 'year', 'day') NULL DEFAULT NULL,
  `province` VARCHAR(80) NOT NULL DEFAULT 'Harare',
  `city` VARCHAR(80) NOT NULL DEFAULT 'Harare',
  `suburb` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NULL,
  `bedrooms` INT UNSIGNED DEFAULT 0,
  `bathrooms` INT UNSIGNED DEFAULT 0,
  `garages` INT UNSIGNED DEFAULT 0,
  `floor_area_sqm` INT UNSIGNED DEFAULT NULL,
  `land_area_sqm` INT UNSIGNED DEFAULT NULL,
  `amenities` TEXT NULL COMMENT 'JSON array or comma separated list of property features',
  `cover_image` VARCHAR(255) NOT NULL,
  `status` ENUM('available', 'under_offer', 'sold', 'rented') NOT NULL DEFAULT 'available',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_property_type` (`property_type`),
  INDEX `idx_city_suburb` (`city`, `suburb`),
  INDEX `idx_price` (`price`),
  INDEX `idx_status` (`status`),
  INDEX `idx_published_featured` (`is_published`, `is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: property_images
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `property_images`;
CREATE TABLE `property_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_property_images_property`
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: inquiries
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT UNSIGNED NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `preferred_contact_method` ENUM('whatsapp', 'phone', 'email') NOT NULL DEFAULT 'whatsapp',
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inquiries_property`
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_inquiry_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: site_settings
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(40) NOT NULL DEFAULT 'general',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Administrator Account (Username: admin | Password: PenielAdmin2026!)
INSERT INTO `admins` (`username`, `email`, `password_hash`, `full_name`, `role`)
VALUES (
  'admin',
  'info@penielproperties.co.zw',
  '$2y$10$ISlekT95EVjwOLbJ5dU/0.mhxQgjtfJLKM.MFukw3z5vy.xGbXMQq',
  'Peniel Administrator',
  'superadmin'
);

-- Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'Peniel Properties', 'general'),
('site_tagline', 'Find a Place to Call Yours', 'general'),
('site_description', 'Discover premium residential and commercial properties for sale and rent across Zimbabwe.', 'general'),
('contact_email', 'info@penielproperties.co.zw', 'contact'),
('contact_phone', '+263 77 212 3456', 'contact'),
('whatsapp_number', '+263772123456', 'contact'),
('office_address', 'Suite 4, Peniel House, Borrowdale Road, Harare, Zimbabwe', 'contact'),
('working_hours', 'Monday - Friday: 8:00 AM - 5:00 PM | Saturday: 9:00 AM - 1:00 PM', 'contact'),
('facebook_url', 'https://facebook.com/penielpropertieszw', 'social'),
('instagram_url', 'https://instagram.com/penielpropertieszw', 'social'),
('linkedin_url', 'https://linkedin.com/company/penielproperties', 'social'),
('default_currency', 'USD', 'finance');

-- Curated Demonstration Properties in Harare & Bulawayo
INSERT INTO `properties` (
  `id`, `reference_no`, `slug`, `title`, `description`, `category`, `property_type`,
  `price`, `currency`, `rental_frequency`, `province`, `city`, `suburb`, `address`,
  `bedrooms`, `bathrooms`, `garages`, `floor_area_sqm`, `land_area_sqm`, `amenities`,
  `cover_image`, `status`, `is_featured`, `is_published`
) VALUES
(
  1,
  'PP-1001',
  'contemporary-architectural-masterpiece-borrowdale-brooke',
  'Contemporary Architectural Masterpiece',
  'A striking modern residence set within the prestigious Borrowdale Brooke community. Designed with expansive floor-to-ceiling glass, natural stone feature walls, and seamless indoor-outdoor living.\n\nThe home boasts a state-of-the-art open-plan chef kitchen with quartz countertops, integrated European appliances, and a scullery. All five bedrooms feature luxurious en-suite bathrooms and private balconies.\n\nOutdoor entertainment is elevated by an infinity-edge swimming pool, sunken fire pit, landscaped indigenous garden, and a fully solar-powered backup infrastructure with prolific borehole water.',
  'sale',
  'house',
  850000.00,
  'USD',
  NULL,
  'Harare',
  'Harare',
  'Borrowdale Brooke',
  'Fairway Drive, Borrowdale Brooke',
  5,
  5,
  3,
  620,
  2400,
  'Prolific Borehole, 15kVA Solar Power System, Infinity Swimming Pool, 24/7 Gated Security, Triple Lock-up Garage, Staff Quarters, High-speed Fiber Internet, Electric Fencing, Air Conditioning',
  'uploads/properties/prop-borrowdale-1.jpg',
  'available',
  1,
  1
),
(
  2,
  'PP-1002',
  'luxury-single-storey-garden-villa-highlands',
  'Luxury Single-Storey Garden Villa',
  'Nestled on an expansive acre in quiet Highlands, this contemporary single-level villa blends warm natural teak accents with rustic stone masonry. Enjoy tranquil suburban living minutes away from top schools and shopping hubs.\n\nFeatures include two expansive verandas opening to a sparkling lap pool, a master wing with walk-in dressing room and outdoor shower garden, home office, and separate self-contained guest cottage.',
  'sale',
  'house',
  520000.00,
  'USD',
  NULL,
  'Harare',
  'Harare',
  'Highlands',
  'Enterprise Road Environs, Highlands',
  4,
  4,
  2,
  450,
  4047,
  'Prolific Borehole, 10kVA Solar System, Swimming Pool, Self-contained Guest Cottage, Double Garage, Manicured Acre Garden, Security Response System, Fireplace',
  'uploads/properties/prop-highlands-1.jpg',
  'available',
  1,
  1
),
(
  3,
  'PP-1003',
  'executive-penthouse-apartment-avondale',
  'Executive Penthouse Apartment at The Vanguard',
  'Sophisticated urban living in the heart of Avondale. This top-floor penthouse apartment offers breathtaking panoramic sunset views over the Harare skyline.\n\nDesigned for expatriates, executives, or discerning investors seeking solid rental yields. Features open-plan living, designer European kitchen, spacious wrap-around terrace, and keycard elevator access.',
  'rent',
  'apartment',
  2500.00,
  'USD',
  'month',
  'Harare',
  'Harare',
  'Avondale',
  'King George Road, Avondale',
  3,
  3,
  2,
  220,
  NULL,
  '24/7 Security Guarding, Backup Generator, Borehole Water Supply, Elevator Access, Communal Swimming Pool, Secure Underground Parking, Fiber Ready',
  'uploads/properties/prop-avondale-1.jpg',
  'available',
  1,
  1
),
(
  4,
  'PP-1004',
  'modern-lock-up-and-go-townhouse-chisipite',
  'Modern Lock-up-and-Go Townhouse',
  'An exclusive cluster home in a secure development of only 8 units in Chisipite. Thoughtfully engineered for modern family comfort with low maintenance.\n\nBoasts high ceilings, modern open plan lounge and dining opening to a private patio, automated double garage, and energy-efficient heat pump water systems.',
  'sale',
  'townhouse',
  340000.00,
  'USD',
  NULL,
  'Harare',
  'Harare',
  'Chisipite',
  'Hindhead Avenue, Chisipite',
  3,
  3,
  2,
  280,
  550,
  'Complex Borehole, 5kVA Inverter System, 24-Hour Guard, Electric Gate, Private Walled Garden, Double Lock-up Garage',
  'uploads/properties/prop-chisipite-1.jpg',
  'available',
  1,
  1
),
(
  5,
  'PP-1005',
  'prestigious-executive-residence-gunhill',
  'Prestigious Executive Residence',
  'A masterfully crafted residence located in diplomatic Gunhill. Perfect for embassy or multinational corporate tenancy. Features grand reception areas, expansive dining room, manicured grounds, tennis court, and state-of-the-art security installations.',
  'rent',
  'house',
  4000.00,
  'USD',
  'month',
  'Harare',
  'Harare',
  'Gunhill',
  'Churchill Avenue, Gunhill',
  5,
  5,
  3,
  580,
  3600,
  'Commercial Borehole, 20kVA Solar Backup, Full Tennis Court, Swimming Pool, Diplomatic Guard House, Double Staff Quarters, Perimeter Electric Fence',
  'uploads/properties/prop-hero-1.jpg',
  'available',
  1,
  1
);

-- Gallery Images for properties
INSERT INTO `property_images` (`property_id`, `image_path`, `caption`, `display_order`) VALUES
(1, 'uploads/properties/prop-borrowdale-1.jpg', 'Exterior Pool & Patio View', 1),
(1, 'uploads/properties/prop-interior-1.jpg', 'Open-Plan Living & Dining Area', 2),
(1, 'uploads/properties/prop-hero-1.jpg', 'Twilight Driveway & Architectural Lighting', 3),

(2, 'uploads/properties/prop-highlands-1.jpg', 'Front Elevation & Sunken Veranda', 1),
(2, 'uploads/properties/prop-interior-1.jpg', 'Designer Lounge & Teak Ceilings', 2),

(3, 'uploads/properties/prop-avondale-1.jpg', 'Building Exterior & Entrance', 1),
(3, 'uploads/properties/prop-interior-1.jpg', 'Penthouse Living Space', 2),

(4, 'uploads/properties/prop-chisipite-1.jpg', 'Townhouse Frontage & Private Driveway', 1),
(4, 'uploads/properties/prop-interior-1.jpg', 'Spacious Living Room', 2),

(5, 'uploads/properties/prop-hero-1.jpg', 'Front Elevation & Entrance', 1),
(5, 'uploads/properties/prop-interior-1.jpg', 'Grand Reception Room', 2);
