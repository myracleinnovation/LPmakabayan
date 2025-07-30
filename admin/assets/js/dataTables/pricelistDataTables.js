// Initialize DataTable for Pricelist
const pricelistDataTable = new DataTable('.pricelist_table', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/API/apiPricelists.php?get_pricelists',
        type: 'GET',
        dataSrc: json => {
            if (json.status === 1) return json.data.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => {
            toastr.error('Error loading pricelist data');
        }
    },
    columns: [
        { data: 'ItemCode' },
        { data: 'CategoryName' },
        { data: 'ProductDesc' },
        { data: 'UnitMeasure' },
        { data: 'UnitPrice', render: data => parseFloat(data).toFixed(2) },
        { data: 'DiscountedPrice', render: data => parseFloat(data).toFixed(2) },
        { data: 'ItemStatus', render: data => `<span class="badge bg-${data == 1 ? 'success' : 'danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: null, render: (_, __, row) => `<div><i class="bi bi-pen edit_pricelist" style="cursor: pointer;" data-pricelist-id="${row.idPriceList}" title="Edit Pricelist"></i></div>` }
    ]
});

// Generate ItemCode
$('#generateItemCode').on('click', () => {
    const generatedCode = generateItemCode();
    $('#itemCode').val(generatedCode).trigger('input');
});

// Validate ItemCode
$('#itemCode').on('input', function () {
    // Convert to uppercase
    $(this).val($(this).val().toUpperCase());
    
    const itemCode = $(this).val();
    
    // Restore original prices if they exist
    const originalUnitPrice = $('#unitPrice').data('original-price');
    const originalDiscountedPrice = $('#discountedPrice').data('original-price');
    
    if (originalUnitPrice && !$('#unitPrice').val()) {
        $('#unitPrice').val(originalUnitPrice);
    }
    if (originalDiscountedPrice && !$('#discountedPrice').val()) {
        $('#discountedPrice').val(originalDiscountedPrice);
    }
    
    // Check for duplicate item code
    if (itemCode) {
        $.ajax({
            url: 'app/API/apiPricelists.php?check_item_code',
            type: 'GET',
            data: { item_code: itemCode },
            success: response => {
                if (response.status === 1 && response.data.exists) {
                    $(this).addClass('is-invalid');
                    $('#itemCodeError').show().text('Item code already exists');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#itemCodeError').hide();
                }
            },
            error: (xhr, status, error) => {
                console.error('ItemCode check error:', {xhr, status, error});
            }
        });
    }
});

// Validate and format TIN
$('#customerTIN').on('input', function () {
    const tin = $(this).val();
    $(this).toggleClass('is-invalid', tin && !validateCustomerTIN(tin));
    $('#tinError').toggle(tin && !validateCustomerTIN(tin)).text('Invalid TIN format. Use XXX-XXX-XXX-XXX');
}).on('keyup', function () {
    const formattedTIN = formatCustomerTIN($(this).val());
    if (formattedTIN) $(this).val(formattedTIN);
});

// Validate and format phone number
$('#contactNumber').on('input', function () {
    const number = $(this).val();
    $(this).toggleClass('is-invalid', number && !validatePhilippineNumber(number));
    $('#contactError').toggle(number && !validatePhilippineNumber(number)).text('Invalid phone number. Must start with 09 and be 11 digits');
}).on('keyup', function () {
    const formattedNumber = formatPhilippineNumber($(this).val());
    if (formattedNumber) $(this).val(formattedNumber);
});

// Validate email
$('#emailAddress').on('input', function () {
    const email = $(this).val();
    $(this).toggleClass('is-invalid', email && !validateEmail(email));
    $('#emailError').toggle(email && !validateEmail(email)).text('Invalid email format');
});

// Validate prices
const validatePrices = () => {
    const unitPrice = parseFloat($('#unitPrice').val()) || 0;
    const discountedPrice = parseFloat($('#discountedPrice').val()) || 0;
    const isInvalid = discountedPrice > unitPrice;
    $('#discountedPrice').toggleClass('is-invalid', isInvalid);
    $('#discountError').toggle(isInvalid);
    return !isInvalid;
};

$('#unitPrice, #discountedPrice').on('input', function() {
    // Keep the original value if it's a valid number
    const value = $(this).val();
    if (value && !isNaN(value)) {
        $(this).val(value);
    }
    validatePrices();
});

// Load categories
const loadCategories = () => {
    $.ajax({
        url: 'app/API/apiCategory.php?get_categories',
        type: 'GET',
        success: response => {
            if (response.status === 1 && response.data && response.data.data) {
                const options = ['<option value="" disabled selected>Select category</option>']
                    .concat(response.data.data.map(cat => `<option value="${cat.idCategory}">${cat.CategoryName}</option>`))
                    .join('');
                $('#categoryId').html(options);
            } else {
                console.error('Invalid categories data received');
                toastr.error('Error loading categories');
            }
        },
        error: (xhr, status, error) => {
            console.error('Categories load error:', {xhr, status, error});
            toastr.error('Error loading categories');
        }
    });
};

// Refresh category dropdown when the pricelist modal opens
$('#pricelistModal').on('show.bs.modal', function () {
    loadCategories();
    pricelistDataTable.ajax.reload();
});

// Search functionality
$('#pricelistCustomSearch').on('keyup', function () {
    pricelistDataTable.search(this.value).draw();
});

// Handle pricelist form submission
const handlePricelistSubmit = (action, data) => {
    // Check for duplicate item code before submitting
    $.ajax({
        url: 'app/API/apiPricelists.php?check_item_code',
        type: 'GET',
        data: { 
            item_code: data.ItemCode,
            exclude_id: action === 'update_pricelist' ? data.idPriceList : null
        },
        success: response => {
            if (response.status === 1 && response.data.exists) {
                toastr.error('Item code already exists');
                return;
            }
            
            // Continue with form submission if no duplicate
            submitPricelistForm(action, data);
        }
    });
};

// Separate function for actual form submission
const submitPricelistForm = (action, data) => {
    $.ajax({
        url: 'app/API/apiPricelists.php',
        type: 'POST',
        data: { [action]: true, ...data },
        success: response => {
            if (response.status === 1) {
                pricelistDataTable.ajax.reload();
                $('#pricelistForm')[0].reset();
                $('#pricelistId').val('');
                $('#itemCode').prop('readonly', false);
                $('#savePricelistBtn').show();
                $('#updatePricelistBtn').hide();
                $('#generateItemCode').show();
                $('.status-field').hide();
                $('.item-title').text('Add New Item');
                $('.is-invalid').removeClass('is-invalid');
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'create_pricelist' ? 'creating' : 'updating'} pricelist item`);
            }
        },
        error: () => toastr.error(`Error ${action === 'create_pricelist' ? 'creating' : 'updating'} pricelist item`)
    });
};

