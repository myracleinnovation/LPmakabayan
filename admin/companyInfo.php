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

    $message = '';
    $message_type = '';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_company':
                    try {
                        $company_id = $_POST['company_id'] ?? 1;
                        $company_name = trim($_POST['company_name']);
                        $tagline = trim($_POST['tagline']);
                        $description = trim($_POST['description']);
                        $mission = trim($_POST['mission']);
                        $vision = trim($_POST['vision']);
                        $about_image = trim($_POST['about_image']);
                        $logo_image = trim($_POST['logo_image']);

                        if (empty($company_name)) {
                            throw new Exception('Company name is required');
                        }

                        $pdo = Db::connect();
                        
                        // Check if company info exists
                        $stmt = $pdo->prepare("SELECT IdCompany FROM Company_Info WHERE IdCompany = ?");
                        $stmt->execute([$company_id]);
                        $exists = $stmt->fetch();

                        if ($exists) {
                            // Update existing
                            $sql = "UPDATE Company_Info SET CompanyName = ?, Tagline = ?, Description = ?, Mission = ?, Vision = ?, AboutImage = ?, LogoImage = ?, UpdatedTimestamp = NOW() WHERE IdCompany = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$company_name, $tagline, $description, $mission, $vision, $about_image, $logo_image, $company_id]);
                        } else {
                            // Insert new
                            $sql = "INSERT INTO Company_Info (CompanyName, Tagline, Description, Mission, Vision, AboutImage, LogoImage, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$company_name, $tagline, $description, $mission, $vision, $about_image, $logo_image]);
                        }

                        $message = 'Company information updated successfully!';
                        $message_type = 'success';
                    } catch (Exception $e) {
                        $message = 'Error updating company information: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;

                case 'add_contact':
                    try {
                        $contact_type = trim($_POST['contact_type']);
                        $contact_label = trim($_POST['contact_label']);
                        $contact_value = trim($_POST['contact_value']);
                        $display_order = (int)($_POST['display_order'] ?? 0);
                        $status = (int)($_POST['status'] ?? 1);

                        if (empty($contact_type) || empty($contact_value)) {
                            throw new Exception('Contact type and value are required');
                        }

                        $pdo = Db::connect();
                        $sql = "INSERT INTO company_contact (ContactType, ContactValue, ContactLabel, ContactIcon, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$contact_type, $contact_value, $contact_label, 'bi-' . $contact_type, $display_order, $status]);

                        $message = 'Contact added successfully!';
                        $message_type = 'success';
                    } catch (Exception $e) {
                        $message = 'Error adding contact: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;

                case 'edit_contact':
                    try {
                        $contact_id = (int)$_POST['contact_id'];
                        $contact_type = trim($_POST['contact_type']);
                        $contact_label = trim($_POST['contact_label']);
                        $contact_value = trim($_POST['contact_value']);
                        $display_order = (int)($_POST['display_order'] ?? 0);
                        $status = (int)($_POST['status'] ?? 1);

                        if (empty($contact_type) || empty($contact_value)) {
                            throw new Exception('Contact type and value are required');
                        }

                        $pdo = Db::connect();
                        $sql = "UPDATE company_contact SET ContactType = ?, ContactValue = ?, ContactLabel = ?, ContactIcon = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdContact = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$contact_type, $contact_value, $contact_label, 'bi-' . $contact_type, $display_order, $status, $contact_id]);

                        $message = 'Contact updated successfully!';
                        $message_type = 'success';
                    } catch (Exception $e) {
                        $message = 'Error updating contact: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;

                case 'delete_contact':
                    try {
                        $contact_id = (int)$_POST['contact_id'];

                        $pdo = Db::connect();
                        $sql = "UPDATE company_contact SET Status = 0, UpdatedTimestamp = NOW() WHERE IdContact = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$contact_id]);

                        $message = 'Contact deleted successfully!';
                        $message_type = 'success';
                    } catch (Exception $e) {
                        $message = 'Error deleting contact: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;
            }
        }
    }

    try {
        $pdo = Db::connect();
        
        $stmt = $pdo->query("SELECT * FROM Company_Info WHERE Status = 1 LIMIT 1");
        $Company_Info = $stmt->fetch();
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }

    try {
        $stmt = $pdo->query("SELECT * FROM company_contact WHERE Status = 1 ORDER BY DisplayOrder ASC");
        $contacts = $stmt->fetchAll();
    } catch (Exception $e) {
        $contacts = [];
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
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="bi bi-building me-2"></i>Company Details</h5>
                        </div>
                        <div class="card-body">
                            <form id="companyInfoForm">
                                <input type="hidden" name="action" value="update_company">
                                <input type="hidden" name="company_id"
                                    value="<?php echo $Company_Info['IdCompany'] ?? 1; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" name="company_name"
                                            value="<?php echo htmlspecialchars($Company_Info['CompanyName'] ?? ''); ?>"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tagline</label>
                                        <input type="text" class="form-control" name="tagline"
                                            value="<?php echo htmlspecialchars($Company_Info['Tagline'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Company Description</label>
                                    <textarea class="form-control" name="description"
                                        rows="3"><?php echo htmlspecialchars($Company_Info['Description'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mission</label>
                                    <textarea class="form-control" name="mission"
                                        rows="4"><?php echo htmlspecialchars($Company_Info['Mission'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vision</label>
                                    <textarea class="form-control" name="vision"
                                        rows="4"><?php echo htmlspecialchars($Company_Info['Vision'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">About Image URL</label>
                                        <input type="text" class="form-control" name="about_image"
                                            value="<?php echo htmlspecialchars($Company_Info['AboutImage'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Logo Image URL</label>
                                        <input type="text" class="form-control" name="logo_image"
                                            value="<?php echo htmlspecialchars($Company_Info['LogoImage'] ?? ''); ?>">
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

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-end mb-3">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
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
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_contact">

                        <div class="mb-3">
                            <label class="form-label">Contact Type *</label>
                            <select class="form-select" name="contact_type" required>
                                <option value="">Select Type</option>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="address">Address</option>
                                <option value="social_media">Social Media</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Label *</label>
                            <input type="text" class="form-control" name="contact_label" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Value *</label>
                            <input type="text" class="form-control" name="contact_value" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
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
                        <button type="submit" class="btn btn-primary">Add Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div class="modal fade" id="editContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_contact">
                        <input type="hidden" name="contact_id" id="edit_contact_id">

                        <div class="mb-3">
                            <label class="form-label">Contact Type *</label>
                            <select class="form-select" name="contact_type" id="edit_contact_type" required>
                                <option value="">Select Type</option>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="address">Address</option>
                                <option value="social_media">Social Media</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Label *</label>
                            <input type="text" class="form-control" name="contact_label" id="edit_contact_label"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Value *</label>
                            <input type="text" class="form-control" name="contact_value" id="edit_contact_value"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" id="edit_display_order">
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
                        <button type="submit" class="btn btn-primary">Update Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the contact "<span id="delete_contact_label"></span>"?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_contact">
                    <input type="hidden" name="contact_id" id="delete_contact_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>