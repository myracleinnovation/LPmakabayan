<?php
session_start();
include 'components/sessionCheck.php';
require_once '../app/Db.php';
require_once 'app/ImageProcessor.php';

$admin_username = $_SESSION['admin_username'];
$admin_id = $_SESSION['admin_id'];

// Handle form submissions
$message = '';
$messageType = '';

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'process_single':
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imageProcessor = new ImageProcessor(1920, 1080, 85);
                    $result = $imageProcessor->processImage($_FILES['image'], '../assets/img/', 'processed_' . time());

                    if ($result['success']) {
                        $message = $result['message'];
                        $messageType = 'success';
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Please select a valid image file.';
                    $messageType = 'danger';
                }
                break;

            case 'process_batch':
                try {
                    $sourceDir = '../assets/img/';
                    $backupDir = '../assets/img/backup/';

                    if (!is_dir($sourceDir)) {
                        throw new Exception('Source directory does not exist: ' . $sourceDir);
                    }

                    if (!is_dir($backupDir)) {
                        if (!mkdir($backupDir, 0755, true)) {
                            throw new Exception('Failed to create backup directory: ' . $backupDir);
                        }
                    }

                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $files = scandir($sourceDir);

                    if ($files === false) {
                        throw new Exception('Failed to read source directory: ' . $sourceDir);
                    }

                    $processedCount = 0;
                    $errorCount = 0;
                    $totalSizeSaved = 0;

                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }

                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (!in_array($extension, $allowedExtensions)) {
                            continue;
                        }

                        try {
                            // Create backup
                            copy($sourceDir . $file, $backupDir . $file);

                            $originalSize = filesize($sourceDir . $file);

                            $tempFile = [
                                'name' => $file,
                                'type' => mime_content_type($sourceDir . $file),
                                'tmp_name' => $sourceDir . $file,
                                'error' => UPLOAD_ERR_OK,
                                'size' => $originalSize,
                            ];

                            $imageProcessor = new ImageProcessor(1920, 1080, 85);
                            $result = $imageProcessor->processImage($tempFile, $sourceDir, pathinfo($file, PATHINFO_FILENAME));

                            if ($result['success']) {
                                $processedCount++;
                                $totalSizeSaved += $originalSize - $result['processed_size'];
                            } else {
                                $errorCount++;
                            }
                        } catch (Exception $e) {
                            $errorCount++;
                        }
                    }

                    $response = [
                        'status' => $errorCount > 0 ? 'warning' : 'success',
                        'message' => "Batch processing completed. Processed: {$processedCount}, Errors: {$errorCount}, Total saved: " . formatBytes($totalSizeSaved),
                    ];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                    break;
                } catch (Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Batch processing failed: ' . $e->getMessage(),
                    ];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
        }
    }
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

// Get image statistics
$imgDir = '../assets/img/';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$totalImages = 0;
$totalSize = 0;
$imageList = [];

if (is_dir($imgDir)) {
    $files = scandir($imgDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, $allowedExtensions)) {
            $filepath = $imgDir . $file;
            $size = filesize($filepath);
            $imageInfo = getimagesize($filepath);

            $totalImages++;
            $totalSize += $size;

            $imageList[] = [
                'name' => $file,
                'size' => $size,
                'dimensions' => $imageInfo ? $imageInfo[0] . 'x' . $imageInfo[1] : 'Unknown',
                'type' => $extension,
            ];
        }
    }
}
?>

<?php if (!$isAjax): ?>

<body>
    <?php include 'components/header.php'; ?>
    <?php include 'components/topNav.php'; ?>
    <?php include 'components/sideNav.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Image Processor</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Image Processor</li>
                </ol>
            </nav>
        </div>

        <!-- Alert Container for AJAX responses -->
        <div id="alert-container"></div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Image Statistics</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-images"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $totalImages ?></h6>
                                            <span class="text-muted small pt-2">Total Images</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-hdd"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= formatBytes($totalSize) ?></h6>
                                            <span class="text-muted small pt-2">Total Size</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-calculator"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $totalImages > 0 ? formatBytes($totalSize / $totalImages) : '0 B' ?>
                                            </h6>
                                            <span class="text-muted small pt-2">Average Size</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Batch Processing</h5>
                            <p class="text-muted">Process all existing images in the assets/img folder. Original files
                                will be backed up.</p>
                            <button type="button" class="btn btn-warning" id="batchProcessBtn">
                                <i class="bi bi-gear me-1"></i>Process All Images
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Image List</h5>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <?php
                                        $searchConfig = [
                                            'id' => 'imageCustomSearch',
                                            'placeholder' => 'Search images...',
                                            'dataTarget' => 'imageListTable',
                                            'minLength' => 2,
                                            'delay' => 300,
                                            'showClear' => true
                                        ];
                                        include '../components/reusable/search.php'; 
                                    ?>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="imageListTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Size</th>
                                            <th>Dimensions</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Image Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editImageForm">
                    <div class="modal-body">
                        <input type="hidden" id="editOldImageName">
                        <div class="mb-3">
                            <label class="form-label">Image Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control shadow-none" id="editImageName" required>
                                <span class="input-group-text">.<span id="editImageExtension"></span></span>
                            </div>
                            <small class="text-muted">Use only letters, numbers, dots, underscores, and hyphens</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="editImageBtn">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Batch processing with AJAX and toastr
            $('#batchProcessBtn').on('click', function() {
                if (confirm('This will process all images. Are you sure?')) {
                    const btn = $(this);
                    const originalText = btn.html();

                    // Show processing state
                    btn.prop('disabled', true).html(
                        '<i class="bi bi-hourglass-split me-1"></i>Processing...');

                    $.ajax({
                        url: 'imageProcessor.php',
                        type: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: {
                            action: 'process_batch'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                            } else {
                                toastr.warning(response.message);
                            }

                            // Reload the image list table if it exists
                            if (typeof imageDataTable !== 'undefined') {
                                imageDataTable.ajax.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Batch processing error:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);

                            let errorMessage = 'An error occurred during batch processing.';
                            if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    errorMessage = response.message || errorMessage;
                                } catch (e) {
                                    errorMessage = xhr.responseText.substring(0, 100) + '...';
                                }
                            }

                            toastr.error(errorMessage);
                        },
                        complete: function() {
                            // Restore button state
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });
    </script>
</body>
<?php endif; ?>
