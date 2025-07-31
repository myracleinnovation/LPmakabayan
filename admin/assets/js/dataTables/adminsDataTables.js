// Initialize DataTable for Admin Accounts
let adminsDataTable;

// Only initialize if the admins table exists on this page
if ($('#adminsTable').length > 0) {
    // Check if DataTable already exists
    if ($.fn.DataTable.isDataTable('#adminsTable')) {
        adminsDataTable = $('#adminsTable').DataTable();
    } else {
        adminsDataTable = $('#adminsTable').DataTable({
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiAdminAccounts.php',
        type: 'POST',
        data: { action: 'get_admins' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading admin data')
    },
    columns: [
        { data: 'Username', render: data => `<div class="text-start">${data}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: 'CreatedTimestamp', render: data => `<div class="text-start">${moment(data).format('MMM DD, YYYY')}</div>` },
        { data: null, render: (_, __, row) => `
            <div class="btn-group" role="group">
                <button class="btn btn-warning btn-sm edit-admin" 
                        data-admin-id="${row.IdAdmin}" 
                        title="Edit Admin">
                    <i class="bi bi-pencil"></i>
                </button>
                ${row.IdAdmin != currentAdminId ? `<button class="btn btn-danger btn-sm delete-admin" 
                        data-admin-id="${row.IdAdmin}" 
                        title="Delete Admin">
                    <i class="bi bi-trash"></i>
                </button>` : ''}
            </div>
        ` }
    ]
    });
    }
}

// Only run these functions if the admins table exists
if ($('#adminsTable').length > 0) {
    // Search functionality
    $('#adminCustomSearch').on('keyup', function () {
        adminsDataTable.search(this.value).draw();
    });

    // Handle admin form submission (create/update)
    const handleAdminSubmit = (action, data) => {
    if (!data.username) {
        toastr.error('Username is required');
        return;
    }
    
    if (action === 'add' && !data.password) {
        toastr.error('Password is required');
        return;
    }

    $.ajax({
        url: 'app/apiAdminAccounts.php',
        type: 'POST',
        data: { action: action, ...data },
        success: response => {
            if (response.success) {
                adminsDataTable.ajax.reload();
                $('#adminForm')[0].reset();
                $('#adminId').val('');
                $('#saveAdminBtn').show();
                $('#updateAdminBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} admin`);
            }
        },
        error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} admin`)
    });
};

// Save admin
$('#saveAdminBtn').on('click', e => {
    e.preventDefault();
    const data = {
        username: $('#username').val()?.trim(),
        password: $('#password').val(),
        status: $('#status').val()
    };
    handleAdminSubmit('add', data);
});

// Update admin
$('#updateAdminBtn').on('click', e => {
    e.preventDefault();
    const data = {
        admin_id: $('#adminId').val()?.trim(),
        username: $('#username').val()?.trim(),
        status: $('#status').val()
    };
    if (!data.admin_id) {
        toastr.error('Admin ID is required');
        return;
    }
    handleAdminSubmit('edit', data);
});

// Reset form
$('#resetAdminForm').on('click', () => {
    $('#adminForm')[0].reset();
    $('#adminId').val('');
    $('#saveAdminBtn').show();
    $('#updateAdminBtn').hide();
});

// Edit admin
$(document).on('click', '.edit-admin', function () {
    const adminId = $(this).data('admin-id');
    $.ajax({
        url: 'app/apiAdminAccounts.php',
        type: 'POST',
        data: { action: 'get', admin_id: adminId },
        success: response => {
            if (response.success) {
                const { IdAdmin, Username, Status } = response.data;
                $('#adminId').val(IdAdmin);
                $('#username').val(Username);
                $('#status').val(Status);
                $('#saveAdminBtn').hide();
                $('#updateAdminBtn').show();
            } else {
                toastr.error(response.message || 'Error retrieving admin data');
            }
        },
        error: () => toastr.error('Error retrieving admin data')
    });
});

// Delete admin
$(document).on('click', '.delete-admin', function () {
    const adminId = $(this).data('admin-id');
    
    if (confirm('Are you sure you want to delete this admin account?')) {
        $.ajax({
            url: 'app/apiAdminAccounts.php',
            type: 'POST',
            data: { action: 'delete', admin_id: adminId },
            success: response => {
                if (response.success) {
                    adminsDataTable.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Error deleting admin');
                }
            },
            error: () => toastr.error('Error deleting admin')
        });
    }
});
}