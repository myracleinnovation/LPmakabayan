<?php

class CompanyFeatures
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllFeatures($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Company_Features";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE FeatureTitle LIKE :search OR FeatureDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, FeatureTitle ASC";
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

    public function getFeatureById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Features WHERE IdFeature = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createFeature($postData)
    {
        $featureTitle = trim($postData['feature_title']);
        $featureDescription = trim($postData['feature_description']);
        $featureImage = trim($postData['feature_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($featureTitle)) {
            throw new Exception('Feature title is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO Company_Features (FeatureTitle, FeatureDescription, FeatureImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$featureTitle, $featureDescription, $featureImage, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create feature');
        }

        return $this->conn->lastInsertId();
    }

    public function updateFeature($postData)
    {
        $id = (int)$postData['feature_id'];
        $featureTitle = trim($postData['feature_title']);
        $featureDescription = trim($postData['feature_description']);
        $featureImage = trim($postData['feature_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($featureTitle)) {
            throw new Exception('Feature title is required');
        }

        $sql = "UPDATE Company_Features SET FeatureTitle = ?, FeatureDescription = ?, FeatureImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdFeature = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$featureTitle, $featureDescription, $featureImage, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update feature');
        }

        return true;
    }

    public function deleteFeature($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Features SET Status = 0, UpdatedTimestamp = NOW() WHERE IdFeature = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete feature');
        }

        return true;
    }

    public function getActiveFeatures()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Features WHERE Status = 1 ORDER BY DisplayOrder ASC, FeatureTitle ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 