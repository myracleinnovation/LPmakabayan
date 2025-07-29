<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company information
    $stmt = $pdo->query("SELECT * FROM Company_Info WHERE Status = 1 LIMIT 1");
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($company) {
        echo json_encode(['success' => true, 'data' => $company]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Company information not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 