function initializeFeaturesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeFeaturesDataTable, 1000);
        return;
    }
    
    // Check if the table exists
    if ($('#featuresTable').length === 0) {
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
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_features';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading features data');
            }
        },
        columns: [
            { 
                data: 'ImageUrl', 
                render: function(data, type, row) {
                    if (data) {
                        return `<img src="${data}" alt="${row.FeatureName}" class="feature-image">`;
                    } else {
                        return `<div class="feature-image bg-light d-flex align-items-center justify-content-center"><i class="bi bi-star text-muted"></i></div>`;
                    }
                }
            },
            { 
                data: 'FeatureName', 
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
                            <i class="bi bi-pencil edit_feature" style="cursor: pointer;" data-feature-id="${row.IdFeature}" title="Edit Feature"></i>
                            <i class="bi bi-trash delete_feature" style="cursor: pointer;" data-feature-id="${row.IdFeature}" data-feature-name="${row.FeatureName}" title="Delete Feature"></i>
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
        if (!data.FeatureName) {
            toastr.error('Feature name is required');
            return;
        }

        $.ajax({
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: { [action]: true, ...data },
            success: response => {
                if (response.success) {
                    featuresDataTable.ajax.reload();
                    $('#featureForm')[0].reset();
                    $('#featureId').val('');
                    $('#saveFeatureBtn').show();
                    $('#updateFeatureBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'create_feature' ? 'creating' : 'updating'} feature`);
                }
            },
            error: () => toastr.error(`Error ${action === 'create_feature' ? 'creating' : 'updating'} feature`)
        });
    };

    // Save feature
    $('#saveFeatureBtn').on('click', e => {
        e.preventDefault();
        const data = {
            FeatureName: $('#featureName').val()?.trim(),
            FeatureDescription: $('#featureDescription').val()?.trim(),
            ImageUrl: $('#imageUrl').val()?.trim(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        handleFeatureSubmit('create_feature', data);
    });

    // Update feature
    $('#updateFeatureBtn').on('click', e => {
        e.preventDefault();
        const data = {
            feature_id: $('#featureId').val()?.trim(),
            FeatureName: $('#featureName').val()?.trim(),
            FeatureDescription: $('#featureDescription').val()?.trim(),
            ImageUrl: $('#imageUrl').val()?.trim(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        if (!data.feature_id) {
            toastr.error('Feature ID is required');
            return;
        }
        handleFeatureSubmit('update_feature', data);
    });

    // Reset form
    $('#resetFeatureForm').on('click', () => {
        $('#featureForm')[0].reset();
        $('#featureId').val('');
        $('#saveFeatureBtn').show();
        $('#updateFeatureBtn').hide();
    });

    // Edit feature
    $(document).on('click', '.edit_feature', function () {
        const featureId = $(this).data('feature-id');
        $.ajax({
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: { action: 'get', feature_id: featureId },
            success: response => {
                if (response.success) {
                    const feature = response.data;
                    $('#edit_feature_id').val(feature.IdFeature);
                    $('#edit_feature_name').val(feature.FeatureName);
                    $('#edit_feature_description').val(feature.FeatureDescription);
                    $('#edit_image_url').val(feature.ImageUrl);
                    $('#edit_display_order').val(feature.DisplayOrder);
                    $('#edit_status').val(feature.Status);
                    $('#editFeatureModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving feature data');
                }
            },
            error: () => toastr.error('Error retrieving feature data')
        });
    });

    // Delete feature
    $(document).on('click', '.delete_feature', function () {
        const featureId = $(this).data('feature-id');
        const featureName = $(this).data('feature-name');
        $('#delete_feature_id').val(featureId);
        $('#delete_feature_name').text(featureName);
        $('#deleteFeatureModal').modal('show');
    });

    // Delete feature button in modal
    $(document).on('click', '#deleteFeatureModal .btn-danger', function() {
        const featureId = $('#delete_feature_id').val();
        $.ajax({
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: { action: 'delete', feature_id: featureId },
            success: response => {
                if (response.success) {
                    featuresDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteFeatureModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting feature');
                }
            },
            error: () => toastr.error('Error deleting feature')
        });
    });

    // Confirm delete feature
    $('#confirmDeleteFeature').on('click', function() {
        const featureId = $('#delete_feature_id').val();
        $.ajax({
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: { action: 'delete', feature_id: featureId },
            success: response => {
                if (response.success) {
                    featuresDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteFeatureModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting feature');
                }
            },
            error: () => toastr.error('Error deleting feature')
        });
    });
}

$(document).ready(function() {
    initializeFeaturesDataTable();
});