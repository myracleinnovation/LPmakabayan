<?php
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
     * Get total process count
     */
    public function getTotalProcess() {
        try {
            $sql = "SELECT COUNT(*) as total FROM Company_Process";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total processes: " . $e->getMessage());
            return 0;
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
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Process query result for ID $id: " . ($result ? 'found' : 'not found'));
            return $result;
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
            // Convert empty strings to NULL for optional fields
            $processDescription = empty(trim($data['process_description'])) ? null : trim($data['process_description']);
            $processImage = empty(trim($data['process_image'])) ? null : trim($data['process_image']);
            
            $sql = "INSERT INTO Company_Process (ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder, Status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['process_title'],
                $processDescription,
                $processImage,
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
            // Convert empty strings to NULL for optional fields
            $processDescription = empty(trim($data['process_description'])) ? null : trim($data['process_description']);
            $processImage = empty(trim($data['process_image'])) ? null : trim($data['process_image']);
            
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
                $processDescription,
                $processImage,
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

    public function getNextProcessNumber()
    {
        $imgDir = '../../assets/img/';
        $existingNumbers = [];
        
        // Scan existing process files
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if (preg_match('/^process(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
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
?> 