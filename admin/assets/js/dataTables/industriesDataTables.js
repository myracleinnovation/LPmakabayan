// Initialize Industries DataTable
$(document).ready(function() {
    console.log('Initializing Industries DataTable...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTable available:', typeof $.fn.DataTable);
    
    const industriesDataTable = $('#industriesTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiIndustries.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_industries';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading industries data');
            }
        },
    columns: [
        { 
            data: 'IndustryImage', 
            render: function(data, type, row) {
                if (data) {
                    return `<img src="${data}" alt="${row.IndustryName}" class="specialty-image">`;
                } else {
                    return `<div class="specialty-image bg-light d-flex align-items-center justify-content-center"><i class="bi bi-briefcase text-muted"></i></div>`;
                }
            }
        },
        { 
            data: 'IndustryName', 
            render: function(data) {
                return `<div class="text-start"><strong>${data}</strong></div>`;
            }
        },
        { 
            data: 'IndustryDescription', 
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
                        <i class="bi bi-pencil edit_industry" style="cursor: pointer;" data-industry-id="${row.IdIndustry}" title="Edit Industry"></i>
                        <i class="bi bi-trash delete_industry" style="cursor: pointer;" data-industry-id="${row.IdIndustry}" data-industry-name="${row.IndustryName}" title="Delete Industry"></i>
                    </div>
                `;
            }
        }
    ]
    });
});

// Search functionality
$('#industriesCustomSearch').on('keyup', function () {
    industriesDataTable.search(this.value).draw();
});

// Handle industry form submission (create/update)
const handleIndustrySubmit = (action, data) => {
    if (!data.IndustryName) {
        toastr.error('Industry name is required');
        return;
    }

    $.ajax({
        url: 'app/apiIndustries.php',
        type: 'POST',
        data: { [action]: true, ...data },
        success: response => {
            if (response.success) {
                industriesDataTable.ajax.reload();
                $('#industryForm')[0].reset();
                $('#industryId').val('');
                $('#saveIndustryBtn').show();
                $('#updateIndustryBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'create_industry' ? 'creating' : 'updating'} industry`);
            }
        },
        error: () => toastr.error(`Error ${action === 'create_industry' ? 'creating' : 'updating'} industry`)
    });
};

// Save industry
$('#saveIndustryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        IndustryName: $('#industryName').val()?.trim(),
        IndustryDescription: $('#industryDescription').val()?.trim(),
        IndustryImage: $('#industryImage').val()?.trim(),
        DisplayOrder: $('#displayOrder').val() || 0,
        Status: $('#status').val() || 1
    };
    handleIndustrySubmit('create_industry', data);
});

// Update industry
$('#updateIndustryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        industry_id: $('#industryId').val()?.trim(),
        IndustryName: $('#industryName').val()?.trim(),
        IndustryDescription: $('#industryDescription').val()?.trim(),
        IndustryImage: $('#industryImage').val()?.trim(),
        DisplayOrder: $('#displayOrder').val() || 0,
        Status: $('#status').val() || 1
    };
    if (!data.industry_id) {
        toastr.error('Industry ID is required');
        return;
    }
    handleIndustrySubmit('update_industry', data);
});

// Reset form
$('#resetIndustryForm').on('click', () => {
    $('#industryForm')[0].reset();
    $('#industryId').val('');
    $('#saveIndustryBtn').show();
    $('#updateIndustryBtn').hide();
});

    // Edit industry
    $(document).on('click', '.edit_industry', function () {
        const industryId = $(this).data('industry-id');
        $.ajax({
            url: 'app/apiIndustries.php',
            type: 'POST',
            data: { action: 'get', industry_id: industryId },
            success: response => {
                if (response.success) {
                    const industry = response.data;
                    $('#edit_industry_id').val(industry.IdIndustry);
                    $('#edit_industry_name').val(industry.IndustryName);
                    $('#edit_industry_description').val(industry.IndustryDescription);
                    $('#edit_industry_image').val(industry.IndustryImage);
                    $('#edit_display_order').val(industry.DisplayOrder);
                    $('#edit_status').val(industry.Status);
                    $('#editIndustryModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving industry data');
                }
            },
            error: () => toastr.error('Error retrieving industry data')
        });
    });

    // Delete industry
    $(document).on('click', '.delete_industry', function () {
        const industryId = $(this).data('industry-id');
        const industryName = $(this).data('industry-name');
        $('#delete_industry_id').val(industryId);
        $('#delete_industry_name').text(industryName);
        $('#deleteIndustryModal').modal('show');
    });

    // Delete industry button in modal
    $(document).on('click', '#deleteIndustryModal .btn-danger', function() {
        const industryId = $('#delete_industry_id').val();
        $.ajax({
            url: 'app/apiIndustries.php',
            type: 'POST',
            data: { action: 'delete', industry_id: industryId },
            success: response => {
                if (response.success) {
                    industriesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteIndustryModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting industry');
                }
            },
            error: () => toastr.error('Error deleting industry')
        });
    });

    // Confirm delete industry
    $('#confirmDeleteIndustry').on('click', function() {
        const industryId = $('#delete_industry_id').val();
        $.ajax({
            url: 'app/apiIndustries.php',
            type: 'POST',
            data: { action: 'delete', industry_id: industryId },
            success: response => {
                if (response.success) {
                    industriesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteIndustryModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting industry');
                }
            },
            error: () => toastr.error('Error deleting industry')
        });
    });
