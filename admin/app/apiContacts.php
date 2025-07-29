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
            $contactLabel = trim($_POST['contact_label']);
            $contactValue = trim($_POST['contact_value']);
            $contactType = trim($_POST['contact_type']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($contactLabel) || empty($contactValue) || empty($contactType)) {
                echo json_encode(['success' => false, 'message' => 'Contact label, value, and type are required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO Company_Contact (ContactLabel, ContactValue, ContactType, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$contactLabel, $contactValue, $contactType, $displayOrder, $status]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Contact added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add contact']);
            }
            break;
            
        case 'edit':
            $contactId = (int)$_POST['contact_id'];
            $contactLabel = trim($_POST['contact_label']);
            $contactValue = trim($_POST['contact_value']);
            $contactType = trim($_POST['contact_type']);
            $displayOrder = (int)$_POST['display_order'];
            $status = (int)$_POST['status'];
            
            if (empty($contactLabel) || empty($contactValue) || empty($contactType)) {
                echo json_encode(['success' => false, 'message' => 'Contact label, value, and type are required']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Company_Contact SET ContactLabel = ?, ContactValue = ?, ContactType = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdContact = ?");
            $result = $stmt->execute([$contactLabel, $contactValue, $contactType, $displayOrder, $status, $contactId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Contact updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update contact']);
            }
            break;
            
        case 'delete':
            $contactId = (int)$_POST['contact_id'];
            
            $stmt = $pdo->prepare("UPDATE Company_Contact SET Status = 0 WHERE IdContact = ?");
            $result = $stmt->execute([$contactId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Contact deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete contact']);
            }
            break;
            
        case 'get':
            $contactId = (int)$_POST['contact_id'];
            
            $stmt = $pdo->prepare("SELECT * FROM Company_Contact WHERE IdContact = ?");
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($contact) {
                echo json_encode(['success' => true, 'data' => $contact]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Contact not found']);
            }
            break;
            
        case 'get_contacts':
            // Get all contacts for DataTables
            $stmt = $pdo->query("SELECT * FROM Company_Contact WHERE Status = 1 ORDER BY DisplayOrder ASC, ContactLabel ASC");
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $contacts
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