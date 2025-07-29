<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../../app/Db.php';

$action = $_POST['action'] ?? '';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    switch ($action) {
        case 'add':
            $projectTitle = trim($_POST['project_title']);
            $projectOwner = trim($_POST['project_owner']);
            $projectLocation = trim($_POST['project_location']);
            $projectCategory = trim($_POST['project_category']);
            $projectDescription = trim($_POST['project_description']);
            $turnoverDate = $_POST['turnover_date'] ?: null;
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            $imageUrl1 = trim($_POST['image_url_1']);
            $imageUrl2 = trim($_POST['image_url_2']);
            $imageUrl3 = trim($_POST['image_url_3']);
            $imageUrl4 = trim($_POST['image_url_4']);
            
            if (empty($projectTitle)) {
                echo json_encode(['success' => false, 'message' => 'Project title is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO Company_Projects (ProjectTitle, ProjectOwner, ProjectLocation, ProjectCategory, ProjectDescription, TurnoverDate, DisplayOrder, Status, ProjectImage1, ProjectImage2, ProjectImage3, ProjectImage4, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$projectTitle, $projectOwner, $projectLocation, $projectCategory, $projectDescription, $turnoverDate, $displayOrder, $status, $imageUrl1, $imageUrl2, $imageUrl3, $imageUrl4]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Project added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add project']);
            }
            break;
            
        case 'edit':
            $projectId = (int)$_POST['project_id'];
            $projectTitle = trim($_POST['project_title']);
            $projectOwner = trim($_POST['project_owner']);
            $projectLocation = trim($_POST['project_location']);
            $projectCategory = trim($_POST['project_category']);
            $projectDescription = trim($_POST['project_description']);
            $turnoverDate = $_POST['turnover_date'] ?: null;
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            $imageUrl1 = trim($_POST['image_url_1']);
            $imageUrl2 = trim($_POST['image_url_2']);
            $imageUrl3 = trim($_POST['image_url_3']);
            $imageUrl4 = trim($_POST['image_url_4']);
            
            if (empty($projectTitle)) {
                echo json_encode(['success' => false, 'message' => 'Project title is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Company_Projects SET ProjectTitle = ?, ProjectOwner = ?, ProjectLocation = ?, ProjectCategory = ?, ProjectDescription = ?, TurnoverDate = ?, DisplayOrder = ?, Status = ?, ProjectImage1 = ?, ProjectImage2 = ?, ProjectImage3 = ?, ProjectImage4 = ?, UpdatedTimestamp = NOW() WHERE IdProject = ?");
            $result = $stmt->execute([$projectTitle, $projectOwner, $projectLocation, $projectCategory, $projectDescription, $turnoverDate, $displayOrder, $status, $imageUrl1, $imageUrl2, $imageUrl3, $imageUrl4, $projectId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update project']);
            }
            break;
            
        case 'delete':
            $projectId = (int)$_POST['project_id'];
            
            $stmt = $pdo->prepare("UPDATE Company_Projects SET Status = 0 WHERE IdProject = ?");
            $result = $stmt->execute([$projectId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
            }
            break;
            
        case 'get':
            $projectId = (int)$_POST['project_id'];
            
            $stmt = $pdo->prepare("SELECT * FROM Company_Projects WHERE IdProject = ?");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($project) {
                echo json_encode(['success' => true, 'data' => $project]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Project not found']);
            }
            break;
            
        case 'get_projects':
            // Get all projects for DataTables
            $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $projects
            ]);
            break;
            
        case 'get_recent_projects':
            // Get recent projects for dashboard
            $stmt = $pdo->query("SELECT * FROM Company_Projects WHERE Status = 1 ORDER BY CreatedTimestamp DESC LIMIT 5");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $projects
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
