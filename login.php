<?php
    session_start();
    include 'admin/components/session.php';
    require_once 'app/Db.php';
    $pdo = Db::connect();
    $error_message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            $error_message = 'Please enter both username and password.';
        } else {
            $loginAttempts = $_SESSION['login_attempts'] ?? 0;
            $lastAttemptTime = $_SESSION['last_attempt_time'] ?? 0;
            
            if ($loginAttempts >= 3) {
                $timeSinceLastAttempt = time() - $lastAttemptTime;
                if ($timeSinceLastAttempt < 300) {
                    $remainingTime = 300 - $timeSinceLastAttempt;
                    $error_message = "Too many failed attempts. Please try again in " . ceil($remainingTime / 60) . " minutes.";
                } else {
                    $_SESSION['login_attempts'] = 0;
                }
            }
            
            if (empty($error_message)) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM Admin_Accounts WHERE Username = ? AND Status = 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($password, $user['Password'])) {
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['last_attempt_time'] = 0;
                        
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_username'] = $user['Username'];
                        $_SESSION['admin_id'] = $user['IdAdmin'];
                        $_SESSION['login_time'] = time();
                        
                        $_SESSION['session_timeout'] = 1800;
                        
                        header('Location: admin/index.php');
                        exit();
                    } else {
                        $_SESSION['login_attempts'] = $loginAttempts + 1;
                        $_SESSION['last_attempt_time'] = time();
                        
                        $error_message = 'Invalid username or password.';
                    }
                } catch (Exception $e) {
                    error_log("Login database error: " . $e->getMessage());
                    
                    $error_message = 'System error. Please try again later.';
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
    <title>Admin Login - Makabayan Avellanosa Construction</title>

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #d2691e;
            --secondary-color: #8b4513;
            --accent-color: #ff8c00;
            --success-color: #228b22;
            --warning-color: #ffa500;
            --danger-color: #dc143c;
            --info-color: #4169e1;
            --dark-color: #2f4f4f;
            --light-color: #f8f9fa;
            --border-color: #dee2e6;
            --text-primary: #2f4f4f;
            --text-secondary: #696969;
            --bg-primary: #fff8dc;
            --bg-secondary: #f5f5dc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Open Sans", sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(210, 105, 30, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }

        .login-header h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid rgba(210, 105, 30, 0.2);
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            transition: all 0.3s ease;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .credentials-info {
            background: rgba(210, 105, 30, 0.1);
            border: 1px solid rgba(210, 105, 30, 0.2);
            border-radius: 12px;
            padding: 15px;
            margin-top: 25px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h3>Admin Login</h3>
                <p>Makabayan Avellanosa Construction</p>
            </div>

            <div class="login-body">
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Enter your username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your password" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-login">Login</button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Website
                        </a>
                    </div>
                </form>

                <div class="credentials-info">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Default credentials: <strong>admin</strong> / <strong>password</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>