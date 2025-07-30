// Initialize DataTable
const orderStatusDataTable = new DataTable('.order_status_table', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/API/apiOrderStatuses.php?get_order_statuses',
        type: 'GET',
        dataSrc: json => {
            if (json.status === 1) return json.data.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading order status data')
    },
    columns: [
        { data: 'StatusName', render: data => `<div class="text-start">${data}</div>` },
        { 
            data: null, 
            render: function(data, type, row) {
                const defaultStatuses = ['Pending', 'Completed', 'Cancelled'];
                const isDefault = defaultStatuses.includes(row.StatusName);
                
                if (isDefault) {
                    return `<div>
                        <i class="bi bi-pen text-muted" style="cursor: not-allowed;" title="Default statuses cannot be edited"></i>
                    </div>`;
                } else {
                    return `<div>
                        <i class="bi bi-pen edit_order_status" style="cursor: pointer;" data-status-id="${row.idOrderStatus}" title="Edit Order Status"></i>
                    </div>`;
                }
            }
        }
    ]
});

// Search functionality
$('#orderStatusCustomSearch').on('keyup', function () {
    orderStatusDataTable.search(this.value).draw();
});

// Handle order status form submission (create/update)
const handleOrderStatusSubmit = (action, data) => {
    if (!data.StatusName) {
        toastr.error('Order status name is required');
        return;
    }

    $.ajax({
        url: 'app/API/apiOrderStatuses.php',
        type: 'POST',
        data: { [action]: true, ...data },
        success: response => {
            if (response.status === 1) {
                orderStatusDataTable.ajax.reload();
                $('#orderStatusForm')[0].reset();
                $('#statusId').val('');
                $('#saveOrderStatusBtn').show();
                $('#updateOrderStatusBtn').hide();
                toastr.success(response.message);
                refreshOrderStatusDropdown();
                reloadOrderStatusRelatedTables();
            } else {
                toastr.error(response.message || `Error ${action === 'create_order_status' ? 'creating' : 'updating'} order status`);
            }
        },
        error: () => toastr.error(`Error ${action === 'create_order_status' ? 'creating' : 'updating'} order status`)
    });
};

// Save order status
$('#saveOrderStatusBtn').on('click', e => {
    e.preventDefault();
    handleOrderStatusSubmit('create_order_status', { StatusName: $('#statusName').val()?.trim() });
});

// Update order status
$('#updateOrderStatusBtn').on('click', e => {
    e.preventDefault();
    const data = {
        status_id: $('#statusId').val()?.trim(),
        StatusName: $('#statusName').val()?.trim()
    };
    if (!data.status_id) {
        toastr.error('Order status ID is required');
        return;
    }
    handleOrderStatusSubmit('update_order_status', data);
});

// Reset form
$('#resetOrderStatusForm').on('click', () => {
    $('#orderStatusForm')[0].reset();
    $('#statusId').val('');
    $('#saveOrderStatusBtn').show();
    $('#updateOrderStatusBtn').hide();
});

// Edit order status
$(document).on('click', '.edit_order_status', function () {
    $.ajax({
        url: 'app/API/apiOrderStatuses.php?get_order_status',
        type: 'GET',
        data: { get_order_status: true, id: $(this).data('status-id') },
        success: response => {
            if (response.status === 1) {
                const { idOrderStatus, StatusName } = response.data;
                $('#statusId').val(idOrderStatus);
                $('#statusName').val(StatusName);
                $('#saveOrderStatusBtn').hide();
                $('#updateOrderStatusBtn').show();
            } else {
                toastr.error(response.message || 'Error retrieving order status data');
            }
        },
        error: () => toastr.error('Error retrieving order status data')
    });
});

// Refresh order status dropdown
const refreshOrderStatusDropdown = () => {
    $.ajax({
        url: 'app/API/apiOrderStatuses.php?get_order_statuses',
        type: 'GET',
        success: response => {
            if (response.status === 1) {
                const options = ['<option value="" disabled selected>Select order status</option>']
                    .concat(response.data.data.map(status => `<option value="${status.idOrderStatus}">${status.StatusName}</option>`))
                    .join('');
                $('select[name="OrderStatusId"]').html(options);
            } else {
                toastr.error('Error loading order statuses');
            }
        },
        error: () => toastr.error('Error loading order statuses')
    });
};

// Reload related tables
const reloadOrderStatusRelatedTables = () => {
    ['orderDataTable'].forEach(table => {
        if (typeof window[table] !== 'undefined') window[table].ajax.reload();
    });
}; 