// Save pricelist
$('#savePricelistBtn').on('click', e => {
    e.preventDefault();
    if (!validatePrices()) return;
    handlePricelistSubmit('create_pricelist', {
        CategoryId: $('#categoryId').val(),
        ItemCode: $('#itemCode').val(),
        ProductDesc: $('textarea[name="ProductDesc"]').val().trim(),
        UnitMeasureId: $('select[name="UnitMeasureId"]').val(),
        UnitPrice: $('#unitPrice').val(),
        DiscountedPrice: $('#discountedPrice').val(),
        ItemStatus: $('select[name="ItemStatus"]').val() || 1
    });
});

// Update pricelist
$('#updatePricelistBtn').on('click', e => { 
    e.preventDefault();
    if (!validatePrices()) return;

    const unitPrice = $('#unitPrice').val() || $('#unitPrice').data('original-price');
    const discountedPrice = $('#discountedPrice').val() || $('#discountedPrice').data('original-price');

    // Validate that prices are not empty
    if (!unitPrice || !discountedPrice) {
        console.error('Empty prices detected');
        toastr.error('Unit price and discounted price are required');
        return;
    }

    const formData = {
        idPriceList: $('#pricelistId').val(),
        CategoryId: $('#categoryId').val(),
        ItemCode: $('#itemCode').val(),
        ProductDesc: $('textarea[name="ProductDesc"]').val().trim(),
        UnitMeasureId: $('select[name="UnitMeasureId"]').val(),
        UnitPrice: unitPrice,
        DiscountedPrice: discountedPrice,
        ItemStatus: $('select[name="ItemStatus"]').val()
    };

    // Check for duplicate item code before submitting
    $.ajax({
        url: 'app/API/apiPricelists.php?check_item_code',
        type: 'GET',
        data: { 
            item_code: formData.ItemCode,
            exclude_id: formData.idPriceList
        },
        success: response => {
            if (response.status === 1 && response.data.exists) {
                toastr.error('Item code already exists');
                return;
            }
            
            // Continue with form submission if no duplicate
            $.ajax({
                url: 'app/API/apiPricelists.php',
                type: 'POST',
                data: { update_pricelist: true, ...formData },
                success: response => {
                    if (response.status === 1) {
                        pricelistDataTable.ajax.reload();
                        $('#pricelistForm')[0].reset();
                        $('#pricelistId').val('');
                        $('#itemCode').prop('readonly', false);
                        $('#savePricelistBtn').show();
                        $('#updatePricelistBtn').hide();
                        $('#generateItemCode').show();
                        $('.status-field').hide();
                        $('.item-title').text('Add New Item');
                        $('.is-invalid').removeClass('is-invalid');
                        // Clear stored prices
                        $('#unitPrice, #discountedPrice').removeData('original-price');
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Error updating pricelist item');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Update error:', {xhr, status, error});
                    toastr.error('Error updating pricelist item');
                }
            });
        },
        error: (xhr, status, error) => {
            console.error('ItemCode check error:', {xhr, status, error});
        }
    });
});

