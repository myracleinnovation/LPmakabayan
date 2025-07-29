// Initialize Dashboard DataTable
$(document).ready(function() {
    console.log('Initializing Dashboard DataTable...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTable available:', typeof $.fn.DataTable);
    
    const recentProjectsDataTable = $('#recentProjectsTable').DataTable({
        columnDefs: [{ orderable: false, targets: [] }],
        order: [[3, 'desc']], // Sort by turnover date descending
        dom: "<'row'<'col-12 mb-3'tr>>",
        processing: true,
        serverSide: false,
        pageLength: 5, // Show only 5 recent projects
        searching: false,
        lengthChange: false,
        info: false,
        ajax: {
            url: 'app/apiProjects.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_recent_projects';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading recent projects data');
            }
        },
        columns: [
            { 
                data: 'ProjectTitle', 
                render: function(data) {
                    return `<a href="projects.php">${data}</a>`;
                }
            },
            { 
                data: 'ProjectOwner', 
                render: function(data) {
                    return data || 'Not specified';
                }
            },
            { 
                data: 'ProjectLocation', 
                render: function(data) {
                    return data || 'Not specified';
                }
            },
            { 
                data: 'TurnoverDate', 
                render: function(data) {
                    if (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        });
                    } else {
                        return '<span class="text-muted">Not set</span>';
                    }
                }
            },
            { 
                data: 'Status', 
                render: function(data) {
                    return `<span class="badge bg-success">Active</span>`;
                }
            }
        ]
    });
}); 