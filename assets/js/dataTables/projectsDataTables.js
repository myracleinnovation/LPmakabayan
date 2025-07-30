function initializeProjectsDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeProjectsDataTable, 1000);
        return;
    }
    
    if ($('#projectsTable').length === 0) {
        return;
    }
    
    const projectsDataTable = $('#projectsTable').DataTable({
        columnDefs: [{
            orderable: false,
            targets: [-1]
        }],
        order: [
            [0, 'asc']
        ],
        dom: "<'row'<'col-12 mb-3'tr>>" +
            "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiProjects.php',
            type: 'POST',
            data: {
                action: 'get_projects'
            },
            dataSrc: function (json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function (xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading projects data');
            }
        },
        columns: [{
                data: 'ProjectTitle',
                render: function (data, type, row) {
                    const description = row.ProjectDescription ?
                        (row.ProjectDescription.length > 50 ? row.ProjectDescription.substring(0, 50) + '...' : row.ProjectDescription) :
                        'No description';
                    return `<div><strong>${data}</strong><br><small class="text-muted">${description}</small></div>`;
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
                data: 'ProjectCategory',
                render: function (data) {
                    return `<span class="badge bg-info">${data || 'Uncategorized'}</span>`;
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
                render: function (data) {
                    return `<span class="badge bg-${data ? 'success' : 'secondary'}">${data ? 'Active' : 'Inactive'}</span>`;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <i class="bi bi-pencil edit_project" style="cursor: pointer;" data-project-id="${row.IdProject}" title="Edit Project"></i>
                            <i class="bi bi-trash delete_project" style="cursor: pointer;" data-project-id="${row.IdProject}" data-project-title="${row.ProjectTitle}" title="Delete Project"></i>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#projectsCustomSearch').on('keyup', function () {
        projectsDataTable.search(this.value).draw();
    });

    const handleProjectSubmit = (action, data) => {
        if (!data.ProjectTitle) {
            toastr.error('Project title is required');
            return;
        }

        $.ajax({
            url: 'app/apiProjects.php',
            type: 'POST',
            data: {
                [action]: true,
                ...data
            },
            success: response => {
                if (response.success) {
                    projectsDataTable.ajax.reload();
                    $('#projectForm')[0].reset();
                    $('#projectId').val('');
                    $('#saveProjectBtn').show();
                    $('#updateProjectBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'create_project' ? 'creating' : 'updating'} project`);
                }
            },
            error: () => toastr.error(`Error ${action === 'create_project' ? 'creating' : 'updating'} project`)
        });
    };

    $('#saveProjectBtn').on('click', e => {
        e.preventDefault();
        const data = gatherProjectData();
        handleProjectSubmit('create_project', data);
    });

    $('#updateProjectBtn').on('click', e => {
        e.preventDefault();
        const data = {
            project_id: $('#projectId').val() ?.trim(),
            ...gatherProjectData()
        };
        if (!data.project_id) {
            toastr.error('Project ID is required');
            return;
        }
        handleProjectSubmit('update_project', data);
    });

    $('#resetProjectForm').on('click', () => {
        $('#projectForm')[0].reset();
        $('#projectId').val('');
        $('#saveProjectBtn').show();
        $('#updateProjectBtn').hide();
    });

    $(document).on('click', '.edit_project', function () {
        const projectId = $(this).data('project-id');
        $.ajax({
            url: 'app/apiProjects.php',
            type: 'POST',
            data: {
                action: 'get',
                project_id: projectId
            },
            success: response => {
                if (response.success) {
                    populateEditForm(response.data);
                    $('#editProjectModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving project data');
                }
            },
            error: () => toastr.error('Error retrieving project data')
        });
    });

    $(document).on('click', '.delete_project', function () {
        const projectId = $(this).data('project-id');
        const projectTitle = $(this).data('project-title');
        $('#delete_project_id').val(projectId);
        $('#delete_project_title').text(projectTitle);
        $('#deleteProjectModal').modal('show');
    });

    $(document).on('click', '#deleteProjectModal .btn-danger', function () {
        const projectId = $('#delete_project_id').val();
        $.ajax({
            url: 'app/apiProjects.php',
            type: 'POST',
            data: {
                action: 'delete',
                project_id: projectId
            },
            success: response => {
                if (response.success) {
                    projectsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteProjectModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting project');
                }
            },
            error: () => toastr.error('Error deleting project')
        });
    });

    function gatherProjectData() {
        return {
            ProjectTitle: $('#projectTitle').val() ?.trim(),
            ProjectOwner: $('#projectOwner').val() ?.trim(),
            ProjectLocation: $('#projectLocation').val() ?.trim(),
            ProjectCategory: $('#projectCategory').val() ?.trim(),
            ProjectDescription: $('#projectDescription').val() ?.trim(),
            TurnoverDate: $('#turnoverDate').val(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1,
            ImageUrl1: $('#imageUrl1').val() ?.trim(),
            ImageUrl2: $('#imageUrl2').val() ?.trim(),
            ImageUrl3: $('#imageUrl3').val() ?.trim(),
            ImageUrl4: $('#imageUrl4').val() ?.trim()
        };
    }

    function populateEditForm(project) {
        $('#edit_project_id').val(project.IdProject);
        $('#edit_project_title').val(project.ProjectTitle);
        $('#edit_project_owner').val(project.ProjectOwner);
        $('#edit_project_location').val(project.ProjectLocation);
        $('#edit_project_category').val(project.ProjectCategory);
        $('#edit_project_description').val(project.ProjectDescription);
        $('#edit_turnover_date').val(project.TurnoverDate);
        $('#edit_display_order').val(project.DisplayOrder);
        $('#edit_status').val(project.Status);
        $('#edit_image_url_1').val(project.ProjectImage1);
        $('#edit_image_url_2').val(project.ProjectImage2);
        $('#edit_image_url_3').val(project.ProjectImage3);
        $('#edit_image_url_4').val(project.ProjectImage4);
    }
}


$(document).ready(function() {
    initializeProjectsDataTable();
});