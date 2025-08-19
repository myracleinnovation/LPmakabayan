function initializeProjectsDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeProjectsDataTable, 1000);
        return;
    }
    
    if ($('#projectsTable').length === 0) {
        return;
    }
    
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#projectsTable')) {
        return;
    }
    
    const projectsDataTable = $('#projectsTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[1, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
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
                            <button class="btn btn-outline-primary edit-project" 
                                    data-project-id="${row.IdProject}" 
                                    title="Edit Project">
                                <i class="bi bi-pencil"></i>
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

    // Clear search when input is cleared
    $('#projectsCustomSearch').on('input', function() {
        if (this.value === '') {
            projectsDataTable.search('').draw();
        }
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-project', function () {
        const projectId = $(this).data('project-id');
        loadProjectData(projectId);
        $('#editProjectModal').modal('show');
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
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.warn('jQuery not available, retrying in 100ms...');
        setTimeout(() => loadProjectData(projectId), 100);
        return;
    }
    
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
                
                // Fill form fields
                $('#edit_project_id').val(project.IdProject);
                $('#edit_project_title').val(project.ProjectTitle);
                $('#edit_project_owner').val(project.ProjectOwner);
                $('#edit_project_location').val(project.ProjectLocation);
                $('#edit_project_area').val(project.ProjectArea);
                $('#edit_project_value').val(project.ProjectValue);
                $('#edit_turnover_date').val(project.TurnoverDate);
                $('#edit_project_category_id').val(project.ProjectCategoryId);
                $('#edit_display_order').val(project.DisplayOrder);
                $('#edit_project_description').val(project.ProjectDescription);
                $('#edit_status').val(project.Status);
                
                // Show current images if they exist
                if (project.ProjectImage1) {
                    $('#current_image1_preview').html(`
                        <small class="text-muted">Current Image 1:</small><br>
                        <img src="../assets/img/${project.ProjectImage1}" alt="Current Image 1" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                    `);
                } else {
                    $('#current_image1_preview').html('');
                }
                
                if (project.ProjectImage2) {
                    $('#current_image2_preview').html(`
                        <small class="text-muted">Current Image 2:</small><br>
                        <img src="../assets/img/${project.ProjectImage2}" alt="Current Image 2" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                    `);
                } else {
                    $('#current_image2_preview').html('');
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error('Error loading project data');
        }
    });
}

// Validation functions
function validateNumericalField(value, fieldName, allowZero = false) {
    if (value === '' || value === null || value === undefined) {
        return true; // Allow empty values
    }
    
    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
        return `${fieldName} should be numbers only`;
    }
    
    if (allowZero) {
        if (numValue < 0) {
            return `${fieldName} should be numbers only`;
        }
    } else {
        if (numValue <= 0) {
            return `${fieldName} should be numbers only`;
        }
    }
    
    return true; // Valid
}

function validateProjectForm(formId) {
    const errors = [];
    
    // Validate Project Area
    const projectArea = $(`#${formId} [name="project_area"]`).val();
    const areaValidation = validateNumericalField(projectArea, 'Project Area', true);
    if (areaValidation !== true) {
        errors.push(areaValidation);
    }
    
    // Validate Project Value
    const projectValue = $(`#${formId} [name="project_value"]`).val();
    const valueValidation = validateNumericalField(projectValue, 'Project Value', true);
    if (valueValidation !== true) {
        errors.push(valueValidation);
    }
    
    // Validate Display Order
    const displayOrder = $(`#${formId} [name="display_order"]`).val();
    const orderValidation = validateNumericalField(displayOrder, 'Display Order', true);
    if (orderValidation !== true) {
        errors.push(orderValidation);
    }
    
    return errors;
}

$(document).ready(function () {
    // Only initialize if the projects table exists on this page
    if ($('#projectsTable').length > 0) {
        // Initialize DataTable
        const projectsDataTable = initializeProjectsDataTable();
        
        // Load categories for dropdowns
        loadCategories();
        
        // Add real-time validation for numerical fields
        $('input[type="number"]').on('input', function() {
            const fieldName = $(this).attr('name');
            const value = $(this).val();
            
            if (value !== '') {
                const validation = validateNumericalField(value, fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()), true);
                if (validation !== true) {
                    $(this).addClass('is-invalid');
                    // Remove any existing error message
                    $(this).siblings('.invalid-feedback').remove();
                    $(this).after(`<div class="invalid-feedback">${validation}</div>`);
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').remove();
            }
        });

        // Handle Add Project Form
        $('#addProjectForm').on('submit', function (e) {
            e.preventDefault();
            
            // Validate form
            const validationErrors = validateProjectForm('addProjectForm');
            if (validationErrors.length > 0) {
                toastr.error(validationErrors.join('. '));
                return;
            }
            
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
            
            // Validate form
            const validationErrors = validateProjectForm('editProjectForm');
            if (validationErrors.length > 0) {
                toastr.error(validationErrors.join('. '));
                return;
            }
            
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
    }
});
