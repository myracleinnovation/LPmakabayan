$(document).ready(function() {
    // Add CSS to hide sorting arrows but keep sorting functionality
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .table thead th.sorting:before,
            .table thead th.sorting:after,
            .table thead th.sorting_asc:before,
            .table thead th.sorting_asc:after,
            .table thead th.sorting_desc:before,
            .table thead th.sorting_desc:after {
                display: none !important;
            }
        `)
        .appendTo('head');

    // Initialize company info functionality
    initializeCompanyInfo();
});

// Initialize company info functionality
function initializeCompanyInfo() {
    // Handle company info form submission
    let isSubmitting = false;
    
    $('#companyInfoForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        
        const formData = new FormData(this);
        formData.append('action', 'update_company');
        
        // Show loading state
        const submitBtn = $('#updateCompanyBtn');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Updating...');
        
        $.ajax({
            url: 'app/apiCompanyInfo.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                isSubmitting = false;
                if (response.status === 1) {
                    toastr.success(response.message);
                    
                    // Reload page after successful update to show new images
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Error updating company information');
                }
            },
            error: function(xhr, status, error) {
                isSubmitting = false;
                console.error('Company info update error:', error);
                toastr.error('An error occurred while updating company information: ' + error);
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle file input changes for image preview
    setupImagePreviews();
    
    // Setup form validation
    setupFormValidation();
}

// Setup image preview functionality
function setupImagePreviews() {
    // About image preview
    $('input[name="about_image"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#current_about_image_preview').html(`
                    <small class="text-muted">New About Image Preview:</small><br>
                    <img src="${e.target.result}" alt="About Image Preview"
                        style="max-width: 200px; max-height: 200px; object-fit: cover;"
                        class="border rounded">
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Logo image preview
    $('input[name="logo_image"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#current_logo_image_preview').html(`
                    <small class="text-muted">New Logo Image Preview:</small><br>
                    <img src="${e.target.result}" alt="Logo Image Preview"
                        style="max-width: 200px; max-height: 200px; object-fit: cover;"
                        class="border rounded">
                `);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Setup form validation
function setupFormValidation() {
    // Company name validation
    $('#companyName').on('blur', function() {
        const value = $(this).val().trim();
        if (!value) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Company name is required</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Tagline validation
    $('#tagline').on('blur', function() {
        const value = $(this).val().trim();
        if (!value) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Tagline is required</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Description validation
    $('#description').on('blur', function() {
        const value = $(this).val().trim();
        if (!value) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Company description is required</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Mission validation
    $('#mission').on('blur', function() {
        const value = $(this).val().trim();
        if (!value) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Mission is required</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Vision validation
    $('#vision').on('blur', function() {
        const value = $(this).val().trim();
        if (!value) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Vision is required</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
}

// Utility function to validate company form
function validateCompanyForm() {
    let isValid = true;
    
    // Check required fields
    const requiredFields = ['#companyName', '#tagline', '#description', '#mission', '#vision'];
    
    requiredFields.forEach(fieldId => {
        const field = $(fieldId);
        const value = field.val().trim();
        
        if (!value) {
            field.addClass('is-invalid');
            if (!field.next('.invalid-feedback').length) {
                field.after('<div class="invalid-feedback">This field is required</div>');
            }
            isValid = false;
        } else {
            field.removeClass('is-invalid');
            field.next('.invalid-feedback').remove();
        }
    });
    
    return isValid;
}

// Utility function to reset form validation
function resetFormValidation() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Utility function to show loading state
function showLoadingState(element, text = 'Loading...') {
    const originalText = element.text();
    element.prop('disabled', true).text(text);
    return originalText;
}

// Utility function to hide loading state
function hideLoadingState(element, originalText) {
    element.prop('disabled', false).text(originalText);
} 