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
    $companySpecialties = new CompanySpecialties($conn);
    $imageHelper = new ImageUploadHelper();

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];



    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_specialties'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companySpecialties->getAllSpecialties($search, $start, $length, $order);
                $totalRecords = $companySpecialties->getTotalSpecialties($search);
                
                $response = [
                    'status' => 1,
                    'message' => 'Specialties retrieved successfully',
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
        } elseif (isset($_GET['get_active_specialties'])) {
            try {
                $data = $companySpecialties->getActiveSpecialties();
                $response = [
                    'status' => 1,
                    'message' => 'Active specialties retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_specialty'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companySpecialties->getSpecialtyById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Specialty retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Specialty not found',
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
                        
                        // Handle specialty_image (only if file is uploaded)
                        if (isset($_FILES['specialty_image']) && $_FILES['specialty_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['specialty_image'];
                            $nextSpecialtyNumber = $companySpecialties->getNextSpecialtyNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'specialty', $nextSpecialtyNumber);
                            
                            if ($result['success']) {
                                $postData['specialty_image'] = $result['filename'];
                                $compressionInfo = "Specialty image: " . $result['message'];
                            } else {
                                throw new Exception('Specialty image: ' . $result['message']);
                            }
                        }

                        $specialtyId = $companySpecialties->createSpecialty($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Specialty created successfully';
                        if (isset($compressionInfo)) {
                            $successMessage .= '. ' . $compressionInfo;
                        }
                        
                        $response = [
                            'status' => 1,
                            'message' => $successMessage,
                            'data' => ['specialty_id' => $specialtyId]
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
                        $specialtyId = (int)$_POST['specialty_id'];
                        
                        // Get current specialty data to preserve existing image
                        $currentSpecialty = $companySpecialties->getSpecialtyById($specialtyId);
                        
                        // Handle specialty_image (only if new file is uploaded)
                        if (isset($_FILES['specialty_image']) && $_FILES['specialty_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['specialty_image'];
                            $nextSpecialtyNumber = $companySpecialties->getNextSpecialtyNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'specialty', $nextSpecialtyNumber, $currentSpecialty['SpecialtyImage']);
                            
                            if ($result['success']) {
                                $postData['specialty_image'] = $result['filename'];
                                $compressionInfo = "Specialty image: " . $result['message'];
                            } else {
                                throw new Exception('Specialty image: ' . $result['message']);
                            }
                        } else {
                            // Keep existing image if no new file uploaded
                            $postData['specialty_image'] = $currentSpecialty['SpecialtyImage'] ?? '';
                        }

                        $companySpecialties->updateSpecialty($postData);
                        
                        // Build success message with compression info
                        $successMessage = 'Specialty updated successfully';
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
                        $id = $_POST['specialty_id'] ?? 0;
                        
                        // Get specialty data to delete associated image
                        $specialty = $companySpecialties->getSpecialtyById($id);
                        if ($specialty) {
                            $uploadDir = '../../assets/img/';
                            
                            // Delete specialty image file if it exists
                            if (!empty($specialty['SpecialtyImage'])) {
                                $imageFile = $uploadDir . $specialty['SpecialtyImage'];
                                if (file_exists($imageFile)) {
                                    unlink($imageFile);
                                }
                            }
                        }
                        
                        $companySpecialties->deleteSpecialty($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Specialty deleted successfully',
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