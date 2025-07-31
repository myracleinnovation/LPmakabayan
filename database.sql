-- =====================================================
-- MAKABAYAN CONSTRUCTION DATABASE SCHEMA
-- =====================================================
-- Database: MakabayanConstruction
-- Description: Complete database structure for Makabayan Avellanosa Construction website
-- Version: 1.0.0
-- Created: 2024
-- =====================================================

-- Drop and recreate the database
DROP DATABASE IF EXISTS makabayanconstruction;
CREATE DATABASE makabayanconstruction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE makabayanconstruction;

-- =====================================================
-- TABLE: Admin_Accounts
-- Description: Stores admin user accounts for login
-- =====================================================
CREATE TABLE IF NOT EXISTS Admin_Accounts (
    IdAdmin INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255),
    FullName VARCHAR(200),
    Role ENUM('admin', 'manager', 'editor') DEFAULT 'admin',
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    LastLogin TIMESTAMP NULL,
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (Username),
    INDEX idx_status (Status),
    INDEX idx_role (Role)
);

-- =====================================================
-- TABLE: Company_Info
-- Description: Stores company information and settings
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Info (
    IdCompany INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName VARCHAR(200) NOT NULL,
    Tagline VARCHAR(300),
    Description TEXT,
    Mission TEXT,
    Vision TEXT,
    AboutImage VARCHAR(255),
    LogoImage VARCHAR(255),
    FaviconImage VARCHAR(255),
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Company_Features
-- Description: Stores company features/services
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Features (
    IdFeature INT AUTO_INCREMENT PRIMARY KEY,
    FeatureTitle VARCHAR(200) NOT NULL,
    FeatureDescription TEXT,
    FeatureImage VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Company_Specialties
-- Description: Stores company specialties/services
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Specialties (
    IdSpecialty INT AUTO_INCREMENT PRIMARY KEY,
    SpecialtyName VARCHAR(200) NOT NULL,
    SpecialtyDescription TEXT,
    SpecialtyImage VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Company_Industries
-- Description: Stores industries the company serves
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Industries (
    IdIndustry INT AUTO_INCREMENT PRIMARY KEY,
    IndustryName VARCHAR(200) NOT NULL,
    IndustryDescription TEXT,
    IndustryImage VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Project_Categories
-- Description: Stores project categories
-- =====================================================
CREATE TABLE IF NOT EXISTS Project_Categories (
    IdCategory INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(200) NOT NULL,
    CategoryDescription TEXT,
    CategoryImage VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Company_Projects
-- Description: Stores company projects/portfolio
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Projects (
    IdProject INT AUTO_INCREMENT PRIMARY KEY,
    ProjectTitle VARCHAR(300) NOT NULL,
    ProjectDescription TEXT,
    ProjectOwner VARCHAR(200),
    ProjectLocation VARCHAR(300),
    ProjectArea DECIMAL(10,2) COMMENT 'Project area in square meters',
    ProjectValue DECIMAL(15,2) COMMENT 'Project value in PHP',
    TurnoverDate DATE,
    ProjectCategoryId INT,
    ProjectImage1 VARCHAR(255),
    ProjectImage2 VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectCategoryId) REFERENCES Project_Categories(IdCategory)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    INDEX idx_category (ProjectCategoryId),
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status),
    INDEX idx_turnover_date (TurnoverDate)
);

-- =====================================================
-- TABLE: Company_Process
-- Description: Stores company process steps
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Process (
    IdProcess INT AUTO_INCREMENT PRIMARY KEY,
    ProcessTitle VARCHAR(200) NOT NULL,
    ProcessDescription TEXT,
    ProcessImage VARCHAR(255),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: Company_Contact
-- Description: Stores company contact information
-- =====================================================
CREATE TABLE IF NOT EXISTS Company_Contact (
    IdContact INT AUTO_INCREMENT PRIMARY KEY,
    ContactType ENUM('phone', 'email', 'address', 'social_media', 'website') NOT NULL,
    ContactValue VARCHAR(500) NOT NULL,
    ContactLabel VARCHAR(200),
    ContactIcon VARCHAR(100),
    DisplayOrder INT DEFAULT 0,
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact_type (ContactType),
    INDEX idx_display_order (DisplayOrder),
    INDEX idx_status (Status)
);

-- =====================================================
-- TABLE: System_Settings
-- Description: Stores system configuration settings
-- =====================================================
CREATE TABLE IF NOT EXISTS System_Settings (
    IdSetting INT AUTO_INCREMENT PRIMARY KEY,
    SettingKey VARCHAR(100) NOT NULL UNIQUE,
    SettingValue TEXT,
    SettingDescription VARCHAR(500),
    SettingType ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    Status TINYINT DEFAULT 1 COMMENT '1-active, 0-inactive',
    CreatedTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedTimestamp TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (SettingKey),
    INDEX idx_status (Status)
);

-- =====================================================
-- SEED DATA
-- =====================================================

-- Seed: Admin Accounts (Password: 'password' for all accounts)
INSERT INTO Admin_Accounts (Username, Password, Email, FullName, Role, Status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@makabayanconstruction.com', 'System Administrator', 'admin', 1),
('makabayan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'info@makabayanconstruction.com', 'Makabayan Manager', 'manager', 1),
('construction', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'construction@makabayanconstruction.com', 'Construction Editor', 'editor', 1);

-- Seed: Company Information
INSERT INTO Company_Info (CompanyName, Tagline, Description, Mission, Vision, AboutImage, LogoImage, FaviconImage) VALUES
('Makabayan Avellanosa Construction', 'Building a Better Future', 
'Superior and quality construction services grounded in modern principles, sustainable solutions, and client satisfaction.',
'To deliver the highest quality of service through the dedication and expertise of our skilled workforce. We uphold strong ethical standards, foster a passion for excellence, remain committed to our craft, and continuously strive for growth and innovation in the industry.',
'To be the leading construction company known for excellence, innovation, and sustainable practices, setting industry standards for quality and customer satisfaction.',
'about.png', 'logo2.png', 'logo.png');

-- Seed: Company Features
INSERT INTO Company_Features (FeatureTitle, FeatureDescription, FeatureImage, DisplayOrder) VALUES
('Modern Construction Techniques', 'Advanced construction methods and technologies using the latest industry standards and innovative approaches to ensure quality and efficiency in every project.', 'pentagon1.png', 1),
('Sustainable Practices', 'Environmentally conscious construction approaches that minimize environmental impact while maximizing efficiency and long-term value for our clients.', 'pentagon2.png', 2),
('Comprehensive Services', 'Complete construction solutions from initial design and planning to final execution and project completion, ensuring seamless project delivery.', 'pentagon3.png', 3),
('Ethical Standards and Client Commitment', 'Strong ethical standards and dedication to client satisfaction, building lasting relationships through transparency, integrity, and exceptional service.', 'pentagon4.png', 4);

-- Seed: Specialties
INSERT INTO Company_Specialties (SpecialtyName, SpecialtyDescription, SpecialtyImage, DisplayOrder) VALUES
('Architectural and Civil Works', 'Complete architectural design and civil construction services including structural engineering, foundation work, and building construction with attention to detail and quality.', 'specialties1.png', 1),
('Mechanical Works', 'HVAC, plumbing, and mechanical system installations ensuring optimal performance and energy efficiency for all mechanical systems.', 'project2.png', 2),
('Electrical Works', 'Complete electrical system design and installation including power distribution, lighting systems, and safety measures in compliance with electrical codes.', 'project1.png', 3),
('Plumbing Works', 'Comprehensive plumbing and water system solutions including water supply, drainage, and sewage systems designed for reliability and efficiency.', 'specialties6.png', 4),
('Auxiliary Works', 'Supporting construction and finishing works including interior finishing, exterior cladding, and specialized construction elements.', 'specialties5.png', 5),
('Swimming Pools', 'Specialized swimming pool construction and design including custom pool features, water treatment systems, and pool area landscaping.', 'specialties3.png', 6);

-- Seed: Industries
INSERT INTO Company_Industries (IndustryName, IndustryDescription, IndustryImage, DisplayOrder) VALUES
('Residential', 'Custom homes and residential developments including single-family homes, townhouses, and residential complexes designed for modern living.', 'industries1.png', 1),
('Hotel and Resorts', 'Hospitality and tourism construction projects including hotels, resorts, and recreational facilities with focus on guest comfort and operational efficiency.', 'industries2.png', 2),
('Foods and Beverages', 'Restaurant and food service facility construction including commercial kitchens, dining areas, and food processing facilities.', 'industries3.png', 3),
('Health and Wellness', 'Medical facilities and wellness centers including hospitals, clinics, and specialized healthcare facilities with modern medical infrastructure.', 'industries4.png', 4),
('Logistics Services', 'Warehouses and logistics facilities including storage facilities, distribution centers, and industrial buildings optimized for operational efficiency.', 'industries5.png', 5),
('Office Space and Leasing', 'Commercial office buildings and leasing spaces including corporate offices, retail spaces, and mixed-use developments.', 'industries6.png', 6);

-- Seed: Project Categories
INSERT INTO Project_Categories (CategoryName, CategoryDescription, CategoryImage, DisplayOrder) VALUES
('Residential Projects', 'Custom homes and residential developments including single-family homes, townhouses, and residential complexes.', 'project1.png', 1),
('Commercial Buildings', 'Office buildings and commercial structures including retail spaces, corporate offices, and mixed-use developments.', 'project8.png', 2),
('Systems Installation', 'Mechanical, electrical, and plumbing systems including HVAC, electrical, and plumbing installations for various project types.', 'specialties5.png', 3);

-- Seed: Projects
INSERT INTO Company_Projects (ProjectTitle, ProjectDescription, ProjectOwner, ProjectLocation, ProjectArea, ProjectValue, TurnoverDate, ProjectCategoryId, ProjectImage1, ProjectImage2, DisplayOrder) VALUES
('Two-Storey Residential With Roof Deck', 'A modern two-storey residential home featuring a functional roof deck and smart space utilization. Designed for comfort and flexibility, this project highlights our strength in residential design and structural execution in a sloped terrain environment. The project includes modern amenities, energy-efficient features, and sustainable design elements.', 'Jun Galase', 'Batangas', 250.00, 8500000.00, '2019-04-01', 1, 'project4.png', 'project5.png', 1),
('Commercial & Office Building', 'A modern commercial and office building designed for efficiency and functionality. This project showcases our expertise in commercial construction with emphasis on structural integrity, modern amenities, and sustainable design principles. The building features flexible office spaces, modern facilities, and energy-efficient systems.', 'Albert Sulog', 'Cavite', 1200.00, 25000000.00, '2016-04-01', 2, 'project6.png', 'project7.png', 2);

-- Seed: Process Steps
INSERT INTO Company_Process (ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder) VALUES
('Project Plan', 'Our construction process starts with a detailed project plan.', 'pentagon5.png', 1),
('Site Preparation', 'Process of getting a piece of land ready for construction.', 'pentagon6.png', 2),
('Execution', 'Construction activities that take place on a project site.', 'pentagon7.png', 3);

-- Seed: Contact Information
INSERT INTO Company_Contact (ContactType, ContactValue, ContactLabel, ContactIcon, DisplayOrder) VALUES
('phone', '+63 912 345 6789', 'Main Office', 'bi-telephone', 1),
('email', 'info@makabayanconstruction.com', 'General Inquiries', 'bi-envelope', 2),
('address', '123 Construction Ave, Metro Manila, Philippines', 'Main Office Address', 'bi-geo-alt', 3),
('phone', '+63 998 765 4321', 'Project Inquiries', 'bi-telephone', 4),
('email', 'projects@makabayanconstruction.com', 'Project Inquiries', 'bi-envelope', 5);

-- Seed: System Settings
INSERT INTO System_Settings (SettingKey, SettingValue, SettingDescription, SettingType) VALUES
('site_name', 'Makabayan Avellanosa Construction', 'Website name', 'text'),
('site_description', 'Building a Better Future', 'Website description', 'text'),
('contact_email', 'info@makabayanconstruction.com', 'Primary contact email', 'text'),
('contact_phone', '+63 912 345 6789', 'Primary contact phone', 'text'),
('social_facebook', 'https://facebook.com/makabayanconstruction', 'Facebook page URL', 'text'),
('social_instagram', 'https://instagram.com/makabayanconstruction', 'Instagram page URL', 'text'),
('social_linkedin', 'https://linkedin.com/company/makabayanconstruction', 'LinkedIn page URL', 'text'),
('maintenance_mode', '0', 'Maintenance mode (1=enabled, 0=disabled)', 'boolean'),
('session_timeout', '3600', 'Session timeout in seconds', 'number'),
('max_login_attempts', '5', 'Maximum login attempts before lockout', 'number');

-- =====================================================
-- DATABASE COMPLETED
-- =====================================================
-- Total Tables: 10
-- Total Records: 35+ seed records
-- Database Size: ~50KB (estimated)
-- =====================================================