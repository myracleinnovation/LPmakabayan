// Initialize DataTable for Project Categories
const projectCategoriesDataTable = new DataTable('#categoriesTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiProjectCategories.php',
        type: 'POST',
        data: { action: 'get_categories' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading project categories data')
    },
    columns: [
        { data: 'IdCategory', render: data => `<div class="text-center">${data}</div>` },
        { data: 'CategoryName', render: data => `<div class="text-start">${data}</div>` },
        { data: 'CategoryDescription', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'CategoryImage', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_category" style="cursor: pointer;" data-category-id="${row.IdCategory}" title="Edit Category"></i>
                <i class="bi bi-trash delete_category" style="cursor: pointer;" data-category-id="${row.IdCategory}" title="Delete Category"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#categoryCustomSearch').on('keyup', function () {
    projectCategoriesDataTable.search(this.value).draw();
});

// Handle category form submission (create/update)
const handleCategorySubmit = (action, data) => {
    if (!data.category_name) {
        toastr.error('Category name is required');
        return;
    }

    $.ajax({
        url: 'app/apiProjectCategories.php',
        type: 'POST',
        data: { action: action, ...data },
        success: response => {
            if (response.success) {
                projectCategoriesDataTable.ajax.reload();
                $('#categoryForm')[0].reset();
                $('#categoryId').val('');
                $('#saveCategoryBtn').show();
                $('#updateCategoryBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} category`);
            }
        },
        error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} category`)
    });
};

// Save category
$('#saveCategoryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        category_name: $('#categoryName').val()?.trim(),
        category_description: $('#categoryDescription').val()?.trim(),
        category_image: $('#categoryImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    handleCategorySubmit('add', data);
});

// Update category
$('#updateCategoryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        category_id: $('#categoryId').val()?.trim(),
        category_name: $('#categoryName').val()?.trim(),
        category_description: $('#categoryDescription').val()?.trim(),
        category_image: $('#categoryImage').val()?.trim(),
        display_order: $('#displayOrder').val() || 0,
        status: $('#status').val()
    };
    if (!data.category_id) {
        toastr.error('Category ID is required');
        return;
    }
    handleCategorySubmit('edit', data);
});

// Reset form
$('#resetCategoryForm').on('click', () => {
    $('#categoryForm')[0].reset();
    $('#categoryId').val('');
    $('#saveCategoryBtn').show();
    $('#updateCategoryBtn').hide();
});

// Edit category
$(document).on('click', '.edit_category', function () {
    const categoryId = $(this).data('category-id');
    $.ajax({
        url: 'app/apiProjectCategories.php',
        type: 'POST',
        data: { action: 'get', category_id: categoryId },
        success: response => {
            if (response.success) {
                const { IdCategory, CategoryName, CategoryDescription, CategoryImage, DisplayOrder, Status } = response.data;
                $('#categoryId').val(IdCategory);
                $('#categoryName').val(CategoryName);
                $('#categoryDescription').val(CategoryDescription);
                $('#categoryImage').val(CategoryImage);
                $('#displayOrder').val(DisplayOrder);
                $('#status').val(Status);
                $('#saveCategoryBtn').hide();
                $('#updateCategoryBtn').show();
            } else {
                toastr.error(response.message || 'Error retrieving category data');
            }
        },
        error: () => toastr.error('Error retrieving category data')
    });
});

// Delete category
$(document).on('click', '.delete_category', function () {
    const categoryId = $(this).data('category-id');
    
    if (confirm('Are you sure you want to delete this category?')) {
        $.ajax({
            url: 'app/apiProjectCategories.php',
            type: 'POST',
            data: { action: 'delete', category_id: categoryId },
            success: response => {
                if (response.success) {
                    projectCategoriesDataTable.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Error deleting category');
                }
            },
            error: () => toastr.error('Error deleting category')
        });
    }
}); 