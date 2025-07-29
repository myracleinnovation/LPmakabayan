<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get project categories
    $stmt = $pdo->query("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($categories) {
        echo json_encode(['success' => true, 'data' => $categories]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No project categories found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 