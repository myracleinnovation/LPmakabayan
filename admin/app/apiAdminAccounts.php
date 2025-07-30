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
    $adminAccounts = new AdminAccounts($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_admins'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $adminAccounts->getAllAdmins($search, $start, $length, $order);
                
                $response = [
                    'status' => 1,
                    'message' => 'Admin accounts retrieved successfully',
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
        } elseif (isset($_GET['get_active_admins'])) {
            try {
                $data = $adminAccounts->getActiveAdmins();
                $response = [
                    'status' => 1,
                    'message' => 'Active admin accounts retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_admin'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $adminAccounts->getAdminById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Admin account retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Admin account not found',
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
                        $adminId = $adminAccounts->createAdmin($_POST);
                        $response = [
                            'status' => 1,
                            'message' => 'Admin account created successfully',
                            'data' => ['admin_id' => $adminId]
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
                        $adminAccounts->updateAdmin($_POST);
                        $response = [
                            'status' => 1,
                            'message' => 'Admin account updated successfully',
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

                case 'update_password':
                    try {
                        $adminId = $_POST['admin_id'] ?? 0;
                        $newPassword = $_POST['new_password'] ?? '';
                        
                        if (empty($newPassword)) {
                            throw new Exception('New password is required');
                        }
                        
                        $adminAccounts->updatePassword($adminId, $newPassword);
                        $response = [
                            'status' => 1,
                            'message' => 'Password updated successfully',
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
                        $id = $_POST['admin_id'] ?? 0;
                        $adminAccounts->deleteAdmin($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Admin account deleted successfully',
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