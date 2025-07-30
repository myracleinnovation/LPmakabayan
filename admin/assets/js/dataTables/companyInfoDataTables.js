$(document).ready(function() {
    // Initialize DataTable for Contacts in Company Info page
    let contactsDataTable;
    
    // Check if DataTable is already initialized
    if ($('#contactsTable').length && !$.fn.DataTable.isDataTable('#contactsTable')) {
        contactsDataTable = new DataTable('#contactsTable', {
            columnDefs: [{ orderable: false, targets: [-1] }],
            order: [[0, 'asc']],
            dom: "<'row'<'col-12 mb-3'tr>>" +
                 "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
            processing: true,
            ajax: {
                url: 'app/apiCompanyContact.php',
                type: 'POST',
                data: { action: 'get_contacts' },
                dataSrc: json => {
                    if (json.success) return json.data || [];
                    toastr.error(json.message || 'Error loading data');
                    return [];
                },
                error: () => toastr.error('Error loading contacts data')
            },
            columns: [
                { data: 'ContactLabel', render: data => `<div class="text-start">${data || '-'}</div>` },
                { data: 'ContactValue', render: data => `<div class="text-start">${data}</div>` },
                { data: 'ContactType', render: data => `<span class="badge bg-primary">${data}</span>` },
                { data: 'DisplayOrder', render: data => `<div class="text-center">${data}</div>` },
                { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
                { data: null, render: (_, __, row) => `
                    <div class="d-flex gap-1">
                        <i class="bi bi-pen edit_contact" style="cursor: pointer;" data-contact-id="${row.IdContact}" title="Edit Contact"></i>
                        <i class="bi bi-trash delete_contact" style="cursor: pointer;" data-contact-id="${row.IdContact}" title="Delete Contact"></i>
                    </div>
                ` }
            ]
        });
    }

    // Handle company info form submission
    $('#companyInfoForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: 'app/apiCompanyInfo.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Company information updated successfully!');
                } else {
                    toastr.error(response.message || 'Error updating company information');
                }
            },
            error: function() {
                toastr.error('Error updating company information');
            }
        });
    });

    // Handle edit contact button clicks from DataTable
    $(document).on('click', '.edit_contact', function() {
        const contactId = $(this).data('contact-id');
        const contactType = $(this).closest('tr').find('td:eq(2)').text().trim();
        const contactValue = $(this).closest('tr').find('td:eq(1)').text().trim();
        const contactLabel = $(this).closest('tr').find('td:eq(0)').text().trim();
        const displayOrder = $(this).closest('tr').find('td:eq(3)').text().trim();
        const status = $(this).closest('tr').find('td:eq(4) .badge').hasClass('bg-success') ? '1' : '0';

        // Populate edit modal
        $('#edit_contact_id').val(contactId);
        $('#edit_contact_type').val(contactType);
        $('#edit_contact_value').val(contactValue);
        $('#edit_contact_label').val(contactLabel);
        $('#edit_display_order').val(displayOrder);
        $('#edit_status').val(status);

        // Show modal
        $('#editContactModal').modal('show');
    });

    // Handle delete contact button clicks from DataTable
    $(document).on('click', '.delete_contact', function() {
        const contactId = $(this).data('contact-id');
        const contactLabel = $(this).closest('tr').find('td:eq(0)').text().trim();

        // Populate delete modal
        $('#delete_contact_id').val(contactId);
        $('#delete_contact_label').text(contactLabel);

        // Show modal
        $('#deleteContactModal').modal('show');
    });

    // Handle contact form submission (create/update)
    const handleContactSubmit = (action, data) => {
        if (!data.contact_type) {
            toastr.error('Contact type is required');
            return;
        }
        
        if (!data.contact_value) {
            toastr.error('Contact value is required');
            return;
        }

        $.ajax({
            url: 'app/apiCompanyContact.php',
            type: 'POST',
            data: { action: action, ...data },
            success: response => {
                if (response.success) {
                    if (contactsDataTable) {
                        contactsDataTable.ajax.reload();
                    }
                    $('#contactForm')[0].reset();
                    $('#contactId').val('');
                    $('#saveContactBtn').show();
                    $('#updateContactBtn').hide();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || `Error ${action === 'add' ? 'creating' : 'updating'} contact`);
                }
            },
            error: () => toastr.error(`Error ${action === 'add' ? 'creating' : 'updating'} contact`)
        });
    };

    // Save contact
    $('#saveContactBtn').on('click', e => {
        e.preventDefault();
        const data = {
            contact_type: $('#contactType').val(),
            contact_value: $('#contactValue').val()?.trim(),
            contact_label: $('#contactLabel').val()?.trim(),
            contact_icon: $('#contactIcon').val()?.trim(),
            display_order: $('#displayOrder').val() || 0,
            status: $('#status').val()
        };
        handleContactSubmit('add', data);
    });

    // Update contact
    $('#updateContactBtn').on('click', e => {
        e.preventDefault();
        const data = {
            contact_id: $('#contactId').val()?.trim(),
            contact_type: $('#contactType').val(),
            contact_value: $('#contactValue').val()?.trim(),
            contact_label: $('#contactLabel').val()?.trim(),
            contact_icon: $('#contactIcon').val()?.trim(),
            display_order: $('#displayOrder').val() || 0,
            status: $('#status').val()
        };
        if (!data.contact_id) {
            toastr.error('Contact ID is required');
            return;
        }
        handleContactSubmit('edit', data);
    });

    // Reset form
    $('#resetContactForm').on('click', () => {
        $('#contactForm')[0].reset();
        $('#contactId').val('');
        $('#saveContactBtn').show();
        $('#updateContactBtn').hide();
    });

    // Edit contact via API
    $(document).on('click', '.edit_contact', function () {
        const contactId = $(this).data('contact-id');
        $.ajax({
            url: 'app/apiCompanyContact.php',
            type: 'POST',
            data: { action: 'get', contact_id: contactId },
            success: response => {
                if (response.success) {
                    const { IdContact, ContactType, ContactValue, ContactLabel, ContactIcon, DisplayOrder, Status } = response.data;
                    $('#contactId').val(IdContact);
                    $('#contactType').val(ContactType);
                    $('#contactValue').val(ContactValue);
                    $('#contactLabel').val(ContactLabel);
                    $('#contactIcon').val(ContactIcon);
                    $('#displayOrder').val(DisplayOrder);
                    $('#status').val(Status);
                    $('#saveContactBtn').hide();
                    $('#updateContactBtn').show();
                } else {
                    toastr.error(response.message || 'Error retrieving contact data');
                }
            },
            error: () => toastr.error('Error retrieving contact data')
        });
    });

    // Delete contact via API
    $(document).on('click', '.delete_contact', function () {
        const contactId = $(this).data('contact-id');
        
        if (confirm('Are you sure you want to delete this contact?')) {
            $.ajax({
                url: 'app/apiCompanyContact.php',
                type: 'POST',
                data: { action: 'delete', contact_id: contactId },
                success: response => {
                    if (response.success) {
                        if (contactsDataTable) {
                            contactsDataTable.ajax.reload();
                        }
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Error deleting contact');
                    }
                },
                error: () => toastr.error('Error deleting contact')
            });
        }
    });
}); 