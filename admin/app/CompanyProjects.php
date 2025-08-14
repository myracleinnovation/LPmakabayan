<?php

class CompanyProjects
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllProjects($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT p.*, pc.CategoryName FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE p.ProjectTitle LIKE :search OR p.ProjectDescription LIKE :search OR p.ProjectOwner LIKE :search OR p.ProjectLocation LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'p.DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY p.DisplayOrder ASC, p.CreatedTimestamp DESC";
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

    public function getTotalProjects($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory";
        
        if (!empty($search)) {
            $sql .= " WHERE p.ProjectTitle LIKE :search OR p.ProjectDescription LIKE :search OR p.ProjectOwner LIKE :search OR p.ProjectLocation LIKE :search";
        }

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    public function getProjectById($id)
    {
        $stmt = $this->conn->prepare("SELECT p.*, pc.CategoryName FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory WHERE p.IdProject = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProject($postData)
    {
        $projectTitle = trim($postData['project_title'] ?? '');
        $projectDescription = trim($postData['project_description'] ?? '');
        $projectOwner = trim($postData['project_owner'] ?? '');
        $projectLocation = trim($postData['project_location'] ?? '');
        $projectArea = (float)($postData['project_area'] ?? 0);
        $projectValue = (float)($postData['project_value'] ?? 0);
        $turnoverDate = $postData['turnover_date'] ?? '';
        $projectCategoryId = (int)($postData['project_category_id'] ?? 0);
        $projectImage1 = trim($postData['project_image1'] ?? '');
        $projectImage2 = trim($postData['project_image2'] ?? '');
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($projectTitle)) {
            throw new Exception('Project title is required');
        }

        // Convert empty strings to NULL for optional fields
        $projectDescription = empty($projectDescription) ? null : $projectDescription;
        $projectOwner = empty($projectOwner) ? null : $projectOwner;
        $projectLocation = empty($projectLocation) ? null : $projectLocation;
        $projectArea = ($projectArea <= 0) ? null : $projectArea;
        $projectValue = ($projectValue <= 0) ? null : $projectValue;
        $turnoverDate = empty($turnoverDate) ? null : $turnoverDate;
        $projectCategoryId = ($projectCategoryId <= 0) ? null : $projectCategoryId;
        $projectImage1 = empty($projectImage1) ? null : $projectImage1;
        $projectImage2 = empty($projectImage2) ? null : $projectImage2;

        $stmt = $this->conn->prepare("INSERT INTO Company_Projects (ProjectTitle, ProjectDescription, ProjectOwner, ProjectLocation, ProjectArea, ProjectValue, TurnoverDate, ProjectCategoryId, ProjectImage1, ProjectImage2, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create project');
        }

        return $this->conn->lastInsertId();
    }

    public function updateProject($postData)
    {
        $id = (int)$postData['project_id'];
        $projectTitle = trim($postData['project_title'] ?? '');
        $projectDescription = trim($postData['project_description'] ?? '');
        $projectOwner = trim($postData['project_owner'] ?? '');
        $projectLocation = trim($postData['project_location'] ?? '');
        $projectArea = (float)($postData['project_area'] ?? 0);
        $projectValue = (float)($postData['project_value'] ?? 0);
        $turnoverDate = $postData['turnover_date'] ?? '';
        $projectCategoryId = (int)($postData['project_category_id'] ?? 0);
        $projectImage1 = trim($postData['project_image1'] ?? '');
        $projectImage2 = trim($postData['project_image2'] ?? '');
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($projectTitle)) {
            throw new Exception('Project title is required');
        }

        // Convert empty strings to NULL for optional fields
        $projectDescription = empty($projectDescription) ? null : $projectDescription;
        $projectOwner = empty($projectOwner) ? null : $projectOwner;
        $projectLocation = empty($projectLocation) ? null : $projectLocation;
        $projectArea = ($projectArea <= 0) ? null : $projectArea;
        $projectValue = ($projectValue <= 0) ? null : $projectValue;
        $turnoverDate = empty($turnoverDate) ? null : $turnoverDate;
        $projectCategoryId = ($projectCategoryId <= 0) ? null : $projectCategoryId;
        $projectImage1 = empty($projectImage1) ? null : $projectImage1;
        $projectImage2 = empty($projectImage2) ? null : $projectImage2;

        $sql = "UPDATE Company_Projects SET ProjectTitle = ?, ProjectDescription = ?, ProjectOwner = ?, ProjectLocation = ?, ProjectArea = ?, ProjectValue = ?, TurnoverDate = ?, ProjectCategoryId = ?, ProjectImage1 = ?, ProjectImage2 = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdProject = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update project');
        }

        return true;
    }

    public function deleteProject($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Projects SET Status = 0, UpdatedTimestamp = NOW() WHERE IdProject = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete project');
        }

        return true;
    }

    public function getActiveProjects()
    {
        $stmt = $this->conn->prepare("SELECT p.*, pc.CategoryName FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory WHERE p.Status = 1 ORDER BY p.DisplayOrder ASC, p.CreatedTimestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectCategories()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Project_Categories WHERE Status = 1 ORDER BY DisplayOrder ASC, CategoryName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextProjectId()
    {
        $stmt = $this->conn->prepare("SELECT MAX(IdProject) as max_id FROM Company_Projects");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_id'] ?? 0) + 1;
    }

    public function getNextProjectNumber()
    {
        $imgDir = '../../assets/img/';
        $existingNumbers = [];
        
        // Scan existing project files
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if (preg_match('/^project(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
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