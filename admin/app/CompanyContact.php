<?php

class CompanyContact
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllContacts($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Company_Contact";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE ContactValue LIKE :search OR ContactLabel LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'DisplayOrder';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY DisplayOrder ASC, ContactType ASC";
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

    public function getTotalContacts($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM Company_Contact";
        
        if (!empty($search)) {
            $sql .= " WHERE ContactValue LIKE :search OR ContactLabel LIKE :search";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        } else {
            $stmt = $this->conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function getContactById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Contact WHERE IdContact = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createContact($postData)
    {
        $contactType = trim($postData['contact_type']);
        $contactValue = trim($postData['contact_value']);
        $contactLabel = trim($postData['contact_label']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($contactType) || empty($contactValue)) {
            throw new Exception('Contact type and value are required');
        }

        // Convert empty strings to NULL for optional fields
        $contactLabel = empty($contactLabel) ? null : $contactLabel;

        $stmt = $this->conn->prepare("INSERT INTO Company_Contact (ContactType, ContactValue, ContactLabel, DisplayOrder, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$contactType, $contactValue, $contactLabel, $displayOrder, $status]);

        if (!$result) {
            throw new Exception('Failed to create contact');
        }

        return $this->conn->lastInsertId();
    }

    public function updateContact($postData)
    {
        $id = (int)$postData['contact_id'];
        $contactType = trim($postData['contact_type']);
        $contactValue = trim($postData['contact_value']);
        $contactLabel = trim($postData['contact_label']);
        $displayOrder = (int)($postData['display_order'] ?? 0);
        $status = (int)($postData['status'] ?? 1);

        if (empty($contactType) || empty($contactValue)) {
            throw new Exception('Contact type and value are required');
        }

        // Convert empty strings to NULL for optional fields
        $contactLabel = empty($contactLabel) ? null : $contactLabel;

        $sql = "UPDATE Company_Contact SET ContactType = ?, ContactValue = ?, ContactLabel = ?, DisplayOrder = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdContact = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$contactType, $contactValue, $contactLabel, $displayOrder, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update contact');
        }

        return true;
    }

    public function deleteContact($id)
    {
        $stmt = $this->conn->prepare("UPDATE Company_Contact SET Status = 0, UpdatedTimestamp = NOW() WHERE IdContact = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete contact');
        }

        return true;
    }

    public function getActiveContacts()
    {
        $stmt = $this->conn->prepare("SELECT * FROM Company_Contact WHERE Status = 1 ORDER BY DisplayOrder ASC, ContactType ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 