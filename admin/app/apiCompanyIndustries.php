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
                        $industryId = $companyIndustries->createIndustry($_POST);
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
                        $companyIndustries->updateIndustry($_POST);
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