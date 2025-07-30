$(document).ready(function() {
    // Initialize DataTable for Process
    let processDataTable;
    
    // Check if DataTable is already initialized
    if ($('#processTable').length && !$.fn.DataTable.isDataTable('#processTable')) {
        processDataTable = new DataTable('#processTable', {
            columnDefs: [{ orderable: false, targets: [-1] }],
            order: [[0, 'asc']],
            dom: "<'row'<'col-12 mb-3'tr>>" +
                 "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
            processing: true,
            ajax: {
                url: 'app/apiCompanyProcess.php',
                type: 'POST',
                data: { action: 'get_process' },
                dataSrc: json => {
                    if (json.success) return json.data || [];
                    toastr.error(json.message || 'Error loading data');
                    return [];
                },
                error: () => toastr.error('Error loading process data')
            },
            columns: [
                { data: 'IdProcess', render: data => `<div class="text-center">${data}</div>` },
                { data: 'ProcessTitle', render: data => `<div class="text-start">${data}</div>` },
                { data: 'ProcessDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
                { data: 'ProcessImage', render: data => `<div class="text-start">${data || '-'}</div>` },
                { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
                { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
                { data: null, render: (_, __, row) => `
                    <div class="d-flex gap-1">
                        <i class="bi bi-pen edit_process" style="cursor: pointer;" data-process-id="${row.IdProcess}" title="Edit Process"></i>
                        <i class="bi bi-trash delete_process" style="cursor: pointer;" data-process-id="${row.IdProcess}" title="Delete Process"></i>
                    </div>
                ` }
            ]
        });

        // Search functionality
        $('#processCustomSearch').on('keyup', function () {
            processDataTable.search(this.value).draw();
        });
    }

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
                if (response.success) {
                    if (processDataTable) {
                        processDataTable.ajax.reload();
                    }
                    $('#processForm')[0].reset();
                    $('#processId').val('');
                    $('#saveProcessBtn').show();
                    $('#updateProcessBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} process`);
                }
            },
            error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} process`)
        });
    };

    // Save process
    $('#saveProcessBtn').on('click', e => {
        e.preventDefault();
        const data = {
            process_title: $('#processTitle').val()?.trim(),
            process_description: $('#processDescription').val()?.trim(),
            process_image: $('#processImage').val()?.trim(),
            display_order: $('#displayOrder').val() || 0,
            status: $('#status').val()
        };
        handleProcessSubmit('add', data);
    });

    // Update process
    $('#updateProcessBtn').on('click', e => {
        e.preventDefault();
        const data = {
            process_id: $('#processId').val()?.trim(),
            process_title: $('#processTitle').val()?.trim(),
            process_description: $('#processDescription').val()?.trim(),
            process_image: $('#processImage').val()?.trim(),
            display_order: $('#displayOrder').val() || 0,
            status: $('#status').val()
        };
        if (!data.process_id) {
            toastr.error('Process ID is required');
            return;
        }
        handleProcessSubmit('edit', data);
    });

    // Reset form
    $('#resetProcessForm').on('click', () => {
        $('#processForm')[0].reset();
        $('#processId').val('');
        $('#saveProcessBtn').show();
        $('#updateProcessBtn').hide();
    });

    // Edit process
    $(document).on('click', '.edit_process', function () {
        const processId = $(this).data('process-id');
        $.ajax({
            url: 'app/apiCompanyProcess.php',
            type: 'POST',
            data: { action: 'get', process_id: processId },
            success: response => {
                if (response.success) {
                    const { IdProcess, ProcessTitle, ProcessDescription, ProcessImage, DisplayOrder, Status } = response.data;
                    $('#processId').val(IdProcess);
                    $('#processTitle').val(ProcessTitle);
                    $('#processDescription').val(ProcessDescription);
                    $('#processImage').val(ProcessImage);
                    $('#displayOrder').val(DisplayOrder);
                    $('#status').val(Status);
                    $('#saveProcessBtn').hide();
                    $('#updateProcessBtn').show();
                } else {
                    toastr.error(response.message || 'Error retrieving process data');
                }
            },
            error: () => toastr.error('Error retrieving process data')
        });
    });

    // Delete process
    $(document).on('click', '.delete_process', function () {
        const processId = $(this).data('process-id');
        
        if (confirm('Are you sure you want to delete this process?')) {
            $.ajax({
                url: 'app/apiCompanyProcess.php',
                type: 'POST',
                data: { action: 'delete', process_id: processId },
                success: response => {
                    if (response.success) {
                        if (processDataTable) {
                            processDataTable.ajax.reload();
                        }
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Error deleting process');
                    }
                },
                error: () => toastr.error('Error deleting process')
            });
        }
    });
}); 