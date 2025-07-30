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
                
                $response = [
                    'status' => 1,
                    'message' => 'Project categories retrieved successfully',
                    'data' => [
                        'data' => $data
                    ]
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => [
                        'data' => []
                    ]
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
                        $categoryId = $projectCategories->createCategory($_POST);
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
                        $projectCategories->updateCategory($_POST);
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