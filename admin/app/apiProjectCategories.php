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
    $projectCategories = new ProjectCategories($conn);

    // Simple image upload function to replace ImageUploadHelper
    function uploadImage($file, $uploadDir, $prefix, $nextNumber, $oldImage = null) {
        try {
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
            $filename = $prefix . $nextNumber . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }

            return [
                'success' => true,
                'filename' => $filename,
                'message' => 'Image uploaded successfully',
                'original_size' => $file['size'],
                'processed_size' => $file['size']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];



    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_categories'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $projectCategories->getAllCategories($search, $start, $length, $order);
                $totalRecords = $projectCategories->getTotalCategories($search);
                
                $response = [
                    'status' => 1,
                    'message' => 'Project categories retrieved successfully',
                    'data' => $data,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ];
            }
        } elseif (isset($_GET['get_active_categories'])) {
            try {
                $data = $projectCategories->getActiveCategories();
                $response = [
                    'status' => 1,
                    'message' => 'Active project categories retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_category'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $projectCategories->getCategoryById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Project category retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Project category not found',
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
                case 'get_categories':
                    try {
                        $search = $_POST['search'] ?? '';
                        $start = $_POST['start'] ?? 0;
                        $length = $_POST['length'] ?? 25;
                        $order = isset($_POST['order']) ? json_decode($_POST['order'], true) : [];
                        
                        $data = $projectCategories->getAllCategories($search, $start, $length, $order);
                        
                        $response = [
                            'status' => 1,
                            'message' => 'Project categories retrieved successfully',
                            'data' => $data
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => []
                        ];
                    }
                    break;

                case 'get_active_categories':
                    try {
                        $data = $projectCategories->getActiveCategories();
                        $response = [
                            'status' => 1,
                            'message' => 'Active project categories retrieved successfully',
                            'data' => $data
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => []
                        ];
                    }
                    break;

                case 'create':
                    try {
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Process uploaded files
                        $postData = $_POST;
                        
                        // Handle category_image (required field)
                        if (!isset($_FILES['category_image']) || $_FILES['category_image']['error'] !== UPLOAD_ERR_OK) {
                            throw new Exception('Category image is required');
                        }
                        
                        $file = $_FILES['category_image'];
                        $nextCategoryNumber = $projectCategories->getNextCategoryNumber();
                        
                        $result = uploadImage($file, $uploadDir, 'category', $nextCategoryNumber);
                        
                        if ($result['success']) {
                            $postData['category_image'] = $result['filename'];
                            $compressionInfo = "Category image: " . $result['message'];
                        } else {
                            throw new Exception('Category image: ' . $result['message']);
                        }

                        $categoryId = $projectCategories->createCategory($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Project category created successfully';
                        if (isset($compressionInfo)) {
                            $successMessage .= '. ' . $compressionInfo;
                        }
                        
                        $response = [
                            'status' => 1,
                            'message' => $successMessage,
                            'data' => ['category_id' => $categoryId]
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
                        $categoryId = (int)$_POST['category_id'];
                        
                        // Get current category data to preserve existing image
                        $currentCategory = $projectCategories->getCategoryById($categoryId);
                        
                        // Handle category_image (only if new file is uploaded)
                        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['category_image'];
                            $nextCategoryNumber = $projectCategories->getNextCategoryNumber();
                            
                            $result = uploadImage($file, $uploadDir, 'category', $nextCategoryNumber, $currentCategory['CategoryImage']);
                            
                            if ($result['success']) {
                                $postData['category_image'] = $result['filename'];
                                $compressionInfo = "Category image: " . $result['message'];
                            } else {
                                throw new Exception('Category image: ' . $result['message']);
                            }
                        } else {
                            // Keep existing image if no new file uploaded
                            $postData['category_image'] = $currentCategory['CategoryImage'] ?? '';
                        }

                        $projectCategories->updateCategory($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Project category updated successfully';
                        if (isset($compressionInfo)) {
                            $successMessage .= '. ' . $compressionInfo;
                        }
                        
                        $response = [
                            'status' => 1,
                            'message' => $successMessage,
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
                        $id = $_POST['category_id'] ?? 0;
                        
                        // Get category data to delete associated image
                        $category = $projectCategories->getCategoryById($id);
                        if ($category) {
                            $uploadDir = '../../assets/img/';
                            
                            // Delete category image file if it exists
                            if (!empty($category['CategoryImage'])) {
                                $imageFile = $uploadDir . $category['CategoryImage'];
                                if (file_exists($imageFile)) {
                                    unlink($imageFile);
                                }
                            }
                        }
                        
                        $projectCategories->deleteCategory($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Project category deleted successfully',
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