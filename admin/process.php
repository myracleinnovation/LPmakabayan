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
                            <button class="btn btn-primary shadow-none" data-bs-toggle="modal"
                                data-bs-target="#addProcessModal">
                                Add Process
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <?php
                                    $searchConfig = [
                                        'id' => 'processCustomSearch',
                                        'placeholder' => 'Search process steps...',
                                        'dataTarget' => 'processTable',
                                        'minLength' => 2,
                                        'delay' => 300,
                                        'showClear' => true,
                                    ];
                                    
                                    include '../components/reusable/search.php';
                                    ?>
                                </div>
                            </div>

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
                    <h5 class="modal-title">Add Process Steps</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProcessForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Process Title *</label>
                                <?php
                                $inputConfig = [
                                    'id' => 'processTitle',
                                    'name' => 'process_title',
                                    'class' => 'form-control shadow-none',
                                ];
                                include '../components/reusable/input.php';
                                ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <?php
                                $inputConfig = [
                                    'id' => 'displayOrder',
                                    'name' => 'display_order',
                                    'class' => 'form-control shadow-none',
                                    'type' => 'number',
                                ];
                                include '../components/reusable/input.php';
                                ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Description</label>
                            <?php
                            $textareaConfig = [
                                'id' => 'processDescription',
                                'name' => 'process_description',
                                'class' => 'form-control shadow-none',
                            ];
                            include '../components/reusable/textarea.php';
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Image</label>
                            <input type="file" class="form-control shadow-none" name="process_image"
                                accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <?php
                            $selectConfig = [
                                'id' => 'status',
                                'name' => 'status',
                                'options' => [
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ],
                                'value' => '1',
                                'class' => 'form-select shadow-none',
                            ];
                            include '../components/reusable/select.php';
                            ?>
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

    <!-- Edit Process Modal -->
    <div class="modal fade" id="editProcessModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProcessForm">
                    <div class="modal-body">
                        <input type="hidden" name="process_id" id="edit_process_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Process Title *</label>
                                <?php
                                $inputConfig = [
                                    'id' => 'edit_process_title',
                                    'name' => 'process_title',
                                    'class' => 'form-control shadow-none',
                                ];
                                include '../components/reusable/input.php';
                                ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <?php
                                $inputConfig = [
                                    'id' => 'edit_display_order',
                                    'name' => 'display_order',
                                    'class' => 'form-control shadow-none',
                                    'type' => 'number',
                                ];
                                include '../components/reusable/input.php';
                                ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Description</label>
                            <?php
                            $textareaConfig = [
                                'id' => 'edit_process_description',
                                'name' => 'process_description',
                                'class' => 'form-control shadow-none',
                            ];
                            include '../components/reusable/textarea.php';
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Process Image</label>
                            <input type="file" class="form-control shadow-none" name="process_image"
                                id="edit_process_image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                            <div id="current_process_image_preview" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <?php
                            $selectConfig = [
                                'id' => 'edit_status',
                                'name' => 'status',
                                'options' => [
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ],
                                'value' => '1',
                                'class' => 'form-select shadow-none',
                            ];
                            include '../components/reusable/select.php';
                            ?>
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
