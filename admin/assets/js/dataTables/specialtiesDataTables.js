function initializeSpecialtiesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeSpecialtiesDataTable, 1000);
        return;
    }
    
    if ($('#specialtiesTable').length === 0) {
        return;
    }
    
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#specialtiesTable')) {
        return;
    }
    
    const specialtiesDataTable = $('#specialtiesTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiCompanySpecialties.php',
            type: 'GET',
            data: { get_specialties: 1 },
            dataSrc: function (json) {
                if (json.status === 1) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function (xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading specialties data');
            }
        },
        columns: [
            {
                data: 'SpecialtyImage',
                render: function (data) {
                    return data ? `<img src="${data}" alt="Specialty" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` : '<span class="text-muted">No image</span>';
                }
            },
            {
                data: 'SpecialtyName',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'SpecialtyDescription',
                render: function (data) {
                    return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '<span class="text-muted">No description</span>';
                }
            },
            {
                data: 'DisplayOrder',
                render: function (data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            {
                data: 'Status',
                render: function (data) {
                    return `<span class="badge bg-${data ? 'success' : 'secondary'}">${data ? 'Active' : 'Inactive'}</span>`;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-warning btn-sm edit-specialty" 
                                    data-specialty-id="${row.IdSpecialty}" 
                                    title="Edit Specialty">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-specialty" 
                                    data-specialty-id="${row.IdSpecialty}" 
                                    data-specialty-name="${row.SpecialtyName}" 
                                    title="Delete Specialty">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#specialtiesCustomSearch').on('keyup', function () {
        specialtiesDataTable.search(this.value).draw();
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-specialty', function () {
        const specialtyId = $(this).data('specialty-id');
        loadSpecialtyData(specialtyId);
        $('#editSpecialtyModal').modal('show');
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-specialty', function () {
        const specialtyId = $(this).data('specialty-id');
        const specialtyName = $(this).data('specialty-name');
        $('#delete_specialty_id').val(specialtyId);
        $('#delete_specialty_name').text(specialtyName);
        $('#deleteSpecialtyModal').modal('show');
    });

    // Handle Delete Confirmation
    $('#deleteSpecialtyModal .btn-danger').on('click', function () {
        const specialtyId = $('#delete_specialty_id').val();
        
        $.ajax({
            url: 'app/apiCompanySpecialties.php',
            type: 'POST',
            data: {
                action: 'delete',
                specialty_id: specialtyId
            },
            success: function (response) {
                if (response.status === 1) {
                    specialtiesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteSpecialtyModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting specialty');
                }
            },
            error: function (xhr, status, error) {
                console.error('Delete specialty error:', xhr.responseText);
                toastr.error('Error deleting specialty');
            }
        });
    });

    return specialtiesDataTable;
}

function loadSpecialtyData(specialtyId) {
    $.ajax({
        url: 'app/apiCompanySpecialties.php',
        type: 'GET',
        data: {
            get_specialty: 1,
            id: specialtyId
        },
        success: function (response) {
            if (response.status === 1) {
                const specialty = response.data;
                $('#edit_specialty_id').val(specialty.IdSpecialty);
                $('#edit_specialty_name').val(specialty.SpecialtyName);
                $('#edit_specialty_description').val(specialty.SpecialtyDescription);
                $('#edit_specialty_image').val(specialty.SpecialtyImage);
                $('#edit_display_order').val(specialty.DisplayOrder);
                $('#edit_status').val(specialty.Status);
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error('Error loading specialty data');
        }
    });
}

$(document).ready(function () {
    // Initialize DataTable
    const specialtiesDataTable = initializeSpecialtiesDataTable();

    // Handle Add Specialty Form
    $('#addSpecialtyForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');

        $.ajax({
            url: 'app/apiCompanySpecialties.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    $('#addSpecialtyModal').modal('hide');
                    $('#addSpecialtyForm')[0].reset();
                    specialtiesDataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error adding specialty');
                }
            },
            error: function (xhr, status, error) {
                console.error('Add specialty error:', xhr.responseText);
                toastr.error('Error adding specialty');
            }
        });
    });

    // Handle Edit Specialty Form
    $('#editSpecialtyForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        $.ajax({
            url: 'app/apiCompanySpecialties.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    $('#editSpecialtyModal').modal('hide');
                    specialtiesDataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error updating specialty');
                }
            },
            error: function (xhr, status, error) {
                console.error('Edit specialty error:', xhr.responseText);
                toastr.error('Error updating specialty');
            }
        });
    });
}); 