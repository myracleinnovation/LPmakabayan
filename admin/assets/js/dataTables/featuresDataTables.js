// Initialize Features DataTable
function initializeFeaturesDataTable() {
    console.log('Initializing Features DataTable...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTable available:', typeof $.fn.DataTable);
    
    // Check if DataTable is available
    if (typeof $.fn.DataTable === 'undefined') {
        console.log('DataTable not available yet, retrying in 1 second...');
        setTimeout(initializeFeaturesDataTable, 1000);
        return;
    }
    
    // Check if the table exists
    if ($('#featuresTable').length === 0) {
        console.log('Features table not found on this page, skipping initialization.');
        return;
    }
    
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#featuresTable')) {
        console.log('Features DataTable already initialized, skipping.');
        return;
    }
    
    const featuresDataTable = $('#featuresTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
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
                        return `<img src="${data}" alt="${row.FeatureTitle}" class="feature-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">`;
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
                            <button class="btn btn-warning btn-sm edit-feature" 
                                    data-feature-id="${row.IdFeature}" 
                                    title="Edit Feature">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-feature" 
                                    data-feature-id="${row.IdFeature}" 
                                    data-feature-title="${row.FeatureTitle}" 
                                    title="Delete Feature">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Search functionality
    $('#featuresCustomSearch').on('keyup', function () {
        featuresDataTable.search(this.value).draw();
    });

    // Handle feature form submission (create/update)
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

    // Add feature form submission
    $('#addFeatureForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            feature_title: formData.get('feature_title'),
            feature_description: formData.get('feature_description'),
            feature_image: formData.get('feature_image'),
            display_order: formData.get('display_order') || 0,
            status: formData.get('status') || 1
        };
        handleFeatureSubmit('create', data);
    });

    // Edit feature
    $(document).on('click', '.edit-feature', function () {
        const featureId = $(this).data('feature-id');
        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'GET',
            data: { get_feature: true, id: featureId },
            success: response => {
                if (response.status === 1) {
                    const feature = response.data;
                    $('#edit_feature_id').val(feature.IdFeature);
                    $('#edit_feature_title').val(feature.FeatureTitle);
                    $('#edit_feature_description').val(feature.FeatureDescription);
                    $('#edit_feature_image').val(feature.FeatureImage);
                    $('#edit_display_order').val(feature.DisplayOrder);
                    $('#edit_status').val(feature.Status);
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

    // Edit feature form submission
    $('#editFeatureForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            feature_id: formData.get('feature_id'),
            feature_title: formData.get('feature_title'),
            feature_description: formData.get('feature_description'),
            feature_image: formData.get('feature_image'),
            display_order: formData.get('display_order') || 0,
            status: formData.get('status') || 1
        };
        handleFeatureSubmit('update', data);
    });

    // Delete feature
    $(document).on('click', '.delete-feature', function () {
        const featureId = $(this).data('feature-id');
        const featureTitle = $(this).data('feature-title');
        $('#delete_feature_id').val(featureId);
        $('#delete_feature_title').text(featureTitle);
        $('#deleteFeatureModal').modal('show');
    });

    // Delete feature button in modal
    $(document).on('click', '#deleteFeatureModal .btn-danger', function() {
        const featureId = $('#delete_feature_id').val();
        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'POST',
            data: { action: 'delete', feature_id: featureId },
            success: response => {
                if (response.status === 1) {
                    featuresDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteFeatureModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting feature');
                }
            },
            error: (xhr, status, error) => {
                console.error('Ajax error:', status, error);
                toastr.error('Error deleting feature');
            }
        });
    });
}

// Start initialization when document is ready
$(document).ready(function() {
    initializeFeaturesDataTable();
}); 