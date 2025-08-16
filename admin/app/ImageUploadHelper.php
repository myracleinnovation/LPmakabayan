<?php

require_once 'ImageProcessor.php';

class ImageUploadHelper {
    private $imageProcessor;
    private $uploadDir;
    
    public function __construct($uploadDir = '../../assets/img/', $maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        $this->uploadDir = $uploadDir;
        $this->imageProcessor = new ImageProcessor($maxWidth, $maxHeight, $quality);
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Process and upload an image with automatic optimization
     * @param array $file $_FILES array element
     * @param string $prefix Prefix for filename (e.g., 'about', 'logo', 'project')
     * @param int $nextNumber Next number for the file
     * @param string $oldImage Old image filename to delete (optional)
     * @return array ['success' => bool, 'filename' => string, 'message' => string]
     */
    public function processAndUpload($file, $prefix, $nextNumber, $oldImage = null) {
        try {
            // Delete old image if it exists
            if (!empty($oldImage)) {
                $oldFile = $this->uploadDir . $oldImage;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // Generate filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $prefix . $nextNumber . '.' . $extension;
            
            // Process image using ImageProcessor
            $result = $this->imageProcessor->processImage($file, $this->uploadDir, $prefix . $nextNumber);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'filename' => $result['filename'],
                    'message' => $result['message'],
                    'original_size' => $result['original_size'],
                    'processed_size' => $result['processed_size'],
                    'compression_ratio' => $result['compression_ratio']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing image: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process multiple images (for projects, etc.)
     * @param array $files Array of $_FILES elements
     * @param string $prefix Prefix for filename
     * @param int $startNumber Starting number for files
     * @return array ['success' => bool, 'filenames' => array, 'message' => string]
     */
    public function processMultipleImages($files, $prefix, $startNumber = 1) {
        $results = [];
        $filenames = [];
        $totalOriginalSize = 0;
        $totalProcessedSize = 0;
        
        foreach ($files as $index => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $result = $this->processAndUpload($file, $prefix, $startNumber + $index);
                
                if ($result['success']) {
                    $filenames[] = $result['filename'];
                    $totalOriginalSize += $result['original_size'];
                    $totalProcessedSize += $result['processed_size'];
                } else {
                    $results[] = $result['message'];
                }
            }
        }
        
        if (empty($filenames)) {
            return [
                'success' => false,
                'message' => 'No images were successfully processed. ' . implode(', ', $results)
            ];
        }
        
        $totalCompression = round((($totalOriginalSize - $totalProcessedSize) / $totalOriginalSize) * 100, 2);
        
        return [
            'success' => true,
            'filenames' => $filenames,
            'message' => count($filenames) . ' images processed successfully. Total compression: ' . $totalCompression . '%',
            'total_original_size' => $totalOriginalSize,
            'total_processed_size' => $totalProcessedSize,
            'total_compression' => $totalCompression
        ];
    }
    
    /**
     * Validate image file
     * @param array $file $_FILES array element
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateImage($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            return ['valid' => false, 'message' => $errorMessages[$file['error']] ?? 'Unknown upload error'];
        }
        
        // Check file size (100MB limit)
        $maxFileSize = 100 * 1024 * 1024; // 100MB in bytes
        if ($file['size'] > $maxFileSize) {
            return ['valid' => false, 'message' => 'File size exceeds 100MB limit'];
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'];
        }
        
        // Validate file type using getimagesize
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'Invalid image file'];
        }
        
        return ['valid' => true, 'message' => 'Image is valid'];
    }
    
    /**
     * Set upload directory
     */
    public function setUploadDir($uploadDir) {
        $this->uploadDir = $uploadDir;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Set image processor settings
     */
    public function setImageProcessorSettings($maxWidth, $maxHeight, $quality) {
        $this->imageProcessor->setMaxDimensions($maxWidth, $maxHeight);
        $this->imageProcessor->setQuality($quality);
    }
}
?>
