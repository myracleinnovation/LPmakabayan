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
                
            case 'get_settings':
                handleGetSettings($pdo);
                break;
                
            case 'update_setting':
                handleUpdateSetting($pdo);
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
        $settingKey = trim($_POST['setting_key']);
        $settingValue = trim($_POST['setting_value']);
        $settingDescription = trim($_POST['setting_description']);
        $settingType = $_POST['setting_type'];
        $status = (int)$_POST['status'];
        
        if (empty($settingKey)) {
            respondWithJson(['success' => false, 'message' => 'Setting key is required']);
        }
        
        if (!in_array($settingType, ['text', 'number', 'boolean', 'json'])) {
            respondWithJson(['success' => false, 'message' => 'Invalid setting type']);
        }
        
        // Check if setting key already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM System_Settings WHERE SettingKey = ?");
        $stmt->execute([$settingKey]);
        if ($stmt->fetchColumn() > 0) {
            respondWithJson(['success' => false, 'message' => 'Setting key already exists']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO System_Settings (SettingKey, SettingValue, SettingDescription, SettingType, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$settingKey, $settingValue, $settingDescription, $settingType, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Setting added successfully'] 
            : ['success' => false, 'message' => 'Failed to add setting']);
    }

    function handleEdit($pdo) {
        $settingId = (int)$_POST['setting_id'];
        $settingKey = trim($_POST['setting_key']);
        $settingValue = trim($_POST['setting_value']);
        $settingDescription = trim($_POST['setting_description']);
        $settingType = $_POST['setting_type'];
        $status = (int)$_POST['status'];
        
        if (empty($settingKey)) {
            respondWithJson(['success' => false, 'message' => 'Setting key is required']);
        }
        
        if (!in_array($settingType, ['text', 'number', 'boolean', 'json'])) {
            respondWithJson(['success' => false, 'message' => 'Invalid setting type']);
        }
        
        // Check if setting key already exists (excluding current setting)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM System_Settings WHERE SettingKey = ? AND IdSetting != ?");
        $stmt->execute([$settingKey, $settingId]);
        if ($stmt->fetchColumn() > 0) {
            respondWithJson(['success' => false, 'message' => 'Setting key already exists']);
        }
        
        $stmt = $pdo->prepare("UPDATE System_Settings SET SettingKey = ?, SettingValue = ?, SettingDescription = ?, SettingType = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdSetting = ?");
        $result = $stmt->execute([$settingKey, $settingValue, $settingDescription, $settingType, $status, $settingId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Setting updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update setting']);
    }

    function handleDelete($pdo) {
        $settingId = (int)$_POST['setting_id'];
        
        $stmt = $pdo->prepare("UPDATE System_Settings SET Status = 0 WHERE IdSetting = ?");
        $result = $stmt->execute([$settingId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Setting deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete setting']);
    }

    function handleGet($pdo) {
        $settingId = (int)$_POST['setting_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM System_Settings WHERE IdSetting = ?");
        $stmt->execute([$settingId]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($setting 
            ? ['success' => true, 'data' => $setting] 
            : ['success' => false, 'message' => 'Setting not found']);
    }

    function handleGetSettings($pdo) {
        $stmt = $pdo->query("SELECT * FROM System_Settings WHERE Status = 1 ORDER BY SettingKey ASC");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $settings]);
    }

    function handleUpdateSetting($pdo) {
        $settingKey = trim($_POST['setting_key']);
        $settingValue = trim($_POST['setting_value']);
        
        if (empty($settingKey)) {
            respondWithJson(['success' => false, 'message' => 'Setting key is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE System_Settings SET SettingValue = ?, UpdatedTimestamp = NOW() WHERE SettingKey = ? AND Status = 1");
        $result = $stmt->execute([$settingValue, $settingKey]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Setting updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update setting']);
    }
?>
