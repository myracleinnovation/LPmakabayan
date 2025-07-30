// Initialize DataTable for Specialties
const specialtiesDataTable = new DataTable('#dataTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiCompanySpecialties.php',
        type: 'POST',
        data: { action: 'get_specialties' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading specialties data')
    },
    columns: [
        { data: 'IdSpecialty', render: data => `<div class="text-center">${data}</div>` },
        { data: 'SpecialtyName', render: data => `<div class="text-start">${data}</div>` },
        { data: 'SpecialtyDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'SpecialtyImage', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_specialty" style="cursor: pointer;" data-specialty-id="${row.IdSpecialty}" title="Edit Specialty"></i>
                <i class="bi bi-trash delete_specialty" style="cursor: pointer;" data-specialty-id="${row.IdSpecialty}" title="Delete Specialty"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#specialtyCustomSearch').on('keyup', function () {
    specialtiesDataTable.search(this.value).draw();
});

// Handle specialty form submission (create/update)
const handleSpecialtySubmit = (action, data) => {
    if (!data.specialty_name) {
        toastr.error('Specialty name is required');
        return;
    }

    $.ajax({
        url: 'app/apiCompanySpecialties.php',
        type: 'POST',
        data: { action: action, ...data },
        success: response => {
            if (response.success) {
                specialtiesDataTable.ajax.reload();
                $('#specialtyForm')[0].reset();
                $('#specialtyId').val('');
                $('#saveSpecialtyBtn').show();
                $('#updateSpecialtyBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} specialty`);
            }
        },
        error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} specialty`)
    });
};

// Save specialty
$('#saveSpecialtyBtn').on('click', e => {
    e.preventDefault();
    const data = {
        specialty_name: $('#specialtyName').val()?.trim(),
        specialty_description: $('#specialtyDescription').val()?.trim(),
        specialty_image: $('#specialtyImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    handleSpecialtySubmit('add', data);
});

// Update specialty
$('#updateSpecialtyBtn').on('click', e => {
    e.preventDefault();
    const data = {
        specialty_id: $('#specialtyId').val()?.trim(),
        specialty_name: $('#specialtyName').val()?.trim(),
        specialty_description: $('#specialtyDescription').val()?.trim(),
        specialty_image: $('#specialtyImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    if (!data.specialty_id) {
        toastr.error('Specialty ID is required');
        return;
    }
    handleSpecialtySubmit('edit', data);
});

// Reset form
$('#resetSpecialtyForm').on('click', () => {
    $('#specialtyForm')[0].reset();
    $('#specialtyId').val('');
    $('#saveSpecialtyBtn').show();
    $('#updateSpecialtyBtn').hide();
});

// Edit specialty
$(document).on('click', '.edit_specialty', function () {
    const specialtyId = $(this).data('specialty-id');
    $.ajax({
        url: 'app/apiCompanySpecialties.php',
        type: 'POST',
        data: { action: 'get', specialty_id: specialtyId },
        success: response => {
            if (response.success) {
                const { IdSpecialty, SpecialtyName, SpecialtyDescription, SpecialtyImage, DisplayOrder, Status } = response.data;
                $('#specialtyId').val(IdSpecialty);
                $('#specialtyName').val(SpecialtyName);
                $('#specialtyDescription').val(SpecialtyDescription);
                $('#specialtyImage').val(SpecialtyImage);
                $('#displayOrder').val(DisplayOrder);
                $('#status').val(Status);
                $('#saveSpecialtyBtn').hide();
                $('#updateSpecialtyBtn').show();
            } else {
                toastr.error(response.message || 'Error retrieving specialty data');
            }
        },
        error: () => toastr.error('Error retrieving specialty data')
    });
});

// Delete specialty
$(document).on('click', '.delete_specialty', function () {
    const specialtyId = $(this).data('specialty-id');
    
    if (confirm('Are you sure you want to delete this specialty?')) {
        $.ajax({
            url: 'app/apiCompanySpecialties.php',
            type: 'POST',
            data: { action: 'delete', specialty_id: specialtyId },
            success: response => {
                if (response.success) {
                    specialtiesDataTable.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Error deleting specialty');
                }
            },
            error: () => toastr.error('Error deleting specialty')
        });
    }
}); 