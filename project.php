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

function fetchProjectCategories($pdo) {
    $stmt = $pdo->query("SELECT CategoryName, CategoryDescription, CategoryImage FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchProjects($pdo) {
    $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY DisplayOrder, TurnoverDate DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$projectCategories = fetchProjectCategories($pdo);
$projects = fetchProjects($pdo);
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- OUR WORK SPEAKS FOR ITSELF SECTION -->
    <section class="bg-warning text-white py-4 py-md-5" style="min-height: auto;">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bolder fs-2 fs-md-1 text-uppercase text-black">Our Work Speaks For Itself</h2>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($projectCategories as $category): ?>
                <div class="col-12 col-md-4 mb-4">
                    <div class="position-relative project-category overflow-hidden">
                        <?php if (!empty($category['CategoryImage'])): ?>
                        <img src="assets/img/<?= htmlspecialchars($category['CategoryImage']); ?>" class="w-100 object-fit-cover"
                            alt="<?= htmlspecialchars($category['CategoryName']); ?>">
                        <?php endif; ?>
                        <div class="category-overlay d-flex align-items-center justify-content-center">
                            <h3 class="text-white fw-bold text-center fs-2 fs-md-5 text-uppercase">
                                <?= htmlspecialchars($category['CategoryName']); ?>
                            </h3>
                        </div>
                    </div>
                    <?php if (!empty($category['CategoryDescription'])): ?>
                    <div class="mt-3">
                        <p class="text-black fs-6"><?= htmlspecialchars($category['CategoryDescription']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php 
    $bgClass = 'bg-black';
    $textClass = 'text-white';
    $titleClass = 'text-warning';
    $dotClass = 'bg-white';
    $justifyClass = '';
    
    foreach ($projects as $index => $project): 
        // Alternate background colors
        if ($index % 2 == 0) {
            $bgClass = 'bg-black';
            $textClass = 'text-white';
            $titleClass = 'text-warning';
            $dotClass = 'bg-white';
            $justifyClass = '';
        } else {
            $bgClass = 'bg-warning';
            $textClass = 'text-black';
            $titleClass = 'text-black';
            $dotClass = 'bg-black';
            $justifyClass = 'justify-content-end';
        }
    ?>
    <!-- PROJECT DETAIL SECTION -->
    <section class="<?= $bgClass; ?> <?= $textClass; ?> py-4 py-md-5" style="min-height: 100vh;">
        <div class="container h-100 d-flex align-items-center justify-content-center flex-column">
            <div class="row align-items-center">
                <div class="col-12 col-lg-6 mb-4 mb-lg-0 <?= ($index % 2 == 1) ? 'order-lg-2' : ''; ?>">
                    <?php if (!empty($project['ProjectTitle'])): ?>
                    <h2 class="fw-bolder <?= $titleClass; ?> mb-3 mb-md-4 fs-1 fs-md-1 text-uppercase">
                        <?= htmlspecialchars($project['ProjectTitle']); ?></h2>
                    <?php endif; ?>
                    <div class="mb-3 mb-md-4">
                        <?php if (!empty($project['ProjectOwner'])): ?>
                        <p class="mb-2 fs-5">Owner:
                            <strong><?= htmlspecialchars($project['ProjectOwner']); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($project['TurnoverDate'])): ?>
                        <p class="mb-2 fs-5">Turnover:
                            <strong><?= date('F Y', strtotime($project['TurnoverDate'])); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($project['ProjectLocation'])): ?>
                        <p class="mb-2 fs-5">Location:
                            <strong><?= htmlspecialchars($project['ProjectLocation']); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($project['ProjectValue'])): ?>
                        <p class="mb-2 fs-5">Project Value:
                            <strong>₱<?= number_format($project['ProjectValue'], 2); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($project['ProjectArea'])): ?>
                        <p class="mb-2 fs-5">Project Area:
                            <strong><?= number_format($project['ProjectArea'], 2); ?> sqm</strong></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($project['ProjectDescription'])): ?>
                    <p class="mb-3 mb-md-4 fs-5">
                        <?= htmlspecialchars($project['ProjectDescription']); ?>
                    </p>
                    <?php endif; ?>
                    <div class="d-flex gap-2 <?= $justifyClass; ?>">
                        <div class="<?= $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?= $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?= $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?= $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?= $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 <?= ($index % 2 == 1) ? 'order-lg-1' : ''; ?>">
                    <div class="row g-3">
                        <?php if (!empty($project['ProjectImage1'])): ?>
                        <div class="col-12">
                            <img src="assets/img/<?= htmlspecialchars($project['ProjectImage1']); ?>"
                                class="w-100 h-100 object-fit-cover" style="height: 200px;"
                                alt="<?= htmlspecialchars($project['ProjectTitle']); ?> - Image 1">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($project['ProjectImage2'])): ?>
                        <div class="col-12">
                            <img src="assets/img/<?= htmlspecialchars($project['ProjectImage2']); ?>"
                                class="w-100 h-100 object-fit-cover" style="height: 200px;"
                                alt="<?= htmlspecialchars($project['ProjectTitle']); ?> - Image 2">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endforeach; ?>

    <!-- OUR COMPANY & SPECIALTIES SECTION -->
    <section class="bg-dark text-white">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-3 mb-md-0">
                <a href="index.php#company">
                    <div class="position-relative w-100 h-100 specialty-item">
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
                    <div class="position-relative w-100 h-100 specialty-item">
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