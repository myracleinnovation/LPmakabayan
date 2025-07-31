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
    $companyInfo = new CompanyInfo($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_Company_Info'])) {
            try {
                $data = $companyInfo->getCompanyInfo();
                $response = [
                    'status' => 1,
                    'message' => 'Company information retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => null
                ];
            }
        } elseif (isset($_GET['get_Company_Info_by_id'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyInfo->getCompanyInfoById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Company information retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Company information not found',
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
                case 'update_company':
                    try {
                        // Handle file uploads
                        $uploadDir = '../../assets/img/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Get current company info to preserve existing images
                        $currentCompany = $companyInfo->getCompanyInfo();

                        // Process uploaded files
                        $postData = $_POST;
                        
                        // Handle about_image (only if file is uploaded)
                        if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['about_image'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old about image file if it exists
                                if (!empty($currentCompany['AboutImage'])) {
                                    $oldFile = $uploadDir . $currentCompany['AboutImage'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextAboutNumber = $companyInfo->getNextAboutNumber();
                                $filename = 'about' . $nextAboutNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['about_image'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing about image if no new file uploaded
                            $postData['about_image'] = $currentCompany['AboutImage'] ?? '';
                        }
                        
                        // Handle logo_image (only if file is uploaded)
                        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['logo_image'];
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($extension, $allowedExtensions)) {
                                // Delete old logo image file if it exists
                                if (!empty($currentCompany['LogoImage'])) {
                                    $oldFile = $uploadDir . $currentCompany['LogoImage'];
                                    if (file_exists($oldFile)) {
                                        unlink($oldFile);
                                    }
                                }
                                
                                $nextLogoNumber = $companyInfo->getNextLogoNumber();
                                $filename = 'logo' . $nextLogoNumber . '.' . $extension;
                                $filepath = $uploadDir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $postData['logo_image'] = $filename;
                                }
                            }
                        } else {
                            // Keep existing logo image if no new file uploaded
                            $postData['logo_image'] = $currentCompany['LogoImage'] ?? '';
                        }

                        $company_id = $_POST['company_id'] ?? 1;
                        $company_name = trim($_POST['company_name']);

                        if (empty($company_name)) {
                            throw new Exception('Company name is required');
                        }

                        $companyInfo->updateCompanyInfo($postData);
                        $response = [
                            'status' => 1,
                            'message' => 'Company information updated successfully!',
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
                        'message' => 'Invalid action: ' . $_POST['action'],
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