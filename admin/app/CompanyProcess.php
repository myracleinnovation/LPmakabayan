<?php
require_once '../../app/Db.php';

class CompanyProcess {
    private $db;
    
    public function __construct() {
        $this->db = Db::connect();
    }
    
    /**
     * Get all process steps
     */
    public function getAllProcesses() {
        try {
            $sql = "SELECT * FROM Company_Process ORDER BY DisplayOrder ASC, ProcessTitle ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all processes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get process by ID
     */
    public function getProcessById($id) {
        try {
            $sql = "SELECT * FROM Company_Process WHERE IdProcess = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting process by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new process
     */
    public function createProcess($data) {
        try {
            $sql = "INSERT INTO Company_Process (ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder, Status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['process_title'],
                $data['process_description'],
                $data['process_image'],
                $data['display_order'],
                $data['status']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating process: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update process
     */
    public function updateProcess($data) {
        try {
            $sql = "UPDATE Company_Process SET 
                    ProcessTitle = ?, 
                    ProcessDescription = ?, 
                    ProcessImage = ?, 
                    DisplayOrder = ?, 
                    Status = ?,
                    UpdatedTimestamp = CURRENT_TIMESTAMP
                    WHERE IdProcess = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['process_title'],
                $data['process_description'],
                $data['process_image'],
                $data['display_order'],
                $data['status'],
                $data['process_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating process: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete process
     */
    public function deleteProcess($id) {
        try {
            $sql = "DELETE FROM Company_Process WHERE IdProcess = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting process: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active processes
     */
    public function getActiveProcesses() {
        try {
            $sql = "SELECT * FROM Company_Process WHERE Status = 1 ORDER BY DisplayOrder ASC, ProcessTitle ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active processes: " . $e->getMessage());
            return false;
        }
    }
}
?> 