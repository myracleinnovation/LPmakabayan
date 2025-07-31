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
    require_once 'app/Db.php';
    $pdo = Db::connect();

    function fetchCompanyInfo($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Info WHERE Status = 1 LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function fetchCompanyFeatures($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Features WHERE Status = 1 ORDER BY DisplayOrder");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $companyInfo = fetchCompanyInfo($pdo);
    $companyFeatures = fetchCompanyFeatures($pdo);
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- HERO SECTION -->
    <div class="position-relative min-vh-100 d-flex align-items-center justify-content-center"
        style="background-image: url('assets/img/bg.png'); background-size: cover; background-position: center;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
        <div class="container position-relative z-1 text-center py-5 px-3 px-md-5">
            <img src="assets/img/<?= htmlspecialchars($companyInfo['LogoImage'] ?? '') ?>" alt="Logo" class="mb-3 mt-5 img-fluid"
                style="max-width: 180px; min-width: 90px;">
            <h1 class="fw-bold text-white text-uppercase fs-1"><?= htmlspecialchars($companyInfo['Tagline'] ?? '') ?>
            </h1>
            <p class="lead text-white w-100 w-md-75 pb-2 pb-md-5 mx-auto mt-3 fs-3">
                <?= htmlspecialchars($companyInfo['Description'] ?? '') ?>
            </p>

            <div class="d-flex flex-column flex-lg-row justify-content-center align-items-center gap-2 mt-5 pt-5">
                <a href="#company" class="btn btn-warning-hover active px-4 py-2 border border-0 border-white">Our
                    Company</a>
                <a href="specialties.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Our
                    Specialties</a>
                <a href="project.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Our
                    Projects</a>
                <a href="connect.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Connect
                    Now</a>
            </div>
        </div>
    </div>

    <!-- OUR COMPANY SECTION -->
    <section id="company" class="bg-warning py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center mb-5 flex-column flex-md-row">
                <div class="col-12 col-md-6 mb-4 mb-md-0">
                    <h2 class="fw-bold mb-3 text-uppercase fs-5">Our Company</h2>
                    <p class="fs-5"><b><?= htmlspecialchars($companyInfo['CompanyName'] ?? '') ?></b> is committed to
                        delivering top-tier architectural, civil, mechanical, electrical, and plumbing works backed by a
                        highly dedicated and skilled team.</p>
                    <p class="fs-5"><?= htmlspecialchars($companyInfo['Mission'] ?? '') ?></p>
                </div>
                <div class="col-12 col-md-6 mb-4 mb-md-0 pb-4">
                    <img src="assets/img/<?= htmlspecialchars($companyInfo['AboutImage'] ?? '') ?>"
                        class="img-fluid object-fit-cover w-100" alt="About Our Company">
                </div>
            </div>
        </div>
    </section>

    <!-- MORE THAN JUST CONSTRUCTION SECTION -->
    <section class="bg-black text-white py-5 min-vh-100">
        <div class="container">
            <div class="text-center mb-4 mt-5 pt-5 mb-5 pb-5">
                <h2 class="fw-bold fs-1">More Than Just Construction</h2>
            </div>
            <div class="row justify-content-center text-center">
                <?php foreach ($companyFeatures as $feature): ?>
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <img src="assets/img/<?= htmlspecialchars($feature['FeatureImage'] ?? '') ?>"
                            class="w-100 h-100 object-fit-cover"
                            alt="<?= htmlspecialchars($feature['FeatureTitle'] ?? '') ?>">
                    </div>
                    <div class="fs-4"><?= htmlspecialchars($feature['FeatureTitle'] ?? '') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES & BUILDS SECTION -->
    <section id="specialties">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-4 mb-md-0">
                <a href="specialties.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:300px;">
                        <img src="assets/img/banner1.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Discover<br>Our Specialties</h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 p-0">
                <a href="project.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:300px;">
                        <img src="assets/img/banner2.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Check Out<br>Our Builds</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>