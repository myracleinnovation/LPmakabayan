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
                
            case 'get_industries':
                handleGetIndustries($pdo);
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
        $industryName = trim($_POST['industry_name']);
        $industryDescription = trim($_POST['industry_description']);
        $industryImage = $_POST['industry_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($industryName)) {
            respondWithJson(['success' => false, 'message' => 'Industry name is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Industries (IndustryName, IndustryDescription, IndustryImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$industryName, $industryDescription, $industryImage, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Industry added successfully'] 
            : ['success' => false, 'message' => 'Failed to add industry']);
    }

    function handleEdit($pdo) {
        $industryId = (int)$_POST['industry_id'];
        $industryName = trim($_POST['industry_name']);
        $industryDescription = trim($_POST['industry_description']);
        $industryImage = $_POST['industry_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($industryName)) {
            respondWithJson(['success' => false, 'message' => 'Industry name is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Industries SET IndustryName = ?, IndustryDescription = ?, IndustryImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdIndustry = ?");
        $result = $stmt->execute([$industryName, $industryDescription, $industryImage, $displayOrder, $status, $industryId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Industry updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update industry']);
    }

    function handleDelete($pdo) {
        $industryId = (int)$_POST['industry_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Industries SET Status = 0 WHERE IdIndustry = ?");
        $result = $stmt->execute([$industryId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Industry deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete industry']);
    }

    function handleGet($pdo) {
        $industryId = (int)$_POST['industry_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Industries WHERE IdIndustry = ?");
        $stmt->execute([$industryId]);
        $industry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($industry 
            ? ['success' => true, 'data' => $industry] 
            : ['success' => false, 'message' => 'Industry not found']);
    }

    function handleGetIndustries($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $industries]);
    }
?> 