<?php
session_start();
header('Content-Type: application/json');

// Include configuration
require_once __DIR__ . '/Config.php';

// Handle login authentication
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter both username and password.'
        ]);
        exit();
    }
    
    // Check for too many login attempts
    $loginAttempts = $_SESSION['login_attempts'] ?? 0;
    $lastAttemptTime = $_SESSION['last_attempt_time'] ?? 0;
    
    if ($loginAttempts >= LOGIN_ATTEMPTS_LIMIT) {
        $timeSinceLastAttempt = time() - $lastAttemptTime;
        if ($timeSinceLastAttempt < LOGIN_TIMEOUT) {
            $remainingTime = LOGIN_TIMEOUT - $timeSinceLastAttempt;
            echo json_encode([
                'success' => false,
                'message' => "Too many failed attempts. Please try again in " . ceil($remainingTime / 60) . " minutes."
            ]);
            exit();
        } else {
            // Reset attempts after timeout
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    try {
        require_once 'Db.php';
        
        // Get database connection using Db class
        $pdo = Db::getConnection();
        
        // Check if user exists in Admin_Accounts table
        $stmt = $pdo->prepare("SELECT * FROM Admin_Accounts WHERE Username = ? AND Status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['Password'])) {
            // Login successful - reset attempts
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['Username'];
            $_SESSION['admin_id'] = $user['IdAdmin'];
            $_SESSION['login_time'] = time();
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => 'admin/index.php',
                'user' => [
                    'username' => $user['Username'],
                    'id' => $user['IdAdmin']
                ]
            ]);
        } else {
            // Login failed - increment attempts
            $_SESSION['login_attempts'] = $loginAttempts + 1;
            $_SESSION['last_attempt_time'] = time();
            
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password. Please try again.'
            ]);
        }
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("LoginAPI database error: " . $e->getMessage());
        
        // Provide a user-friendly error message
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please check your database configuration.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'System error. Please try again later.'
            ]);
        }
    }
    exit();
}

// Handle logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    // Clear all session data
    session_unset();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'login.php'
    ]);
    exit();
}

// Handle session check
if (isset($_POST['action']) && $_POST['action'] === 'check_session') {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        // Check if session has expired
        $loginTime = $_SESSION['login_time'] ?? 0;
        if (time() - $loginTime > SESSION_TIMEOUT) {
            // Session expired
            session_unset();
            session_destroy();
            echo json_encode([
                'success' => true,
                'logged_in' => false,
                'message' => 'Session expired'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'username' => $_SESSION['admin_username'] ?? '',
                'admin_id' => $_SESSION['admin_id'] ?? '',
                'login_time' => $loginTime
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
    exit();
}

// Handle database connection test
if (isset($_POST['action']) && $_POST['action'] === 'test_db') {
    try {
        require_once 'Db.php';
        $result = Db::testConnection();
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database test failed: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Invalid request
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
?>