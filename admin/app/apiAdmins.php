<?php
    session_start();
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
    
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
                
            case 'get_admins':
                handleGetAdmins($pdo);
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
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $status = (int)$_POST['status'];
        
        if (empty($username) || empty($password)) {
            respondWithJson(['success' => false, 'message' => 'Username and password are required']);
        }
        
        if (isUsernameExists($pdo, $username)) {
            respondWithJson(['success' => false, 'message' => 'Username already exists']);
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Admin_Accounts (Username, Password, Status, CreatedTimestamp) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$username, $passwordHash, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Admin account added successfully'] 
            : ['success' => false, 'message' => 'Failed to add admin account']);
    }

    function handleEdit($pdo) {
        $adminId = (int)$_POST['admin_id'];
        $username = trim($_POST['username']);
        $status = (int)$_POST['status'];
        
        if (empty($username)) {
            respondWithJson(['success' => false, 'message' => 'Username is required']);
        }
        
        if (isUsernameExists($pdo, $username, $adminId)) {
            respondWithJson(['success' => false, 'message' => 'Username already exists']);
        }
        
        $stmt = $pdo->prepare("UPDATE Admin_Accounts SET Username = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdAdmin = ?");
        $result = $stmt->execute([$username, $status, $adminId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Admin account updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update admin account']);
    }

    function handleDelete($pdo) {
        $adminId = (int)$_POST['admin_id'];
        
        // Don't allow deleting own account
        if ($adminId == $_SESSION['admin_id']) {
            respondWithJson(['success' => false, 'message' => 'You cannot delete your own account']);
        }
        
        $stmt = $pdo->prepare("UPDATE Admin_Accounts SET Status = 0 WHERE IdAdmin = ?");
        $result = $stmt->execute([$adminId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Admin account deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete admin account']);
    }

    function handleGet($pdo) {
        $adminId = (int)$_POST['admin_id'];
        
        $stmt = $pdo->prepare("SELECT IdAdmin, Username, Status, CreatedTimestamp FROM Admin_Accounts WHERE IdAdmin = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($admin 
            ? ['success' => true, 'data' => $admin] 
            : ['success' => false, 'message' => 'Admin account not found']);
    }

    function handleGetAdmins($pdo) {
        $stmt = $pdo->query("SELECT IdAdmin, Username, Status, CreatedTimestamp FROM Admin_Accounts WHERE Status = 1 ORDER BY CreatedTimestamp DESC");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $admins]);
    }

    function isUsernameExists($pdo, $username, $adminId = null) {
        $query = "SELECT COUNT(*) FROM Admin_Accounts WHERE Username = ?";
        if ($adminId) {
            $query .= " AND IdAdmin != ?";
        }
        $stmt = $pdo->prepare($query);
        $params = $adminId ? [$username, $adminId] : [$username];
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
?>