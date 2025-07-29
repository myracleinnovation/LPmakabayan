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
            $industryName = trim($_POST['industry_name']);
            $industryDescription = trim($_POST['industry_description']);
            $imageUrl = trim($_POST['image_url']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($industryName)) {
                echo json_encode(['success' => false, 'message' => 'Industry name is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO Company_Industries (IndustryName, IndustryDescription, ImageUrl, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$industryName, $industryDescription, $imageUrl, $displayOrder, $status]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Industry added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add industry']);
            }
            break;
            
        case 'edit':
            $industryId = (int)$_POST['industry_id'];
            $industryName = trim($_POST['industry_name']);
            $industryDescription = trim($_POST['industry_description']);
            $imageUrl = trim($_POST['image_url']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($industryName)) {
                echo json_encode(['success' => false, 'message' => 'Industry name is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Company_Industries SET IndustryName = ?, IndustryDescription = ?, ImageUrl = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdIndustry = ?");
            $result = $stmt->execute([$industryName, $industryDescription, $imageUrl, $displayOrder, $status, $industryId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Industry updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update industry']);
            }
            break;
            
        case 'delete':
            $industryId = (int)$_POST['industry_id'];
            
            $stmt = $pdo->prepare("UPDATE Company_Industries SET Status = 0 WHERE IdIndustry = ?");
            $result = $stmt->execute([$industryId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Industry deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete industry']);
            }
            break;
            
        case 'get':
            $industryId = (int)$_POST['industry_id'];
            
            $stmt = $pdo->prepare("SELECT * FROM Company_Industries WHERE IdIndustry = ?");
            $stmt->execute([$industryId]);
            $industry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($industry) {
                echo json_encode(['success' => true, 'data' => $industry]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Industry not found']);
            }
            break;
            
        case 'get_industries':
            // Get all industries for DataTables
            $stmt = $pdo->query("SELECT * FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder ASC, IndustryName ASC");
            $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $industries
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