// Edit pricelist
$(document).on('click', '.edit_pricelist', function () {
    const pricelistId = $(this).data('pricelist-id');
    
    $.ajax({
        url: 'app/API/apiPricelists.php?get_pricelist',
        type: 'GET',
        data: { get_pricelist: true, idPriceList: pricelistId },
        success: response => {
            if (response.status === 1) {
                const { idPriceList, CategoryId, ItemCode, ProductDesc, UnitMeasureId, UnitPrice, DiscountedPrice, ItemStatus } = response.data;
                
                // Store original prices in data attributes
                $('#unitPrice').data('original-price', UnitPrice);
                $('#discountedPrice').data('original-price', DiscountedPrice);
                
                $('#pricelistId').val(idPriceList);
                $('#categoryId').val(CategoryId);
                $('#itemCode').val(ItemCode).prop('readonly', false);
                $('textarea[name="ProductDesc"]').val(ProductDesc);
                $('select[name="UnitMeasureId"]').val(UnitMeasureId);
                $('#unitPrice').val(UnitPrice);
                $('#discountedPrice').val(DiscountedPrice);
                $('select[name="ItemStatus"]').val(ItemStatus);
                $('#savePricelistBtn').hide();
                $('#updatePricelistBtn').show();
                $('#generateItemCode').show();
                $('.status-field').show();
                $('.item-title').text('Update Item');
            } else {
                toastr.error(response.message || 'Error retrieving pricelist item');
            }
        },
        error: (xhr, status, error) => {
            console.error('Edit error:', {xhr, status, error});
            toastr.error('Error retrieving pricelist item');
        }
    });
});

// Reset form
$('#resetPricelistForm').on('click', () => {
    $('#pricelistForm')[0].reset();
    $('#pricelistId').val('');
    $('#itemCode').prop('readonly', false);
    $('#savePricelistBtn').show();
    $('#updatePricelistBtn').hide();
    $('#generateItemCode').show();
    $('.status-field').hide();
    $('.item-title').text('Add New Item');
    $('.is-invalid').removeClass('is-invalid');
});