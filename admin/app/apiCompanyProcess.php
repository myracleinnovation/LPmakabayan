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

    spl_autoload_register(function ($class) {
        $classFile = $class . '.php';
        if (file_exists($classFile)) {
            require_once($classFile);
        } else {
            throw new Exception("Required class file not found: " . $class);
        }
    });

    $conn = Db::connect();
    $companyProcess = new CompanyProcess();

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    // Function to validate and process image upload
    function processImageUpload($file, $uploadDir, $companyProcess, $oldImage = null) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            throw new Exception($errorMessages[$file['error']] ?? 'Unknown upload error');
        }

        // Check file size (100MB limit)
        $maxFileSize = 100 * 1024 * 1024; // 100MB in bytes
        if ($file['size'] > $maxFileSize) {
            throw new Exception('File size exceeds 100MB limit');
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WebP');
        }

        // Validate file type using getimagesize
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('Invalid image file');
        }

        // Delete old image if it exists
        if (!empty($oldImage)) {
            $oldFile = $uploadDir . $oldImage;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Generate unique filename
        $nextProcessNumber = $companyProcess->getNextProcessNumber();
        $filename = 'process' . $nextProcessNumber . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save uploaded file');
        }

        return $filename;
    }

    try {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            // Get all processes
            if (isset($_GET['get_processes'])) {
                $processes = $companyProcess->getAllProcesses();
                if ($processes !== false) {
                    echo json_encode([
                        'status' => 1,
                        'message' => 'Processes retrieved successfully',
                        'data' => $processes
                    ]);
                } else {
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Error retrieving processes'
                    ]);
                }
            }
            // Test endpoint to check if table exists and has data
            elseif (isset($_GET['test_table'])) {
                try {
                    $sql = "SELECT COUNT(*) as count FROM Company_Process";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    echo json_encode([
                        'status' => 1,
                        'message' => 'Table test successful',
                        'data' => [
                            'table_exists' => true,
                            'record_count' => $count
                        ]
                    ]);
                } catch (PDOException $e) {
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Table test failed: ' . $e->getMessage(),
                        'data' => [
                            'table_exists' => false,
                            'error' => $e->getMessage()
                        ]
                    ]);
                }
            }
            // Get single process
            elseif (isset($_GET['get_process']) && isset($_GET['id'])) {
                $processId = $_GET['id'];
                error_log("Getting process with ID: " . $processId);
                
                $process = $companyProcess->getProcessById($processId);
                error_log("Process result: " . ($process ? 'found' : 'not found'));
                
                if ($process !== false) {
                    echo json_encode([
                        'status' => 1,
                        'message' => 'Process retrieved successfully',
                        'data' => $process
                    ]);
                } else {
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Process not found or error occurred'
                    ]);
                }
            }
            // Get active processes
            elseif (isset($_GET['get_active_processes'])) {
                $processes = $companyProcess->getActiveProcesses();
                if ($processes !== false) {
                    echo json_encode([
                        'status' => 1,
                        'message' => 'Active processes retrieved successfully',
                        'data' => $processes
                    ]);
                } else {
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Error retrieving active processes'
                    ]);
                }
            }
            else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid request'
                ]);
            }
        }
        elseif ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'create':
                    // Validate required fields
                    if (empty($input['process_title'])) {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Process title is required'
                        ]);
                        exit;
                    }
                    
                    // Handle file uploads
                    $uploadDir = '../../assets/img/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Process uploaded files
                    $data = [
                        'process_title' => trim($input['process_title']),
                        'process_description' => trim($input['process_description'] ?? ''),
                        'process_image' => '',
                        'display_order' => (int)($input['display_order'] ?? 0),
                        'status' => (int)($input['status'] ?? 1)
                    ];
                    
                    // Handle process_image (only if file is uploaded)
                    if (isset($_FILES['process_image']) && $_FILES['process_image']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $filename = processImageUpload($_FILES['process_image'], $uploadDir, $companyProcess);
                            $data['process_image'] = $filename;
                        } catch (Exception $e) {
                            echo json_encode([
                                'status' => 0,
                                'message' => 'Error uploading process image: ' . $e->getMessage()
                            ]);
                            exit;
                        }
                    }
                    
                    $result = $companyProcess->createProcess($data);
                    if ($result) {
                        echo json_encode([
                            'status' => 1,
                            'message' => 'Process created successfully',
                            'data' => ['id' => $result]
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Error creating process'
                        ]);
                    }
                    break;
                    
                case 'update':
                    // Validate required fields
                    if (empty($input['process_id']) || empty($input['process_title'])) {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Process ID and title are required'
                        ]);
                        exit;
                    }
                    
                    // Handle file uploads
                    $uploadDir = '../../assets/img/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Process uploaded files
                    $data = [
                        'process_id' => (int)$input['process_id'],
                        'process_title' => trim($input['process_title']),
                        'process_description' => trim($input['process_description'] ?? ''),
                        'process_image' => '',
                        'display_order' => (int)($input['display_order'] ?? 0),
                        'status' => (int)($input['status'] ?? 1)
                    ];
                    
                    // Get current process data to preserve existing image
                    $currentProcess = $companyProcess->getProcessById($data['process_id']);
                    
                    // Handle process_image (only if new file is uploaded)
                    if (isset($_FILES['process_image']) && $_FILES['process_image']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $filename = processImageUpload($_FILES['process_image'], $uploadDir, $companyProcess, $currentProcess['ProcessImage']);
                            $data['process_image'] = $filename;
                        } catch (Exception $e) {
                            echo json_encode([
                                'status' => 0,
                                'message' => 'Error uploading process image: ' . $e->getMessage()
                            ]);
                            exit;
                        }
                    } else {
                        // Keep existing image if no new file uploaded
                        $data['process_image'] = $currentProcess['ProcessImage'] ?? '';
                    }
                    
                    $result = $companyProcess->updateProcess($data);
                    if ($result) {
                        echo json_encode([
                            'status' => 1,
                            'message' => 'Process updated successfully'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Error updating process'
                        ]);
                    }
                    break;
                    
                case 'delete':
                    if (empty($input['process_id'])) {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Process ID is required'
                        ]);
                        exit;
                    }
                    
                    // Get process data to delete associated image
                    $process = $companyProcess->getProcessById($input['process_id']);
                    if ($process) {
                        $uploadDir = '../../assets/img/';
                        
                        // Delete process image file if it exists
                        if (!empty($process['ProcessImage'])) {
                            $imageFile = $uploadDir . $process['ProcessImage'];
                            if (file_exists($imageFile)) {
                                unlink($imageFile);
                            }
                        }
                    }
                    
                    $result = $companyProcess->deleteProcess($input['process_id']);
                    if ($result) {
                        echo json_encode([
                            'status' => 1,
                            'message' => 'Process deleted successfully'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'Error deleting process'
                        ]);
                    }
                    break;
                    
                default:
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Invalid action'
                    ]);
                    break;
            }
        }
        else {
            echo json_encode([
                'status' => 0,
                'message' => 'Method not allowed'
            ]);
        }
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        echo json_encode([
            'status' => 0,
            'message' => 'Server error occurred'
        ]);
    }
?> 