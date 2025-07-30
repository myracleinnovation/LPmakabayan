function initializeAdminsDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeAdminsDataTable, 1000);
        return;
    }
    
    // Check if the table exists
    if ($('#adminsTable').length === 0) {
        return;
    }
    
    const adminsDataTable = $('#adminsTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_admins';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading admins data');
            }
        },
        columns: [
            { 
                data: 'Username', 
                render: function(data, type, row) {
                    let html = `<strong>${data}</strong>`;
                    if (row.IdAdmin == window.currentAdminId) {
                        html += `<span class="badge bg-info ms-2">Current User</span>`;
                    }
                    return html;
                }
            },
            { 
                data: 'Status', 
                render: function(data) {
                    return `<span class="badge bg-${data ? 'success' : 'secondary'}">${data ? 'Active' : 'Inactive'}</span>`;
                }
            },
            { 
                data: 'CreatedTimestamp', 
                render: function(data) {
                    if (data) {
                        const date = new Date(data);
                        return `<small class="text-muted">${date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        })}</small>`;
                    } else {
                        return '<small class="text-muted">Not set</small>';
                    }
                }
            },
            { 
                data: null, 
                render: function(data, type, row) {
                    let html = `
                        <div class="btn-group" role="group">
                            <i class="bi bi-pencil edit_admin" style="cursor: pointer;" data-admin-id="${row.IdAdmin}" title="Edit Admin"></i>
                    `;
                    
                    if (row.IdAdmin != window.currentAdminId) {
                        html += `<i class="bi bi-trash delete_admin" style="cursor: pointer;" data-admin-id="${row.IdAdmin}" data-admin-username="${row.Username}" title="Delete Admin"></i>`;
                    }
                    
                    html += `</div>`;
                    return html;
                }
            }
        ]
    });

    // Search functionality
    $('#adminsCustomSearch').on('keyup', function () {
        adminsDataTable.search(this.value).draw();
    });

    // Handle admin form submission (create/update)
    const handleAdminSubmit = (action, data) => {
        if (!data.Username) {
            toastr.error('Username is required');
            return;
        }

        if (action === 'create_admin' && !data.Password) {
            toastr.error('Password is required for new admin');
            return;
        }

        $.ajax({
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: { [action]: true, ...data },
                    success: response => {
            if (response.success) {
                adminsDataTable.ajax.reload();
                $('#adminForm')[0].reset();
                $('#adminId').val('');
                $('#saveAdminBtn').show();
                $('#updateAdminBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'create_admin' ? 'creating' : 'updating'} admin`);
            }
        },
            error: () => toastr.error(`Error ${action === 'create_admin' ? 'creating' : 'updating'} admin`)
        });
    };

    // Save admin
    $('#saveAdminBtn').on('click', e => {
        e.preventDefault();
        const data = {
            Username: $('#username').val()?.trim(),
            Password: $('#password').val(),
            Status: $('#status').val() || 1
        };
        handleAdminSubmit('create_admin', data);
    });

    // Update admin
    $('#updateAdminBtn').on('click', e => {
        e.preventDefault();
        const data = {
            admin_id: $('#adminId').val()?.trim(),
            Username: $('#username').val()?.trim(),
            Status: $('#status').val() || 1
        };
        if (!data.admin_id) {
            toastr.error('Admin ID is required');
            return;
        }
        handleAdminSubmit('update_admin', data);
    });

    // Reset form
    $('#resetAdminForm').on('click', () => {
        $('#adminForm')[0].reset();
        $('#adminId').val('');
        $('#saveAdminBtn').show();
        $('#updateAdminBtn').hide();
    });

    // Edit admin
    $(document).on('click', '.edit_admin', function () {
        const adminId = $(this).data('admin-id');
        $.ajax({
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: { action: 'get', admin_id: adminId },
            success: response => {
                if (response.success) {
                    const admin = response.data;
                    $('#edit_admin_id').val(admin.IdAdmin);
                    $('#edit_username').val(admin.Username);
                    $('#edit_status').val(admin.Status);
                    $('#editAdminModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving admin data');
                }
            },
            error: () => toastr.error('Error retrieving admin data')
        });
    });

    // Delete admin
    $(document).on('click', '.delete_admin', function () {
        const adminId = $(this).data('admin-id');
        const adminUsername = $(this).data('admin-username');
        $('#delete_admin_id').val(adminId);
        $('#delete_admin_username').text(adminUsername);
        $('#deleteAdminModal').modal('show');
    });

    // Delete admin button in modal
    $(document).on('click', '#deleteAdminModal .btn-danger', function() {
        const adminId = $('#delete_admin_id').val();
        $.ajax({
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: { action: 'delete', admin_id: adminId },
            success: response => {
                if (response.success) {
                    adminsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteAdminModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting admin');
                }
            },
            error: () => toastr.error('Error deleting admin')
        });
    });

    // Confirm delete admin
    $('#confirmDeleteAdmin').on('click', function() {
        const adminId = $('#delete_admin_id').val();
        $.ajax({
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: { action: 'delete', admin_id: adminId },
            success: response => {
                if (response.success) {
                    adminsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteAdminModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting admin');
                }
            },
            error: () => toastr.error('Error deleting admin')
        });
    });

    // Set current admin ID for comparison
    window.currentAdminId = window.currentAdminId || 0;
}

$(document).ready(function() {
    initializeAdminsDataTable();
});