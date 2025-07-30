// Initialize DataTable for Company Info
const companyInfoDataTable = new DataTable('#dataTable', {
    columnDefs: [{ orderable: false, targets: [-1] }],
    order: [[0, 'asc']],
    dom: "<'row'<'col-12 mb-3'tr>>" +
         "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
    processing: true,
    ajax: {
        url: 'app/apiCompanyInfo.php',
        type: 'POST',
        data: { action: 'get_company_info' },
        dataSrc: json => {
            if (json.success) return json.data || [];
            toastr.error(json.message || 'Error loading data');
            return [];
        },
        error: () => toastr.error('Error loading company info data')
    },
    columns: [
        { data: 'CompanyName', render: data => `<div class="text-start">${data}</div>` },
        { data: 'Tagline', render: data => `<div class="text-start">${data || '-'}</div>` },
        { data: 'Status', render: data => `<span class="badge ${data == 1 ? 'bg-success' : 'bg-danger'}">${data == 1 ? 'Active' : 'Inactive'}</span>` },
        { data: 'CreatedTimestamp', render: data => `<div class="text-start">${moment(data).format('MMM DD, YYYY')}</div>` },
        { data: null, render: (_, __, row) => `
            <div class="d-flex gap-1">
                <i class="bi bi-pen edit_company_info" style="cursor: pointer;" data-company-id="${row.IdCompany}" title="Edit Company Info"></i>
            </div>
        ` }
    ]
});

// Search functionality
$('#companyInfoCustomSearch').on('keyup', function () {
    companyInfoDataTable.search(this.value).draw();
});

// Handle company info form submission (update only)
const handleCompanyInfoSubmit = (data) => {
    if (!data.company_name) {
        toastr.error('Company name is required');
        return;
    }

    $.ajax({
        url: 'app/apiCompanyInfo.php',
        type: 'POST',
        data: { action: 'edit', ...data },
        success: response => {
            if (response.success) {
                companyInfoDataTable.ajax.reload();
                $('#companyInfoForm')[0].reset();
                toastr.success(response.message);
            } else {
                toastr.error(response.message || 'Error updating company info');
            }
        },
        error: () => toastr.error('Error updating company info')
    });
};

// Update company info
$('#updateCompanyInfoBtn').on('click', e => {
    e.preventDefault();
    const data = {
        company_id: $('#companyId').val()?.trim(),
        company_name: $('#companyName').val()?.trim(),
        tagline: $('#tagline').val()?.trim(),
        description: $('#description').val()?.trim(),
        mission: $('#mission').val()?.trim(),
        vision: $('#vision').val()?.trim(),
        about_image: $('#aboutImage').val()?.trim(),
        logo_image: $('#logoImage').val()?.trim(),
        favicon_image: $('#faviconImage').val()?.trim(),
        status: $('#status').val()
    };
    if (!data.company_id) {
        toastr.error('Company ID is required');
        return;
    }
    handleCompanyInfoSubmit(data);
});

// Reset form
$('#resetCompanyInfoForm').on('click', () => {
    $('#companyInfoForm')[0].reset();
});

// Edit company info
$(document).on('click', '.edit_company_info', function () {
    const companyId = $(this).data('company-id');
    $.ajax({
        url: 'app/apiCompanyInfo.php',
        type: 'POST',
        data: { action: 'get', company_id: companyId },
        success: response => {
            if (response.success) {
                const { IdCompany, CompanyName, Tagline, Description, Mission, Vision, AboutImage, LogoImage, FaviconImage, Status } = response.data;
                $('#companyId').val(IdCompany);
                $('#companyName').val(CompanyName);
                $('#tagline').val(Tagline);
                $('#description').val(Description);
                $('#mission').val(Mission);
                $('#vision').val(Vision);
                $('#aboutImage').val(AboutImage);
                $('#logoImage').val(LogoImage);
                $('#faviconImage').val(FaviconImage);
                $('#status').val(Status);
            } else {
                toastr.error(response.message || 'Error retrieving company info data');
            }
        },
        error: () => toastr.error('Error retrieving company info data')
    });
}); 