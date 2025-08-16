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
    require_once('ImageUploadHelper.php');

    spl_autoload_register(function ($class) {
        $classFile = $class . '.php';
        if (file_exists($classFile)) {
            require_once($classFile);
        } else {
            throw new Exception("Required class file not found: " . $class);
        }
    });

    $conn = Db::connect();
    $companyProjects = new CompanyProjects($conn);
    $imageHelper = new ImageUploadHelper();

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];



    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_projects'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companyProjects->getAllProjects($search, $start, $length, $order);
                $totalRecords = $companyProjects->getTotalProjects($search);
                
                $response = [
                    'status' => 1,
                    'message' => 'Projects retrieved successfully',
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
        } elseif (isset($_GET['get_active_projects'])) {
            try {
                $data = $companyProjects->getActiveProjects();
                $response = [
                    'status' => 1,
                    'message' => 'Active projects retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_project'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyProjects->getProjectById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Project retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Project not found',
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
        } elseif (isset($_GET['get_categories'])) {
            try {
                $data = $companyProjects->getProjectCategories();
                $response = [
                    'status' => 1,
                    'message' => 'Categories retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debug: Log the POST data
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            error_log("Action detected: " . $action);
            
        } else {
            // Fallback: Check if we have project_id for update or other indicators
            if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
                $action = 'update';
                error_log("Action inferred as update from project_id");
            } else {
                $action = 'create';
                error_log("Action inferred as create (no project_id)");
            }
        }
        
        if (isset($action)) {
            switch ($action) {
                case 'create':
                    try {
                        // Debug: Log the specific fields we're looking for
                        error_log("project_title: " . ($_POST['project_title'] ?? 'NOT SET'));
                        error_log("project_description: " . ($_POST['project_description'] ?? 'NOT SET'));
                        error_log("project_owner: " . ($_POST['project_owner'] ?? 'NOT SET'));
                        error_log("project_location: " . ($_POST['project_location'] ?? 'NOT SET'));
                        
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Process uploaded files
                        $postData = $_POST;
                        
                        // Handle project_image1 (only if file is uploaded)
                        if (isset($_FILES['project_image1']) && $_FILES['project_image1']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image1'];
                            $nextProjectNumber = $companyProjects->getNextProjectNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'project', $nextProjectNumber);
                            
                            if ($result['success']) {
                                $postData['project_image1'] = $result['filename'];
                                $compressionInfo1 = "Image 1: " . $result['message'];
                            } else {
                                throw new Exception('Image 1: ' . $result['message']);
                            }
                        }
                        
                        // Handle project_image2 (only if file is uploaded)
                        if (isset($_FILES['project_image2']) && $_FILES['project_image2']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image2'];
                            $nextProjectNumber2 = $companyProjects->getNextProjectNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'project', $nextProjectNumber2);
                            
                            if ($result['success']) {
                                $postData['project_image2'] = $result['filename'];
                                $compressionInfo2 = "Image 2: " . $result['message'];
                            } else {
                                throw new Exception('Image 2: ' . $result['message']);
                            }
                        }

                        $projectId = $companyProjects->createProject($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Project created successfully';
                        if (isset($compressionInfo1)) {
                            $successMessage .= '. ' . $compressionInfo1;
                        }
                        if (isset($compressionInfo2)) {
                            $successMessage .= '. ' . $compressionInfo2;
                        }
                        
                        $response = [
                            'status' => 1,
                            'message' => $successMessage,
                            'data' => ['project_id' => $projectId]
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
                        // Debug: Log the specific fields we're looking for
                        error_log("project_title: " . ($_POST['project_title'] ?? 'NOT SET'));
                        error_log("project_description: " . ($_POST['project_description'] ?? 'NOT SET'));
                        error_log("project_owner: " . ($_POST['project_owner'] ?? 'NOT SET'));
                        error_log("project_location: " . ($_POST['project_location'] ?? 'NOT SET'));
                        
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Process uploaded files
                        $postData = $_POST;
                        $projectId = (int)$_POST['project_id'];
                        
                        // Get current project data to preserve existing images
                        $currentProject = $companyProjects->getProjectById($projectId);
                        
                        // Handle project_image1 (only if new file is uploaded)
                        if (isset($_FILES['project_image1']) && $_FILES['project_image1']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image1'];
                            $nextProjectNumber = $companyProjects->getNextProjectNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'project', $nextProjectNumber, $currentProject['ProjectImage1']);
                            
                            if ($result['success']) {
                                $postData['project_image1'] = $result['filename'];
                                $compressionInfo1 = "Image 1: " . $result['message'];
                            } else {
                                throw new Exception('Image 1: ' . $result['message']);
                            }
                        } else {
                            // Keep existing image1 if no new file uploaded
                            $postData['project_image1'] = $currentProject['ProjectImage1'] ?? '';
                        }
                        
                        // Handle project_image2 (only if new file is uploaded)
                        if (isset($_FILES['project_image2']) && $_FILES['project_image2']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image2'];
                            $nextProjectNumber2 = $companyProjects->getNextProjectNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'project', $nextProjectNumber2, $currentProject['ProjectImage2']);
                            
                            if ($result['success']) {
                                $postData['project_image2'] = $result['filename'];
                                $compressionInfo2 = "Image 2: " . $result['message'];
                            } else {
                                throw new Exception('Image 2: ' . $result['message']);
                            }
                        } else {
                            // Keep existing image2 if no new file uploaded
                            $postData['project_image2'] = $currentProject['ProjectImage2'] ?? '';
                        }

                        $companyProjects->updateProject($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Project updated successfully';
                        if (isset($compressionInfo1)) {
                            $successMessage .= '. ' . $compressionInfo1;
                        }
                        if (isset($compressionInfo2)) {
                            $successMessage .= '. ' . $compressionInfo2;
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
                        $id = $_POST['project_id'] ?? 0;
                        
                        // Get project data to delete associated images
                        $project = $companyProjects->getProjectById($id);
                        if ($project) {
                            $uploadDir = '../../assets/img/';
                            
                            // Delete image1 file if it exists
                            if (!empty($project['ProjectImage1'])) {
                                $image1File = $uploadDir . $project['ProjectImage1'];
                                if (file_exists($image1File)) {
                                    unlink($image1File);
                                }
                            }
                            
                            // Delete image2 file if it exists
                            if (!empty($project['ProjectImage2'])) {
                                $image2File = $uploadDir . $project['ProjectImage2'];
                                if (file_exists($image2File)) {
                                    unlink($image2File);
                                }
                            }
                        }
                        
                        $companyProjects->deleteProject($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Project deleted successfully',
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
                        'message' => 'Invalid action: ' . $action,
                        'data' => null
                    ];
                    break;
            }
        } else {
            $response = [
                'status' => 0,
                'message' => 'No action specified',
                'data' => null
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
?> 