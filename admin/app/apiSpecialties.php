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
            $specialtyName = trim($_POST['specialty_name']);
            $specialtyDescription = trim($_POST['specialty_description']);
            $imageUrl = trim($_POST['image_url']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($specialtyName)) {
                echo json_encode(['success' => false, 'message' => 'Specialty name is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO Company_Specialties (SpecialtyName, SpecialtyDescription, ImageUrl, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$specialtyName, $specialtyDescription, $imageUrl, $displayOrder, $status]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Specialty added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add specialty']);
            }
            break;
            
        case 'edit':
            $specialtyId = (int)$_POST['specialty_id'];
            $specialtyName = trim($_POST['specialty_name']);
            $specialtyDescription = trim($_POST['specialty_description']);
            $imageUrl = trim($_POST['image_url']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($specialtyName)) {
                echo json_encode(['success' => false, 'message' => 'Specialty name is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Company_Specialties SET SpecialtyName = ?, SpecialtyDescription = ?, ImageUrl = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdSpecialty = ?");
            $result = $stmt->execute([$specialtyName, $specialtyDescription, $imageUrl, $displayOrder, $status, $specialtyId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Specialty updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update specialty']);
            }
            break;
            
        case 'delete':
            $specialtyId = (int)$_POST['specialty_id'];
            
            $stmt = $pdo->prepare("UPDATE Company_Specialties SET Status = 0 WHERE IdSpecialty = ?");
            $result = $stmt->execute([$specialtyId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Specialty deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete specialty']);
            }
            break;
            
        case 'get':
            $specialtyId = (int)$_POST['specialty_id'];
            
            $stmt = $pdo->prepare("SELECT * FROM Company_Specialties WHERE IdSpecialty = ?");
            $stmt->execute([$specialtyId]);
            $specialty = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($specialty) {
                echo json_encode(['success' => true, 'data' => $specialty]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Specialty not found']);
            }
            break;
            
        case 'get_specialties':
            // Get all specialties for DataTables
            $stmt = $pdo->query("SELECT * FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder ASC, SpecialtyName ASC");
            $specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $specialties
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
