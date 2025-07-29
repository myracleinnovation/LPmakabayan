<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company industries
    $stmt = $pdo->query("SELECT * FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($industries) {
        echo json_encode(['success' => true, 'data' => $industries]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No industries found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 