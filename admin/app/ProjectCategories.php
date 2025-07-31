<?php

class ProjectCategories
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllCategories($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Project_Categories";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE CategoryName LIKE :search OR CategoryDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, CategoryName ASC";
        }

        $sql .= " LIMIT :start, :length";
        $stmt = $this->conn->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCategories($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM Project_Categories";
        
        if (!empty($search)) {
            $sql .= " WHERE CategoryName LIKE :search OR CategoryDescription LIKE :search";
        }

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Project_Categories WHERE IdCategory = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCategory($postData)
    {
        $categoryName = trim($postData['category_name']);
        $categoryDescription = trim($postData['category_description']);
        $categoryImage = trim($postData['category_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($categoryName)) {
            throw new Exception('Category name is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO Project_Categories (CategoryName, CategoryDescription, CategoryImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$categoryName, $categoryDescription, $categoryImage, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create category');
        }

        return $this->conn->lastInsertId();
    }

    public function updateCategory($postData)
    {
        $id = (int)$postData['category_id'];
        $categoryName = trim($postData['category_name']);
        $categoryDescription = trim($postData['category_description']);
        $categoryImage = trim($postData['category_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($categoryName)) {
            throw new Exception('Category name is required');
        }

        $sql = "UPDATE Project_Categories SET CategoryName = ?, CategoryDescription = ?, CategoryImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdCategory = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$categoryName, $categoryDescription, $categoryImage, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update category');
        }

        return true;
    }

    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare("UPDATE Project_Categories SET Status = 0, UpdatedTimestamp = NOW() WHERE IdCategory = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete category');
        }

        return true;
    }

    public function getActiveCategories()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC, CategoryName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectCategories()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC, CategoryName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextCategoryNumber()
    {
        $imgDir = '../../assets/img/';
        $existingNumbers = [];
        
        // Scan existing category files
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if (preg_match('/^category(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
                    $existingNumbers[] = (int)$matches[1];
                }
            }
        }
        
        // Find the next available number
        if (empty($existingNumbers)) {
            return 1;
        }
        
        sort($existingNumbers);
        $nextNumber = 1;
        
        foreach ($existingNumbers as $number) {
            if ($number > $nextNumber) {
                break;
            }
            $nextNumber = $number + 1;
        }
        
        return $nextNumber;
    }
} 