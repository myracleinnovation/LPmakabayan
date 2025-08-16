<?php
session_start();
include 'components/sessionCheck.php';
include 'components/header.php';
require_once '../app/Db.php';
require_once 'app/ImageProcessor.php';

$admin_username = $_SESSION['admin_username'];
$admin_id = $_SESSION['admin_id'];

// Handle form submissions
$message = '';
$messageType = '';

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
                $sourceDir = '../assets/img/';
                $backupDir = '../assets/img/backup/';

                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $files = scandir($sourceDir);
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

                $message = "Batch processing completed. Processed: {$processedCount}, Errors: {$errorCount}, Total saved: " . formatBytes($totalSizeSaved);
                $messageType = $errorCount > 0 ? 'warning' : 'success';
                break;
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

<body>
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
                <!-- Image Statistics -->
                <div class="col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Image Statistics</h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-images"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $totalImages ?></h6>
                                    <span class="text-muted small pt-2">Total Images</span>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <p class="mb-1"><strong>Total Size:</strong> <?= formatBytes($totalSize) ?></p>
                                <p class="mb-1"><strong>Average Size:</strong>
                                    <?= $totalImages > 0 ? formatBytes($totalSize / $totalImages) : '0 B' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Image Processing -->
                <div class="col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title">Process Single Image</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <form method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100">
                                <input type="hidden" name="action" value="process_single">
                                <div class="mb-3">
                                    <label class="form-label">Select Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" required>
                                    <small class="text-muted">Maximum dimensions: 1920x1080, Quality: 85%</small>
                                </div>
                                <div class="mt-auto">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-upload me-1"></i>Process Image
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Batch Processing -->
                <div class="col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title">Batch Processing</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="text-muted">Process all existing images in the assets/img folder. Original files
                                will be backed up.</p>
                            <div class="mt-auto">
                                <form method="POST"
                                    onsubmit="return confirm('This will process all images. Are you sure?')">
                                    <input type="hidden" name="action" value="process_batch">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-gear me-1"></i>Process All Images
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title">Processing Settings</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <label class="form-label">Max Width</label>
                                <input type="number" class="form-control" value="1920" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Max Height</label>
                                <input type="number" class="form-control" value="1080" readonly>
                            </div>
                            <div class="mt-auto">
                                <label class="form-label">Quality</label>
                                <input type="number" class="form-control" value="85" readonly>
                                <small class="text-muted">JPEG/WebP compression quality (1-100)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image List -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title">Image List</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Input -->
                            <div class="row mb-3 mt-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control shadow-none" id="imageCustomSearch"
                                            placeholder="Search images...">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="imageListTable" class="table table-hover">
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
                                <input type="text" class="form-control" id="editImageName" required>
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
</body>
