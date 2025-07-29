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

// Initialize variables for messages (if needed for legacy support)
$message = '';
$message_type = '';

// Get industries
try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    $stmt = $pdo->query("SELECT * FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder ASC, IndustryName ASC");
    $industries = $stmt->fetchAll();
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
            <h1>Industries</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Industries</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Industries</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIndustryModal">
                                <i class="bi bi-plus"></i> Add New Industry
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show"
                                role="alert">
                                <i
                                    class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <div class="table-responsive">
                                <table id="industriesTable" class="table table-hover industries_table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Industry Name</th>
                                            <th>Description</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-briefcase display-1 text-muted"></i>
                                <p class="text-muted">No industries found</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Add Industry Modal -->
    <div class="modal fade" id="addIndustryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Industry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry Name *</label>
                                <input type="text" class="form-control" name="industry_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Description</label>
                            <textarea class="form-control" name="industry_description" rows="4"
                                placeholder="Describe the industry and your services for this sector..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Image URL</label>
                            <input type="text" class="form-control" name="industry_image"
                                placeholder="Enter the URL of the industry image">
                            <small class="text-muted">Example: assets/img/industries1.png</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Industry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Industry Modal -->
    <div class="modal fade" id="editIndustryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Industry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="industry_id" id="edit_industry_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry Name *</label>
                                <input type="text" class="form-control" name="industry_name" id="edit_industry_name"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Description</label>
                            <textarea class="form-control" name="industry_description" id="edit_industry_description"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Image URL</label>
                            <input type="text" class="form-control" name="industry_image" id="edit_industry_image">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Industry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteIndustryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the industry "<span id="delete_industry_name"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="industry_id" id="delete_industry_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Industry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'components/footer.php'; ?>
    
    <!-- DataTables JavaScript -->
    <script src="assets/js/dataTables/industriesDataTables.js"></script>
</body>

</html>