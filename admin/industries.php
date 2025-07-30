<?php
    session_start();
    include 'components/sessionCheck.php';
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

                            <div class="table-responsive">
                                <table id="industriesTable" class="table table-hover">
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
                                        <!-- Data will be loaded by DataTables -->
                                    </tbody>
                                </table>
                            </div>
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
                    <h5 class="modal-title"><i class="bi bi-plus me-2"></i>Add New Industry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addIndustryForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Industry Name *</label>
                                <input type="text" class="form-control" name="industry_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Description</label>
                            <textarea class="form-control" name="industry_description" rows="4"
                                placeholder="Describe the industry and its characteristics..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industry Image URL</label>
                            <input type="text" class="form-control" name="industry_image"
                                placeholder="Enter the URL of the industry image">
                            <small class="text-muted">Example: assets/img/industry1.png</small>
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Industry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editIndustryForm">
                    <div class="modal-body">
                        <input type="hidden" name="industry_id" id="edit_industry_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Industry Name *</label>
                                <input type="text" class="form-control" name="industry_name" id="edit_industry_name"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
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

    <!-- Delete Industry Modal -->
    <div class="modal fade" id="deleteIndustryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the industry "<span id="delete_industry_name"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                    <input type="hidden" id="delete_industry_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete Industry</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>