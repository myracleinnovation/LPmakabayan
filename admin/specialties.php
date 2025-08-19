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
                            <button class="btn btn-primary shadow-none" data-bs-toggle="modal"
                                data-bs-target="#addSpecialtyModal">
                                Add Specialties
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control shadow-none"
                                            id="specialtiesCustomSearch" placeholder="Search specialties..."
                                            aria-label="Search specialties">
                                    </div>
                                </div>
                            </div>

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

    <!-- Add Specialty Modal -->
    <div class="modal fade" id="addSpecialtyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Specialties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSpecialtyForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Specialty Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-none" id="specialtyName"
                                    name="specialty_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control shadow-none" id="displayOrder"
                                    name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Description</label>
                            <textarea class="form-control shadow-none" id="specialtyDescription" name="specialty_description" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Image</label>
                            <input type="file" class="form-control shadow-none" name="specialty_image"
                                accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select shadow-none" id="status" name="status">
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

    <!-- Edit Specialty Modal -->
    <div class="modal fade" id="editSpecialtyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Specialty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSpecialtyForm">
                    <div class="modal-body">
                        <input type="hidden" name="specialty_id" id="edit_specialty_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Specialty Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-none" id="edit_specialty_name"
                                    name="specialty_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control shadow-none" id="edit_display_order"
                                    name="display_order" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Description</label>
                            <textarea class="form-control shadow-none" id="edit_specialty_description" name="specialty_description"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialty Image</label>
                            <input type="file" class="form-control shadow-none" name="specialty_image"
                                id="edit_specialty_image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                            <div id="current_specialty_image_preview" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select shadow-none" id="edit_status" name="status">
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
