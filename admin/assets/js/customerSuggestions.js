(function () {
    class CustomerSuggestionHandler {
        constructor(inputSelector, suggestionsContainerSelector, prefix) {
            this.customerNameInput = $(inputSelector);
            this.suggestionsContainer = $(suggestionsContainerSelector);
            this.prefix = prefix;
            this.searchTimeout = null;
            this.init();
        }
  
        init() {
            this.customerNameInput.on('input', this.handleInput.bind(this));
            this.suggestionsContainer.on('click', '.suggestion-item', this.handleSuggestionClick.bind(this));
  
            $(document).on('click', (e) => {
                if (
                    !$(e.target).closest(this.suggestionsContainer).length &&
                    !$(e.target).is(this.customerNameInput)
                ) {
                    this.suggestionsContainer.addClass('d-none').empty();
                }
            });
        }
  
        handleInput() {
            clearTimeout(this.searchTimeout);
            const searchTerm = this.customerNameInput.val().trim();
            const customerType = $(`#${this.prefix}CustomerType`).val();
  
            if (searchTerm.length < 1) {
                this.suggestionsContainer.addClass('d-none').empty();
                return;
            }
  
            this.searchTimeout = setTimeout(() => {
                this.searchCustomers(searchTerm, customerType);
            }, 300);
        }
  
        handleSuggestionClick(e) {
            e.preventDefault();
            e.stopPropagation();
            const customer = $(e.currentTarget).data('customer');
            this.fillCustomerDetails(customer);
            this.suggestionsContainer.addClass('d-none').empty();
        }
  
        searchCustomers(searchTerm, customerType) {
            $.ajax({
                url: 'app/API/apiCustomers.php',
                type: 'POST',
                data: {
                    search_customers: 1,
                    term: searchTerm.toUpperCase(),
                    type: customerType || null,
                    status: 1 // Only get active customers
                },
                dataType: 'json',
                success: (res) => {
                    if (res.status === 1 && res.data) {
                        this.displaySuggestions(res.data);
                    } else {
                        this.suggestionsContainer.addClass('d-none').empty();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error searching customers:', error);
                    this.suggestionsContainer.addClass('d-none').empty();
                },
            });
        }
  
        displaySuggestions(customers) {
            this.suggestionsContainer.empty();
  
            if (customers.length === 0) {
                this.suggestionsContainer.addClass('d-none');
                return;
            }
  
            customers.forEach((customer) => {
                const customerType = parseInt(customer.CustomerType);
                const displayName = customerType === 1 ? customer.BusinessName : customer.CustomerName;
                
                const item = $('<div>')
                    .addClass('suggestion-item p-2 border-bottom cursor-pointer')
                    .data('customer', customer)
                    .html(`
              <div class="d-flex justify-content-between">
                <div>
                  <strong>${displayName}</strong>
                  <div class="small text-muted">
                    ${customerType === 1 ? 'Business' : 'Individual'} | TIN: ${customer.CustomerTIN || 'N/A'}
                  </div>
                </div>
                <div class="text-end">
                  <div>${customer.ContactNum || 'N/A'}</div>
                  <div class="small text-muted">${customer.ContactEmail || 'N/A'}</div>
                </div>
              </div>
            `);
  
                this.suggestionsContainer.append(item);
            });
  
            this.suggestionsContainer.removeClass('d-none');
        }
  
        fillCustomerDetails(customer) {
            const customerType = parseInt(customer.CustomerType);
            const customerNameInput = this.customerNameInput;
  
            // Set the value of the customer name input based on customer type
            if (this.prefix === 'editOrder') {
                // For edit modal, we need to handle both name fields
                if (customerType === 1) {
                    $('#editOrderBusinessName').val(customer.BusinessName).removeClass('d-none');
                    $('#editOrderCustomerName').val('').addClass('d-none');
                } else {
                    $('#editOrderBusinessName').val('').addClass('d-none');
                    $('#editOrderCustomerName').val(customer.CustomerName).removeClass('d-none');
                }
            } else {
                // For other modals, use the single input field
                customerNameInput.val(customerType === 1 ? customer.BusinessName : customer.CustomerName);
            }
  
            // Set other common fields
            $(`#${this.prefix}CustomerType`).val(customer.CustomerType).trigger('change');
            $(`#${this.prefix}CustomerTIN`).val(customer.CustomerTIN);
            $(`#${this.prefix}CustomerAddress`).val(customer.CustomerAddress);
            $(`#${this.prefix}ContactEmail`).val(customer.ContactEmail);
            $(`#${this.prefix}ContactNum`).val(customer.ContactNum);
  
            const contactPersonInput = $(`#${this.prefix}ContactPerson`);
            const personDesignationInput = $(`#${this.prefix}PersonDesignation`);
            const contactPersonGroup = $(`#${this.prefix}ContactPersonGroup`);
            const personDesignationGroup = $(`#${this.prefix}PersonDesignationGroup`);
  
            // Toggle visibility and required status of contact person and designation fields
            if (customerType === 1) {
                // Business customer
                contactPersonInput.val(customer.ContactPerson || '').prop('required', true).prop('disabled', false);
                personDesignationInput.val(customer.PersonDesignation || '').prop('required', true).prop('disabled', false);
                contactPersonGroup.show();
                personDesignationGroup.show();
                // Set name attribute for business
                customerNameInput.attr('name', 'BusinessName');
            } else {
                // Individual customer
                contactPersonInput.val('').prop('required', false).prop('disabled', true);
                personDesignationInput.val('').prop('required', false).prop('disabled', true);
                contactPersonGroup.hide();
                personDesignationGroup.hide();
                // Set name attribute for individual
                customerNameInput.attr('name', 'CustomerName');
            }
  
            // Update label for customer name input
            const label = customerType === 1 ? 'Business Name' : 'Customer Name';
            $(`#${this.prefix}CustomerNameLabel`).text(label);
  
            // Specific handling for 'manage' prefix (customers modal)
            if (this.prefix === 'manage') {
                $('#customerId').val(customer.idCustomer);
                $('#customerProfileCardTitle').text('Update Customer');
                $('#saveCustomerBtn').hide();
                $('#updateCustomerBtn').show();
                $('#manageCustomerStatusColumn').show();
                $('#manageCustomerStatus').val(customer.CustomerStatus || 1);
            }
  
            // Validate customer data
            if (!validateEmail(customer.ContactEmail)) {
                toastr.error('Please enter a valid email address');
                return;
            }
  
            // Validate required fields based on customer type
            if (customerType === '1') { // Business
                if (!customer.BusinessName) {
                    toastr.error('Please enter a business name');
                    return;
                }
                if (!customer.ContactPerson) {
                    toastr.error('Please enter a contact person');
                    return;
                }
                if (!customer.PersonDesignation) {
                    toastr.error('Please enter a person designation');
                    return;
                }
            } else { // Individual
                if (!customer.CustomerName) {
                    toastr.error('Please enter a customer name');
                    return;
                }
            }
  
            if (!customer.CustomerAddress) {
                toastr.error('Please enter a customer address');
                return;
            }
        }
    }
  
    $(document).ready(function () {
        // Initialize CustomerSuggestionHandler for different contexts
        new CustomerSuggestionHandler('#newOrderBusinessName', '#newOrderCustomerSuggestions', 'newOrder');
        new CustomerSuggestionHandler('#editOrderBusinessName', '#editOrderCustomerSuggestions', 'editOrder');
        new CustomerSuggestionHandler('#editOrderCustomerName', '#editOrderCustomerSuggestions', 'editOrder');
        new CustomerSuggestionHandler('#modalCustomerName', '#customerSuggestions2', 'manage');
  
        // Handle customer type change in the main customers modal
        $('#manageCustomerType')
            .on('change', function () {
                const customerType = $(this).val();
                const customerNameLabel = $('#modalCustomerNameLabel');
                const contactPersonGroup = $('#contactPersonGroup');
                const personDesignationGroup = $('#personDesignationGroup');
                const manageContactPerson = $('#manageContactPerson');
                const managePersonDesignation = $('#managePersonDesignation');
  
                if (customerType === '1') {
                    customerNameLabel.text('Business Name');
                    contactPersonGroup.show();
                    personDesignationGroup.show();
                    manageContactPerson.prop('required', true).prop('disabled', false);
                    managePersonDesignation.prop('required', true).prop('disabled', false);
                    $('#modalCustomerName').attr('name', 'BusinessName');
                } else {
                    customerNameLabel.text('Customer Name');
                    contactPersonGroup.hide();
                    personDesignationGroup.hide();
                    manageContactPerson.val('').prop('required', false).prop('disabled', true);
                    managePersonDesignation.val('').prop('required', false).prop('disabled', true);
                    $('#modalCustomerName').attr('name', 'CustomerName');
                }
            })
            .trigger('change');
  
        // Handle customer type changes in new order modal
        $('#newOrderCustomerType').on('change', function () {
            const customerType = $(this).val();
            const customerNameLabel = $('#newOrderCustomerNameLabel');
            const customerNameInput = $('#newOrderBusinessName');
            const contactPerson = $('#newOrderContactPerson');
            const personDesignation = $('#newOrderPersonDesignation');
            const businessFields = $('.business-fields');
  
            if (customerType === '1') {
                customerNameLabel.text('Business Name');
                businessFields.show();
                contactPerson.prop('required', true).prop('disabled', false);
                personDesignation.prop('required', true).prop('disabled', false);
                customerNameInput.attr('name', 'BusinessName');
            } else {
                customerNameLabel.text('Customer Name');
                businessFields.hide();
                contactPerson.prop('required', false).prop('disabled', true).val('');
                personDesignation.prop('required', false).prop('disabled', true).val('');
                customerNameInput.attr('name', 'CustomerName');
            }
        }).trigger('change');
  
        // Handle customer type changes in edit order modal
        $('#editOrderCustomerType').on('change', function () {
            const customerType = $(this).val();
            const customerNameLabel = $('#editOrderCustomerNameLabel');
            const editOrderBusinessName = $('#editOrderBusinessName');
            const editOrderCustomerName = $('#editOrderCustomerName');
            const contactPerson = $('#editOrderContactPerson');
            const personDesignation = $('#editOrderPersonDesignation');
            const businessFields = $('.business-fields');
  
            if (customerType === '1') {
                customerNameLabel.text('Business Name');
                businessFields.show();
                contactPerson.prop('required', true).prop('disabled', false);
                personDesignation.prop('required', true).prop('disabled', false);
                editOrderBusinessName.show();
                editOrderCustomerName.hide();
                editOrderBusinessName.prop('required', true);
                editOrderCustomerName.prop('required', false);
            } else {
                customerNameLabel.text('Customer Name');
                businessFields.hide();
                contactPerson.prop('required', false).prop('disabled', true).val('');
                personDesignation.prop('required', false).prop('disabled', true).val('');
                editOrderBusinessName.hide();
                editOrderCustomerName.show();
                editOrderBusinessName.prop('required', false);
                editOrderCustomerName.prop('required', true);
            }
        }).trigger('change');
    });
  })();