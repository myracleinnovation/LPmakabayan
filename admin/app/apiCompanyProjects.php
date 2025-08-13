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
    $companyProjects = new CompanyProjects($conn);

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
                        
                        // Handle project_image1 (only if file is uploaded)
                        if (isset($_FILES['project_image1']) && $_FILES['project_image1']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image1'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                $nextProjectNumber = $companyProjects->getNextProjectNumber();
                                $filename = 'project' . $nextProjectNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['project_image1'] = $filename;
                                }
                            }
                        }
                        
                        // Handle project_image2 (only if file is uploaded)
                        if (isset($_FILES['project_image2']) && $_FILES['project_image2']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image2'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                $nextProjectNumber2 = $companyProjects->getNextProjectNumber();
                                $filename = 'project' . $nextProjectNumber2 . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['project_image2'] = $filename;
                                }
                            }
                        }

                        // Add display settings to postData
                        $postData['ShowTitle'] = isset($_POST['show_project_title']) ? 1 : 0;
                        $postData['ShowOwner'] = isset($_POST['show_project_owner']) ? 1 : 0;
                        $postData['ShowLocation'] = isset($_POST['show_project_location']) ? 1 : 0;
                        $postData['ShowArea'] = isset($_POST['show_project_area']) ? 1 : 0;
                        $postData['ShowValue'] = isset($_POST['show_project_value']) ? 1 : 0;
                        $postData['ShowTurnoverDate'] = isset($_POST['show_turnover_date']) ? 1 : 0;
                        $postData['ShowCategory'] = isset($_POST['show_project_category']) ? 1 : 0;
                        $postData['ShowDescription'] = isset($_POST['show_project_description']) ? 1 : 0;
                        $postData['ShowImage1'] = isset($_POST['show_project_image1']) ? 1 : 0;
                        $postData['ShowImage2'] = isset($_POST['show_project_image2']) ? 1 : 0;

                        $projectId = $companyProjects->createProject($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Project created successfully',
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
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old image1 file if it exists
                                if (!empty($currentProject['ProjectImage1'])) {
                                    $oldFile = $uploadDir . $currentProject['ProjectImage1'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextProjectNumber = $companyProjects->getNextProjectNumber();
                                $filename = 'project' . $nextProjectNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['project_image1'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing image1 if no new file uploaded
                            $postData['project_image1'] = $currentProject['ProjectImage1'] ?? '';
                        }
                        
                        // Handle project_image2 (only if new file is uploaded)
                        if (isset($_FILES['project_image2']) && $_FILES['project_image2']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['project_image2'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old image2 file if it exists
                                if (!empty($currentProject['ProjectImage2'])) {
                                    $oldFile = $uploadDir . $currentProject['ProjectImage2'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextProjectNumber2 = $companyProjects->getNextProjectNumber();
                                $filename = 'project' . $nextProjectNumber2 . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['project_image2'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing image2 if no new file uploaded
                            $postData['project_image2'] = $currentProject['ProjectImage2'] ?? '';
                        }

                        // Add display settings to postData
                        $postData['ShowTitle'] = isset($_POST['show_project_title']) ? 1 : 0;
                        $postData['ShowOwner'] = isset($_POST['show_project_owner']) ? 1 : 0;
                        $postData['ShowLocation'] = isset($_POST['show_project_location']) ? 1 : 0;
                        $postData['ShowArea'] = isset($_POST['show_project_area']) ? 1 : 0;
                        $postData['ShowValue'] = isset($_POST['show_project_value']) ? 1 : 0;
                        $postData['ShowTurnoverDate'] = isset($_POST['show_turnover_date']) ? 1 : 0;
                        $postData['ShowCategory'] = isset($_POST['show_project_category']) ? 1 : 0;
                        $postData['ShowDescription'] = isset($_POST['show_project_description']) ? 1 : 0;
                        $postData['ShowImage1'] = isset($_POST['show_project_image1']) ? 1 : 0;
                        $postData['ShowImage2'] = isset($_POST['show_project_image2']) ? 1 : 0;

                        $companyProjects->updateProject($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Project updated successfully',
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