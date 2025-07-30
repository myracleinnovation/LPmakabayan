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
?>

<body>
    <?php include 'components/topNav.php'; ?>
    <?php include 'components/sideNav.php'; ?>

    <!-- ======= Main ======= -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Process</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Process</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Process Steps</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProcessModal">
                                <i class="bi bi-plus"></i> Add New Process Step
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="table-responsive">
                                <table id="processTable" class="table table-hover process_table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Process Title</th>
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

    <!-- Add Process Modal -->
    <div class="modal fade" id="addProcessModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus me-2"></i>Add New Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProcessForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Process Title *</label>
                                <input type="text" class="form-control" name="process_title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Description</label>
                            <textarea class="form-control" name="process_description" rows="4"
                                placeholder="Describe the process step..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Image URL</label>
                            <input type="text" class="form-control" name="process_image"
                                placeholder="Enter the URL of the process image">
                            <small class="text-muted">Example: assets/img/pentagon5.png</small>
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
                        <button type="submit" class="btn btn-primary">Add Process Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Process Modal -->
    <div class="modal fade" id="editProcessModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProcessForm">
                    <div class="modal-body">
                        <input type="hidden" name="process_id" id="edit_process_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Process Title *</label>
                                <input type="text" class="form-control" name="process_title" id="edit_process_title"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Description</label>
                            <textarea class="form-control" name="process_description" id="edit_process_description"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Image URL</label>
                            <input type="text" class="form-control" name="process_image" id="edit_process_image">
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
                        <button type="submit" class="btn btn-primary">Update Process Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Process Modal -->
    <div class="modal fade" id="deleteProcessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the process step "<span id="delete_process_title"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                    <input type="hidden" id="delete_process_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete Process Step</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
    <script src="assets/js/dataTables/processDataTables.js"></script>
</body>

</html> 