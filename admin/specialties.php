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

// Get specialties
try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    $stmt = $pdo->query("SELECT * FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder ASC, SpecialtyName ASC");
    $specialties = $stmt->fetchAll();
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
            <h1>Specialties</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Specialties</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Specialties</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSpecialtyModal">
                                <i class="bi bi-plus"></i> Add New Specialty
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="table-responsive">
                                <table id="specialtiesTable" class="table table-hover specialties_table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Specialty Name</th>
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
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Add Specialty Modal -->
    <div class="modal fade" id="addSpecialtyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus me-2"></i>Add New Specialty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSpecialtyForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Specialty Name *</label>
                                <input type="text" class="form-control" name="specialty_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Description</label>
                            <textarea class="form-control" name="specialty_description" rows="4"
                                placeholder="Describe the specialty service and its benefits..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Image URL</label>
                            <input type="text" class="form-control" name="image_url"
                                placeholder="Enter the URL of the specialty image">
                            <small class="text-muted">Example: assets/img/specialties1.png</small>
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
                        <button type="submit" class="btn btn-primary">Add Specialty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Specialty Modal -->
    <div class="modal fade" id="editSpecialtyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Specialty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSpecialtyForm">
                    <div class="modal-body">
                        <input type="hidden" name="specialty_id" id="edit_specialty_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Specialty Name *</label>
                                <input type="text" class="form-control" name="specialty_name" id="edit_specialty_name"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Description</label>
                            <textarea class="form-control" name="specialty_description" id="edit_specialty_description"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Image URL</label>
                            <input type="text" class="form-control" name="image_url" id="edit_image_url">
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
                        <button type="submit" class="btn btn-primary">Update Specialty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSpecialtyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the specialty "<span id="delete_specialty_name"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                    <input type="hidden" id="delete_specialty_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete Specialty</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- DataTables JavaScript -->
    <script src="assets/js/dataTables/specialtiesDataTables.js"></script>
</body>

</html>