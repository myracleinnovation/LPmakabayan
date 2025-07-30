<?php
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
                
            case 'get_categories':
                handleGetCategories($pdo);
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
        $categoryName = trim($_POST['category_name']);
        $categoryDescription = trim($_POST['category_description']);
        $categoryImage = $_POST['category_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($categoryName)) {
            respondWithJson(['success' => false, 'message' => 'Category name is required']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO Project_Categories (CategoryName, CategoryDescription, CategoryImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$categoryName, $categoryDescription, $categoryImage, $displayOrder, $status]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Category added successfully'] 
            : ['success' => false, 'message' => 'Failed to add category']);
    }

    function handleEdit($pdo) {
        $categoryId = (int)$_POST['category_id'];
        $categoryName = trim($_POST['category_name']);
        $categoryDescription = trim($_POST['category_description']);
        $categoryImage = $_POST['category_image'] ?? '';
        $displayOrder = (int)$_POST['display_order'];
        $status = (int)$_POST['status'];
        
        if (empty($categoryName)) {
            respondWithJson(['success' => false, 'message' => 'Category name is required']);
        }
        
        $stmt = $pdo->prepare("UPDATE Project_Categories SET CategoryName = ?, CategoryDescription = ?, CategoryImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdCategory = ?");
        $result = $stmt->execute([$categoryName, $categoryDescription, $categoryImage, $displayOrder, $status, $categoryId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Category updated successfully'] 
            : ['success' => false, 'message' => 'Failed to update category']);
    }

    function handleDelete($pdo) {
        $categoryId = (int)$_POST['category_id'];
        
        // Check if category is being used by any projects
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Company_Projects WHERE ProjectCategoryId = ? AND Status = 1");
        $stmt->execute([$categoryId]);
        $projectCount = $stmt->fetchColumn();
        
        if ($projectCount > 0) {
            respondWithJson(['success' => false, 'message' => 'Cannot delete category. It is being used by ' . $projectCount . ' project(s)']);
        }
        
        $stmt = $pdo->prepare("UPDATE Project_Categories SET Status = 0 WHERE IdCategory = ?");
        $result = $stmt->execute([$categoryId]);
        
        respondWithJson($result 
            ? ['success' => true, 'message' => 'Category deleted successfully'] 
            : ['success' => false, 'message' => 'Failed to delete category']);
    }

    function handleGet($pdo) {
        $categoryId = (int)$_POST['category_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM Project_Categories WHERE IdCategory = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        respondWithJson($category 
            ? ['success' => true, 'data' => $category] 
            : ['success' => false, 'message' => 'Category not found']);
    }

    function handleGetCategories($pdo) {
        $stmt = $pdo->query("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC, CreatedTimestamp DESC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respondWithJson(['success' => true, 'data' => $categories]);
    }
?> 