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
            url: 'app/apiCompanyProjects.php',
            type: 'GET',
            data: function (d) {
                d.get_projects = 1;
                return d;
            },
            dataSrc: function (json) {
                if (json.status === 1) {
                    // Update DataTables with total records for pagination
                    if (json.recordsTotal !== undefined) {
                        $('#projectsTable').DataTable().page.len(json.recordsTotal).draw();
                    }
                    return json.data || [];
                }
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
                data: 'CategoryName',
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
                            <button class="btn btn-warning btn-sm edit-project" 
                                    data-project-id="${row.IdProject}" 
                                    title="Edit Project">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-project" 
                                    data-project-id="${row.IdProject}" 
                                    data-project-title="${row.ProjectTitle}" 
                                    title="Delete Project">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#projectsCustomSearch').on('keyup', function () {
        projectsDataTable.search(this.value).draw();
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-project', function () {
        const projectId = $(this).data('project-id');
        loadProjectData(projectId);
        $('#editProjectModal').modal('show');
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-project', function () {
        const projectId = $(this).data('project-id');
        const projectTitle = $(this).data('project-title');
        $('#delete_project_id').val(projectId);
        $('#delete_project_title').text(projectTitle);
        $('#deleteProjectModal').modal('show');
    });

    // Handle Delete Confirmation
    $('#deleteProjectModal .btn-danger').on('click', function () {
        const projectId = $('#delete_project_id').val();
        
        $.ajax({
            url: 'app/apiCompanyProjects.php',
            type: 'POST',
            data: {
                action: 'delete',
                project_id: projectId
            },
            success: function (response) {
                if (response.status === 1) {
                    projectsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteProjectModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting project');
                }
            },
            error: function (xhr, status, error) {
                console.error('Delete project error:', xhr.responseText);
                toastr.error('Error deleting project');
            }
        });
    });

    return projectsDataTable;
}

function loadCategories() {
    $.ajax({
        url: 'app/apiCompanyProjects.php',
        type: 'GET',
        data: {
            get_categories: 1
        },
        success: function (response) {
            if (response.status === 1) {
                const categories = response.data;
                let options = '<option value="">Select Category</option>';
                categories.forEach(function (category) {
                    options += `<option value="${category.IdCategory}">${category.CategoryName}</option>`;
                });
                $('select[name="project_category_id"], #edit_project_category_id').html(options);
            } else {
                console.error('Error loading categories:', response.message);
            }
        },
        error: function () {
            console.error('Error loading categories');
        }
    });
}

function loadProjectData(projectId) {
    $.ajax({
        url: 'app/apiCompanyProjects.php',
        type: 'GET',
        data: {
            get_project: 1,
            id: projectId
        },
        success: function (response) {
            if (response.status === 1) {
                const project = response.data;
                $('#edit_project_id').val(project.IdProject);
                $('#edit_project_title').val(project.ProjectTitle);
                $('#edit_project_owner').val(project.ProjectOwner);
                $('#edit_project_location').val(project.ProjectLocation);
                $('#edit_project_area').val(project.ProjectArea);
                $('#edit_project_value').val(project.ProjectValue);
                $('#edit_turnover_date').val(project.TurnoverDate);
                $('#edit_project_category_id').val(project.ProjectCategoryId);
                $('#edit_project_description').val(project.ProjectDescription);
                $('#edit_display_order').val(project.DisplayOrder);
                $('#edit_status').val(project.Status);
                $('#edit_project_image1').val(project.ProjectImage1);
                $('#edit_project_image2').val(project.ProjectImage2);
                $('#edit_project_image3').val(project.ProjectImage3);
                $('#edit_project_image4').val(project.ProjectImage4);
                $('#edit_project_image5').val(project.ProjectImage5);
                $('#edit_project_image6').val(project.ProjectImage6);
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error('Error loading project data');
        }
    });
}

$(document).ready(function () {
    // Initialize DataTable
    const projectsDataTable = initializeProjectsDataTable();
    
    // Load categories for dropdowns
    loadCategories();

    // Handle Add Project Form
    $('#addProjectForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');

        $.ajax({
            url: 'app/apiCompanyProjects.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    $('#addProjectModal').modal('hide');
                    $('#addProjectForm')[0].reset();
                    projectsDataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error adding project');
                }
            },
            error: function (xhr, status, error) {
                console.error('Add project error:', xhr.responseText);
                toastr.error('Error adding project');
            }
        });
    });

    // Handle Edit Project Form
    $('#editProjectForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        $.ajax({
            url: 'app/apiCompanyProjects.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    $('#editProjectModal').modal('hide');
                    projectsDataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error updating project');
                }
            },
            error: function (xhr, status, error) {
                console.error('Edit project error:', xhr.responseText);
                toastr.error('Error updating project');
            }
        });
    });
});
