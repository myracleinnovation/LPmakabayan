// Initialize DataTable for Features
const featuresDataTable = new DataTable('#featuresTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiCompanyFeatures.php',
        type: 'POST',
        data: { action: 'get_features' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading features data')
    },
    columns: [
        { data: 'IdFeature', render: data => `<div class="text-center">${data}</div>` },
        { data: 'FeatureTitle', render: data => `<div class="text-start">${data}</div>` },
        { data: 'FeatureDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'FeatureImage', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_feature" style="cursor: pointer;" data-feature-id="${row.IdFeature}" title="Edit Feature"></i>
                <i class="bi bi-trash delete_feature" style="cursor: pointer;" data-feature-id="${row.IdFeature}" title="Delete Feature"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#featureCustomSearch').on('keyup', function () {
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
            if (response.success) {
                featuresDataTable.ajax.reload();
                $('#featureForm')[0].reset();
                $('#featureId').val('');
                $('#saveFeatureBtn').show();
                $('#updateFeatureBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} feature`);
            }
        },
        error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} feature`)
    });
};

// Save feature
$('#saveFeatureBtn').on('click', e => {
    e.preventDefault();
    const data = {
        feature_title: $('#featureTitle').val()?.trim(),
        feature_description: $('#featureDescription').val()?.trim(),
        feature_image: $('#featureImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    handleFeatureSubmit('add', data);
});

// Update feature
$('#updateFeatureBtn').on('click', e => {
    e.preventDefault();
    const data = {
        feature_id: $('#featureId').val()?.trim(),
        feature_title: $('#featureTitle').val()?.trim(),
        feature_description: $('#featureDescription').val()?.trim(),
        feature_image: $('#featureImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    if (!data.feature_id) {
        toastr.error('Feature ID is required');
        return;
    }
    handleFeatureSubmit('edit', data);
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
        url: 'app/apiCompanyFeatures.php',
        type: 'POST',
        data: { action: 'get', feature_id: featureId },
        success: response => {
            if (response.success) {
                const { IdFeature, FeatureTitle, FeatureDescription, FeatureImage, DisplayOrder, Status } = response.data;
                $('#featureId').val(IdFeature);
                $('#featureTitle').val(FeatureTitle);
                $('#featureDescription').val(FeatureDescription);
                $('#featureImage').val(FeatureImage);
                $('#displayOrder').val(DisplayOrder);
                $('#status').val(Status);
                $('#saveFeatureBtn').hide();
                $('#updateFeatureBtn').show();
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
    
    if (confirm('Are you sure you want to delete this feature?')) {
        $.ajax({
            url: 'app/apiCompanyFeatures.php',
            type: 'POST',
            data: { action: 'delete', feature_id: featureId },
            success: response => {
                if (response.success) {
                    featuresDataTable.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Error deleting feature');
                }
            },
            error: () => toastr.error('Error deleting feature')
        });
    }
}); 