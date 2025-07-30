// Initialize Contacts DataTable
function initializeContactsDataTable() {
    if (typeof $.fn.DataTable === 'undefined') {
        setTimeout(initializeContactsDataTable, 1000);
        return;
    }
    
    // Check if the table exists
    if ($('#contactsTable').length === 0) {
        return;
    }
    
    const contactsDataTable = $('#contactsTable').DataTable({
        columnDefs: [{ orderable: false, targets: [-1] }],
        order: [[0, 'asc']],
        dom: "<'row'<'col-12 mb-3'tr>>" +
             "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
        processing: true,
        serverSide: false,
        ajax: {
            url: 'app/apiContacts.php',
            type: 'POST',
            data: function(d) {
                d.action = 'get_contacts';
                return d;
            },
            dataSrc: function(json) {
                if (json.success) return json.data || [];
                toastr.error(json.message || 'Error loading data');
                return [];
            },
            error: function() {
                toastr.error('Error loading contacts data');
            }
        },
        columns: [
            { 
                data: 'ContactLabel', 
                render: function(data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'ContactValue', 
                render: function(data) {
                    return `<small class="text-muted">${data}</small>`;
                }
            },
            { 
                data: 'ContactType', 
                render: function(data) {
                    const typeLabels = {
                        'phone': 'Phone',
                        'email': 'Email',
                        'address': 'Address',
                        'social_media': 'Social Media'
                    };
                    return `<span class="badge bg-info">${typeLabels[data] || data}</span>`;
                }
            },
            { 
                data: 'DisplayOrder', 
                render: function(data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            { 
                data: 'Status', 
                render: function(data) {
                    return `<span class="badge bg-${data ? 'success' : 'secondary'}">${data ? 'Active' : 'Inactive'}</span>`;
                }
            },
            { 
                data: null, 
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <i class="bi bi-pencil edit_contact" style="cursor: pointer;" data-contact-id="${row.IdContact}" title="Edit Contact"></i>
                            <i class="bi bi-trash delete_contact" style="cursor: pointer;" data-contact-id="${row.IdContact}" data-contact-label="${row.ContactLabel}" title="Delete Contact"></i>
                        </div>
                    `;
                }
            }
        ]
    });

    // Search functionality
    $('#contactsCustomSearch').on('keyup', function () {
        contactsDataTable.search(this.value).draw();
    });

    // Handle contact form submission (create/update)
    const handleContactSubmit = (action, data) => {
        if (!data.ContactLabel || !data.ContactValue || !data.ContactType) {
            toastr.error('Contact label, value, and type are required');
            return;
        }

        $.ajax({
            url: 'app/apiContacts.php',
            type: 'POST',
            data: { [action]: true, ...data },
                    success: response => {
            if (response.success) {
                contactsDataTable.ajax.reload();
                $('#contactForm')[0].reset();
                $('#contactId').val('');
                $('#saveContactBtn').show();
                $('#updateContactBtn').hide();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || `Error ${action === 'create_contact' ? 'creating' : 'updating'} contact`);
            }
        },
            error: () => toastr.error(`Error ${action === 'create_contact' ? 'creating' : 'updating'} contact`)
        });
    };

    // Save contact
    $('#saveContactBtn').on('click', e => {
        e.preventDefault();
        const data = {
            ContactLabel: $('#contactLabel').val()?.trim(),
            ContactValue: $('#contactValue').val()?.trim(),
            ContactType: $('#contactType').val(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        handleContactSubmit('create_contact', data);
    });

    // Update contact
    $('#updateContactBtn').on('click', e => {
        e.preventDefault();
        const data = {
            contact_id: $('#contactId').val()?.trim(),
            ContactLabel: $('#contactLabel').val()?.trim(),
            ContactValue: $('#contactValue').val()?.trim(),
            ContactType: $('#contactType').val(),
            DisplayOrder: $('#displayOrder').val() || 0,
            Status: $('#status').val() || 1
        };
        if (!data.contact_id) {
            toastr.error('Contact ID is required');
            return;
        }
        handleContactSubmit('update_contact', data);
    });

    // Reset form
    $('#resetContactForm').on('click', () => {
        $('#contactForm')[0].reset();
        $('#contactId').val('');
        $('#saveContactBtn').show();
        $('#updateContactBtn').hide();
    });

    // Edit contact
    $(document).on('click', '.edit_contact', function () {
        const contactId = $(this).data('contact-id');
        $.ajax({
            url: 'app/apiContacts.php',
            type: 'POST',
            data: { action: 'get', contact_id: contactId },
            success: response => {
                if (response.success) {
                    const contact = response.data;
                    $('#edit_contact_id').val(contact.IdContact);
                    $('#edit_contact_label').val(contact.ContactLabel);
                    $('#edit_contact_value').val(contact.ContactValue);
                    $('#edit_contact_type').val(contact.ContactType);
                    $('#edit_display_order').val(contact.DisplayOrder);
                    $('#edit_status').val(contact.Status);
                    $('#editContactModal').modal('show');
                } else {
                    toastr.error(response.message || 'Error retrieving contact data');
                }
            },
            error: () => toastr.error('Error retrieving contact data')
        });
    });

    // Delete contact
    $(document).on('click', '.delete_contact', function () {
        const contactId = $(this).data('contact-id');
        const contactLabel = $(this).data('contact-label');
        $('#delete_contact_id').val(contactId);
        $('#delete_contact_label').text(contactLabel);
        $('#deleteContactModal').modal('show');
    });

    // Delete contact button in modal
    $(document).on('click', '#deleteContactModal .btn-danger', function() {
        const contactId = $('#delete_contact_id').val();
        $.ajax({
            url: 'app/apiContacts.php',
            type: 'POST',
            data: { action: 'delete', contact_id: contactId },
            success: response => {
                if (response.success) {
                    contactsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteContactModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting contact');
                }
            },
            error: () => toastr.error('Error deleting contact')
        });
    });

    // Confirm delete contact
    $('#confirmDeleteContact').on('click', function() {
        const contactId = $('#delete_contact_id').val();
        $.ajax({
            url: 'app/apiContacts.php',
            type: 'POST',
            data: { action: 'delete', contact_id: contactId },
            success: response => {
                if (response.success) {
                    contactsDataTable.ajax.reload();
                    toastr.success(response.message);
                    $('#deleteContactModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting contact');
                }
            },
            error: () => toastr.error('Error deleting contact')
        });
    });
}

$(document).ready(function() {
    initializeContactsDataTable();
});