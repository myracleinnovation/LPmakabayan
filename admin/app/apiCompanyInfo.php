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
        case 'update_company':
            $companyName = trim($_POST['company_name']);
            $tagline = trim($_POST['tagline']);
            $description = trim($_POST['description']);
            $mission = trim($_POST['mission']);
            $vision = trim($_POST['vision']);
            $aboutImage = trim($_POST['about_image']);
            $logoImage = trim($_POST['logo_image']);
            $companyId = (int)$_POST['company_id'];
            
            if (empty($companyName)) {
                echo json_encode(['success' => false, 'message' => 'Company name is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Company_Info SET CompanyName = ?, Tagline = ?, Description = ?, Mission = ?, Vision = ?, AboutImage = ?, LogoImage = ? WHERE IdCompany = ?");
            $result = $stmt->execute([$companyName, $tagline, $description, $mission, $vision, $aboutImage, $logoImage, $companyId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Company information updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update company information']);
            }
            break;
            
        case 'get_company':
            $stmt = $pdo->query("SELECT * FROM Company_Info WHERE Status = 1 LIMIT 1");
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($company) {
                echo json_encode(['success' => true, 'data' => $company]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Company information not found']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
