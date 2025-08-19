<?php
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
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