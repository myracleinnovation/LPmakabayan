<?php

class SystemSettings
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllSettings($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM System_Settings";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE SettingKey LIKE :search OR SettingValue LIKE :search OR SettingDescription LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'SettingKey';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY SettingKey ASC";
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

    public function getSettingById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM System_Settings WHERE IdSetting = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSettingByKey($key)
    {
        $stmt = $this->conn->prepare("SELECT * FROM System_Settings WHERE SettingKey = ? AND Status = 1");
        $stmt->execute([$key]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSetting($postData)
    {
        $settingKey = trim($postData['setting_key']);
        $settingValue = trim($postData['setting_value']);
        $settingDescription = trim($postData['setting_description']);
        $settingType = trim($postData['setting_type'] ?? 'text');
        $status = (int)($postData['status'] ?? 1);

        if (empty($settingKey) || empty($settingValue)) {
            throw new Exception('Setting key and value are required');
        }

        $stmt = $this->conn->prepare("INSERT INTO System_Settings (SettingKey, SettingValue, SettingDescription, SettingType, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$settingKey, $settingValue, $settingDescription, $settingType, $status]);

        if (!$result) {
            throw new Exception('Failed to create setting');
        }

        return $this->conn->lastInsertId();
    }

    public function updateSetting($postData)
    {
        $id = (int)$postData['setting_id'];
        $settingKey = trim($postData['setting_key']);
        $settingValue = trim($postData['setting_value']);
        $settingDescription = trim($postData['setting_description']);
        $settingType = trim($postData['setting_type'] ?? 'text');
        $status = (int)($postData['status'] ?? 1);

        if (empty($settingKey) || empty($settingValue)) {
            throw new Exception('Setting key and value are required');
        }

        $sql = "UPDATE System_Settings SET SettingKey = ?, SettingValue = ?, SettingDescription = ?, SettingType = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdSetting = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$settingKey, $settingValue, $settingDescription, $settingType, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update setting');
        }

        return true;
    }

    public function deleteSetting($id)
    {
        $stmt = $this->conn->prepare("UPDATE System_Settings SET Status = 0, UpdatedTimestamp = NOW() WHERE IdSetting = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete setting');
        }

        return true;
    }

    public function getActiveSettings()
    {
        $stmt = $this->conn->prepare("SELECT * FROM System_Settings WHERE Status = 1 ORDER BY SettingKey ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 