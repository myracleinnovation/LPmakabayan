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
                
            case 'get_specialties':
                handleGetSpecialties($pdo);
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
        $specialtyName = trim($_POST['specialty_name']);
        $specialtyDescription = trim($_POST['specialty_description']);
        $specialtyImage = $_POST['specialty_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($specialtyName)) {
            respondWithJson(['success' => false, 'message' => 'Specialty name is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Specialties (SpecialtyName, SpecialtyDescription, SpecialtyImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$specialtyName, $specialtyDescription, $specialtyImage, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Specialty added successfully'] 
            : ['success' => false, 'message' => 'Failed to add specialty']);
    }

    function handleEdit($pdo) {
        $specialtyId = (int)$_POST['specialty_id'];
        $specialtyName = trim($_POST['specialty_name']);
        $specialtyDescription = trim($_POST['specialty_description']);
        $specialtyImage = $_POST['specialty_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($specialtyName)) {
            respondWithJson(['success' => false, 'message' => 'Specialty name is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Specialties SET SpecialtyName = ?, SpecialtyDescription = ?, SpecialtyImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdSpecialty = ?");
        $result = $stmt->execute([$specialtyName, $specialtyDescription, $specialtyImage, $displayOrder, $status, $specialtyId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Specialty updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update specialty']);
    }

    function handleDelete($pdo) {
        $specialtyId = (int)$_POST['specialty_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Specialties SET Status = 0 WHERE IdSpecialty = ?");
        $result = $stmt->execute([$specialtyId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Specialty deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete specialty']);
    }

    function handleGet($pdo) {
        $specialtyId = (int)$_POST['specialty_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Specialties WHERE IdSpecialty = ?");
        $stmt->execute([$specialtyId]);
        $specialty = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($specialty 
            ? ['success' => true, 'data' => $specialty] 
            : ['success' => false, 'message' => 'Specialty not found']);
    }

    function handleGetSpecialties($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $specialties]);
    }
?> 