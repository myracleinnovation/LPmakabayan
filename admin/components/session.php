<?php
    // Only redirect to admin if we're on the login page and admin is logged in
    if (basename($_SERVER['PHP_SELF']) === 'login.php' && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $loginTime = $_SESSION['login_time'] ?? 0;
        $currentTime = time();
    
        if ($currentTime - $loginTime > 1800) {
            session_unset();
            session_destroy();
        } else {
            header('Location: admin/index.php');
            exit();
        }
    }
?>