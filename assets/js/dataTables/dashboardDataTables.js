$(document).ready(function () {
    const recentProjectsDataTable = $('#recentProjectsTable').DataTable({
        columnDefs: [{
            orderable: false,
            targets: []
        }],
        order: [
            [3, 'desc']
        ],
        dom: "<'row'<'col-12 mb-3'tr>>",
        processing: true,
        serverSide: false,
        pageLength: 5,
        searching: false,
        lengthChange: false,
        info: false,
        ajax: {
            url: 'app/apiCompanyProjects.php',
            type: 'POST',
            data: {
                action: 'get_recent_projects'
            },
            dataSrc: function (json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function () {
                toastr.error('Error loading recent projects data');
            }
        },
        columns: [{
                data: 'ProjectTitle',
                render: function (data) {
                    return `<a href="projects.php">${data}</a>`;
                }
            },
            {
                data: 'ProjectOwner',
                render: function (data) {
                    return data || 'Not specified';
                }
            },
            {
                data: 'ProjectLocation',
                render: function (data) {
                    return data || 'Not specified';
                }
            },
            {
                data: 'TurnoverDate',
                render: function (data) {
                    return data ? new Date(data).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }) : '<span class="text-muted">Not set</span>';
                }
            },
            {
                data: 'Status',
                render: function () {
                    return `<span class="badge bg-success">Active</span>`;
                }
            }
        ]
    });
});