<?php
    // Session check for admin pages
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../login.php');
        exit();
    }

    // Check session timeout
    $loginTime = $_SESSION['login_time'] ?? 0;
    $currentTime = time();
    $sessionTimeout = $_SESSION['session_timeout'] ?? 1800; // Default 30 minutes

    if ($currentTime - $loginTime > $sessionTimeout) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

    // Update login time to extend session
    $_SESSION['login_time'] = $currentTime;
?> 