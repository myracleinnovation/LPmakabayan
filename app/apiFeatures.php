<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company features
    $stmt = $pdo->query("SELECT * FROM Company_Features WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($features) {
        echo json_encode(['success' => true, 'data' => $features]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No features found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 