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
                
            case 'get_contacts':
                handleGetContacts($pdo);
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
        $contactType = $_POST['contact_type'];
        $contactValue = trim($_POST['contact_value']);
        $contactLabel = trim($_POST['contact_label']);
        $contactIcon = $_POST['contact_icon'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($contactValue)) {
            respondWithJson(['success' => false, 'message' => 'Contact value is required']);
        }
        
        if (!in_array($contactType, ['phone', 'email', 'address', 'social_media', 'website'])) {
            respondWithJson(['success' => false, 'message' => 'Invalid contact type']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Contact (ContactType, ContactValue, ContactLabel, ContactIcon, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$contactType, $contactValue, $contactLabel, $contactIcon, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Contact added successfully'] 
            : ['success' => false, 'message' => 'Failed to add contact']);
    }

    function handleEdit($pdo) {
        $contactId = (int)$_POST['contact_id'];
        $contactType = $_POST['contact_type'];
        $contactValue = trim($_POST['contact_value']);
        $contactLabel = trim($_POST['contact_label']);
        $contactIcon = $_POST['contact_icon'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($contactValue)) {
            respondWithJson(['success' => false, 'message' => 'Contact value is required']);
        }
        
        if (!in_array($contactType, ['phone', 'email', 'address', 'social_media', 'website'])) {
            respondWithJson(['success' => false, 'message' => 'Invalid contact type']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Contact SET ContactType = ?, ContactValue = ?, ContactLabel = ?, ContactIcon = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdContact = ?");
        $result = $stmt->execute([$contactType, $contactValue, $contactLabel, $contactIcon, $displayOrder, $status, $contactId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Contact updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update contact']);
    }

    function handleDelete($pdo) {
        $contactId = (int)$_POST['contact_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Contact SET Status = 0 WHERE IdContact = ?");
        $result = $stmt->execute([$contactId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Contact deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete contact']);
    }

    function handleGet($pdo) {
        $contactId = (int)$_POST['contact_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Contact WHERE IdContact = ?");
        $stmt->execute([$contactId]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($contact 
            ? ['success' => true, 'data' => $contact] 
            : ['success' => false, 'message' => 'Contact not found']);
    }

    function handleGetContacts($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Contact WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $contacts]);
    }
?> 