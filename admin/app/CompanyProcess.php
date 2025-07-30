<?php

class CompanyProcess
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllProcesses($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Company_Process";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE ProcessTitle LIKE :search OR ProcessDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, ProcessTitle ASC";
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

    public function getProcessById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Process WHERE IdProcess = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProcess($postData)
    {
        $processTitle = trim($postData['process_title']);
        $processDescription = trim($postData['process_description']);
        $processImage = trim($postData['process_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($processTitle)) {
            throw new Exception('Process title is required');
        }

        $stmt = $this->conn->prepare("INSERT INTO Company_Process (ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$processTitle, $processDescription, $processImage, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create process');
        }

        return $this->conn->lastInsertId();
    }

    public function updateProcess($postData)
    {
        $id = (int)$postData['process_id'];
        $processTitle = trim($postData['process_title']);
        $processDescription = trim($postData['process_description']);
        $processImage = trim($postData['process_image']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($processTitle)) {
            throw new Exception('Process title is required');
        }

        $sql = "UPDATE Company_Process SET ProcessTitle = ?, ProcessDescription = ?, ProcessImage = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdProcess = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$processTitle, $processDescription, $processImage, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update process');
        }

        return true;
    }

    public function deleteProcess($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Process SET Status = 0, UpdatedTimestamp = NOW() WHERE IdProcess = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete process');
        }

        return true;
    }

    public function getActiveProcesses()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Process WHERE Status = 1 ORDER BY DisplayOrder ASC, ProcessTitle ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 