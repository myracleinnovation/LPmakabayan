$(document).ready(function() {
    // Add CSS to hide sorting arrows but keep sorting functionality
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            #contactsTable thead th.sorting:before,
            #contactsTable thead th.sorting:after,
            #contactsTable thead th.sorting_asc:before,
            #contactsTable thead th.sorting_asc:after,
            #contactsTable thead th.sorting_desc:before,
            #contactsTable thead th.sorting_desc:after {
                display: none !important;
            }
        `)
        .appendTo('head');

    // Initialize contacts functionality
    initializeContactsDataTable();
});

// Initialize contacts DataTable
function initializeContactsDataTable() {
    let contactsDataTable;
    
    if ($('#contactsTable').length > 0) {
        // Initialize DataTable
        contactsDataTable = $('#contactsTable').DataTable({
            columnDefs: [
                { orderable: true, targets: [0] },  // ContactLabel
                { orderable: true, targets: [1] },  // ContactValue  
                { orderable: true, targets: [2] },  // ContactType
                { orderable: true, targets: [3] },  // DisplayOrder
                { orderable: true, targets: [4] },  // Status
                { orderable: false, targets: [5] }  // Actions
            ],
            order: [[1, 'asc']],
            dom: "<'row'<'col-12 mb-3'tr>>" +
                    "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
            processing: true,
            ajax: {
                url: 'app/apiCompanyContact.php',
                type: 'POST',
                data: { action: 'get_contacts' },
                dataSrc: function(json) {
                    if (json.status === 1) {
                        return json.data || [];
                    }
                    toastr.error(json.message || 'Error loading data');
                    return [];
                }
            },
            columns: [
                { 
                    data: 'ContactLabel', 
                    render: function(data) {
                        return `<div class="text-start">${data || '-'}</div>`;
                    }
                },
                { 
                    data: 'ContactValue', 
                    render: function(data) {
                        return `<div class="text-start">${data}</div>`;
                    }
                },
                { 
                    data: 'ContactType', 
                    render: function(data) {
                        return `<span class="badge bg-primary">${data}</span>`;
                    }
                },
                { 
                    data: 'DisplayOrder', 
                    render: function(data) {
                        return `<div class="text-center">${data}</div>`;
                    }
                },
                { 
                    data: 'Status', 
                    render: function(data) {
                        return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>`;
                    }
                },
                { 
                    data: null, 
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group gap-2" role="group">
                                <button class="btn btn-outline-primary btn-sm edit-contact-btn" 
                                        data-contact-id="${row.IdContact}" 
                                        title="Edit Contact">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ]
        });

        // Search functionality
        $('#contactsCustomSearch').on('keyup', function() {
            contactsDataTable.search(this.value).draw();
        });

        // Setup event handlers
        setupContactEventHandlers(contactsDataTable);
    }
}

// Setup contact event handlers
function setupContactEventHandlers(contactsDataTable) {
    // Modal event handlers
    setupModalHandlers();
    
    // Contact action handlers
    setupContactActionHandlers(contactsDataTable);
}

// Setup modal event handlers
function setupModalHandlers() {
    // Reset forms when modals are closed
    $('#addContactModal').on('hidden.bs.modal', function() {
        $('#addContactForm')[0].reset();
        $('#saveContactBtn').prop('disabled', false).text('Add Contact');
    });

    $('#editContactModal').on('hidden.bs.modal', function() {
        $('#editContactForm')[0].reset();
        $('#updateContactBtn').prop('disabled', false).text('Update Contact');
    });
}

// Setup contact action handlers
function setupContactActionHandlers(contactsDataTable) {
    // Add contact
    $('#saveContactBtn').on('click', function(e) {
        e.preventDefault();
        addContact(contactsDataTable);
    });

    // Update contact
    $('#updateContactBtn').on('click', function(e) {
        e.preventDefault();
        updateContact(contactsDataTable);
    });

    // Edit contact button click
    $(document).on('click', '.edit-contact-btn', function(e) {
        e.preventDefault();
        const contactId = $(this).data('contact-id');
        if (contactId) {
            loadContactForEdit(contactId);
        }
    });


}

// Add new contact
function addContact(contactsDataTable) {
    const formData = {
        contact_type: $('#addContactType').val(),
        contact_value: $('#addContactValue').val().trim(),
        contact_label: $('#addContactLabel').val().trim(),
        display_order: $('#addDisplayOrder').val() || 0,
        status: $('#addStatus').val()
    };

    if (!validateContactData(formData)) {
        return;
    }

    submitContactData('add', formData, contactsDataTable);
}

// Update existing contact
function updateContact(contactsDataTable) {
    const formData = {
        contact_id: $('#editContactId').val().trim(),
        contact_type: $('#editContactType').val(),
        contact_value: $('#editContactValue').val().trim(),
        contact_label: $('#editContactLabel').val().trim(),
        display_order: $('#editDisplayOrder').val() || 0,
        status: $('#editStatus').val()
    };

    if (!validateContactData(formData)) {
        return;
    }

    submitContactData('edit', formData, contactsDataTable);
}

