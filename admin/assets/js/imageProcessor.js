// Image Processor JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // File input preview
    const fileInput = document.querySelector('input[name="image"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showImagePreview(file);
            }
        });
    }
    
    // Show image preview
    function showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Remove existing preview
            const existingPreview = document.querySelector('.image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Create preview container
            const previewContainer = document.createElement('div');
            previewContainer.className = 'image-preview mt-3 p-3 border rounded';
            previewContainer.innerHTML = `
                <h6>Image Preview</h6>
                <div class="row">
                    <div class="col-md-6">
                        <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div class="col-md-6">
                        <p><strong>File:</strong> ${file.name}</p>
                        <p><strong>Size:</strong> ${formatBytes(file.size)}</p>
                        <p><strong>Type:</strong> ${file.type}</p>
                    </div>
                </div>
            `;
            
            // Insert preview after file input
            fileInput.parentNode.appendChild(previewContainer);
        };
        reader.readAsDataURL(file);
    }
    
    // Format bytes to human readable format
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
            // Single image processing
        const singleImageForm = document.querySelector('form[action*="process_single"]');
        if (singleImageForm) {
            singleImageForm.addEventListener('submit', function(e) {
                const fileInput = this.querySelector('input[name="image"]');
                if (!fileInput.files[0]) {
                    e.preventDefault();
                    alert('Please select an image file first.');
                    return;
                }

                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
                submitBtn.disabled = true;

                // Trigger table refresh after processing
                setTimeout(function() {
                    $(document).trigger('imageProcessingComplete');
                }, 2000);
            });
        }
    
    // Batch processing confirmation
    const batchForm = document.querySelector('form[action*="process_batch"]');
    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            if (!confirm('This will process all images in the assets/img folder. Original files will be backed up. Are you sure you want to continue?')) {
                e.preventDefault();
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
            submitBtn.disabled = true;

            // Trigger table refresh after processing
            setTimeout(function() {
                $(document).trigger('imageProcessingComplete');
            }, 3000);
        });
    }
    
    // Auto-refresh image list after processing
    const processForms = document.querySelectorAll('form[method="POST"]');
    processForms.forEach(form => {
        form.addEventListener('submit', function() {
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
                submitBtn.disabled = true;
                
                // Re-enable button after a delay (in case of errors)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
    
    // Image list sorting
    const imageTable = document.querySelector('.table');
    if (imageTable) {
        const headers = imageTable.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(index);
            });
        });
    }
    
    // Sort table function
    function sortTable(columnIndex) {
        const table = document.querySelector('.table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            // Try to parse as number first
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return aNum - bNum;
            }
            
            // Otherwise sort as string
            return aValue.localeCompare(bValue);
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Search functionality for image list
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mb-3 mt-3';
    searchInput.placeholder = 'Search...';
    
    const imageListCard = document.querySelector('.card-body');
    if (imageListCard && imageTable) {
        imageListCard.insertBefore(searchInput, imageTable.parentNode);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = imageTable.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Drag and drop functionality for single image upload
    const singleImageCard = document.querySelector('.card-body form');
    if (singleImageCard) {
        singleImageCard.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-primary');
        });
        
        singleImageCard.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
        });
        
        singleImageCard.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    }
    
    // Progress bar for batch processing
    function createProgressBar() {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress mt-3';
        progressContainer.innerHTML = `
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%">0%</div>
        `;
        return progressContainer;
    }
    
    // Update progress bar
    function updateProgress(percentage) {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.textContent = Math.round(percentage) + '%';
        }
    }
    
    // Add tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + U to focus file upload
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            if (fileInput) {
                fileInput.click();
            }
        }
        
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
    
    // Add keyboard shortcut hints
    const shortcuts = document.createElement('div');
    shortcuts.className = 'mt-3 text-muted small';
    shortcuts.innerHTML = `
        <strong>Keyboard shortcuts:</strong> 
        Ctrl+U (Select File), Ctrl+F (Search)
    `;
    
    // Use the existing singleImageForm variable from the outer scope
    if (singleImageForm) {
        singleImageForm.appendChild(shortcuts);
    }
});
