// Initialize DataTable for Industries
const industriesDataTable = new DataTable('#dataTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiCompanyIndustries.php',
        type: 'POST',
        data: { action: 'get_industries' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading industries data')
    },
    columns: [
        { data: 'IdIndustry', render: data => `<div class="text-center">${data}</div>` },
        { data: 'IndustryName', render: data => `<div class="text-start">${data}</div>` },
        { data: 'IndustryDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'IndustryImage', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_industry" style="cursor: pointer;" data-industry-id="${row.IdIndustry}" title="Edit Industry"></i>
                <i class="bi bi-trash delete_industry" style="cursor: pointer;" data-industry-id="${row.IdIndustry}" title="Delete Industry"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#industryCustomSearch').on('keyup', function () {
    industriesDataTable.search(this.value).draw();
});

// Handle industry form submission (create/update)
const handleIndustrySubmit = (action, data) => {
    if (!data.industry_name) {
        toastr.error('Industry name is required');
        return;
    }

    $.ajax({
        url: 'app/apiCompanyIndustries.php',
        type: 'POST',
        data: { action: action, ...data },
        success: response => {
            if (response.success) {
                industriesDataTable.ajax.reload();
                $('#industryForm')[0].reset();
                $('#industryId').val('');
                $('#saveIndustryBtn').show();
                $('#updateIndustryBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} industry`);
            }
        },
        error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} industry`)
    });
};

// Save industry
$('#saveIndustryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        industry_name: $('#industryName').val()?.trim(),
        industry_description: $('#industryDescription').val()?.trim(),
        industry_image: $('#industryImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    handleIndustrySubmit('add', data);
});

// Update industry
$('#updateIndustryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        industry_id: $('#industryId').val()?.trim(),
        industry_name: $('#industryName').val()?.trim(),
        industry_description: $('#industryDescription').val()?.trim(),
        industry_image: $('#industryImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    if (!data.industry_id) {
        toastr.error('Industry ID is required');
        return;
    }
    handleIndustrySubmit('edit', data);
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
        url: 'app/apiCompanyIndustries.php',
        type: 'POST',
        data: { action: 'get', industry_id: industryId },
        success: response => {
            if (response.success) {
                const { IdIndustry, IndustryName, IndustryDescription, IndustryImage, DisplayOrder, Status } = response.data;
                $('#industryId').val(IdIndustry);
                $('#industryName').val(IndustryName);
                $('#industryDescription').val(IndustryDescription);
                $('#industryImage').val(IndustryImage);
                $('#displayOrder').val(DisplayOrder);
                $('#status').val(Status);
                $('#saveIndustryBtn').hide();
                $('#updateIndustryBtn').show();
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
    
    if (confirm('Are you sure you want to delete this industry?')) {
        $.ajax({
            url: 'app/apiCompanyIndustries.php',
            type: 'POST',
            data: { action: 'delete', industry_id: industryId },
            success: response => {
                if (response.success) {
                    industriesDataTable.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Error deleting industry');
                }
            },
            error: () => toastr.error('Error deleting industry')
        });
    }
}); 