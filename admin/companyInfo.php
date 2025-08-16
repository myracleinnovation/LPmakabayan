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
                            <h5 class="card-title">Company Details</h5>
                        </div>
                        <div class="card-body">
                            <form id="companyInfoForm">
                                <input type="hidden" name="company_id" value="<?php echo $Company_Info['IdCompany'] ?? 1; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Company Name</label>
                                        </div>
                                        <?php
                                            $inputConfig = [
                                                'id' => 'companyName',
                                                'name' => 'company_name',
                                                'value' => $Company_Info['CompanyName'] ?? '',
                                                'class' => 'form-control shadow-none'
                                            ];
                                            include '../components/reusable/input.php'; 
                                        ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Tagline</label>
                                        </div>
                                        <?php
                                            $inputConfig = [
                                                'id' => 'tagline',
                                                'name' => 'tagline',
                                                'value' => $Company_Info['Tagline'] ?? '',
                                                'class' => 'form-control shadow-none'
                                            ];
                                            include '../components/reusable/input.php'; 
                                        ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Company Description</label>
                                    </div>
                                    <?php
                                        $textareaConfig = [
                                            'id' => 'description',
                                            'name' => 'description',
                                            'value' => $Company_Info['Description'] ?? '',
                                            'class' => 'form-control shadow-none'
                                        ];
                                        include '../components/reusable/textarea.php'; 
                                    ?>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Mission</label>
                                    </div>
                                    <?php
                                        $textareaConfig = [
                                            'id' => 'mission',
                                            'name' => 'mission',
                                            'value' => $Company_Info['Mission'] ?? '',
                                            'class' => 'form-control shadow-none'
                                        ];
                                        include '../components/reusable/textarea.php'; 
                                    ?>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Vision</label>
                                    </div>
                                    <?php
                                        $textareaConfig = [
                                            'id' => 'vision',
                                            'name' => 'vision',
                                            'value' => $Company_Info['Vision'] ?? '',
                                            'class' => 'form-control shadow-none'
                                        ];
                                        include '../components/reusable/textarea.php'; 
                                    ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">About Image</label>
                                        </div>
                                        <input type="file" class="form-control shadow-none" name="about_image" accept="image/*">
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
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Logo Image</label>
                                        </div>
                                        <input type="file" class="form-control shadow-none" name="logo_image" accept="image/*">
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
                                        Update
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
                            <h5 class="card-title">Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-end mb-3 mt-3">
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addContactModal">
                                    Add Contact
                                </button>
                            </div>

                            <!-- Search Section -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <?php
                                        $searchConfig = [
                                            'id' => 'contactsCustomSearch',
                                            'placeholder' => 'Search contacts...',
                                            'dataTarget' => 'contactsTable',
                                            'minLength' => 2,
                                            'delay' => 300,
                                            'showClear' => true
                                        ];
                                        include '../components/reusable/search.php'; 
                                    ?>
                                </div>
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
                    <h5 class="modal-title">Add Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="contactForm">
                    <div class="modal-body">
                        <input type="hidden" id="contactId" name="contact_id">

                        <div class="mb-3">
                            <label class="form-label">Contact Type *</label>
                            <?php
                                $selectConfig = [
                                    'id' => 'contactType',
                                    'name' => 'contact_type',
                                    'options' => [
                                        'email' => 'Email',
                                        'phone' => 'Phone',
                                        'address' => 'Address',
                                    ],
                                    'value' => 'phone',
                                    'class' => 'form-select shadow-none'
                                ];
                                include '../components/reusable/select.php'; 
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Label *</label>
                            <?php
                                $inputConfig = [
                                    'id' => 'contactLabel',
                                    'name' => 'contact_label',
                                    'class' => 'form-control shadow-none'
                                ];
                                include '../components/reusable/input.php'; 
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Value *</label>
                            <?php
                                $inputConfig = [
                                    'id' => 'contactValue',
                                    'name' => 'contact_value',
                                    'class' => 'form-control shadow-none'
                                ];
                                include '../components/reusable/input.php'; 
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Icon</label>
                            <?php
                                $inputConfig = [
                                    'id' => 'contactIcon',
                                    'name' => 'contact_icon',
                                    'class' => 'form-control shadow-none'
                                ];
                                include '../components/reusable/input.php'; 
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <?php
                                $inputConfig = [
                                    'id' => 'displayOrder',
                                    'name' => 'display_order',
                                    'class' => 'form-control shadow-none'
                                ];
                                include '../components/reusable/input.php'; 
                            ?>
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
                                    'class' => 'form-select shadow-none'
                                ];
                                include '../components/reusable/select.php'; 
                            ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveContactBtn">Add</button>
                        <button type="button" class="btn btn-primary" id="updateContactBtn"
                            style="display: none;">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>
