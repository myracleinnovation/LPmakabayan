<?php
    session_start();
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../login.php');
        exit();
    }
    
    include 'components/header.php';
    require_once '../app/Db.php';

    $admin_username = $_SESSION['admin_username'];
    $admin_id = $_SESSION['admin_id'];

    // Get statistics
    try {
        $pdo = Db::connect();
        
        // Count projects
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Company_Projects WHERE Status = 1");
        $totalProjects = $stmt->fetch()['total'];
        
        // Count specialties
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Company_Specialties WHERE Status = 1");
        $totalSpecialties = $stmt->fetch()['total'];
        
        // Count industries
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Company_Industries WHERE Status = 1");
        $totalIndustries = $stmt->fetch()['total'];
        
        // Count features
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Company_Features WHERE Status = 1");
        $totalFeatures = $stmt->fetch()['total'];
        
        // Count process steps
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Company_Process WHERE Status = 1");
        $totalProcess = $stmt->fetch()['total'];
        
        // Count project categories
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Project_Categories WHERE Status = 1");
        $totalCategories = $stmt->fetch()['total'];
        
        // Get recent projects
        $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY CreatedTimestamp DESC LIMIT 5");
        $recentProjects = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
?>

<body>
    <?php include 'components/topNav.php'; ?>
    <?php include 'components/sideNav.php'; ?>

    <!-- ======= Main ======= -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-lg-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Projects</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-briefcase"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $totalProjects; ?></h6>
                                    <span class="text-success small pt-1 fw-bold">Active Projects</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Specialties</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $totalSpecialties; ?></h6>
                                    <span class="text-success small pt-1 fw-bold">Active Specialties</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card customers-card">
                        <div class="card-body">
                            <h5 class="card-title">Industries</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $totalIndustries; ?></h6>
                                    <span class="text-success small pt-1 fw-bold">Active Industries</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Features</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-star"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $totalFeatures; ?></h6>
                                    <span class="text-success small pt-1 fw-bold">Active Features</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="projects.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-briefcase me-2"></i>Manage Projects
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="projectCategories.php" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-folder me-2"></i>Project Categories
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="specialties.php" class="btn btn-outline-success w-100">
                                        <i class="bi bi-tools me-2"></i>Specialties
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="industries.php" class="btn btn-outline-info w-100">
                                        <i class="bi bi-building me-2"></i>Industries
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="features.php" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-star me-2"></i>Features
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="process.php" class="btn btn-outline-dark w-100">
                                        <i class="bi bi-list-check me-2"></i>Process
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Recent Projects</h5>
                            <a href="projects.php" class="btn btn-primary btn-sm">View All Projects</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentProjects)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Project Title</th>
                                            <th>Owner</th>
                                            <th>Location</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProjects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['ProjectTitle']); ?></td>
                                            <td><?php echo htmlspecialchars($project['ProjectOwner']); ?></td>
                                            <td><?php echo htmlspecialchars($project['ProjectLocation']); ?></td>
                                            <td>
                                                <?php 
                                                if ($project['ProjectCategoryId']) {
                                                    $stmt = $pdo->prepare("SELECT CategoryName FROM Project_Categories WHERE IdCategory = ?");
                                                    $stmt->execute([$project['ProjectCategoryId']]);
                                                    $category = $stmt->fetch();
                                                    echo htmlspecialchars($category['CategoryName'] ?? 'N/A');
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <a href="projects.php" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-briefcase display-1 text-muted"></i>
                                <p class="text-muted">No projects found</p>
                                <a href="projects.php" class="btn btn-primary">Add Your First Project</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Total Process Steps:</strong> <?php echo $totalProcess; ?></p>
                                    <p><strong>Total Categories:</strong> <?php echo $totalCategories; ?></p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Admin User:</strong> <?php echo htmlspecialchars($admin_username); ?></p>
                                    <p><strong>Last Login:</strong> <?php echo date('M d, Y H:i'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Links</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <a href="companyInfo.php" class="btn btn-outline-info btn-sm w-100">
                                        <i class="bi bi-info-circle me-1"></i>Company Info
                                    </a>
                                </div>
                                <div class="col-6 mb-2">
                                    <a href="settings.php" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="bi bi-gear me-1"></i>Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>
</body>

</html>