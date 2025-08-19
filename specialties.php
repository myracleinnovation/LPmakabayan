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

function fetchSpecialties($pdo)
{
    $stmt = $pdo->query('SELECT SpecialtyName, SpecialtyDescription, SpecialtyImage FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchProcessSteps($pdo)
{
    $stmt = $pdo->query('SELECT ProcessTitle, ProcessDescription, ProcessImage FROM Company_Process WHERE Status = 1 ORDER BY DisplayOrder');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchIndustries($pdo)
{
    $stmt = $pdo->query('SELECT IndustryName, IndustryDescription, IndustryImage FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$specialties = fetchSpecialties($pdo);
$processSteps = fetchProcessSteps($pdo);
$industries = fetchIndustries($pdo);
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- OUR WORK SPEAKS FOR ITSELF SECTION -->
    <section class="bg-warning text-white py-4 py-md-5">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bolder fs-1 fs-md-1 text-uppercase text-black">Excellence in Construction.<br>Precision in
                    every detail.</h2>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($specialties as $specialty): ?>
                <div class="col-12 col-md-4 mb-4">
                    <div class="position-relative project-category overflow-hidden">
                        <?php if (!empty($specialty['SpecialtyImage'])): ?>
                        <img src="assets/img/<?= htmlspecialchars($specialty['SpecialtyImage']) ?>"
                            class="w-100 object-fit-cover" alt="<?= htmlspecialchars($specialty['SpecialtyName']) ?>">
                        <?php endif; ?>
                        <div class="category-overlay d-flex align-items-center justify-content-center">
                            <h3 class="text-white fw-bold text-center fs-2 fs-md-5 text-uppercase">
                                <?= htmlspecialchars($specialty['SpecialtyName']) ?></h3>
                        </div>
                    </div>
                    <?php if (!empty($specialty['SpecialtyDescription'])): ?>
                    <div class="mt-3">
                        <p class="text-black fs-6"><?= htmlspecialchars($specialty['SpecialtyDescription']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- OUR PROCESS SECTION -->
    <section class="bg-black text-white py-5 min-vh-100">
        <div class="container">
            <div class="text-center mb-4 mt-5 pt-5 mb-5 pb-5">
                <h2 class="fw-bold fs-1 text-uppercase">Our Process</h2>
            </div>
            <div class="row justify-content-center text-center">
                <?php foreach ($processSteps as $process): ?>
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <?php if (!empty($process['ProcessImage'])): ?>
                        <img src="assets/img/<?= htmlspecialchars($process['ProcessImage']) ?>"
                            class="w-100 h-100 object-fit-cover"
                            alt="<?= htmlspecialchars($process['ProcessTitle']) ?>">
                        <?php endif; ?>
                    </div>
                    <h1 class="fs-3 text-uppercase fw-bold"><?= htmlspecialchars($process['ProcessTitle']) ?></h1>
                    <?php if (!empty($process['ProcessDescription'])): ?>
                    <h2 class="fs-5 fw-normal"><?= htmlspecialchars($process['ProcessDescription']) ?></h2>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- INDUSTRIES WE SERVE SECTION -->
    <section class="bg-warning text-white py-4 py-md-5">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bolder fs-1 fs-md-1 text-uppercase text-black">Industries We Serve</h2>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($industries as $industry): ?>
                <div class="col-12 col-md-4 mb-4">
                    <div class="position-relative project-category overflow-hidden">
                        <?php if (!empty($industry['IndustryImage'])): ?>
                        <img src="assets/img/<?= htmlspecialchars($industry['IndustryImage']) ?>"
                            class="w-100 object-fit-cover" alt="<?= htmlspecialchars($industry['IndustryName']) ?>">
                        <?php endif; ?>
                        <div class="category-overlay d-flex align-items-center justify-content-center">
                            <h3 class="text-white fw-bold text-center fs-2 fs-md-5 text-uppercase">
                                <?= htmlspecialchars($industry['IndustryName']) ?></h3>
                        </div>
                    </div>
                    <?php if (!empty($industry['IndustryDescription'])): ?>
                    <div class="mt-3">
                        <p class="text-black fs-6"><?= htmlspecialchars($industry['IndustryDescription']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- OUR COMPANY & SPECIALTIES SECTION -->
    <section class="bg-dark text-white">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-3 mb-md-0">
                <a href="index.php#company">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:250px;">
                        <img src="assets/img/banner3.jpg" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Our Company</h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 p-0">
                <a href="project.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:250px;">
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
