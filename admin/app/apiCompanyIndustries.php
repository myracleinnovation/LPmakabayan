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
    $companyIndustries = new CompanyIndustries($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_industries'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companyIndustries->getAllIndustries($search, $start, $length, $order);
                $totalRecords = $companyIndustries->getTotalIndustries($search);
                
                $response = [
                    'status' => 1,
                    'message' => 'Industries retrieved successfully',
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
        } elseif (isset($_GET['get_active_industries'])) {
            try {
                $data = $companyIndustries->getActiveIndustries();
                $response = [
                    'status' => 1,
                    'message' => 'Active industries retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_industry'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyIndustries->getIndustryById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Industry retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Industry not found',
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
                        
                        // Handle industry_image (only if file is uploaded)
                        if (isset($_FILES['industry_image']) && $_FILES['industry_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['industry_image'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                $nextIndustryNumber = $companyIndustries->getNextIndustryNumber();
                                $filename = 'industry' . $nextIndustryNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['industry_image'] = $filename;
                                }
                            }
                        }

                        $industryId = $companyIndustries->createIndustry($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Industry created successfully',
                            'data' => ['industry_id' => $industryId]
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
                        $industryId = (int)$_POST['industry_id'];
                        
                        // Get current industry data to preserve existing image
                        $currentIndustry = $companyIndustries->getIndustryById($industryId);
                        
                        // Handle industry_image (only if new file is uploaded)
                        if (isset($_FILES['industry_image']) && $_FILES['industry_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['industry_image'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old industry image file if it exists
                                if (!empty($currentIndustry['IndustryImage'])) {
                                    $oldFile = $uploadDir . $currentIndustry['IndustryImage'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextIndustryNumber = $companyIndustries->getNextIndustryNumber();
                                $filename = 'industry' . $nextIndustryNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['industry_image'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing image if no new file uploaded
                            $postData['industry_image'] = $currentIndustry['IndustryImage'] ?? '';
                        }

                        $companyIndustries->updateIndustry($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Industry updated successfully',
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
                        $id = $_POST['industry_id'] ?? 0;
                        
                        // Get industry data to delete associated image
                        $industry = $companyIndustries->getIndustryById($id);
                        if ($industry) {
                            $uploadDir = '../../assets/img/';
                            
                            // Delete industry image file if it exists
                            if (!empty($industry['IndustryImage'])) {
                                $imageFile = $uploadDir . $industry['IndustryImage'];
                                if (file_exists($imageFile)) {
                                    unlink($imageFile);
                                }
                            }
                        }
                        
                        $companyIndustries->deleteIndustry($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Industry deleted successfully',
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