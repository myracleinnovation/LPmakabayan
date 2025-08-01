<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'CompanyProcess.php';

$processModel = new CompanyProcess();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get all processes
        if (isset($_GET['get_processes'])) {
            $processes = $processModel->getAllProcesses();
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
        // Get single process
        elseif (isset($_GET['get_process']) && isset($_GET['id'])) {
            $process = $processModel->getProcessById($_GET['id']);
            if ($process) {
                echo json_encode([
                    'status' => 1,
                    'message' => 'Process retrieved successfully',
                    'data' => $process
                ]);
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Process not found'
                ]);
            }
        }
        // Get active processes
        elseif (isset($_GET['get_active_processes'])) {
            $processes = $processModel->getActiveProcesses();
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
                    $file = $_FILES['process_image'];
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($extension, $allowedExtensions)) {
                        $nextProcessNumber = $processModel->getNextProcessNumber();
                        $filename = 'process' . $nextProcessNumber . '.' . $extension;
                        $filepath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $data['process_image'] = $filename;
                        }
                    }
                }
                
                $result = $processModel->createProcess($data);
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
                $currentProcess = $processModel->getProcessById($data['process_id']);
                
                // Handle process_image (only if new file is uploaded)
                if (isset($_FILES['process_image']) && $_FILES['process_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['process_image'];
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($extension, $allowedExtensions)) {
                        // Delete old process image file if it exists
                        if (!empty($currentProcess['ProcessImage'])) {
                            $oldFile = $uploadDir . $currentProcess['ProcessImage'];
                            if (file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }
                        
                        $nextProcessNumber = $processModel->getNextProcessNumber();
                        $filename = 'process' . $nextProcessNumber . '.' . $extension;
                        $filepath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $data['process_image'] = $filename;
                        }
                    }
                } else {
                    // Keep existing image if no new file uploaded
                    $data['process_image'] = $currentProcess['ProcessImage'] ?? '';
                }
                
                $result = $processModel->updateProcess($data);
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
                $process = $processModel->getProcessById($input['process_id']);
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
                
                $result = $processModel->deleteProcess($input['process_id']);
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