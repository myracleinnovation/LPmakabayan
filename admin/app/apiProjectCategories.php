<?php
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
                        
                        // Handle category_image (only if file is uploaded)
                        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['category_image'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                $nextCategoryNumber = $projectCategories->getNextCategoryNumber();
                                $filename = 'category' . $nextCategoryNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['category_image'] = $filename;
                                }
                            }
                        }

                        $categoryId = $projectCategories->createCategory($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Project category created successfully',
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
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old category image file if it exists
                                if (!empty($currentCategory['CategoryImage'])) {
                                    $oldFile = $uploadDir . $currentCategory['CategoryImage'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextCategoryNumber = $projectCategories->getNextCategoryNumber();
                                $filename = 'category' . $nextCategoryNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['category_image'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing image if no new file uploaded
                            $postData['category_image'] = $currentCategory['CategoryImage'] ?? '';
                        }

                        $projectCategories->updateCategory($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Project category updated successfully',
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