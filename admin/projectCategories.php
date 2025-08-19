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

// Autoloader for classes
spl_autoload_register(function ($class) {
    $classFile = 'app/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    } else {
        throw new Exception('Required class file not found: ' . $class);
    }
});

// Get total project categories count for display order dropdown
$pdo = Db::connect();
$projectCategories = new ProjectCategories($pdo);
$totalCategories = $projectCategories->getTotalCategories();
?>

<body>
    <?php include 'components/topNav.php'; ?>
    <?php include 'components/sideNav.php'; ?>

    <!-- ======= Main ======= -->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Project Categories</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Project Categories</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">All Project Categories</h5>
                            <button class="btn btn-primary shadow-none" data-bs-toggle="modal"
                                data-bs-target="#addCategoryModal">
                                Add Categories
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="alert-container"></div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control shadow-none"
                                            id="categoriesCustomSearch" placeholder="Search project categories..."
                                            aria-label="Search project categories">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="categoriesTable" class="table table-hover categories_table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Category Name</th>
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-none" id="categoryName"
                                    name="category_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <select class="form-select shadow-none" id="displayOrder" name="display_order">
                                    <option value="0">First (0)</option>
                                    <?php for($i = 1; $i <= $totalCategories + 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Description</label>
                            <textarea class="form-control shadow-none" id="categoryDescription" name="category_description" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control shadow-none" name="category_image"
                                accept="image/*" required>
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

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="category_id" id="edit_category_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-none" id="edit_category_name"
                                    name="category_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <select class="form-select shadow-none" id="edit_display_order" name="display_order">
                                    <option value="0">First (0)</option>
                                    <?php for($i = 1; $i <= $totalCategories + 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Description</label>
                            <textarea class="form-control shadow-none" id="edit_category_description" name="category_description"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" class="form-control shadow-none" name="category_image"
                                id="edit_category_image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                            <div id="current_category_image_preview" class="mt-2"></div>
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
