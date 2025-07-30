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
                
            case 'get_features':
                handleGetFeatures($pdo);
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
        $featureTitle = trim($_POST['feature_title']);
        $featureDescription = trim($_POST['feature_description']);
        $featureImage = $_POST['feature_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($featureTitle)) {
            respondWithJson(['success' => false, 'message' => 'Feature title is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Company_Features (FeatureTitle, FeatureDescription, FeatureImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$featureTitle, $featureDescription, $featureImage, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Feature added successfully'] 
            : ['success' => false, 'message' => 'Failed to add feature']);
    }

    function handleEdit($pdo) {
        $featureId = (int)$_POST['feature_id'];
        $featureTitle = trim($_POST['feature_title']);
        $featureDescription = trim($_POST['feature_description']);
        $featureImage = $_POST['feature_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($featureTitle)) {
            respondWithJson(['success' => false, 'message' => 'Feature title is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Company_Features SET FeatureTitle = ?, FeatureDescription = ?, FeatureImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdFeature = ?");
        $result = $stmt->execute([$featureTitle, $featureDescription, $featureImage, $displayOrder, $status, $featureId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Feature updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update feature']);
    }

    function handleDelete($pdo) {
        $featureId = (int)$_POST['feature_id'];
        
        $stmt = $pdo->prepare("UPDATE Company_Features SET Status = 0 WHERE IdFeature = ?");
        $result = $stmt->execute([$featureId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Feature deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete feature']);
    }

    function handleGet($pdo) {
        $featureId = (int)$_POST['feature_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Company_Features WHERE IdFeature = ?");
        $stmt->execute([$featureId]);
        $feature = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($feature 
            ? ['success' => true, 'data' => $feature] 
            : ['success' => false, 'message' => 'Feature not found']);
    }

    function handleGetFeatures($pdo) {
        $stmt = $pdo->query("SELECT * FROM Company_Features WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $features]);
    }
?> 