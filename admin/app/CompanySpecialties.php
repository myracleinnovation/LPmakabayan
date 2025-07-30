<?php

class CompanySpecialties
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllSpecialties($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Company_Specialties";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE SpecialtyName LIKE :search OR SpecialtyDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, SpecialtyName ASC";
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

    public function getTotalSpecialties($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM Company_Specialties";
        
        if (!empty($search)) {
            $sql .= " WHERE SpecialtyName LIKE :search OR SpecialtyDescription LIKE :search";
        }

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    public function getSpecialtyById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Specialties WHERE IdSpecialty = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSpecialty($postData)
    {
        $specialtyName = trim($postData['specialty_name']);
        $specialtyDescription = trim($postData['specialty_description']);
        $specialtyImage = trim($postData['specialty_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($specialtyName)) {
            throw new Exception('Specialty name is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO Company_Specialties (SpecialtyName, SpecialtyDescription, SpecialtyImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$specialtyName, $specialtyDescription, $specialtyImage, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create specialty');
        }

        return $this->conn->lastInsertId();
    }

    public function updateSpecialty($postData)
    {
        $id = (int)$postData['specialty_id'];
        $specialtyName = trim($postData['specialty_name']);
        $specialtyDescription = trim($postData['specialty_description']);
        $specialtyImage = trim($postData['specialty_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($specialtyName)) {
            throw new Exception('Specialty name is required');
        }

        $sql = "UPDATE Company_Specialties SET SpecialtyName = ?, SpecialtyDescription = ?, SpecialtyImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdSpecialty = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$specialtyName, $specialtyDescription, $specialtyImage, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update specialty');
        }

        return true;
    }

    public function deleteSpecialty($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Specialties SET Status = 0, UpdatedTimestamp = NOW() WHERE IdSpecialty = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete specialty');
        }

        return true;
    }

    public function getActiveSpecialties()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Specialties WHERE Status = 1 ORDER BY DisplayOrder ASC, SpecialtyName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 