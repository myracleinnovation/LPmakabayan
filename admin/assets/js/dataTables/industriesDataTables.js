function initializeIndustriesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeIndustriesDataTable, 1000);
        return;
    }
    
    if ($('#industriesTable').length === 0) {
        return;
    }
    
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#industriesTable')) {
        return;
    }
    
    const industriesDataTable = $('#industriesTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiCompanyIndustries.php',
            type: 'GET',
            data: { get_industries: 1 },
            dataSrc: function (json) {
                if (json.status === 1) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function (xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading industries data');
            }
        },
        columns: [
            {
                data: 'IndustryImage',
                render: function (data) {
                    return data ? `<img src="../assets/img/${data}" alt="Industry" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` : '<span class="text-muted">No image</span>';
                }
            },
            {
                data: 'IndustryName',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'IndustryDescription',
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
                            <button class="btn btn-warning  edit-industry" 
                                    data-industry-id="${row.IdIndustry}" 
                                    title="Edit Industry">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger  delete-industry" 
                                    data-industry-id="${row.IdIndustry}" 
                                    data-industry-name="${row.IndustryName}" 
                                    title="Delete Industry">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#industriesCustomSearch').on('keyup', function () {
        industriesDataTable.search(this.value).draw();
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-industry', function () {
        const industryId = $(this).data('industry-id');
        loadIndustryData(industryId);
        $('#editIndustryModal').modal('show');
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-industry', function () {
        const industryId = $(this).data('industry-id');
        const industryName = $(this).data('industry-name');
        $('#delete_industry_id').val(industryId);
        $('#delete_industry_name').text(industryName);
        $('#deleteIndustryModal').modal('show');
    });

    // Handle Delete Confirmation
    $('#deleteIndustryModal .btn-danger').on('click', function () {
        const industryId = $('#delete_industry_id').val();
        
        $.ajax({
            url: 'app/apiCompanyIndustries.php',
            type: 'POST',
            data: {
                action: 'delete',
                industry_id: industryId
            },
            success: function (response) {
                if (response.status === 1) {
                    industriesDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteIndustryModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting industry');
                }
            },
            error: function (xhr, status, error) {
                console.error('Delete industry error:', xhr.responseText);
                toastr.error('Error deleting industry');
            }
        });
    });

    return industriesDataTable;
}

function loadIndustryData(industryId) {
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.warn('jQuery not available, retrying in 100ms...');
        setTimeout(() => loadIndustryData(industryId), 100);
        return;
    }
    
    $.ajax({
        url: 'app/apiCompanyIndustries.php',
        type: 'GET',
        data: {
            get_industry: 1,
            id: industryId
        },
        success: function (response) {
            if (response.status === 1) {
                const industry = response.data;
                
                // Fill form fields
                $('#edit_industry_id').val(industry.IdIndustry);
                $('#edit_industry_name').val(industry.IndustryName);
                $('#edit_industry_description').val(industry.IndustryDescription);
                $('#edit_display_order').val(industry.DisplayOrder);
                $('#edit_status').val(industry.Status);
                
                // Show current image if it exists
                if (industry.IndustryImage) {
                    $('#current_industry_image_preview').html(`
                        <small class="text-muted">Current Image:</small><br>
                        <img src="../assets/img/${industry.IndustryImage}" alt="Current Industry Image" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                    `);
                } else {
                    $('#current_industry_image_preview').html('');
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error('Error loading industry data');
        }
    });
}

$(document).ready(function () {
    // Only initialize if the industries table exists on this page
    if ($('#industriesTable').length > 0) {
        // Initialize DataTable
        const industriesDataTable = initializeIndustriesDataTable();

        // Handle Add Industry Form
        $('#addIndustryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'create');

            $.ajax({
                url: 'app/apiCompanyIndustries.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#addIndustryModal').modal('hide');
                        $('#addIndustryForm')[0].reset();
                        industriesDataTable.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Error adding industry');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Add industry error:', xhr.responseText);
                    toastr.error('Error adding industry');
                }
            });
        });

        // Handle Edit Industry Form
        $('#editIndustryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

            $.ajax({
                url: 'app/apiCompanyIndustries.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#editIndustryModal').modal('hide');
                        industriesDataTable.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Error updating industry');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Edit industry error:', xhr.responseText);
                    toastr.error('Error updating industry');
                }
            });
        });
    }
}); 