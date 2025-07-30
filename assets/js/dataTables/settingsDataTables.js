function initializeSettingsDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeSettingsDataTable, 1000);
        return;
    }
    
    if ($('#settingsTable').length === 0) {
        return;
    }
    
    const settingsDataTable = $('#settingsTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiSettings.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_settings';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading settings data');
            }
        },
        columns: [
            { 
                data: 'SettingKey', 
                render: function(data) {
                    return `<div class="text-start"><strong>${data}</strong></div>`;
                }
            },
            { 
                data: 'SettingValue', 
                render: function(data) {
                    return `<div class="text-start">${data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'No value'}</div>`;
                }
            },
            { 
                data: 'SettingDescription', 
                render: function(data) {
                    return `<div class="text-start">${data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'No description'}</div>`;
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
                            <i class="bi bi-pencil edit_setting" style="cursor: pointer;" data-setting-id="${row.IdSetting}" title="Edit Setting"></i>
                            <i class="bi bi-trash delete_setting" style="cursor: pointer;" data-setting-id="${row.IdSetting}" data-setting-key="${row.SettingKey}" title="Delete Setting"></i>
                        </div>
                    `;
                }
            }
        ]
    });

    // Search functionality
    $('#settingsCustomSearch').on('keyup', function () {
        settingsDataTable.search(this.value).draw();
    });

    // Handle setting form submission (create/update)
    const handleSettingSubmit = (action, data) => {
        if (!data.SettingKey) {
            toastr.error('Setting key is required');
            return;
        }

        $.ajax({
            url: 'app/apiSettings.php',
            type: 'POST',
            data: { [action]: true, ...data },
            success: response => {
                if (response.success) {
                    settingsDataTable.ajax.reload();
                    $('#settingForm')[0].reset();
                    $('#settingId').val('');
                    $('#saveSettingBtn').show();
                    $('#updateSettingBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'create_setting' ? 'creating' : 'updating'} setting`);
                }
            },
            error: () => toastr.error(`Error ${action === 'create_setting' ? 'creating' : 'updating'} setting`)
        });
    };

    // Save setting
    $('#saveSettingBtn').on('click', e => {
        e.preventDefault();
        const data = {
            SettingKey: $('#settingKey').val()?.trim(),
            SettingValue: $('#settingValue').val()?.trim(),
            SettingDescription: $('#settingDescription').val()?.trim(),
            Status: $('#status').val() || 1
        };
        handleSettingSubmit('create_setting', data);
    });

    // Update setting
    $('#updateSettingBtn').on('click', e => {
        e.preventDefault();
        const data = {
            setting_id: $('#settingId').val()?.trim(),
            SettingKey: $('#settingKey').val()?.trim(),
            SettingValue: $('#settingValue').val()?.trim(),
            SettingDescription: $('#settingDescription').val()?.trim(),
            Status: $('#status').val() || 1
        };
        if (!data.setting_id) {
            toastr.error('Setting ID is required');
            return;
        }
        handleSettingSubmit('update_setting', data);
    });

    // Reset form
    $('#resetSettingForm').on('click', () => {
        $('#settingForm')[0].reset();
        $('#settingId').val('');
        $('#saveSettingBtn').show();
        $('#updateSettingBtn').hide();
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
                    const setting = response.data;
                    $('#edit_setting_id').val(setting.IdSetting);
                    $('#edit_setting_key').val(setting.SettingKey);
                    $('#edit_setting_value').val(setting.SettingValue);
                    $('#edit_setting_description').val(setting.SettingDescription);
                    $('#edit_status').val(setting.Status);
                    $('#editSettingModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving setting data');
                }
            },
            error: () => toastr.error('Error retrieving setting data')
        });
    });

    // Delete setting
    $(document).on('click', '.delete_setting', function () {
        const settingId = $(this).data('setting-id');
        const settingKey = $(this).data('setting-key');
        $('#delete_setting_id').val(settingId);
        $('#delete_setting_key').text(settingKey);
        $('#deleteSettingModal').modal('show');
    });

    // Delete setting button in modal
    $(document).on('click', '#deleteSettingModal .btn-danger', function() {
        const settingId = $('#delete_setting_id').val();
        $.ajax({
            url: 'app/apiSettings.php',
            type: 'POST',
            data: { action: 'delete', setting_id: settingId },
            success: response => {
                if (response.success) {
                    settingsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteSettingModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting setting');
                }
            },
            error: () => toastr.error('Error deleting setting')
        });
    });

    // Confirm delete setting
    $('#confirmDeleteSetting').on('click', function() {
        const settingId = $('#delete_setting_id').val();
        $.ajax({
            url: 'app/apiSettings.php',
            type: 'POST',
            data: { action: 'delete', setting_id: settingId },
            success: response => {
                if (response.success) {
                    settingsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteSettingModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting setting');
                }
            },
            error: () => toastr.error('Error deleting setting')
        });
    });
}

$(document).ready(function() {
    initializeSettingsDataTable();
});