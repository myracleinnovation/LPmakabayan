<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company specialties
    $stmt = $pdo->query("SELECT * FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($specialties) {
        echo json_encode(['success' => true, 'data' => $specialties]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No specialties found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 