function initializeProjectCategoriesDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeProjectCategoriesDataTable, 1000);
        return;
    }
    
    if ($('#categoriesTable').length === 0) {
        return;
    }
    
    const projectCategoriesDataTable = $('#categoriesTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiProjectCategories.php',
            type: 'GET',
            data: { get_categories: 1 },
            dataSrc: function (json) {
                if (json.status === 1) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function (xhr, status, error) {
                console.error('Ajax error:', status, error);
                toastr.error('Error loading project categories data');
            }
        },
            columns: [
            {
                data: 'CategoryImage',
                render: function (data) {
                    return data ? `<img src="../assets/img/${data}" alt="Category" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` : '<span class="text-muted">No image</span>';
                }
            },
            {
                data: 'CategoryName',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'CategoryDescription',
                render: function (data) {
                    return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '<span class="text-muted">No description</span>';
                }
            },
            {
                data: 'DisplayOrder',
                render: function (data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
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
                            <button class="btn btn-outline-primary edit-category" 
                                    data-category-id="${row.IdCategory}" 
                                    title="Edit Category">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#categoriesCustomSearch').on('keyup', function () {
        projectCategoriesDataTable.search(this.value).draw();
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-category', function () {
        const categoryId = $(this).data('category-id');
        loadCategoryData(categoryId);
        $('#editCategoryModal').modal('show');
    });

    return projectCategoriesDataTable;
}

function loadCategoryData(categoryId) {
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.warn('jQuery not available, retrying in 100ms...');
        setTimeout(() => loadCategoryData(categoryId), 100);
        return;
    }
    
    $.ajax({
        url: 'app/apiProjectCategories.php',
        type: 'GET',
        data: {
            get_category: 1,
            id: categoryId
        },
        success: function (response) {
            if (response.status === 1) {
                const category = response.data;
                
                // Fill form fields
                $('#edit_category_id').val(category.IdCategory);
                $('#edit_category_name').val(category.CategoryName);
                $('#edit_category_description').val(category.CategoryDescription);
                $('#edit_display_order').val(category.DisplayOrder);
                $('#edit_status').val(category.Status);
                
                // Show current image if it exists
                if (category.CategoryImage) {
                    $('#current_category_image_preview').html(`
                        <small class="text-muted">Current Image:</small><br>
                        <img src="../assets/img/${category.CategoryImage}" alt="Current Category Image" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover;" class="border rounded">
                    `);
                } else {
                    $('#current_category_image_preview').html('');
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error('Error loading category data');
        }
    });
}

$(document).ready(function () {
    // Only initialize if the categories table exists on this page
    if ($('#categoriesTable').length > 0) {
        // Initialize DataTable
        const projectCategoriesDataTable = initializeProjectCategoriesDataTable();

        // Handle Add Category Form
        $('#addCategoryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'create');

            $.ajax({
                url: 'app/apiProjectCategories.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#addCategoryModal').modal('hide');
                        $('#addCategoryForm')[0].reset();
                        projectCategoriesDataTable.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Error adding category');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Add category error:', xhr.responseText);
                    toastr.error('Error adding category');
                }
            });
        });

        // Handle Edit Category Form
        $('#editCategoryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

            $.ajax({
                url: 'app/apiProjectCategories.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('#editCategoryModal').modal('hide');
                        projectCategoriesDataTable.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Error updating category');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Edit category error:', xhr.responseText);
                    toastr.error('Error updating category');
                }
            });
        });
    }
}); 