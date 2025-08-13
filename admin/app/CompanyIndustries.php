<?php

class CompanyIndustries
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllIndustries($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Company_Industries";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE IndustryName LIKE :search OR IndustryDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, IndustryName ASC";
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

    public function getTotalIndustries($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM Company_Industries";
        
        if (!empty($search)) {
            $sql .= " WHERE IndustryName LIKE :search OR IndustryDescription LIKE :search";
        }

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    public function getIndustryById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Industries WHERE IdIndustry = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createIndustry($postData)
    {
        $industryName = trim($postData['industry_name']);
        $industryDescription = trim($postData['industry_description']);
        $industryImage = trim($postData['industry_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($industryName)) {
            throw new Exception('Industry name is required');
        }

        // Convert empty strings to NULL for optional fields
        $industryDescription = empty($industryDescription) ? null : $industryDescription;
        $industryImage = empty($industryImage) ? null : $industryImage;

        $stmt = $this->conn->prepare("INSERT INTO Company_Industries (IndustryName, IndustryDescription, IndustryImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$industryName, $industryDescription, $industryImage, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create industry');
        }

        return $this->conn->lastInsertId();
    }

    public function updateIndustry($postData)
    {
        $id = (int)$postData['industry_id'];
        $industryName = trim($postData['industry_name']);
        $industryDescription = trim($postData['industry_description']);
        $industryImage = trim($postData['industry_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($industryName)) {
            throw new Exception('Industry name is required');
        }

        // Convert empty strings to NULL for optional fields
        $industryDescription = empty($industryDescription) ? null : $industryDescription;
        $industryImage = empty($industryImage) ? null : $industryImage;

        $sql = "UPDATE Company_Industries SET IndustryName = ?, IndustryDescription = ?, IndustryImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdIndustry = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$industryName, $industryDescription, $industryImage, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update industry');
        }

        return true;
    }

    public function deleteIndustry($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Industries SET Status = 0, UpdatedTimestamp = NOW() WHERE IdIndustry = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete industry');
        }

        return true;
    }

    public function getActiveIndustries()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Industries WHERE Status = 1 ORDER BY DisplayOrder ASC, IndustryName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextIndustryNumber()
    {
        $imgDir = '../../assets/img/';
        $existingNumbers = [];
        
        // Scan existing industry files
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if (preg_match('/^industry(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
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