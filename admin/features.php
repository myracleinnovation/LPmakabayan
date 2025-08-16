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
            <h1>Features</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Features</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Features</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                                Add Features
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control shadow-none" id="featuresCustomSearch"
                                            placeholder="Search features...">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="featuresTable" class="table table-hover features_table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Feature Title</th>
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

    <!-- Add Feature Modal -->
    <div class="modal fade" id="addFeatureModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Features</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addFeatureForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Feature Title *</label>
                                <input type="text" class="form-control" name="feature_title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feature Description</label>
                            <textarea class="form-control" name="feature_description" rows="4"
                                placeholder="Describe the feature and its benefits..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feature Image</label>
                            <input type="file" class="form-control" name="feature_image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
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
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Feature Modal -->
    <div class="modal fade" id="editFeatureModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editFeatureForm">
                    <div class="modal-body">
                        <input type="hidden" name="feature_id" id="edit_feature_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Feature Title *</label>
                                <input type="text" class="form-control" name="feature_title" id="edit_feature_title"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order"
                                    id="edit_display_order">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feature Description</label>
                            <textarea class="form-control" name="feature_description" id="edit_feature_description" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feature Image</label>
                            <input type="file" class="form-control" name="feature_image" id="edit_feature_image"
                                accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                            <div id="current_feature_image_preview" class="mt-2"></div>
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
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>
