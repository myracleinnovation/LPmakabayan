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
    $companyProcess = new CompanyProcess($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_processes']) || isset($_GET['get_process'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companyProcess->getAllProcesses($search, $start, $length, $order);
                
                $response = [
                    'success' => true,
                    'message' => 'Processes retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_active_processes'])) {
            try {
                $data = $companyProcess->getActiveProcesses();
                $response = [
                    'success' => true,
                    'message' => 'Active processes retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_process'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyProcess->getProcessById($id);
                
                if ($data) {
                    $response = [
                        'success' => true,
                        'message' => 'Process retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Process not found',
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
                case 'create':
                    try {
                        $processId = $companyProcess->createProcess($_POST);
                        $response = [
                            'success' => true,
                            'message' => 'Process created successfully',
                            'data' => ['process_id' => $processId]
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
                        $companyProcess->updateProcess($_POST);
                        $response = [
                            'success' => true,
                            'message' => 'Process updated successfully',
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

                case 'get':
                    try {
                        $id = $_POST['process_id'] ?? 0;
                        $data = $companyProcess->getProcessById($id);
                        
                        if ($data) {
                            $response = [
                                'success' => true,
                                'message' => 'Process retrieved successfully',
                                'data' => $data
                            ];
                        } else {
                            $response = [
                                'success' => false,
                                'message' => 'Process not found',
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
                    break;

                case 'delete':
                    try {
                        $id = $_POST['process_id'] ?? 0;
                        $companyProcess->deleteProcess($id);
                        $response = [
                            'success' => true,
                            'message' => 'Process deleted successfully',
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