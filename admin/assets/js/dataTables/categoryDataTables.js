// Initialize DataTable
const categoryDataTable = new DataTable('.category_table', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/API/apiCategory.php?get_categories',
        type: 'GET',
        dataSrc: json => {
            if (json.status === 1) return json.data.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading category data')
    },
    columns: [
        { data: 'CategoryName', render: data => `<div class="text-start">${data}</div>` },
        { data: null, render: (_, __, row) => `<div><i class="bi bi-pen edit_category" style="cursor: pointer;" data-category-id="${row.idCategory}" title="Edit Category"></i></div>` }
    ]
});

// Search functionality
$('#categoryCustomSearch').on('keyup', function () {
    categoryDataTable.search(this.value).draw();
});

// Handle category form submission (create/update)
const handleCategorySubmit = (action, data) => {
    if (!data.CategoryName) {
        toastr.error('Category name is required');
        return;
    }

    $.ajax({
        url: 'app/API/apiCategory.php',
        type: 'POST',
        data: { [action]: true, ...data },
        success: response => {
            if (response.status === 1) {
                categoryDataTable.ajax.reload();
                $('#categoryForm')[0].reset();
                $('#categoryId').val('');
                $('#saveCategoryBtn').show();
                $('#updateCategoryBtn').hide();
                toastr.success(response.message);
                refreshCategoryDropdown();
                reloadRelatedTables();
            } else {
                toastr.error(response.message || `Error ${action === 'create_category' ? 'creating' : 'updating'} category`);
            }
        },
        error: () => toastr.error(`Error ${action === 'create_category' ? 'creating' : 'updating'} category`)
    });
};

// Save category
$('#saveCategoryBtn').on('click', e => {
    e.preventDefault();
    handleCategorySubmit('create_category', { CategoryName: $('#categoryName').val()?.trim() });
});

// Update category
$('#updateCategoryBtn').on('click', e => {
    e.preventDefault();
    const data = {
        category_id: $('#categoryId').val()?.trim(),
        CategoryName: $('#categoryName').val()?.trim()
    };
    if (!data.category_id) {
        toastr.error('Category ID is required');
        return;
    }
    handleCategorySubmit('update_category', data);
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
    $.ajax({
        url: 'app/API/apiCategory.php?get_category',
        type: 'GET',
        data: { get_category: true, id: $(this).data('category-id') },
        success: response => {
            if (response.status === 1) {
                const { idCategory, CategoryName } = response.data;
                $('#categoryId').val(idCategory);
                $('#categoryName').val(CategoryName);
                $('#saveCategoryBtn').hide();
                $('#updateCategoryBtn').show();
            } else {
                toastr.error(response.message || 'Error retrieving category data');
            }
        },
        error: () => toastr.error('Error retrieving category data')
    });
});

// Refresh category dropdown
const refreshCategoryDropdown = () => {
    $.ajax({
        url: 'app/API/apiCategory.php?get_categories',
        type: 'GET',
        success: response => {
            if (response.status === 1) {
                const options = ['<option value="" disabled selected>Select category</option>']
                    .concat(response.data.data.map(cat => `<option value="${cat.idCategory}">${cat.CategoryName}</option>`))
                    .join('');
                $('#pricelistModal #categoryId').html(options);
                $('select[name="CategoryId"]').html(options);
            } else {
                toastr.error('Error loading categories');
            }
        },
        error: () => toastr.error('Error loading categories')
    });
};

// Reload related tables
const reloadRelatedTables = () => {
    ['orderDataTable', 'pricelistDataTable'].forEach(table => {
        if (typeof window[table] !== 'undefined' && window[table] && typeof window[table].ajax !== 'undefined') {
            window[table].ajax.reload();
        }
    });
};