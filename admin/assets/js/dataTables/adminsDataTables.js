$(document).ready(function() {
    // Get current admin ID from data attribute
    const currentAdminId = parseInt($('#adminsTable').data('current-admin') || 0);
    
    if ($('#adminsTable').length > 0) {
        if ($.fn.DataTable.isDataTable('#adminsTable')) {
            window.adminsDataTable = $('#adminsTable').DataTable();
        } else {
            window.adminsDataTable = $('#adminsTable').DataTable({
                columnDefs: [{ orderable: false, targets: [-1] }],
                order: [[0, 'asc']],
                dom: "<'row'<'col-12 mb-3'tr>>" +
                     "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'app/apiAdminAccounts.php',
                    type: 'GET',
                    data: { get_admins: true },
                    dataSrc: function(json) {
                        if (json.status === 1) {
                            return json.data.data || [];
                        } else {
                            toastr.error(json.message || 'Error loading data');
                            return [];
                        }
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);
                        toastr.error('Error loading admin data');
                    }
                },
                columns: [
                    { data: 'Username', render: function(data) {
                        return `<div class="text-start">${data || ''}</div>`;
                    }},
                    { data: 'Status', render: function(data) {
                        const status = data == 1 ? 'Active' : 'Inactive';
                        const badgeClass = data == 1 ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${badgeClass}">${status}</span>`;
                    }},
                    { data: 'CreatedTimestamp', render: function(data) {
                        if (!data) return '<div class="text-start">-</div>';
                        return `<div class="text-start">${moment(data).format('MMM DD, YYYY')}</div>`;
                    }},
                    { data: null, render: function(data, type, row) {
                        const editBtn = `<button class="btn btn-outline-primary edit-admin" 
                                    data-admin-id="${row.IdAdmin}" 
                                    title="Edit Admin">
                                <i class="bi bi-pencil"></i>
                            </button>`;
                        
                        return `<div class="btn-group" role="group">${editBtn}</div>`;
                    }}
                ],
                initComplete: function() {
                }
            });
        }
    }

    if ($('#adminsTable').length > 0) {
        $('#adminCustomSearch').on('keyup', function () {
            if (window.adminsDataTable) {
                window.adminsDataTable.search(this.value).draw();
            }
        });

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
                    if (response.status === 1) {
                        if (window.adminsDataTable) {
                            window.adminsDataTable.ajax.reload();
                        }
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

        $('#saveAdminBtn').on('click', e => {
            e.preventDefault();
            const data = {
                username: $('#username').val()?.trim(),
                password: $('#password').val(),
                status: $('#status').val()
            };
            handleAdminSubmit('create', data);
        });

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
            handleAdminSubmit('update', data);
        });

        $('#resetAdminForm').on('click', () => {
            $('#adminForm')[0].reset();
            $('#adminId').val('');
            $('#saveAdminBtn').show();
            $('#updateAdminBtn').hide();
        });

        $(document).on('click', '.edit-admin', function () {
            const adminId = $(this).data('admin-id');
            $.ajax({
                url: 'app/apiAdminAccounts.php',
                type: 'GET',
                data: { get_admin: true, id: adminId },
                success: response => {
                    if (response.status === 1) {
                        const { IdAdmin, Username, Status } = response.data;
                        $('#edit_admin_id').val(IdAdmin);
                        $('#edit_username').val(Username);
                        $('#edit_status').val(Status);
                        $('#editAdminModal').modal('show');
                    } else {
                        toastr.error(response.message || 'Error retrieving admin data');
                    }
                },
                error: () => toastr.error('Error retrieving admin data')
            });
        });
    }
});