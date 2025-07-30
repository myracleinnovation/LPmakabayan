// Initialize DataTable for Logs
const logsDataTable = new DataTable('.logs_table', {
    columnDefs: [{
        orderable: false,
        targets: []
    }],
    order: [
        [0, 'desc']
    ], // Sort by timestamp descending
    dom: "<'row'<'col-12 mb-3'tr>>" +
        "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    serverSide: true,
    ajax: {
        url: 'app/API/apiLogs.php',
        type: 'POST',
        data: function (d) {
            d.get_logs_datatable = true;
        },
        dataSrc: json => json.data || []
    },
    columns: [{
            data: 'timestamp',
            render: data => `<div class="text-start">${moment(data).format('MMM DD, YYYY h:mm A')}</div>`
        },
        {
            data: 'ToolName',
            render: data => `<div class="text-start"><span class="badge bg-primary">${data}</span></div>`
        },
        {
            data: 'Subject',
            render: data => {
                let badgeClass = 'bg-secondary';
                switch (data) {
                    case 'CREATE':
                        badgeClass = 'bg-success';
                        break;
                    case 'UPDATE':
                        badgeClass = 'bg-warning';
                        break;
                    case 'DELETE':
                        badgeClass = 'bg-danger';
                        break;
                    case 'VIEW':
                        badgeClass = 'bg-info';
                        break;
                    case 'LOGIN':
                        badgeClass = 'bg-primary';
                        break;
                    case 'LOGOUT':
                        badgeClass = 'bg-dark';
                        break;
                    case 'ERROR':
                        badgeClass = 'bg-danger';
                        break;
                }
                return `<div class="text-start"><span class="badge ${badgeClass}">${data}</span></div>`;
            }
        },
        {
            data: 'OrderAuthor',
            render: data => `<div class="text-start fw-semibold">${data}</div>`
        },
        {
            data: 'Description',
            render: data => {
                const truncated = data.length > 100 ? data.substring(0, 100) + '...' : data;
                return `<div class="text-start" title="${data}">${truncated}</div>`;
            }
        }
    ],
    pageLength: 25,
    lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
    ],
    language: {
        processing: '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...',
        search: "Search:",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total entries)",
        emptyTable: "No logs found",
        zeroRecords: "No matching logs found",
        paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous"
        }
    },
    responsive: true,
    drawCallback: function (settings) {
        // Add tooltips to truncated descriptions
        $('[title]').tooltip();
    }
});

// Search functionality
$('#logSearchInput').on('keyup', function () {
    logsDataTable.search(this.value).draw();
});

// Auto-refresh logs every 30 seconds when modal is open
let refreshInterval;
$('#LogsModal').on('shown.bs.modal', function () {
    refreshInterval = setInterval(function () {
        logsDataTable.ajax.reload(null, false); // false = stay on current page
    }, 30000); // 30 seconds
});

$('#LogsModal').on('hidden.bs.modal', function () {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
});