// Initialize Features DataTable
function initializeFeaturesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeFeaturesDataTable, 1000);
        return;
    }
    
    if ($('#featuresTable').length === 0) {
        return;
    }
    
    if ($.fn.DataTable.isDataTable('#featuresTable')) {
        return;
    }
    
    const featuresDataTable = $('#featuresTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        ajax: {
            url: 'app/apiCompanyFeatures.php',
            type: 'GET',
            data: function(d) {
                d.get_features = true;
                return d;
            },
            dataSrc: function(json) {
                if (json.status === 1) return json.data.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading features data');
            }
        },
        columns: [
            { 
                data: 'FeatureImage', 
                render: function(data, type, row) {
                    if (data) {
                        return `<img src="../assets/img/${data}" alt="${row.FeatureTitle}" class="feature-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">`;
                    } else {
                        return `<div class="feature-image bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 8px;"><i class="bi bi-star text-muted"></i></div>`;
                    }
                }
            },
            { 
                data: 'FeatureTitle', 
                render: function(data) {
                    return `<div class="text-start"><strong>${data}</strong></div>`;
                }
            },
            { 
                data: 'FeatureDescription', 
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
                            <button class="btn btn-outline-primary edit-feature" 
                                    data-feature-id="${row.IdFeature}" 
                                    title="Edit Feature">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#featuresCustomSearch').on('keyup', function () {
        featuresDataTable.search(this.value).draw();
    });

    const handleFeatureSubmit = (action, data) => {
        if (!data.feature_title) {
            toastr.error('Feature title is required');
            return;
        }

        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'POST',
            data: { action: action, ...data },
            success: response => {
                if (response.status === 1) {
                    featuresDataTable.ajax.reload();
                    $('#addFeatureForm')[0].reset();
                    $('#editFeatureForm')[0].reset();
                    toastr.success(response.message);
                    $('.modal').modal('hide');
                } else {
                    toastr.error(response.message || `Error ${action === 'create' ? 'creating' : 'updating'} feature`);
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error(`Error ${action === 'create' ? 'creating' : 'updating'} feature`);
            }
        });
    };

    $('#addFeatureForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');

        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: response => {
                if (response.status === 1) {
                    featuresDataTable.ajax.reload();
                    $('#addFeatureForm')[0].reset();
                    toastr.success(response.message);
                    $('#addFeatureModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error creating feature');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error creating feature');
            }
        });
    });

    $(document).on('click', '.edit-feature', function () {
        const featureId = $(this).data('feature-id');
        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'GET',
            data: { get_feature: true, id: featureId },
            success: response => {
                if (response.status === 1) {
                    const feature = response.data;
                    
                    // Fill form fields
                    $('#edit_feature_id').val(feature.IdFeature);
                    $('#edit_feature_title').val(feature.FeatureTitle);
                    $('#edit_feature_description').val(feature.FeatureDescription);
                    $('#edit_display_order').val(feature.DisplayOrder);
                    $('#edit_status').val(feature.Status);
                    
                    // Show current image if it exists
                    if (feature.FeatureImage) {
                        $('#current_feature_image_preview').html(`
                            <small class="text-muted">Current Image:</small><br>
                            <img src="../assets/img/${feature.FeatureImage}" alt="Current Feature Image" 
                                 style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                        `);
                    } else {
                        $('#current_feature_image_preview').html('');
                    }
                    
                    $('#editFeatureModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving feature data');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error retrieving feature data');
            }
        });
    });

    $('#editFeatureForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: response => {
                if (response.status === 1) {
                    featuresDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#editFeatureModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error updating feature');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error updating feature');
            }
        });
    });
}

$(document).ready(function() {
    // Only initialize if the features table exists on this page
    if ($('#featuresTable').length > 0) {
        initializeFeaturesDataTable();
    }
}); 