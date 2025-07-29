<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Db.php';

try {
    // Get database connection using Db class
    $pdo = Db::getConnection();
    
    // Get company projects with category information
    $stmt = $pdo->query("
        SELECT p.*, c.CategoryName 
        FROM Company_Projects p 
        LEFT JOIN Project_Categories c ON p.ProjectCategoryId = c.IdCategory 
        WHERE p.Status = 1 
        ORDER BY p.DisplayOrder ASC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($projects) {
        echo json_encode(['success' => true, 'data' => $projects]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No projects found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 