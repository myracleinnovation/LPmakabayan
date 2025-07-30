<?php

class AdminAccounts
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllAdmins($search = '', $start = 0, $length = 25, $order = [])
    {
        $sql = "SELECT * FROM Admin_Accounts";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE Username LIKE :search OR Email LIKE :search OR FullName LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($order)) {
            $column = $order[0]['column'] ?? 'Username';
            $dir = $order[0]['dir'] ?? 'asc';
            $sql .= " ORDER BY $column $dir";
        } else {
            $sql .= " ORDER BY Username ASC";
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

    public function getAdminById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Admin_Accounts WHERE IdAdmin = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM Admin_Accounts WHERE Username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND IdAdmin != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM Admin_Accounts WHERE Email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND IdAdmin != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function createAdmin($postData)
    {
        $username = trim($postData['username']);
        $password = $postData['password'];
        $email = trim($postData['email']);
        $fullName = trim($postData['full_name']);
        $role = $postData['role'] ?? 'admin';
        $status = (int)($postData['status'] ?? 1);

        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required');
        }

        if ($this->usernameExists($username)) {
            throw new Exception('Username already exists');
        }

        if (!empty($email) && $this->emailExists($email)) {
            throw new Exception('Email already exists');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO Admin_Accounts (Username, Password, Email, FullName, Role, Status, CreatedTimestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$username, $hashedPassword, $email, $fullName, $role, $status]);

        if (!$result) {
            throw new Exception('Failed to create admin account');
        }

        return $this->conn->lastInsertId();
    }

    public function updateAdmin($postData)
    {
        $id = (int)$postData['admin_id'];
        $username = trim($postData['username']);
        $email = trim($postData['email']);
        $fullName = trim($postData['full_name']);
        $role = $postData['role'] ?? 'admin';
        $status = (int)($postData['status'] ?? 1);

        if (empty($username)) {
            throw new Exception('Username is required');
        }

        if ($this->usernameExists($username, $id)) {
            throw new Exception('Username already exists');
        }

        if (!empty($email) && $this->emailExists($email, $id)) {
            throw new Exception('Email already exists');
        }

        $sql = "UPDATE Admin_Accounts SET Username = ?, Email = ?, FullName = ?, Role = ?, Status = ?, UpdatedTimestamp = NOW() WHERE IdAdmin = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$username, $email, $fullName, $role, $status, $id]);

        if (!$result) {
            throw new Exception('Failed to update admin account');
        }

        return true;
    }

    public function updatePassword($adminId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("UPDATE Admin_Accounts SET Password = ?, UpdatedTimestamp = NOW() WHERE IdAdmin = ?");
        $result = $stmt->execute([$hashedPassword, $adminId]);

        if (!$result) {
            throw new Exception('Failed to update password');
        }

        return true;
    }

    public function deleteAdmin($id)
    {
        $stmt = $this->conn->prepare("UPDATE Admin_Accounts SET Status = 0, UpdatedTimestamp = NOW() WHERE IdAdmin = ?");
        $result = $stmt->execute([$id]);

        if (!$result) {
            throw new Exception('Failed to delete admin account');
        }

        return true;
    }

    public function getActiveAdmins()
    {
        $stmt = $this->conn->prepare("SELECT IdAdmin, Username, Email, FullName, Role FROM Admin_Accounts WHERE Status = 1 ORDER BY Username ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 