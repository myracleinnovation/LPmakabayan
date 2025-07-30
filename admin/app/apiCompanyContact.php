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
    $companyContact = new CompanyContact($conn);

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_contacts'])) {
            try {
                $search = $_GET['search'] ?? '';
                $start = $_GET['start'] ?? 0;
                $length = $_GET['length'] ?? 25;
                $order = isset($_GET['order']) ? json_decode($_GET['order'], true) : [];
                
                $data = $companyContact->getAllContacts($search, $start, $length, $order);
                
                $response = [
                    'status' => 1,
                    'message' => 'Contacts retrieved successfully',
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
        } elseif (isset($_GET['get_active_contacts'])) {
            try {
                $data = $companyContact->getActiveContacts();
                $response = [
                    'status' => 1,
                    'message' => 'Active contacts retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        } elseif (isset($_GET['get_contact'])) {
            try {
                $id = $_GET['id'] ?? 0;
                $data = $companyContact->getContactById($id);
                
                if ($data) {
                    $response = [
                        'status' => 1,
                        'message' => 'Contact retrieved successfully',
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Contact not found',
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
                case 'get_contacts':
                    try {
                        $search = $_POST['search'] ?? '';
                        $start = $_POST['start'] ?? 0;
                        $length = $_POST['length'] ?? 25;
                        $order = isset($_POST['order']) ? json_decode($_POST['order'], true) : [];
                        
                        $data = $companyContact->getAllContacts($search, $start, $length, $order);
                        
                        $response = [
                            'status' => 1,
                            'message' => 'Contacts retrieved successfully',
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

                case 'get_active_contacts':
                    try {
                        $data = $companyContact->getActiveContacts();
                        $response = [
                            'status' => 1,
                            'message' => 'Active contacts retrieved successfully',
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

                case 'get':
                    try {
                        $id = $_POST['contact_id'] ?? 0;
                        $data = $companyContact->getContactById($id);
                        
                        if ($data) {
                            $response = [
                                'status' => 1,
                                'message' => 'Contact retrieved successfully',
                                'data' => $data
                            ];
                        } else {
                            $response = [
                                'status' => 0,
                                'message' => 'Contact not found',
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
                    break;

                case 'add':
                    try {
                        $contactId = $companyContact->createContact($_POST);
                        $response = [
                            'status' => 1,
                            'message' => 'Contact created successfully',
                            'data' => ['contact_id' => $contactId]
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;

                case 'edit':
                    try {
                        $companyContact->updateContact($_POST);
                        $response = [
                            'status' => 1,
                            'message' => 'Contact updated successfully',
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
                        $id = $_POST['contact_id'] ?? 0;
                        $companyContact->deleteContact($id);
                        $response = [
                            'status' => 1,
                            'message' => 'Contact deleted successfully',
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