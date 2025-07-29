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

// Get projects
try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
    $projects = $stmt->fetchAll();
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
            <h1>Projects</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Projects</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                <i class="bi bi-plus"></i> Add New Project
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="table-responsive">
                                <table id="projectsTable" class="table table-hover projects_table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Owner</th>
                                            <th>Location</th>
                                            <th>Category</th>
                                            <th>Turnover Date</th>
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

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus me-2"></i>Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProjectForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Title *</label>
                                <input type="text" class="form-control" name="project_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Owner</label>
                                <input type="text" class="form-control" name="project_owner">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="project_location">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="project_category">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Turnover Date</label>
                                <input type="date" class="form-control" name="turnover_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Project Description</label>
                            <textarea class="form-control" name="project_description" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 1 URL</label>
                                <input type="text" class="form-control" name="image_url_1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 2 URL</label>
                                <input type="text" class="form-control" name="image_url_2">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 3 URL</label>
                                <input type="text" class="form-control" name="image_url_3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 4 URL</label>
                                <input type="text" class="form-control" name="image_url_4">
                            </div>
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
                        <button type="submit" class="btn btn-primary">Add Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProjectForm">
                    <div class="modal-body">
                        <input type="hidden" name="project_id" id="edit_project_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Title *</label>
                                <input type="text" class="form-control" name="project_title" id="edit_project_title"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Owner</label>
                                <input type="text" class="form-control" name="project_owner" id="edit_project_owner">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="project_location"
                                    id="edit_project_location">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="project_category"
                                    id="edit_project_category">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Turnover Date</label>
                                <input type="date" class="form-control" name="turnover_date" id="edit_turnover_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Project Description</label>
                            <textarea class="form-control" name="project_description" id="edit_project_description"
                                rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 1 URL</label>
                                <input type="text" class="form-control" name="image_url_1" id="edit_image_url_1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 2 URL</label>
                                <input type="text" class="form-control" name="image_url_2" id="edit_image_url_2">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 3 URL</label>
                                <input type="text" class="form-control" name="image_url_3" id="edit_image_url_3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image 4 URL</label>
                                <input type="text" class="form-control" name="image_url_4" id="edit_image_url_4">
                            </div>
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
                        <button type="submit" class="btn btn-primary">Update Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the project "<span id="delete_project_title"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                    <input type="hidden" id="delete_project_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete Project</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- DataTables JavaScript -->
    <script src="assets/js/dataTables/projectsDataTables.js"></script>
</body>