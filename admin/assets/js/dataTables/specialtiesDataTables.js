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
                    return data ? `<img src="../assets/img/${data}" alt="Specialty" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` : '<span class="text-muted">No image</span>';
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
                            <button class="btn btn-outline-primary edit-specialty" 
                                    data-specialty-id="${row.IdSpecialty}" 
                                    title="Edit Specialty">
                                <i class="bi bi-pencil"></i>
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

    return specialtiesDataTable;
}

function loadSpecialtyData(specialtyId) {
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.warn('jQuery not available, retrying in 100ms...');
        setTimeout(() => loadSpecialtyData(specialtyId), 100);
        return;
    }
    
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
                
                // Fill form fields
                $('#edit_specialty_id').val(specialty.IdSpecialty);
                $('#edit_specialty_name').val(specialty.SpecialtyName);
                $('#edit_specialty_description').val(specialty.SpecialtyDescription);
                $('#edit_display_order').val(specialty.DisplayOrder);
                $('#edit_status').val(specialty.Status);
                
                // Show current image if it exists
                if (specialty.SpecialtyImage) {
                    $('#current_specialty_image_preview').html(`
                        <small class="text-muted">Current Image:</small><br>
                        <img src="../assets/img/${specialty.SpecialtyImage}" alt="Current Specialty Image" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                    `);
                } else {
                    $('#current_specialty_image_preview').html('');
                }
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
    // Only initialize if the specialties table exists on this page
    if ($('#specialtiesTable').length > 0) {
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
    }
}); 