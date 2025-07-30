// Initialize DataTable for Customers
const customerDataTable = new DataTable('.customer_table', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[1, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/API/apiCustomers.php?get_customers',
        type: 'GET',
        dataSrc: json => json.data || []
    },
    columns: [
        { data: 'CustomerType', render: data => `<div class="text-start">${data == 1 ? 'Business' : 'Individual'}</div>` },
        { data: null, render: row => `<div class="text-start">${row.CustomerType == 1 ? row.BusinessName : row.CustomerName}</div>` },
        { data: 'ContactNum', render: data => `<div class="text-start">${data || 'N/A'}</div>` },
        { data: 'CustomerStatus', render: data => `<div class="text-start"><span class="badge bg-${data == 1 ? 'success' : 'danger'}">${data == 1 ? 'Active' : 'Inactive'}</span></div>` },
        { data: null, render: (_, __, row) => `<div><i class="bi bi-pen edit_customer" style="cursor: pointer;" data-customer-id="${row.idCustomer}" title="Edit Customer"></i></div>` }
    ]
});

// Handle customer form submission
const handleCustomerSubmit = (action, data) => {
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(data.ContactEmail)) {
        toastr.error('Please enter a valid email address');
        return;
    }
    if (!data.CustomerName || !data.CustomerAddress || !data.ContactNum || !data.ContactEmail) {
        toastr.error('Please fill in all required fields');
        return;
    }
    if (data.CustomerType == '1' && (!data.ContactPerson || !data.PersonDesignation)) {
        toastr.error('Please fill in contact person and designation for business customers');
        return;
    }

    $.ajax({
        url: 'app/API/apiCustomers.php',
        type: 'POST',
        data: { [action]: true, ...data },
        success: response => {
            if (response.status === 1) {
                customerDataTable.ajax.reload();
                $('#customerForm')[0].reset();
                $('#manageCustomerType').val('1').trigger('change');
                $('#saveCustomerBtn').show();
                $('#updateCustomerBtn').hide();
                $('#customerProfileCardTitle').text('Customer Profile');
                $('#manageCustomerStatusColumn').hide();
                $('.is-invalid').removeClass('is-invalid');
                $('#emailError').hide();
                toastr.success(response.message || `Customer ${action === 'create_customer' ? 'added' : 'updated'} successfully`);
            } else {
                toastr.error(response.message || `Error ${action === 'create_customer' ? 'creating' : 'updating'} customer`);
            }
        },
        error: () => toastr.error(`Error ${action === 'create_customer' ? 'creating' : 'updating'} customer`)
    });
};

// Save customer
$('#saveCustomerBtn').on('click', e => {
    e.preventDefault();
    handleCustomerSubmit('create_customer', {
        CustomerType: $('#manageCustomerType').val(),
        CustomerName: $('#modalCustomerName').val(),
        BusinessName: $('#manageCustomerType').val() == '1' ? $('#modalCustomerName').val() : '',
        CustomerTIN: $('#manageCustomerTIN').val(),
        CustomerAddress: $('#manageCustomerAddress').val(),
        ContactPerson: $('#manageCustomerType').val() == '1' ? $('#manageContactPerson').val() : '',
        PersonDesignation: $('#manageCustomerType').val() == '1' ? $('#managePersonDesignation').val() : '',
        ContactNum: $('#manageContactNum').val(),
        ContactEmail: $('#manageContactEmail').val().trim().toLowerCase(),
        CustomerStatus: 1
    });
});

// Update customer
$('#updateCustomerBtn').on('click', e => {
    e.preventDefault();
    handleCustomerSubmit('update_customer', {
        idCustomer: $('#customerId').val(),
        CustomerType: $('#manageCustomerType').val(),
        CustomerName: $('#modalCustomerName').val(),
        BusinessName: $('#manageCustomerType').val() == '1' ? $('#modalCustomerName').val() : '',
        CustomerTIN: $('#manageCustomerTIN').val(),
        CustomerAddress: $('#manageCustomerAddress').val(),
        ContactPerson: $('#manageCustomerType').val() == '1' ? $('#manageContactPerson').val() : '',
        PersonDesignation: $('#manageCustomerType').val() == '1' ? $('#managePersonDesignation').val() : '',
        ContactNum: $('#manageContactNum').val(),
        ContactEmail: $('#manageContactEmail').val().trim().toLowerCase(),
        CustomerStatus: $('#manageCustomerStatus').val()
    });
});

// Edit customer
$(document).on('click', '.edit_customer', function () {
    $.ajax({
        url: 'app/API/apiCustomers.php?get_customer',
        type: 'GET',
        data: { get_customer: true, idCustomer: $(this).data('customer-id') },
        success: response => {
            if (response.status === 1) {
                const { idCustomer, CustomerType, CustomerTIN, CustomerName, BusinessName, CustomerAddress, ContactPerson, PersonDesignation, ContactEmail, ContactNum, CustomerStatus } = response.data;
                $('#manageCustomerType').val(CustomerType).trigger('change');
                $('#customerId').val(idCustomer);
                $('#manageCustomerTIN').val(CustomerTIN);
                $('#modalCustomerName').val(CustomerType == 1 ? BusinessName : CustomerName);
                $('#manageCustomerAddress').val(CustomerAddress);
                $('#manageContactPerson').val(ContactPerson || '');
                $('#managePersonDesignation').val(PersonDesignation || '');
                $('#manageContactEmail').val(ContactEmail);
                $('#manageContactNum').val(ContactNum);
                $('#manageCustomerStatus').val(CustomerStatus);
                $('#manageCustomerStatusColumn').show();
                $('#saveCustomerBtn').hide();
                $('#updateCustomerBtn').show();
                $('#customerProfileCardTitle').text('Update Customer');
            } else {
                toastr.error(response.message || 'Error retrieving customer');
            }
        },
        error: () => toastr.error('Error retrieving customer')
    });
});

// Reset form
$('#resetCustomerForm').on('click', () => {
    $('#customerForm')[0].reset();
    $('#customerId').val('');
    $('#manageCustomerType').val('1').trigger('change');
    $('#saveCustomerBtn').show();
    $('#updateCustomerBtn').hide();
    $('#customerProfileCardTitle').text('Customer Profile');
    $('#manageCustomerStatusColumn').hide();
    $('.is-invalid').removeClass('is-invalid');
    $('#emailError').hide();
});

// Handle customer type change
$('#manageCustomerType').on('change', function () {
    const isBusiness = $(this).val() === '1';
    $('#contactPersonGroup, #personDesignationGroup').toggle(isBusiness);
    $('#modalCustomerNameLabel').text(isBusiness ? 'Business Name' : 'Customer Name');
    $('#manageContactPerson, #managePersonDesignation').prop('required', isBusiness).val(isBusiness ? '' : '');
});

// Hide status column on modal open
$('#customersModal').on('show.bs.modal', () => $('#manageCustomerStatusColumn').hide());