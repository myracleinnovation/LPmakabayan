$(document).ready(function() {
    // Initialize DataTable for Images only if the table exists
    let imageDataTable;

    // Only initialize if the image table exists on this page
    if ($('#imageListTable').length > 0) {
        // Check if DataTable already exists
        if ($.fn.DataTable.isDataTable('#imageListTable')) {
            imageDataTable = $('#imageListTable').DataTable();
        } else {
            imageDataTable = $('#imageListTable').DataTable({
                columnDefs: [{ orderable: false, targets: [-1] }],
                order: [[1, 'asc']],
                dom: "<'row'<'col-12 mb-3'tr>>" +
                     "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'app/apiImageProcessor.php',
                    type: 'GET',
                    data: function(d) {
                        d.get_images = true;
                    },
                    dataSrc: function(json) {
                        if (json.status === 1) {
                            return json.data || [];
                        } else {
                            toastr.error(json.message || 'Error loading data');
                            return [];
                        }
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);
                        toastr.error('Error loading image data');
                    }
                },
                columns: [
                    {
                        data: 'name',
                        render: function(data) {
                            return `<img src="../assets/img/${data}" alt="${data}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">`;
                        }
                    },
                    { 
                        data: 'name', 
                        render: function(data) {
                            return `<div class="text-start">${data || ''}</div>`;
                        }
                    },
                    { 
                        data: 'formatted_size', 
                        render: function(data) {
                            return `<div class="text-start">${data || ''}</div>`;
                        }
                    },
                    { 
                        data: 'dimensions', 
                        render: function(data) {
                            return `<div class="text-start">${data || ''}</div>`;
                        }
                    },
                    { 
                        data: 'type',
                        render: function(data) {
                            return `<span class="badge bg-secondary">${data ? data.toUpperCase() : ''}</span>`;
                        }
                    },
                    { 
                        data: null, 
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group gap-2" role="group">
                                    <button type="button" class="btn btn-outline-primary edit-image-btn" 
                                            data-image-name="${row.name}" data-bs-toggle="modal" data-bs-target="#editImageModal"
                                            title="Edit Image Name">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-image-btn" 
                                            data-image-name="${row.name}"
                                            title="Delete Image">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                initComplete: function() {
                }
            });
        }
    }

    // Only run these functions if the image table exists
    if ($('#imageListTable').length > 0) {
        // Search functionality
        $('#imageCustomSearch').on('keyup', function () {
            if (imageDataTable) {
                imageDataTable.search(this.value).draw();
            }
        });

        // Handle edit image button click
        $(document).on('click', '.edit-image-btn', function() {
            var imageName = $(this).data('image-name');
            var fileName = imageName.replace(/\.[^/.]+$/, ""); // Remove extension
            var extension = imageName.split('.').pop();
            
            $('#editImageName').val(fileName);
            $('#editImageExtension').text(extension);
            $('#editOldImageName').val(imageName);
        });

        // Handle edit form submission
        $('#editImageForm').on('submit', function(e) {
            e.preventDefault();
            
            var oldName = $('#editOldImageName').val();
            var newFileName = $('#editImageName').val();
            var extension = $('#editImageExtension').text();
            var newName = newFileName + '.' + extension;
            
            // Validate new filename
            if (!/^[a-zA-Z0-9._-]+$/.test(newFileName)) {
                toastr.error('Invalid filename. Use only letters, numbers, dots, underscores, and hyphens.');
                return;
            }
            
            // Show loading state
            $('#editImageBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Updating...');
            
            $.ajax({
                url: 'app/apiImageProcessor.php',
                type: 'POST',
                data: {
                    action: 'update_image_name',
                    old_name: oldName,
                    new_name: newName
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#editImageModal').modal('hide');
                        if (imageDataTable) {
                            imageDataTable.ajax.reload();
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while updating the image name.');
                },
                complete: function() {
                    $('#editImageBtn').prop('disabled', false).html('<i class="bi bi-check me-1"></i>Update Name');
                }
            });
        });

        // Handle delete image button click
        $(document).on('click', '.delete-image-btn', function() {
            var imageName = $(this).data('image-name');
            
            if (confirm('Are you sure you want to delete "' + imageName + '"? This action cannot be undone.')) {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
                
                $.ajax({
                    url: 'app/apiImageProcessor.php',
                    type: 'POST',
                    data: {
                        action: 'delete_image',
                        image_name: imageName
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 1) {
                            toastr.success(response.message);
                            if (imageDataTable) {
                                imageDataTable.ajax.reload();
                            }
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while deleting the image.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            }
        });

        // Handle modal close
        $('#editImageModal').on('hidden.bs.modal', function() {
            $('#editImageForm')[0].reset();
            $('#editImageBtn').prop('disabled', false).html('<i class="bi bi-check me-1"></i>Update Name');
        });

        // Real-time filename validation
        $('#editImageName').on('input', function() {
            var value = $(this).val();
            var isValid = /^[a-zA-Z0-9._-]*$/.test(value);
            
            if (isValid) {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#editImageBtn').prop('disabled', false);
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
                $('#editImageBtn').prop('disabled', true);
            }
        });

        // Refresh table when processing is complete
        $(document).on('imageProcessingComplete', function() {
            if (imageDataTable) {
                imageDataTable.ajax.reload();
            }
        });
    }
});