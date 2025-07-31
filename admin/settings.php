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
            <h1>Settings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="row">
                    <!-- Change Password -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="bi bi-key me-2"></i>Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password *</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">New Password *</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password *</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Accounts -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title"><i class="bi bi-people me-2"></i>Admin Accounts</h5>
                                <button class="btn  btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addAdminModal">
                                    <i class="bi bi-plus me-1"></i>Add Admin
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="adminsTable" class="table table-hover admins_table" data-current-admin="<?php echo $admin_id; ?>">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Status</th>
                                                <th>Created</th>
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
            </div>
        </section>
    </main>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addAdminForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
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
                        <button type="submit" class="btn btn-primary">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editAdminForm">
                    <div class="modal-body">
                        <input type="hidden" name="admin_id" id="edit_admin_id">

                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
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
                        <button type="submit" class="btn btn-primary">Update Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the admin account "<span id="delete_admin_username"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <form id="deleteAdminForm">
                    <input type="hidden" name="admin_id" id="delete_admin_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
    $(document).ready(function() {
        // Handle Change Password Form
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = $('input[name="current_password"]').val();
            const newPassword = $('input[name="new_password"]').val();
            const confirmPassword = $('input[name="confirm_password"]').val();
            
            if (newPassword !== confirmPassword) {
                toastr.error('New passwords do not match');
                return;
            }
            
            if (newPassword.length < 6) {
                toastr.error('Password must be at least 6 characters long');
                return;
            }
            
            $.ajax({
                url: 'app/apiAdminAccounts.php',
                type: 'POST',
                data: {
                    action: 'update_password',
                    admin_id: <?php echo $admin_id; ?>,
                    current_password: currentPassword,
                    new_password: newPassword
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#changePasswordForm')[0].reset();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while changing password');
                }
            });
        });

        // Handle Add Admin Form
        $('#addAdminForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'create',
                username: $('input[name="username"]').val(),
                password: $('input[name="password"]').val(),
                status: $('select[name="status"]').val()
            };
            
            $.ajax({
                url: 'app/apiAdminAccounts.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#addAdminForm')[0].reset();
                        $('#addAdminModal').modal('hide');
                        if (window.adminsDataTable) {
                            window.adminsDataTable.ajax.reload();
                        }
                    } else {
                        toastr.error(response.message || 'Error adding admin');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred while adding admin: ' + error);
                }
            });
        });

        // Handle Edit Admin Form
        $('#editAdminForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'update',
                admin_id: $('#edit_admin_id').val(),
                username: $('#edit_username').val(),
                status: $('#edit_status').val()
            };
            
            $.ajax({
                url: 'app/apiAdminAccounts.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {   
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#editAdminForm')[0].reset();
                        $('#editAdminModal').modal('hide');
                        if (window.adminsDataTable) {
                            window.adminsDataTable.ajax.reload();
                        }
                    } else {
                        toastr.error(response.message || 'Error updating admin');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Edit admin error:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    toastr.error('An error occurred while updating admin: ' + error);
                }
            });
        });

        // Handle Delete Admin Form
        $('#deleteAdminForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'delete',
                admin_id: $('#delete_admin_id').val()
            };
            
            $.ajax({
                url: 'app/apiAdminAccounts.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#deleteAdminModal').modal('hide');
                        if (window.adminsDataTable) {
                            window.adminsDataTable.ajax.reload();
                        }
                    } else {
                        toastr.error(response.message || 'Error deleting admin');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred while deleting admin: ' + error);
                }
            });
        });
    });
    </script>
</body>

</html>