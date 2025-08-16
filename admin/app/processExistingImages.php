<?php
/**
 * Utility script to process and compress existing images
 * Run this script to compress all existing images in the assets/img folder
 */

require_once 'ImageProcessor.php';
require_once '../../app/Db.php';

class ImageBatchProcessor {
    private $imageProcessor;
    private $sourceDir;
    private $backupDir;
    private $processedCount = 0;
    private $errorCount = 0;
    private $totalSizeSaved = 0;
    
    public function __construct($sourceDir = '../../assets/img/', $backupDir = '../../assets/img/backup/') {
        $this->sourceDir = $sourceDir;
        $this->backupDir = $backupDir;
        $this->imageProcessor = new ImageProcessor(1920, 1080, 85);
        
        // Create backup directory
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Process all images in the source directory
     */
    public function processAllImages() {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($this->sourceDir);
        
        echo "Starting image processing...\n";
        echo "Source directory: {$this->sourceDir}\n";
        echo "Backup directory: {$this->backupDir}\n\n";
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) continue;
            
            $this->processImage($file);
        }
        
        $this->printSummary();
    }
    
    /**
     * Process a single image
     */
    private function processImage($filename) {
        $sourcePath = $this->sourceDir . $filename;
        $backupPath = $this->backupDir . $filename;
        
        echo "Processing: {$filename}";
        
        try {
            // Create backup
            if (!copy($sourcePath, $backupPath)) {
                throw new Exception("Failed to create backup");
            }
            
            // Get original file size
            $originalSize = filesize($sourcePath);
            
            // Create temporary file array for ImageProcessor
            $tempFile = [
                'name' => $filename,
                'type' => mime_content_type($sourcePath),
                'tmp_name' => $sourcePath,
                'error' => UPLOAD_ERR_OK,
                'size' => $originalSize
            ];
            
            // Process image
            $result = $this->imageProcessor->processImage($tempFile, $this->sourceDir, pathinfo($filename, PATHINFO_FILENAME));
            
            if ($result['success']) {
                $processedSize = $result['processed_size'];
                $sizeSaved = $originalSize - $processedSize;
                $this->totalSizeSaved += $sizeSaved;
                $this->processedCount++;
                
                echo " ✓ Processed successfully\n";
                echo "   Original: " . $this->formatBytes($originalSize) . " → Processed: " . $this->formatBytes($processedSize);
                echo " (Saved: " . $this->formatBytes($sizeSaved) . ")\n";
                echo "   Dimensions: " . $result['original_dimensions'] . " → " . $result['processed_dimensions'] . "\n";
                echo "   Compression: " . $result['compression_ratio'] . "%\n\n";
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->errorCount++;
            echo " ✗ Error: " . $e->getMessage() . "\n\n";
            
            // Restore from backup if processing failed
            if (file_exists($backupPath)) {
                copy($backupPath, $sourcePath);
                echo "   Restored original file from backup\n\n";
            }
        }
    }
    
    /**
     * Process specific image files
     */
    public function processSpecificImages($filenames) {
        echo "Processing specific images...\n\n";
        
        foreach ($filenames as $filename) {
            if (file_exists($this->sourceDir . $filename)) {
                $this->processImage($filename);
            } else {
                echo "File not found: {$filename}\n\n";
            }
        }
        
        $this->printSummary();
    }
    
    /**
     * Create thumbnails for all images
     */
    public function createThumbnails($thumbWidth = 300, $thumbHeight = 200) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($this->sourceDir);
        $thumbnailDir = $this->sourceDir . 'thumbnails/';
        
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        echo "Creating thumbnails...\n";
        echo "Thumbnail directory: {$thumbnailDir}\n\n";
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) continue;
            
            $sourcePath = $this->sourceDir . $file;
            $thumbPath = $thumbnailDir . 'thumb_' . $file;
            
            echo "Creating thumbnail for: {$file}";
            
            $result = $this->imageProcessor->createThumbnail($sourcePath, $thumbPath, $thumbWidth, $thumbHeight);
            
            if ($result['success']) {
                echo " ✓ Thumbnail created\n";
            } else {
                echo " ✗ Error: " . $result['message'] . "\n";
            }
        }
        
        echo "\nThumbnail creation completed!\n";
    }
    
    /**
     * Print processing summary
     */
    private function printSummary() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "PROCESSING SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        echo "Total images processed: {$this->processedCount}\n";
        echo "Errors encountered: {$this->errorCount}\n";
        echo "Total size saved: " . $this->formatBytes($this->totalSizeSaved) . "\n";
        echo "Backup files created in: {$this->backupDir}\n";
        echo str_repeat("=", 50) . "\n";
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Usage examples
if (php_sapi_name() === 'cli') {
    // Command line usage
    $processor = new ImageBatchProcessor();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'all':
                $processor->processAllImages();
                break;
            case 'thumbnails':
                $processor->createThumbnails();
                break;
            case 'specific':
                if (isset($argv[2])) {
                    $filenames = explode(',', $argv[2]);
                    $processor->processSpecificImages($filenames);
                } else {
                    echo "Usage: php processExistingImages.php specific filename1.jpg,filename2.png\n";
                }
                break;
            default:
                echo "Usage:\n";
                echo "  php processExistingImages.php all                    - Process all images\n";
                echo "  php processExistingImages.php thumbnails             - Create thumbnails\n";
                echo "  php processExistingImages.php specific file1,file2   - Process specific files\n";
        }
    } else {
        echo "Usage:\n";
        echo "  php processExistingImages.php all                    - Process all images\n";
        echo "  php processExistingImages.php thumbnails             - Create thumbnails\n";
        echo "  php processExistingImages.php specific file1,file2   - Process specific files\n";
    }
} else {
    // Web interface usage
    echo "<h2>Image Processing Utility</h2>";
    echo "<p>This script can be run from command line to process existing images.</p>";
    echo "<p>Usage examples:</p>";
    echo "<ul>";
    echo "<li><code>php processExistingImages.php all</code> - Process all images</li>";
    echo "<li><code>php processExistingImages.php thumbnails</code> - Create thumbnails</li>";
    echo "<li><code>php processExistingImages.php specific file1.jpg,file2.png</code> - Process specific files</li>";
    echo "</ul>";
}
?>
