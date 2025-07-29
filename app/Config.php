<?php
    // Set to false for live/production, true for development
    define('DEV_MODE', true);
    define('MYRACLE_LOGIN_URL', 'https://access.myracle.ph/views/index.php');
    
    // Database Configuration
    if (DEV_MODE) {
        // Development/Local Database Settings
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'MakabayanConstruction');
        define('DB_USER', 'root');
        define('DB_PASS', '');
    } else {
        // Production/Live Database Settings
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'webmaster_');
        define('DB_USER', 'webmaster');
        define('DB_PASS', '78Dw$8mc5');
    }
    
    // Application Settings
    define('APP_NAME', 'Makabayan Avellanosa Construction');
    define('APP_VERSION', '1.0.0');
    define('ADMIN_EMAIL', 'admin@makabayanconstruction.com');
    
    // Session Configuration
    define('SESSION_TIMEOUT', 3600); // 1 hour
    define('SESSION_NAME', 'makabayan_session');
    
    // Security Settings
    define('PASSWORD_COST', 12); // For password_hash()
    define('LOGIN_ATTEMPTS_LIMIT', 5);
    define('LOGIN_TIMEOUT', 900); // 15 minutes
?> 