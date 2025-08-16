// Auto Image Processor for Admin Panel
// This script automatically processes images when they are uploaded across all admin pages

document.addEventListener('DOMContentLoaded', function() {
    
    // Configuration
    const config = {
        maxWidth: 1920,
        maxHeight: 1080,
        quality: 85,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        maxFileSize: 100 * 1024 * 1024 // 100MB
    };
    
    // Find all file inputs that accept images
    const imageFileInputs = document.querySelectorAll('input[type="file"][accept*="image"], input[type="file"][accept="image/*"]');
    
    imageFileInputs.forEach(function(fileInput) {
        // Add change event listener
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                processImageFiles(files, fileInput);
            }
        });
        
        // Add drag and drop functionality
        const parentElement = fileInput.closest('.form-group, .mb-3, .col-md-6, .col-lg-6, .col-12') || fileInput.parentElement;
        
        if (parentElement) {
            parentElement.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-primary', 'border-2');
            });
            
            parentElement.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('border-primary', 'border-2');
            });
            
            parentElement.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-primary', 'border-2');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    processImageFiles(files, fileInput);
                }
            });
        }
    });
    
    /**
     * Process image files before upload
     */
    function processImageFiles(files, fileInput) {
        const processedFiles = [];
        let hasErrors = false;
        let errorMessages = [];
        
        Array.from(files).forEach(function(file, index) {
            // Validate file
            const validation = validateImageFile(file);
            if (!validation.valid) {
                hasErrors = true;
                errorMessages.push(`${file.name}: ${validation.message}`);
                return;
            }
            
            // Show processing indicator
            showProcessingIndicator(fileInput, file.name);
            
            // Process image using Canvas API for client-side optimization
            processImageWithCanvas(file, function(processedBlob, originalSize, processedSize) {
                // Create a new File object with the processed blob
                const processedFile = new File([processedBlob], file.name, {
                    type: file.type,
                    lastModified: Date.now()
                });
                
                processedFiles.push(processedFile);
                
                // Show compression info
                const compressionRatio = ((originalSize - processedSize) / originalSize * 100).toFixed(1);
                showCompressionInfo(fileInput, file.name, originalSize, processedSize, compressionRatio);
                
                // Update file input with processed files
                if (processedFiles.length === files.length) {
                    const dataTransfer = new DataTransfer();
                    processedFiles.forEach(file => dataTransfer.items.add(file));
                    fileInput.files = dataTransfer.files;
                    
                    // Trigger change event
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        if (hasErrors) {
            showError(fileInput, errorMessages.join('<br>'));
        }
    }
    
    /**
     * Validate image file
     */
    function validateImageFile(file) {
        // Check file type
        if (!config.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'
            };
        }
        
        // Check file size
        if (file.size > config.maxFileSize) {
            return {
                valid: false,
                message: 'File size exceeds 100MB limit'
            };
        }
        
        return { valid: true };
    }
    
    /**
     * Process image using Canvas API
     */
    function processImageWithCanvas(file, callback) {
        const img = new Image();
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        img.onload = function() {
            const originalWidth = img.width;
            const originalHeight = img.height;
            
            // Calculate new dimensions
            const ratio = Math.min(config.maxWidth / originalWidth, config.maxHeight / originalHeight);
            let newWidth = originalWidth;
            let newHeight = originalHeight;
            
            // Only resize if image is larger than max dimensions
            if (ratio < 1) {
                newWidth = Math.round(originalWidth * ratio);
                newHeight = Math.round(originalHeight * ratio);
            }
            
            // Set canvas dimensions
            canvas.width = newWidth;
            canvas.height = newHeight;
            
            // Draw image with new dimensions
            ctx.drawImage(img, 0, 0, newWidth, newHeight);
            
            // Convert to blob with quality setting
            canvas.toBlob(function(blob) {
                const originalSize = file.size;
                const processedSize = blob.size;
                callback(blob, originalSize, processedSize);
            }, file.type, config.quality / 100);
        };
        
        img.onerror = function() {
            callback(file, file.size, file.size); // Return original file if processing fails
        };
        
        img.src = URL.createObjectURL(file);
    }
    
    /**
     * Show processing indicator
     */
    function showProcessingIndicator(fileInput, filename) {
        const container = fileInput.closest('.form-group, .mb-3, .col-md-6, .col-lg-6, .col-12') || fileInput.parentElement;
        
        // Remove ALL existing indicators first
        const existingIndicators = container.querySelectorAll('.processing-indicator, .compression-info, .alert-danger');
        existingIndicators.forEach(indicator => indicator.remove());
        
        const indicator = document.createElement('div');
        indicator.className = 'processing-indicator alert alert-info alert-sm mt-2';
        indicator.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span>Processing ${filename}...</span>
            </div>
        `;
        
        container.appendChild(indicator);
        
        // Auto-remove after 1 second (very fast)
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.remove();
            }
        }, 1000);
    }
    
    /**
     * Show compression information
     */
    function showCompressionInfo(fileInput, filename, originalSize, processedSize, compressionRatio) {
        const container = fileInput.closest('.form-group, .mb-3, .col-md-6, .col-lg-6, .col-12') || fileInput.parentElement;
        
        // Remove ALL existing indicators first
        const existingIndicators = container.querySelectorAll('.processing-indicator, .compression-info, .alert-danger');
        existingIndicators.forEach(indicator => indicator.remove());
        
        const originalSizeFormatted = formatBytes(originalSize);
        const processedSizeFormatted = formatBytes(processedSize);
        
        const info = document.createElement('div');
        info.className = 'compression-info alert alert-success alert-sm mt-2';
        info.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle me-2"></i>
                <div>
                    <strong>${filename}</strong> processed successfully<br>
                    <small class="text-muted">
                        Original: ${originalSizeFormatted} → Processed: ${processedSizeFormatted} 
                        (${compressionRatio}% compression)
                    </small>
                </div>
            </div>
        `;
        
        container.appendChild(info);
        
        // Auto-remove after 1.5 seconds (very fast)
        setTimeout(() => {
            if (info.parentNode) {
                info.remove();
            }
        }, 1500);
    }
    
    /**
     * Show error message
     */
    function showError(fileInput, message) {
        const container = fileInput.closest('.form-group, .mb-3, .col-md-6, .col-lg-6, .col-12') || fileInput.parentElement;
        
        // Remove ALL existing indicators first
        const existingIndicators = container.querySelectorAll('.processing-indicator, .compression-info, .alert-danger');
        existingIndicators.forEach(indicator => indicator.remove());
        
        const error = document.createElement('div');
        error.className = 'alert alert-danger alert-sm mt-2';
        error.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        
        container.appendChild(error);
        
        // Auto-remove after 1.5 seconds (very fast)
        setTimeout(() => {
            if (error.parentNode) {
                error.remove();
            }
        }, 1500);
    }
    
    /**
     * Format bytes to human readable format
     */
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    // Add CSS for alerts
    const style = document.createElement('style');
    style.textContent = `
        .alert-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .processing-indicator, .compression-info {
            border: none;
            border-radius: 0.375rem;
        }
    `;
    document.head.appendChild(style);
});
