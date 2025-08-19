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
                <!-- Change Password -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form id="changePasswordForm">
                                <div class="mb-3">
                                    <label class="form-label">Current Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control shadow-none" id="current_password"
                                            name="current_password" placeholder="Enter current password..."
                                            autocomplete="current-password" required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="toggleCurrentPassword" style="border-left: 0;">
                                            <i class="bi bi-eye" id="currentPasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control shadow-none" id="new_password"
                                            name="new_password" placeholder="Enter new password..."
                                            autocomplete="new-password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword"
                                            style="border-left: 0;">
                                            <i class="bi bi-eye" id="newPasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control shadow-none" id="confirm_password"
                                            name="confirm_password" placeholder="Confirm new password..."
                                            autocomplete="new-password" required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="toggleConfirmPassword" style="border-left: 0;">
                                            <i class="bi bi-eye" id="confirmPasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary" id="changePasswordBtn" disabled>
                                        Change Password
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
                            <h5 class="card-title">Admin Accounts</h5>
                            <button class="btn btn-primary shadow-none" data-bs-toggle="modal"
                                data-bs-target="#addAdminModal">
                                Add Admin
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Search Section -->
                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control shadow-none" id="adminCustomSearch"
                                            placeholder="Search admin accounts..." aria-label="Search admin accounts">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="adminsTable" class="table table-hover admins_table"
                                    data-current-admin="<?php echo $admin_id; ?>">
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
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-none" id="username" name="username"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control shadow-none" id="password"
                                    name="password" placeholder="Enter password..." autocomplete="new-password"
                                    required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleAddPassword"
                                    style="border-left: 0;">
                                    <i class="bi bi-eye" id="addPasswordIcon"></i>
                                </button>
                            </div>
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
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-none" id="edit_username"
                                name="username" required>
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
    <script>
        $(document).ready(function() {
            // Password visibility toggle functionality
            function togglePasswordVisibility(inputId, iconId) {
                const input = $(inputId);
                const icon = $(iconId);

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            }

            // Bind password toggle events
            $('#toggleCurrentPassword').on('click', function() {
                togglePasswordVisibility('#current_password', '#currentPasswordIcon');
            });

            $('#toggleNewPassword').on('click', function() {
                togglePasswordVisibility('#new_password', '#newPasswordIcon');
            });

            $('#toggleConfirmPassword').on('click', function() {
                togglePasswordVisibility('#confirm_password', '#confirmPasswordIcon');
            });

            $('#toggleAddPassword').on('click', function() {
                togglePasswordVisibility('#password', '#addPasswordIcon');
            });

            // Real-time password validation
            function validatePasswordForm() {
                const currentPassword = $('#current_password').val().trim();
                const newPassword = $('#new_password').val().trim();
                const confirmPassword = $('#confirm_password').val().trim();
                const changePasswordBtn = $('#changePasswordBtn');

                // Check if all fields are filled
                const allFieldsFilled = currentPassword !== '' && newPassword !== '' && confirmPassword !== '';

                // Check if new password meets minimum length
                const passwordLengthValid = newPassword.length >= 6;

                // Check if passwords match
                const passwordsMatch = newPassword === confirmPassword;

                // Check if new password is different from current password
                const passwordDifferent = newPassword !== currentPassword;

                // Enable button only if all conditions are met
                if (allFieldsFilled && passwordLengthValid && passwordsMatch && passwordDifferent) {
                    changePasswordBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
                } else {
                    changePasswordBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
                }
            }



            // Bind validation events
            $('#current_password, #new_password, #confirm_password').on('input', function() {
                validatePasswordForm();
            });

            // Show toastr validation messages when user tries to submit with invalid data
            function showValidationToastr(currentPassword, newPassword, confirmPassword) {
                if (currentPassword === '') {
                    toastr.error('Current password is required');
                    return false;
                }

                if (newPassword === '') {
                    toastr.error('New password is required');
                    return false;
                }

                if (newPassword.length < 6) {
                    toastr.error('Password must be at least 6 characters long');
                    return false;
                }

                if (newPassword === currentPassword) {
                    toastr.error('New password must be different from current password');
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    toastr.error('New passwords do not match');
                    return false;
                }

                return true;
            }

            // Initial validation check
            validatePasswordForm();

            // Prevent clicking disabled button
            $('#changePasswordBtn').on('click', function(e) {
                if ($(this).prop('disabled')) {
                    e.preventDefault();
                    e.stopPropagation();
                    toastr.error('Please complete all required fields correctly before submitting');
                    return false;
                }
            });

            // Handle Change Password Form
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();

                // Check if button is disabled - if so, don't submit
                if ($('#changePasswordBtn').prop('disabled')) {
                    toastr.error('Please complete all required fields correctly before submitting');
                    return;
                }

                const currentPassword = $('input[name="current_password"]').val().trim();
                const newPassword = $('input[name="new_password"]').val().trim();
                const confirmPassword = $('input[name="confirm_password"]').val().trim();

                // Show toastr validation messages if validation fails
                if (!showValidationToastr(currentPassword, newPassword, confirmPassword)) {
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
                            // Reset button state
                            $('#changePasswordBtn').prop('disabled', true).removeClass(
                                'btn-primary').addClass('btn-secondary');
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
        });
    </script>
</body>

</html>
