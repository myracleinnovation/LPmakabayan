<?php
session_start();

// Check if admin is logged in, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $loginTime = $_SESSION['login_time'] ?? 0;
    $currentTime = time();

    // Check if session has expired (30 minutes = 1800 seconds)
    if ($currentTime - $loginTime > 1800) {
        // Session expired, clear it
        session_unset();
        session_destroy();
    } else {
        // Still logged in, redirect to admin dashboard
        header('Location: admin/index.php');
        exit();
    }
}

include 'components/header.php';
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- OUR COMPANY & SPECIALTIES SECTION -->
    <section class="bg-dark text-white">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-3 mb-md-0">
                <a href="index.php#company">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height: 300px;">
                        <img src="assets/img/banner3.jpg" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Our Company</h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 p-0">
                <a href="specialties.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height: 300px;">
                        <img src="assets/img/banner1.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Discover<br>Our Specialties</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>