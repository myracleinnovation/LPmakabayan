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
                
            case 'get_company_info':
                handleGetCompanyInfo($pdo);
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
        $companyName = trim($_POST['company_name']);
        $tagline = trim($_POST['tagline']);
        $description = trim($_POST['description']);
        $mission = trim($_POST['mission']);
        $vision = trim($_POST['vision']);
        $aboutImage = $_POST['about_image'] ?? '';
        $logoImage = $_POST['logo_image'] ?? '';
        $faviconImage = $_POST['favicon_image'] ?? '';
        $status = (int)$_POST['status'];
        
        if (empty($companyName)) {
            respondWithJson(['success' => false, 'message' => 'Company name is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Info (CompanyName, Tagline, Description, Mission, Vision, AboutImage, LogoImage, FaviconImage, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$companyName, $tagline, $description, $mission, $vision, $aboutImage, $logoImage, $faviconImage, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Company information added successfully'] 
            : ['success' => false, 'message' => 'Failed to add company information']);
    }

    function handleEdit($pdo) {
        $companyId = (int)$_POST['company_id'];
        $companyName = trim($_POST['company_name']);
        $tagline = trim($_POST['tagline']);
        $description = trim($_POST['description']);
        $mission = trim($_POST['mission']);
        $vision = trim($_POST['vision']);
        $aboutImage = $_POST['about_image'] ?? '';
        $logoImage = $_POST['logo_image'] ?? '';
        $faviconImage = $_POST['favicon_image'] ?? '';
        $status = (int)$_POST['status'];
        
        if (empty($companyName)) {
            respondWithJson(['success' => false, 'message' => 'Company name is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Info SET CompanyName = ?, Tagline = ?, Description = ?, Mission = ?, Vision = ?, AboutImage = ?, LogoImage = ?, FaviconImage = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdCompany = ?");
        $result = $stmt->execute([$companyName, $tagline, $description, $mission, $vision, $aboutImage, $logoImage, $faviconImage, $status, $companyId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Company information updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update company information']);
    }

    function handleDelete($pdo) {
        $companyId = (int)$_POST['company_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Info SET Status = 0 WHERE IdCompany = ?");
        $result = $stmt->execute([$companyId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Company information deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete company information']);
    }

    function handleGet($pdo) {
        $companyId = (int)$_POST['company_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Info WHERE IdCompany = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($company 
            ? ['success' => true, 'data' => $company] 
            : ['success' => false, 'message' => 'Company information not found']);
    }

    function handleGetCompanyInfo($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Info WHERE Status = 1 ORDER BY CreatedTimestamp DESC");
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $companies]);
    }
?> 