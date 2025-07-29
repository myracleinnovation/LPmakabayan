<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../app/Db.php';

// Get admin info
$admin_username = $_SESSION['admin_username'];
$admin_id = $_SESSION['admin_id'];

// Get dashboard statistics
try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Count total projects
    $stmt = $pdo->query("SELECT COUNT(*) as total_projects FROM Company_Projects WHERE Status = 1");
    $total_projects = $stmt->fetch()['total_projects'];
    
    // Count total specialties
    $stmt = $pdo->query("SELECT COUNT(*) as total_specialties FROM Company_Specialties WHERE Status = 1");
    $total_specialties = $stmt->fetch()['total_specialties'];
    
    // Count total industries
    $stmt = $pdo->query("SELECT COUNT(*) as total_industries FROM Company_Industries WHERE Status = 1");
    $total_industries = $stmt->fetch()['total_industries'];
    
    // Get recent projects
    $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY CreatedTimestamp DESC LIMIT 5");
    $recent_projects = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}
?>

<?php include 'components/header.php'; ?>

<body>
    <!-- ======= Header ======= -->
    <?php include 'components/topNav.php'; ?>
    <!-- ======= Sidebar ======= -->
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
                <!-- Stats Cards -->
                <div class="col-lg-4">
                    <div class="info-card sales">
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?php echo $total_projects; ?></h6>
                                <span class="text-white-50 small pt-2">Total Projects</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card revenue">
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?php echo $total_specialties; ?></h6>
                                <span class="text-white-50 small pt-2">Specialties</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card customers">
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div class="ps-3">
                                <h6><?php echo $total_industries; ?></h6>
                                <span class="text-white-50 small pt-2">Industries</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="col-12">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body p-3">
                            <h5 class="card-title">Recent Projects</h5>

                            <table id="recentProjectsTable" class="table table-borderless datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">Project</th>
                                        <th scope="col">Owner</th>
                                        <th scope="col">Location</th>
                                        <th scope="col">Turnover Date</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'components/footer.php'; ?>

    <!-- DataTables JavaScript -->
    <script src="assets/js/dataTables/dashboardDataTables.js"></script>
</body>

</html>