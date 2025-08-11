// Initialize Process DataTable
function initializeProcessDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeProcessDataTable, 1000);
        return;
    }
    
    if ($('#processTable').length === 0) {
        return;
    }
    
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#processTable')) {
        return;
    }
    
    const processDataTable = $('#processTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiCompanyProcess.php',
            type: 'GET',
            data: function(d) {
                d.get_processes = true;
                return d;
            },
            dataSrc: function(json) {
                if (json.status === 1) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading process data');
            }
        },
        columns: [
            { 
                data: 'ProcessImage', 
                render: function(data, type, row) {
                    if (data) {
                        return `<img src="../assets/img/${data}" alt="${row.ProcessTitle}" class="process-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">`;
                    } else {
                        return `<div class="process-image bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 8px;"><i class="bi bi-gear text-muted"></i></div>`;
                    }
                }
            },
            { 
                data: 'ProcessTitle', 
                render: function(data) {
                    return `<div class="text-start"><strong>${data}</strong></div>`;
                }
            },
            { 
                data: 'ProcessDescription', 
                render: function(data) {
                    return `<div class="text-start">${data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'No description'}</div>`;
                }
            },
            { 
                data: 'DisplayOrder', 
                render: function(data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            { 
                data: 'Status', 
                render: function(data) {
                    return `<span class="badge bg-${data ? 'success' : 'secondary'}">${data ? 'Active' : 'Inactive'}</span>`;
                }
            },
            { 
                data: null, 
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary edit-process" 
                                    data-process-id="${row.IdProcess}" 
                                    title="Edit Process">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Search functionality
    $('#processCustomSearch').on('keyup', function () {
        processDataTable.search(this.value).draw();
    });

    // Handle process form submission (create/update)
    const handleProcessSubmit = (action, data) => {
        if (!data.process_title) {
            toastr.error('Process title is required');
            return;
        }

        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'POST',
            data: { action: action, ...data },
            success: response => {
                if (response.status === 1) {
                    processDataTable.ajax.reload();
                    $('#addProcessForm')[0].reset();
                    $('#editProcessForm')[0].reset();
                    toastr.success(response.message);
                    $('.modal').modal('hide');
                } else {
                    toastr.error(response.message || `Error ${action === 'create' ? 'creating' : 'updating'} process`);
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error(`Error ${action === 'create' ? 'creating' : 'updating'} process`);
            }
        });
    };

    // Add process form submission
    $('#addProcessForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');

        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: response => {
                if (response.status === 1) {
                    processDataTable.ajax.reload();
                    $('#addProcessForm')[0].reset();
                    toastr.success(response.message);
                    $('#addProcessModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error creating process');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error creating process');
            }
        });
    });

    // Edit process
    $(document).on('click', '.edit-process', function () {
        const processId = $(this).data('process-id');
        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'GET',
            data: { get_process: true, id: processId },
            success: response => {
                if (response.status === 1) {
                    const process = response.data;
                    
                    // Fill form fields
                    $('#edit_process_id').val(process.IdProcess);
                    $('#edit_process_title').val(process.ProcessTitle);
                    $('#edit_process_description').val(process.ProcessDescription);
                    $('#edit_display_order').val(process.DisplayOrder);
                    $('#edit_status').val(process.Status);
                    
                    // Show current image if it exists
                    if (process.ProcessImage) {
                        $('#current_process_image_preview').html(`
                            <small class="text-muted">Current Image:</small><br>
                            <img src="../assets/img/${process.ProcessImage}" alt="Current Process Image" 
                                 style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                        `);
                    } else {
                        $('#current_process_image_preview').html('');
                    }
                    
                    $('#editProcessModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving process data');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error retrieving process data');
            }
        });
    });

    // Edit process form submission
    $('#editProcessForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: response => {
                if (response.status === 1) {
                    processDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#editProcessModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error updating process');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error updating process');
            }
        });
    });
}

// Start initialization when document is ready
$(document).ready(function() {
    // Only initialize if the process table exists on this page
    if ($('#processTable').length > 0) {
        initializeProcessDataTable();
    }
}); 