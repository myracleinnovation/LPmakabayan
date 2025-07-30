<?php
    include '../components/header.php';
    require_once '../../app/Db.php';
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {
        $pdo = Db::connect();
        
        switch ($action) {
            case 'add':
                handleAdd($pdo);
                break;
                
            case 'edit':
                handleEdit($pdo);
                break;
                
            case 'delete':
                handleDelete($pdo);
                break;
                
            case 'get':
                handleGet($pdo);
                break;
                
            case 'get_processes':
                handleGetProcesses($pdo);
                break;
                
            default:
                respondWithJson(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } catch (Exception $e) {
        respondWithJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    function respondWithJson($data) {
        echo json_encode($data);
        exit();
    }

    function handleAdd($pdo) {
        $processTitle = trim($_POST['process_title']);
        $processDescription = trim($_POST['process_description']);
        $processImage = $_POST['process_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($processTitle)) {
            respondWithJson(['success' => false, 'message' => 'Process title is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Process (ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$processTitle, $processDescription, $processImage, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Process added successfully'] 
            : ['success' => false, 'message' => 'Failed to add process']);
    }

    function handleEdit($pdo) {
        $processId = (int)$_POST['process_id'];
        $processTitle = trim($_POST['process_title']);
        $processDescription = trim($_POST['process_description']);
        $processImage = $_POST['process_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($processTitle)) {
            respondWithJson(['success' => false, 'message' => 'Process title is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Process SET ProcessTitle = ?, ProcessDescription = ?, ProcessImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdProcess = ?");
        $result = $stmt->execute([$processTitle, $processDescription, $processImage, $displayOrder, $status, $processId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Process updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update process']);
    }

    function handleDelete($pdo) {
        $processId = (int)$_POST['process_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Process SET Status = 0 WHERE IdProcess = ?");
        $result = $stmt->execute([$processId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Process deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete process']);
    }

    function handleGet($pdo) {
        $processId = (int)$_POST['process_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Process WHERE IdProcess = ?");
        $stmt->execute([$processId]);
        $process = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($process 
            ? ['success' => true, 'data' => $process] 
            : ['success' => false, 'message' => 'Process not found']);
    }

    function handleGetProcesses($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Process WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $processes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $processes]);
    }
?> 