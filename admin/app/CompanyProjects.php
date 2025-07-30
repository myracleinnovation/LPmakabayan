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

    public function getProjectById($id)
    {
        $stmt = $this->conn->prepare("SELECT p.*, pc.CategoryName FROM Company_Projects p LEFT JOIN Project_Categories pc ON p.ProjectCategoryId = pc.IdCategory WHERE p.IdProject = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProject($postData)
    {
        $projectTitle = trim($postData['project_title']);
        $projectDescription = trim($postData['project_description']);
        $projectOwner = trim($postData['project_owner']);
        $projectLocation = trim($postData['project_location']);
        $projectArea = (float)($postData['project_area'] ?? 0);
        $projectValue = (float)($postData['project_value'] ?? 0);
        $turnoverDate = $postData['turnover_date'];
        $projectCategoryId = (int)($postData['project_category_id'] ?? 0);
        $projectImage1 = trim($postData['project_image1'] ?? '');
        $projectImage2 = trim($postData['project_image2'] ?? '');
        $projectImage3 = trim($postData['project_image3'] ?? '');
        $projectImage4 = trim($postData['project_image4'] ?? '');
        $projectImage5 = trim($postData['project_image5'] ?? '');
        $projectImage6 = trim($postData['project_image6'] ?? '');
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($projectTitle)) {
            throw new Exception('Project title is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO Company_Projects (ProjectTitle, ProjectDescription, ProjectOwner, ProjectLocation, ProjectArea, ProjectValue, TurnoverDate, ProjectCategoryId, ProjectImage1, ProjectImage2, ProjectImage3, ProjectImage4, ProjectImage5, ProjectImage6, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $projectImage3, $projectImage4, $projectImage5, $projectImage6, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create project');
        }

        return $this->conn->lastInsertId();
    }

    public function updateProject($postData)
    {
        $id = (int)$postData['project_id'];
        $projectTitle = trim($postData['project_title']);
        $projectDescription = trim($postData['project_description']);
        $projectOwner = trim($postData['project_owner']);
        $projectLocation = trim($postData['project_location']);
        $projectArea = (float)($postData['project_area'] ?? 0);
        $projectValue = (float)($postData['project_value'] ?? 0);
        $turnoverDate = $postData['turnover_date'];
        $projectCategoryId = (int)($postData['project_category_id'] ?? 0);
        $projectImage1 = trim($postData['project_image1'] ?? '');
        $projectImage2 = trim($postData['project_image2'] ?? '');
        $projectImage3 = trim($postData['project_image3'] ?? '');
        $projectImage4 = trim($postData['project_image4'] ?? '');
        $projectImage5 = trim($postData['project_image5'] ?? '');
        $projectImage6 = trim($postData['project_image6'] ?? '');
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($projectTitle)) {
            throw new Exception('Project title is required');
        }

        $sql = "UPDATE Company_Projects SET ProjectTitle = ?, ProjectDescription = ?, ProjectOwner = ?, ProjectLocation = ?, ProjectArea = ?, ProjectValue = ?, TurnoverDate = ?, ProjectCategoryId = ?, ProjectImage1 = ?, ProjectImage2 = ?, ProjectImage3 = ?, ProjectImage4 = ?, ProjectImage5 = ?, ProjectImage6 = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdProject = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$projectTitle, $projectDescription, $projectOwner, $projectLocation, $projectArea, $projectValue, $turnoverDate, $projectCategoryId, $projectImage1, $projectImage2, $projectImage3, $projectImage4, $projectImage5, $projectImage6, $displayOrder, $status, $id]);

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
} 