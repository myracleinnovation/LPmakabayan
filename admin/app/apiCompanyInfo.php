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
    $companyInfo = new CompanyInfo($conn);
    $imageHelper = new ImageUploadHelper();

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
                            $nextAboutNumber = $companyInfo->getNextAboutNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'about', $nextAboutNumber, $currentCompany['AboutImage']);
                            
                            if ($result['success']) {
                                $postData['about_image'] = $result['filename'];
                                // Add compression info to response message
                                $compressionInfo = "About image: " . $result['message'];
                            } else {
                                throw new Exception('About image: ' . $result['message']);
                            }
                        } else {
                            // Keep existing about image if no new file uploaded
                            $postData['about_image'] = $currentCompany['AboutImage'] ?? '';
                        }
                        
                        // Handle logo_image (only if file is uploaded)
                        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['logo_image'];
                            $nextLogoNumber = $companyInfo->getNextLogoNumber();
                            
                            $result = $imageHelper->processAndUpload($file, 'logo', $nextLogoNumber, $currentCompany['LogoImage']);
                            
                            if ($result['success']) {
                                $postData['logo_image'] = $result['filename'];
                                // Add compression info to response message
                                $logoCompressionInfo = "Logo image: " . $result['message'];
                            } else {
                                throw new Exception('Logo image: ' . $result['message']);
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
                        
                        // Build success message with compression info
                        $successMessage = 'Company information updated successfully!';
                        if (isset($compressionInfo)) {
                            $successMessage .= ' ' . $compressionInfo;
                        }
                        if (isset($logoCompressionInfo)) {
                            $successMessage .= ' ' . $logoCompressionInfo;
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