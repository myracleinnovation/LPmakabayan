<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company process steps
    $stmt = $pdo->query("SELECT * FROM Company_Process WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $process = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($process) {
        echo json_encode(['success' => true, 'data' => $process]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No process steps found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 