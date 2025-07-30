<?php

class CompanyInfo
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getCompanyInfo()
    {
        $stmt = $this->conn->prepare("SELECT * FROM company_info WHERE Status = 1 ORDER BY IdCompany ASC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCompanyInfoById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM company_info WHERE IdCompany = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCompanyInfo($postData)
    {
        $id = (int)$postData['company_id'];
        $companyName = trim($postData['company_name']);
        $tagline = trim($postData['tagline']);
        $description = trim($postData['description']);
        $mission = trim($postData['mission']);
        $vision = trim($postData['vision']);
        $aboutImage = trim($postData['about_image']);
        $logoImage = trim($postData['logo_image']);
        $faviconImage = trim($postData['favicon_image']);
        $status = (int)($postData['status'] ?? 1);

        if (empty($companyName)) {
            throw new Exception('Company name is required');
        }

        $sql = "UPDATE company_info SET CompanyName = ?, Tagline = ?, Description = ?, Mission = ?, Vision = ?, AboutImage = ?, LogoImage = ?, FaviconImage = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdCompany = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$companyName, $tagline, $description, $mission, $vision, $aboutImage, $logoImage, $faviconImage, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update company information');
        }

        return true;
    }

    public function createCompanyInfo($postData)
    {
        $companyName = trim($postData['company_name']);
        $tagline = trim($postData['tagline']);
        $description = trim($postData['description']);
        $mission = trim($postData['mission']);
        $vision = trim($postData['vision']);
        $aboutImage = trim($postData['about_image']);
        $logoImage = trim($postData['logo_image']);
        $faviconImage = trim($postData['favicon_image']);
        $status = (int)($postData['status'] ?? 1);

        if (empty($companyName)) {
            throw new Exception('Company name is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO company_info (CompanyName, Tagline, Description, Mission, Vision, AboutImage, LogoImage, FaviconImage, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$companyName, $tagline, $description, $mission, $vision, $aboutImage, $logoImage, $faviconImage, $status]);

        if (!$result) {
            throw new Exception('Failed to create company information');
        }

        return $this->conn->lastInsertId();
    }
} 