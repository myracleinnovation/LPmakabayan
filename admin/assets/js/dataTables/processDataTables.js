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
                        return `<img src="${data}" alt="${row.ProcessTitle}" class="process-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">`;
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
                            <button class="btn btn-warning btn-sm edit-process" 
                                    data-process-id="${row.IdProcess}" 
                                    title="Edit Process">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-process" 
                                    data-process-id="${row.IdProcess}" 
                                    data-process-title="${row.ProcessTitle}" 
                                    title="Delete Process">
                                <i class="bi bi-trash"></i>
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
        const data = {
            process_title: formData.get('process_title'),
            process_description: formData.get('process_description'),
            process_image: formData.get('process_image'),
            display_order: formData.get('display_order') || 0,
            status: formData.get('status') || 1
        };
        handleProcessSubmit('create', data);
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
                    $('#edit_process_id').val(process.IdProcess);
                    $('#edit_process_title').val(process.ProcessTitle);
                    $('#edit_process_description').val(process.ProcessDescription);
                    $('#edit_process_image').val(process.ProcessImage);
                    $('#edit_display_order').val(process.DisplayOrder);
                    $('#edit_status').val(process.Status);
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
        const data = {
            process_id: formData.get('process_id'),
            process_title: formData.get('process_title'),
            process_description: formData.get('process_description'),
            process_image: formData.get('process_image'),
            display_order: formData.get('display_order') || 0,
            status: formData.get('status') || 1
        };
        handleProcessSubmit('update', data);
    });

    // Delete process
    $(document).on('click', '.delete-process', function () {
        const processId = $(this).data('process-id');
        const processTitle = $(this).data('process-title');
        $('#delete_process_id').val(processId);
        $('#delete_process_title').text(processTitle);
        $('#deleteProcessModal').modal('show');
    });

    // Delete process button in modal
    $(document).on('click', '#deleteProcessModal .btn-danger', function() {
        const processId = $('#delete_process_id').val();
        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'POST',
            data: { action: 'delete', process_id: processId },
            success: response => {
                if (response.status === 1) {
                    processDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteProcessModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting process');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error deleting process');
            }
        });
    });
}

// Start initialization when document is ready
$(document).ready(function() {
    initializeProcessDataTable();
}); 