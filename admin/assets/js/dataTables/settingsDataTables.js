// Initialize DataTable for Settings
const settingsDataTable = new DataTable('#dataTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiSettings.php',
        type: 'POST',
        data: { action: 'get_settings' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading settings data')
    },
    columns: [
        { data: 'SettingKey', render: data => `<div class="text-start">${data}</div>` },
        { data: 'SettingValue', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'SettingDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'SettingType', render: data => `<span class="badge bg-info">${data}</span>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_setting" style="cursor: pointer;" data-setting-id="${row.IdSetting}" title="Edit Setting"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#settingCustomSearch').on('keyup', function () {
    settingsDataTable.search(this.value).draw();
});

// Handle setting form submission (update only)
const handleSettingSubmit = (data) => {
    if (!data.setting_key) {
        toastr.error('Setting key is required');
        return;
    }

    $.ajax({
        url: 'app/apiSettings.php',
        type: 'POST',
        data: { action: 'edit', ...data },
        success: response => {
            if (response.success) {
                settingsDataTable.ajax.reload();
                $('#settingForm')[0].reset();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || 'Error updating setting');
            }
        },
        error: () => toastr.error('Error updating setting')
    });
};

// Update setting
$('#updateSettingBtn').on('click', e => {
    e.preventDefault();
    const data = {
        setting_id: $('#settingId').val()?.trim(),
        setting_key: $('#settingKey').val()?.trim(),
        setting_value: $('#settingValue').val()?.trim(),
        setting_description: $('#settingDescription').val()?.trim(),
        setting_type: $('#settingType').val(),
        status: $('#status').val()
    };
    if (!data.setting_id) {
        toastr.error('Setting ID is required');
        return;
    }
    handleSettingSubmit(data);
});

// Reset form
$('#resetSettingForm').on('click', () => {
    $('#settingForm')[0].reset();
});

// Edit setting
$(document).on('click', '.edit_setting', function () {
    const settingId = $(this).data('setting-id');
    $.ajax({
        url: 'app/apiSettings.php',
        type: 'POST',
        data: { action: 'get', setting_id: settingId },
        success: response => {
            if (response.success) {
                const { IdSetting, SettingKey, SettingValue, SettingDescription, SettingType, Status } = response.data;
                $('#settingId').val(IdSetting);
                $('#settingKey').val(SettingKey);
                $('#settingValue').val(SettingValue);
                $('#settingDescription').val(SettingDescription);
                $('#settingType').val(SettingType);
                $('#status').val(Status);
            } else {
                toastr.error(response.message || 'Error retrieving setting data');
            }
        },
        error: () => toastr.error('Error retrieving setting data')
    });
}); 