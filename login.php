<?php
session_start();
include './admin/components/session.php';
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
                $error_message = 'Too many failed attempts. Please try again in ' . ceil($remainingTime / 60) . ' minutes.';
            } else {
                $_SESSION['login_attempts'] = 0;
            }
        }

        if (empty($error_message)) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM Admin_Accounts WHERE Username = ? AND Status = 1');
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
                error_log('Login database error: ' . $e->getMessage());

                $error_message = 'System error. Please try again later.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charSet="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Makabayan Avellanosa Construction</title>
    <meta name="description"
        content="Makabayan Avellanosa Construction is a construction company in the Philippines specialized in streamlining workflows in construction and real estate industries." />
    <meta name="author" content="Makabayan Avellanosa Construction" />
    <meta name="keywords"
        content="Makabayan Avellanosa Construction,Makabayan Avellanosa,Makabayan,Construction,Real Estate,Customized Apps,Simplified Tools,Online Platforms,Software Development,Application Development,Web App,Mobile App,Progressive Web App" />
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon" sizes="16x16" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
</head>

<body>
    <main>
        <div class="container">
            <section
                class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Login</h5>
                                        <p class="text-center small">Enter your username & password to login</p>
                                    </div>

                                    <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php echo htmlspecialchars($error_message); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php endif; ?>

                                    <form class="row g-3 needs-validation" method="POST" action="" novalidate>
                                        <div class="col-12">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group has-validation">
                                                <input type="text" name="username" class="form-control shadow-none"
                                                    id="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                                <div class="invalid-feedback">Please enter your username.</div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <div class="input-group has-validation">
                                                <input type="password" name="password" class="form-control shadow-none"
                                                    id="password" required>
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="togglePassword" style="border-left: 0;">
                                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                                </button>
                                                <div class="invalid-feedback">Please enter your password!</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary w-100" type="submit">Login</button>
                                        </div>
                                    </form>

                                    <div class="mt-3">
                                        <div class="alert alert-info" role="alert">
                                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Demo Access
                                            </h6>
                                            <small>
                                                <strong>Default Admin Credentials:</strong><br>
                                                Username: <strong>admin</strong><br>
                                                Password: <strong>password</strong>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="text-center mt-3">
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-2"></i>Back to Website
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Ensure eye icon is visible */
        .bi-eye,
        .bi-eye-slash {
            font-size: 16px;
            color: #6c757d;
        }

        .btn-outline-secondary:hover .bi-eye,
        .btn-outline-secondary:hover .bi-eye-slash {
            color: #495057;
        }
    </style>

    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.remove('bi-eye');
                        eyeIcon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('bi-eye-slash');
                        eyeIcon.classList.add('bi-eye');
                    }
                });
            }
        });
    </script>
</body>

</html>
