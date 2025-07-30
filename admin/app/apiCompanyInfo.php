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
        'success' => false,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_Company_Info'])) {
            try {
                $data = $companyInfo->getCompanyInfo();
                $response = [
                    'success' => true,
                    'message' => 'Company information retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => false,
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
                        'success' => true,
                        'message' => 'Company information retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Company information not found',
                        'data' => null
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
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
                        $company_id = $_POST['company_id'] ?? 1;
                        $company_name = trim($_POST['company_name']);
                        $tagline = trim($_POST['tagline']);
                        $description = trim($_POST['description']);
                        $mission = trim($_POST['mission']);
                        $vision = trim($_POST['vision']);
                        $about_image = trim($_POST['about_image']);
                        $logo_image = trim($_POST['logo_image']);

                        if (empty($company_name)) {
                            throw new Exception('Company name is required');
                        }

                        $companyInfo->updateCompanyInfo($_POST);
                        $response = [
                            'success' => true,
                            'message' => 'Company information updated successfully!',
                            'data' => null
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                case 'create':
                    try {
                        $companyId = $companyInfo->createCompanyInfo($_POST);
                        $response = [
                            'success' => true,
                            'message' => 'Company information created successfully',
                            'data' => ['company_id' => $companyId]
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                case 'update':
                    try {
                        $companyInfo->updateCompanyInfo($_POST);
                        $response = [
                            'success' => true,
                            'message' => 'Company information updated successfully',
                            'data' => null
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                default:
                    $response = [
                        'success' => false,
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