<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company contact information
    $stmt = $pdo->query("SELECT * FROM Company_Contact WHERE Status = 1 ORDER BY DisplayOrder ASC");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($contacts) {
        echo json_encode(['success' => true, 'data' => $contacts]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No contact information found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 