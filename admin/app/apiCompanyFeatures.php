<?php
    // Increase upload limits for larger images
    ini_set('upload_max_filesize', '100M');
    ini_set('post_max_size', '200M');
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 600);
    
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    require_once('../../app/Db.php');

    spl_autoload_register(function ($class) {
        $classFile = $class . '.php';
        if (file_exists($classFile)) {
            require_once($classFile);
        } else {
            throw new Exception("Required class file not found: " . $class);
        }
    });

    $conn = Db::connect();
    $companyFeatures = new CompanyFeatures($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    // Function to validate and process image upload
    function processImageUpload($file, $uploadDir, $companyFeatures, $oldImage = null) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            throw new Exception($errorMessages[$file['error']] ?? 'Unknown upload error');
        }

        // Check file size (100MB limit)
        $maxFileSize = 100 * 1024 * 1024; // 100MB in bytes
        if ($file['size'] > $maxFileSize) {
            throw new Exception('File size exceeds 100MB limit');
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WebP');
        }

        // Validate file type using getimagesize
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('Invalid image file');
        }

        // Delete old image if it exists
        if (!empty($oldImage)) {
            $oldFile = $uploadDir . $oldImage;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Generate unique filename
        $nextFeatureNumber = $companyFeatures->getNextFeatureNumber();
        $filename = 'feature' . $nextFeatureNumber . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save uploaded file');
        }

        return $filename;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_features'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companyFeatures->getAllFeatures($search, $start, $length, $order);
                
                $response = [
                    'status' => 1,
                    'message' => 'Features retrieved successfully',
                    'data' => [
                        'data' => $data
                    ]
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => [
                        'data' => []
                    ]
                ];
            }
        } elseif (isset($_GET['get_active_features'])) {
            try {
                $data = $companyFeatures->getActiveFeatures();
                $response = [
                    'status' => 1,
                    'message' => 'Active features retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_feature'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyFeatures->getFeatureById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Feature retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Feature not found',
                        'data' => null
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => null
                ];
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    try {
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Process uploaded files
                        $postData = $_POST;
                        
                        // Handle feature_image (only if file is uploaded)
                        if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
                            $filename = processImageUpload($_FILES['feature_image'], $uploadDir, $companyFeatures);
                            $postData['feature_image'] = $filename;
                        }

                        $featureId = $companyFeatures->createFeature($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Feature created successfully',
                            'data' => ['feature_id' => $featureId]
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                case 'update':
                    try {
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Process uploaded files
                        $postData = $_POST;
                        $featureId = (int)$_POST['feature_id'];
                        
                        // Get current feature data to preserve existing image
                        $currentFeature = $companyFeatures->getFeatureById($featureId);
                        
                        // Handle feature_image (only if new file is uploaded)
                        if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
                            $filename = processImageUpload($_FILES['feature_image'], $uploadDir, $companyFeatures, $currentFeature['FeatureImage']);
                            $postData['feature_image'] = $filename;
                        } else {
                            // Keep existing image if no new file uploaded
                            $postData['feature_image'] = $currentFeature['FeatureImage'] ?? '';
                        }

                        $companyFeatures->updateFeature($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Feature updated successfully',
                            'data' => null
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                case 'delete':
                    try {
                        $id = $_POST['feature_id'] ?? 0;
                        
                        // Get feature data to delete associated image
                        $feature = $companyFeatures->getFeatureById($id);
                        if ($feature) {
                            $uploadDir = '../../assets/img/';
                            
                            // Delete feature image file if it exists
                            if (!empty($feature['FeatureImage'])) {
                                $imageFile = $uploadDir . $feature['FeatureImage'];
                                if (file_exists($imageFile)) {
                                    unlink($imageFile);
                                }
                            }
                        }
                        
                        $companyFeatures->deleteFeature($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Feature deleted successfully',
                            'data' => null
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                default:
                    $response = [
                        'status' => 0,
                        'message' => 'Invalid action',
                        'data' => null
                    ];
                    break;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
?> 