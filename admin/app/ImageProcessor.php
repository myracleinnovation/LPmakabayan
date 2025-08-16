<?php

class ImageProcessor {
    private $maxWidth = 1920;
    private $maxHeight = 1080;
    private $quality = 85;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 50 * 1024 * 1024; // 50MB
    
    public function __construct($maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->quality = $quality;
    }
    
    /**
     * Process and optimize an uploaded image
     * @param array $file $_FILES array element
     * @param string $uploadDir Directory to save the processed image
     * @param string $filename Desired filename (without extension)
     * @return array ['success' => bool, 'filename' => string, 'message' => string, 'original_size' => int, 'processed_size' => int]
     */
    public function processImage($file, $uploadDir, $filename) {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            $originalSize = $file['size'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finalFilename = $filename . '.' . $extension;
            $filepath = $uploadDir . $finalFilename;
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Get image info
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'message' => 'Invalid image file'];
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $imageType = $imageInfo[2];
            
            // Calculate new dimensions while maintaining aspect ratio
            $dimensions = $this->calculateDimensions($originalWidth, $originalHeight);
            $newWidth = $dimensions['width'];
            $newHeight = $dimensions['height'];
            
            // Create image resource
            $sourceImage = $this->createImageResource($file['tmp_name'], $imageType);
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create new image with calculated dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
                $this->preserveTransparency($newImage, $sourceImage, $imageType);
            }
            
            // Resize image
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save processed image
            $saved = $this->saveImage($newImage, $filepath, $imageType, $extension);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            
            if (!$saved) {
                return ['success' => false, 'message' => 'Failed to save processed image'];
            }
            
            $processedSize = filesize($filepath);
            $compressionRatio = round((($originalSize - $processedSize) / $originalSize) * 100, 2);
            
            return [
                'success' => true,
                'filename' => $finalFilename,
                'message' => "Image processed successfully. Original: {$this->formatBytes($originalSize)}, Processed: {$this->formatBytes($processedSize)}, Compression: {$compressionRatio}%",
                'original_size' => $originalSize,
                'processed_size' => $processedSize,
                'compression_ratio' => $compressionRatio,
                'original_dimensions' => "{$originalWidth}x{$originalHeight}",
                'processed_dimensions' => "{$newWidth}x{$newHeight}"
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error processing image: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
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
            return ['success' => false, 'message' => $errorMessages[$file['error']] ?? 'Unknown upload error'];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'File size exceeds ' . $this->formatBytes($this->maxFileSize) . ' limit'];
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedExtensions)];
        }
        
        // Validate file type using getimagesize
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'Invalid image file'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Calculate new dimensions while maintaining aspect ratio
     */
    private function calculateDimensions($originalWidth, $originalHeight) {
        $ratio = min($this->maxWidth / $originalWidth, $this->maxHeight / $originalHeight);
        
        // If image is smaller than max dimensions, keep original size
        if ($ratio >= 1) {
            return ['width' => $originalWidth, 'height' => $originalHeight];
        }
        
        return [
            'width' => round($originalWidth * $ratio),
            'height' => round($originalHeight * $ratio)
        ];
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource($filepath, $imageType) {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filepath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filepath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filepath);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($filepath);
            default:
                return false;
        }
    }
    
    /**
     * Preserve transparency for PNG and GIF images
     */
    private function preserveTransparency($newImage, $sourceImage, $imageType) {
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, imagesx($newImage), imagesy($newImage), $transparent);
        } elseif ($imageType === IMAGETYPE_GIF) {
            $transparentIndex = imagecolortransparent($sourceImage);
            if ($transparentIndex >= 0) {
                $transparentColor = imagecolorsforindex($sourceImage, $transparentIndex);
                $transparentIndex = imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                imagecolortransparent($newImage, $transparentIndex);
            }
        }
    }
    
    /**
     * Save processed image to file
     */
    private function saveImage($image, $filepath, $imageType, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $filepath, $this->quality);
            case 'png':
                return imagepng($image, $filepath, round((100 - $this->quality) / 10));
            case 'gif':
                return imagegif($image, $filepath);
            case 'webp':
                return imagewebp($image, $filepath, $this->quality);
            default:
                return false;
        }
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
    
    /**
     * Generate thumbnail for an image
     */
    public function createThumbnail($sourcePath, $thumbPath, $thumbWidth = 300, $thumbHeight = 200) {
        try {
            $imageInfo = getimagesize($sourcePath);
            if ($imageInfo === false) {
                return ['success' => false, 'message' => 'Invalid source image'];
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $imageType = $imageInfo[2];
            
            // Calculate thumbnail dimensions
            $ratio = min($thumbWidth / $originalWidth, $thumbHeight / $originalHeight);
            $newWidth = round($originalWidth * $ratio);
            $newHeight = round($originalHeight * $ratio);
            
            // Create image resources
            $sourceImage = $this->createImageResource($sourcePath, $imageType);
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create source image resource'];
            }
            
            $thumbImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
                $this->preserveTransparency($thumbImage, $sourceImage, $imageType);
            }
            
            // Resize
            imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save thumbnail
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            $saved = $this->saveImage($thumbImage, $thumbPath, $imageType, $extension);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($thumbImage);
            
            if (!$saved) {
                return ['success' => false, 'message' => 'Failed to save thumbnail'];
            }
            
            return ['success' => true, 'message' => 'Thumbnail created successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating thumbnail: ' . $e->getMessage()];
        }
    }
    
    /**
     * Set maximum dimensions
     */
    public function setMaxDimensions($width, $height) {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
    }
    
    /**
     * Set quality for JPEG and WebP compression
     */
    public function setQuality($quality) {
        $this->quality = max(1, min(100, $quality));
    }
    
    /**
     * Set maximum file size
     */
    public function setMaxFileSize($sizeInBytes) {
        $this->maxFileSize = $sizeInBytes;
    }
}
