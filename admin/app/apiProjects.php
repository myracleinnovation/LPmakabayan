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
                
            case 'get_projects':
                handleGetProjects($pdo);
                break;
                
            case 'get_categories':
                handleGetCategories($pdo);
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
        $projectTitle = trim($_POST['project_title']);
        $projectDescription = trim($_POST['project_description']);
        $projectOwner = trim($_POST['project_owner']);
        $projectLocation = trim($_POST['project_location']);
        $projectArea = (float)$_POST['project_area'];
        $projectValue = (float)$_POST['project_value'];
        $turnoverDate = $_POST['turnover_date'];
        $projectCategoryId = (int)$_POST['project_category_id'];
        $projectImage1 = $_POST['project_image1'] ?? '';
        $projectImage2 = $_POST['project_image2'] ?? '';
        $projectImage3 = $_POST['project_image3'] ?? '';
        $projectImage4 = $_POST['project_image4'] ?? '';
        $projectImage5 = $_POST['project_image5'] ?? '';
        $projectImage6 = $_POST['project_image6'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($projectTitle)) {
            respondWithJson(['success' => false, 'message' => 'Project title is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Projects (ProjectTitle, ProjectDescription, ProjectOwner, ProjectLocation, ProjectArea, ProjectValue, TurnoverDate, ProjectCategoryId, ProjectImage1, ProjectImage2, ProjectImage3, ProjectImage4, ProjectImage5, ProjectImage6, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $projectImage3, $projectImage4, $projectImage5, $projectImage6, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Project added successfully'] 
            : ['success' => false, 'message' => 'Failed to add project']);
    }

    function handleEdit($pdo) {
        $projectId = (int)$_POST['project_id'];
        $projectTitle = trim($_POST['project_title']);
        $projectDescription = trim($_POST['project_description']);
        $projectOwner = trim($_POST['project_owner']);
        $projectLocation = trim($_POST['project_location']);
        $projectArea = (float)$_POST['project_area'];
        $projectValue = (float)$_POST['project_value'];
        $turnoverDate = $_POST['turnover_date'];
        $projectCategoryId = (int)$_POST['project_category_id'];
        $projectImage1 = $_POST['project_image1'] ?? '';
        $projectImage2 = $_POST['project_image2'] ?? '';
        $projectImage3 = $_POST['project_image3'] ?? '';
        $projectImage4 = $_POST['project_image4'] ?? '';
        $projectImage5 = $_POST['project_image5'] ?? '';
        $projectImage6 = $_POST['project_image6'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($projectTitle)) {
            respondWithJson(['success' => false, 'message' => 'Project title is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Projects SET ProjectTitle = ?, ProjectDescription = ?, ProjectOwner = ?, ProjectLocation = ?, ProjectArea = ?, ProjectValue = ?, TurnoverDate = ?, ProjectCategoryId = ?, ProjectImage1 = ?, ProjectImage2 = ?, ProjectImage3 = ?, ProjectImage4 = ?, ProjectImage5 = ?, ProjectImage6 = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdProject = ?");
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $projectImage3, $projectImage4, $projectImage5, $projectImage6, $displayOrder, $status, $projectId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Project updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update project']);
    }

    function handleDelete($pdo) {
        $projectId = (int)$_POST['project_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Projects SET Status = 0 WHERE IdProject = ?");
        $result = $stmt->execute([$projectId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Project deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete project']);
    }

    function handleGet($pdo) {
        $projectId = (int)$_POST['project_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Projects WHERE IdProject = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($project 
            ? ['success' => true, 'data' => $project] 
            : ['success' => false, 'message' => 'Project not found']);
    }

    function handleGetProjects($pdo) {
        $stmt = $pdo->query("SELECT p.*, pc.CategoryName FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory WHERE p.Status = 1 ORDER BY p.DisplayOrder ASC, p.CreatedTimestamp DESC");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $projects]);
    }

    function handleGetCategories($pdo) {
        $stmt = $pdo->query("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $categories]);
    }
?> 