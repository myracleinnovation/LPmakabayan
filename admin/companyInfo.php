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

try {
    $pdo = Db::connect();

    $stmt = $pdo->query('SELECT * FROM Company_Info WHERE Status = 1 LIMIT 1');
    $Company_Info = $stmt->fetch();
} catch (Exception $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}
?>

<body>
    <?php include 'components/topNav.php'; ?>
    <?php include 'components/sideNav.php'; ?>

    <!-- ======= Main ======= -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Company Information</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Company Information</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <!-- Alert Messages will be handled by toastr -->

                <!-- Company Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="bi bi-building me-2"></i>Company Details</h5>
                        </div>
                        <div class="card-body">
                            <form id="companyInfoForm">
                                <input type="hidden" name="company_id" value="<?php echo $Company_Info['IdCompany'] ?? 1; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" name="company_name"
                                            value="<?php echo htmlspecialchars($Company_Info['CompanyName'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tagline</label>
                                        <input type="text" class="form-control" name="tagline"
                                            value="<?php echo htmlspecialchars($Company_Info['Tagline'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Company Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($Company_Info['Description'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mission</label>
                                    <textarea class="form-control" name="mission" rows="4"><?php echo htmlspecialchars($Company_Info['Mission'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vision</label>
                                    <textarea class="form-control" name="vision" rows="4"><?php echo htmlspecialchars($Company_Info['Vision'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">About Image</label>
                                        <input type="file" class="form-control" name="about_image" accept="image/*">
                                        <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                                        <div id="current_about_image_preview" class="mt-2">
                                            <?php if (!empty($Company_Info['AboutImage'])): ?>
                                            <small class="text-muted">Current About Image:</small><br>
                                            <img src="../assets/img/<?php echo htmlspecialchars($Company_Info['AboutImage']); ?>" alt="Current About Image"
                                                style="max-width: 200px; max-height: 200px; object-fit: cover;"
                                                class="border rounded">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Logo Image</label>
                                        <input type="file" class="form-control" name="logo_image" accept="image/*">
                                        <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                                        <div id="current_logo_image_preview" class="mt-2">
                                            <?php if (!empty($Company_Info['LogoImage'])): ?>
                                            <small class="text-muted">Current Logo Image:</small><br>
                                            <img src="../assets/img/<?php echo htmlspecialchars($Company_Info['LogoImage']); ?>" alt="Current Logo Image"
                                                style="max-width: 200px; max-height: 200px; object-fit: cover;"
                                                class="border rounded">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary" id="updateCompanyBtn">
                                        <i class="bi bi-save me-2"></i>Update Company Information
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-end mb-3">
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addContactModal">
                                    <i class="bi bi-plus me-2"></i>Add Contact
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table id="contactsTable" class="table table-hover contacts_table">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Value</th>
                                            <th>Type</th>
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

    <!-- Add Contact Modal -->
    <div class="modal fade" id="addContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="contactForm">
                    <div class="modal-body">
                        <input type="hidden" id="contactId" name="contact_id">

                        <div class="mb-3">
                            <label class="form-label">Contact Type *</label>
                            <select class="form-select" id="contactType" name="contact_type" required>
                                <option value="">Select Type</option>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="address">Address</option>
                                <option value="social_media">Social Media</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Label *</label>
                            <input type="text" class="form-control" id="contactLabel" name="contact_label"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Value *</label>
                            <input type="text" class="form-control" id="contactValue" name="contact_value"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Icon</label>
                            <input type="text" class="form-control" id="contactIcon" name="contact_icon"
                                placeholder="bi-phone">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="displayOrder" name="display_order"
                                value="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveContactBtn">Add Contact</button>
                        <button type="button" class="btn btn-primary" id="updateContactBtn"
                            style="display: none;">Update Contact</button>
                        <button type="button" class="btn btn-secondary" id="resetContactForm">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>
