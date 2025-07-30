function initializeSpecialtiesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeSpecialtiesDataTable, 1000);
        return;
    }
    
    if ($('#specialtiesTable').length === 0) {
        return;
    }
    
    const specialtiesDataTable = $('#specialtiesTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_specialties';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading specialties data');
            }
        },
        columns: [
            { 
                data: 'ImageUrl', 
                render: function(data, type, row) {
                    if (data) {
                        return `<img src="${data}" alt="${row.SpecialtyName}" class="specialty-image">`;
                    } else {
                        return `<div class="specialty-image bg-light d-flex align-items-center justify-content-center"><i class="bi bi-tools text-muted"></i></div>`;
                    }
                }
            },
            { 
                data: 'SpecialtyName', 
                render: function(data) {
                    return `<div class="text-start"><strong>${data}</strong></div>`;
                }
            },
            { 
                data: 'SpecialtyDescription', 
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
                            <i class="bi bi-pencil edit_specialty" style="cursor: pointer;" data-specialty-id="${row.IdSpecialty}" title="Edit Specialty"></i>
                            <i class="bi bi-trash delete_specialty" style="cursor: pointer;" data-specialty-id="${row.IdSpecialty}" data-specialty-name="${row.SpecialtyName}" title="Delete Specialty"></i>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#specialtiesCustomSearch').on('keyup', function () {
        specialtiesDataTable.search(this.value).draw();
    });

    const handleSpecialtySubmit = (action, data) => {
        if (!data.SpecialtyName) {
            toastr.error('Specialty name is required');
            return;
        }

        $.ajax({
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: { [action]: true, ...data },
            success: response => {
                if (response.success) {
                    specialtiesDataTable.ajax.reload();
                    $('#specialtyForm')[0].reset();
                    $('#specialtyId').val('');
                    $('#saveSpecialtyBtn').show();
                    $('#updateSpecialtyBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'create_specialty' ? 'creating' : 'updating'} specialty`);
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error(`Error ${action === 'create_specialty' ? 'creating' : 'updating'} specialty`);
            }
        });
    };

    $('#saveSpecialtyBtn').on('click', e => {
        e.preventDefault();
        const data = {
            SpecialtyName: $('#specialtyName').val()?.trim(),
            SpecialtyDescription: $('#specialtyDescription').val()?.trim(),
            ImageUrl: $('#imageUrl').val()?.trim(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        handleSpecialtySubmit('create_specialty', data);
    });

    $('#updateSpecialtyBtn').on('click', e => {
        e.preventDefault();
        const data = {
            specialty_id: $('#specialtyId').val()?.trim(),
            SpecialtyName: $('#specialtyName').val()?.trim(),
            SpecialtyDescription: $('#specialtyDescription').val()?.trim(),
            ImageUrl: $('#imageUrl').val()?.trim(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        if (!data.specialty_id) {
            toastr.error('Specialty ID is required');
            return;
        }
        handleSpecialtySubmit('update_specialty', data);
    });

    $('#resetSpecialtyForm').on('click', () => {
        $('#specialtyForm')[0].reset();
        $('#specialtyId').val('');
        $('#saveSpecialtyBtn').show();
        $('#updateSpecialtyBtn').hide();
    });

    $(document).on('click', '.edit_specialty', function () {
        const specialtyId = $(this).data('specialty-id');
        $.ajax({
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: { action: 'get', specialty_id: specialtyId },
            success: response => {
                if (response.success) {
                    const specialty = response.data;
                    $('#edit_specialty_id').val(specialty.IdSpecialty);
                    $('#edit_specialty_name').val(specialty.SpecialtyName);
                    $('#edit_specialty_description').val(specialty.SpecialtyDescription);
                    $('#edit_image_url').val(specialty.ImageUrl);
                    $('#edit_display_order').val(specialty.DisplayOrder);
                    $('#edit_status').val(specialty.Status);
                    $('#editSpecialtyModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving specialty data');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error retrieving specialty data');
            }
        });
    });

    $(document).on('click', '.delete_specialty', function () {
        const specialtyId = $(this).data('specialty-id');
        const specialtyName = $(this).data('specialty-name');
        $('#delete_specialty_id').val(specialtyId);
        $('#delete_specialty_name').text(specialtyName);
        $('#deleteSpecialtyModal').modal('show');
    });

    $(document).on('click', '#deleteSpecialtyModal .btn-danger', function() {
        const specialtyId = $('#delete_specialty_id').val();
        $.ajax({
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: { action: 'delete', specialty_id: specialtyId },
            success: response => {
                if (response.success) {
                    specialtiesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteSpecialtyModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting specialty');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error deleting specialty');
            }
        });
    });

    $('#confirmDeleteSpecialty').on('click', function() {
        const specialtyId = $('#delete_specialty_id').val();
        $.ajax({
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: { action: 'delete', specialty_id: specialtyId },
            success: response => {
                if (response.success) {
                    specialtiesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteSpecialtyModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting specialty');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error deleting specialty');
            }
        });
    });
}


$(document).ready(function() {
    initializeSpecialtiesDataTable();
});