// Load contact data for editing
function loadContactForEdit(contactId) {
    // Show loading state
    const editBtn = $(`.edit-contact-btn[data-contact-id="${contactId}"]`);
    editBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

    $.ajax({
        url: 'app/apiCompanyContact.php',
        type: 'POST',
        data: { action: 'get', contact_id: contactId },
        success: function(response) {
            if (response.status === 1 && response.data) {
                populateEditModal(response.data);
                $('#editContactModal').modal('show');
            } else {
                toastr.error(response.message || 'Error retrieving contact data');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading contact:', error);
            toastr.error('Error retrieving contact data');
        },
        complete: function() {
            // Reset button state
            editBtn.prop('disabled', false).html('<i class="bi bi-pencil"></i>');
        }
    });
}

// Populate edit modal with contact data
function populateEditModal(contactData) {
    $('#editContactId').val(contactData.IdContact);
    $('#editContactType').val(contactData.ContactType);
    $('#editContactValue').val(contactData.ContactValue);
    $('#editContactLabel').val(contactData.ContactLabel);
    $('#editDisplayOrder').val(contactData.DisplayOrder);
    $('#editStatus').val(contactData.Status);
}



// Validate contact data
function validateContactData(data) {
    if (!data.contact_type || data.contact_type.trim() === '') {
        toastr.error('Contact type is required');
        return false;
    }
    
    if (!data.contact_label || data.contact_label.trim() === '') {
        toastr.error('Contact label is required');
        return false;
    }
    
    if (!data.contact_value || data.contact_value.trim() === '') {
        toastr.error('Contact value is required');
        return false;
    }

    if (data.contact_id && !data.contact_id.trim()) {
        toastr.error('Contact ID is required for updates');
        return false;
    }

    return true;
}

// Submit contact data to server
function submitContactData(action, data, contactsDataTable) {
    const submitBtn = action === 'add' ? $('#saveContactBtn') : $('#updateContactBtn');
    const originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: 'app/apiCompanyContact.php',
        type: 'POST',
        data: { action: action, ...data },
        success: function(response) {
            if (response.status === 1) {
                contactsDataTable.ajax.reload();
                
                if (action === 'add') {
                    $('#addContactForm')[0].reset();
                    $('#addContactModal').modal('hide');
                } else {
                    $('#editContactForm')[0].reset();
                    $('#editContactModal').modal('hide');
                }
                
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} contact`);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error submitting contact:', error);
            toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} contact. Please try again.`);
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

// Test function for edit modal (for debugging)
function testEditModal() {
    console.log('Testing edit modal functionality...');
    
    // Test data with realistic values
    const testData = {
        IdContact: 1,
        ContactType: 'email',
        ContactValue: 'test@example.com',
        ContactLabel: 'Test Contact',
        DisplayOrder: 1,
        Status: 1
    };
    
    console.log('Test data:', testData);
    
    // Check if modal exists
    if ($('#editContactModal').length === 0) {
        console.error('Edit modal not found!');
        toastr.error('Edit modal not found on the page');
        return;
    }
    
    // Check if form fields exist
    const requiredFields = [
        'editContactId', 'editContactType', 'editContactValue', 
        'editContactLabel', 'editDisplayOrder', 'editStatus'
    ];
    
    const missingFields = [];
    requiredFields.forEach(fieldId => {
        if ($(`#${fieldId}`).length === 0) {
            missingFields.push(fieldId);
        }
    });
    
    if (missingFields.length > 0) {
        console.error('Missing form fields:', missingFields);
        toastr.error(`Missing form fields: ${missingFields.join(', ')}`);
        return;
    }
    
    // Populate the edit modal with test data
    $('#editContactId').val(testData.IdContact);
    $('#editContactType').val(testData.ContactType);
    $('#editContactValue').val(testData.ContactValue);
    $('#editContactLabel').val(testData.ContactLabel);
    $('#editDisplayOrder').val(testData.DisplayOrder);
    $('#editStatus').val(testData.Status);
    
    // Verify the values were set
    console.log('Modal values after population:');
    console.log('ID:', $('#editContactId').val());
    console.log('Type:', $('#editContactType').val());
    console.log('Value:', $('#editContactValue').val());
    console.log('Label:', $('#editContactLabel').val());
    console.log('Order:', $('#editDisplayOrder').val());
    console.log('Status:', $('#editStatus').val());
    
    // Show the edit modal
    $('#editContactModal').modal('show');
    
    console.log('Edit modal should now be visible with test data');
    toastr.success('Test modal opened successfully!');
}

// Test function to simulate edit button click
function testEditButtonClick() {
    console.log('Testing edit button click simulation...');
    
    // Simulate clicking the first edit button in the table
    const firstEditBtn = $('.edit-contact-btn').first();
    
    if (firstEditBtn.length === 0) {
        console.error('No edit buttons found in the table');
        toastr.error('No edit buttons found. Please make sure the table has data.');
        return;
    }
    
    const contactId = firstEditBtn.data('contact-id');
    console.log('Simulating click on edit button with ID:', contactId);
    
    // Trigger the click event
    firstEditBtn.trigger('click');
}

// Test function to check DataTable data
function testDataTableData() {
    console.log('Testing DataTable data...');
    
    if (typeof contactsDataTable === 'undefined') {
        console.error('DataTable not initialized');
        toastr.error('DataTable not initialized');
        return;
    }
    
    const data = contactsDataTable.data().toArray();
    console.log('DataTable data:', data);
    
    if (data.length === 0) {
        console.warn('No data in DataTable');
        toastr.warning('No data in the table');
    } else {
        console.log(`Found ${data.length} contacts in the table`);
        toastr.success(`Found ${data.length} contacts in the table`);
    }
}

// Test function to reload DataTable
function testDataTableReload() {
    console.log('Testing DataTable reload...');
    
    if (typeof contactsDataTable === 'undefined') {
        console.error('DataTable not initialized');
        toastr.error('DataTable not initialized');
        return;
    }
    
    contactsDataTable.ajax.reload();
    console.log('DataTable reloaded');
    toastr.success('DataTable reloaded successfully');
}
