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
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $status = (int)$_POST['status'];
            
            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Username and password are required']);
                exit();
            }
            
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Admin_Accounts WHERE Username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit();
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Admin_Accounts (Username, Password, Status, CreatedTimestamp) VALUES (?, ?, ?, NOW())");
            $result = $stmt->execute([$username, $passwordHash, $status]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Admin account added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add admin account']);
            }
            break;
            
        case 'edit':
            $adminId = (int)$_POST['admin_id'];
            $username = trim($_POST['username']);
            $status = (int)$_POST['status'];
            
            if (empty($username)) {
                echo json_encode(['success' => false, 'message' => 'Username is required']);
                exit();
            }
            
            // Check if username already exists for other accounts
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Admin_Accounts WHERE Username = ? AND IdAdmin != ?");
            $stmt->execute([$username, $adminId]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Admin_Accounts SET Username = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdAdmin = ?");
            $result = $stmt->execute([$username, $status, $adminId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Admin account updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update admin account']);
            }
            break;
            
        case 'delete':
            $adminId = (int)$_POST['admin_id'];
            
            // Don't allow deleting own account
            if ($adminId == $_SESSION['admin_id']) {
                echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE Admin_Accounts SET Status = 0 WHERE IdAdmin = ?");
            $result = $stmt->execute([$adminId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Admin account deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete admin account']);
            }
            break;
            
        case 'get':
            $adminId = (int)$_POST['admin_id'];
            
            $stmt = $pdo->prepare("SELECT IdAdmin, Username, Status, CreatedTimestamp FROM Admin_Accounts WHERE IdAdmin = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                echo json_encode(['success' => true, 'data' => $admin]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin account not found']);
            }
            break;
            
        case 'get_admins':
            // Get all admin accounts for DataTables
            $stmt = $pdo->query("SELECT IdAdmin, Username, Status, CreatedTimestamp FROM Admin_Accounts WHERE Status = 1 ORDER BY CreatedTimestamp DESC");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $admins
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