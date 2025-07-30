// =====================================================
// ADMIN CRUD OPERATIONS JAVASCRIPT
// =====================================================
// Handles all Create, Read, Update, Delete operations
// for the Makabayan Construction admin panel
// =====================================================

class AdminCRUD {
    constructor() {
        this.currentAction = '';
        this.currentId = null;
        this.currentTable = '';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        // Add button events
        $(document).on('click', '.btn-add', (e) => {
            e.preventDefault();
            this.showAddModal();
        });

        // Edit button events
        $(document).on('click', '.btn-edit', (e) => {
            e.preventDefault();
            const id = $(e.currentTarget).data('id');
            this.showEditModal(id);
        });

        // Delete button events
        $(document).on('click', '.btn-delete', (e) => {
            e.preventDefault();
            const id = $(e.currentTarget).data('id');
            this.showDeleteConfirm(id);
        });

        // Form submit events
        $(document).on('submit', '.crud-form', (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        });

        // Modal close events
        $(document).on('click', '.btn-close, .btn-cancel', (e) => {
            e.preventDefault();
            this.closeModal();
        });

        // Image preview events
        $(document).on('change', 'input[type="file"]', (e) => {
            this.handleImagePreview(e);
        });
    }

    // =====================================================
    // DATA LOADING METHODS
    // =====================================================

    loadData() {
        const currentPage = this.getCurrentPage();
        if (!currentPage) return;

        switch (currentPage) {
            case 'admins':
                this.loadAdmins();
                break;
            case 'company-info':
                this.loadCompanyInfo();
                break;
            case 'features':
                this.loadFeatures();
                break;
            case 'specialties':
                this.loadSpecialties();
                break;
            case 'industries':
                this.loadIndustries();
                break;
            case 'projects':
                this.loadProjects();
                break;
            case 'project-categories':
                this.loadProjectCategories();
                break;
            case 'process':
                this.loadProcess();
                break;
            case 'contacts':
                this.loadContacts();
                break;
            case 'settings':
                this.loadSettings();
                break;
        }
    }

    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('index.php')) return 'admins';
        if (path.includes('companyInfo.php')) return 'company-info';
        if (path.includes('features.php')) return 'features';
        if (path.includes('specialties.php')) return 'specialties';
        if (path.includes('industries.php')) return 'industries';
        if (path.includes('projects.php')) return 'projects';
        if (path.includes('project-categories.php')) return 'project-categories';
        if (path.includes('process.php')) return 'process';
        if (path.includes('contacts.php')) return 'contacts';
        if (path.includes('settings.php')) return 'settings';
        return null;
    }

    // =====================================================
    // LOAD METHODS FOR EACH SECTION
    // =====================================================

    loadAdmins() {
        this.currentTable = 'admins';
        $.ajax({
            url: 'app/apiAdmins.php',
            type: 'POST',
            data: { action: 'get_admins' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'admins');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load admin data');
            }
        });
    }

    loadCompanyInfo() {
        this.currentTable = 'company_info';
        $.ajax({
            url: 'app/apiCompanyInfo.php',
            type: 'POST',
            data: { action: 'get_company_info' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'company_info');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load company information');
            }
        });
    }

    loadFeatures() {
        this.currentTable = 'features';
        $.ajax({
            url: 'app/apiFeatures.php',
            type: 'POST',
            data: { action: 'get_features' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'features');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load features');
            }
        });
    }

    loadSpecialties() {
        this.currentTable = 'specialties';
        $.ajax({
            url: 'app/apiSpecialties.php',
            type: 'POST',
            data: { action: 'get_specialties' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'specialties');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load specialties');
            }
        });
    }

    loadIndustries() {
        this.currentTable = 'industries';
        $.ajax({
            url: 'app/apiIndustries.php',
            type: 'POST',
            data: { action: 'get_industries' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'industries');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load industries');
            }
        });
    }

    loadProjects() {
        this.currentTable = 'projects';
        $.ajax({
            url: 'app/apiProjects.php',
            type: 'POST',
            data: { action: 'get_projects' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'projects');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load projects');
            }
        });
    }

    loadProjectCategories() {
        this.currentTable = 'project_categories';
        $.ajax({
            url: 'app/apiProjectCategories.php',
            type: 'POST',
            data: { action: 'get_categories' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'project_categories');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load project categories');
            }
        });
    }

    loadProcess() {
        this.currentTable = 'process';
        $.ajax({
            url: 'app/apiProcess.php',
            type: 'POST',
            data: { action: 'get_processes' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'process');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load process steps');
            }
        });
    }

    loadContacts() {
        this.currentTable = 'contacts';
        $.ajax({
            url: 'app/apiContacts.php',
            type: 'POST',
            data: { action: 'get_contacts' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'contacts');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load contacts');
            }
        });
    }

    loadSettings() {
        this.currentTable = 'settings';
        $.ajax({
            url: 'app/apiSettings.php',
            type: 'POST',
            data: { action: 'get_settings' },
            success: (response) => {
                if (response.success) {
                    this.renderTable(response.data, 'settings');
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load settings');
            }
        });
    }

    // =====================================================
    // MODAL METHODS
    // =====================================================

    showAddModal() {
        this.currentAction = 'add';
        this.currentId = null;
        this.showModal('Add New Record', this.getFormTemplate());
    }

    showEditModal(id) {
        this.currentAction = 'edit';
        this.currentId = id;
        
        // Get record data
        $.ajax({
            url: `app/api${this.getApiFile()}.php`,
            type: 'POST',
            data: { 
                action: 'get',
                [this.getIdField()]: id 
            },
            success: (response) => {
                if (response.success) {
                    this.showModal('Edit Record', this.getFormTemplate(), response.data);
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to load record data');
            }
        });
    }

    showDeleteConfirm(id) {
        if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            this.deleteRecord(id);
        }
    }

    showModal(title, content, data = null) {
        const modal = `
            <div class="modal fade" id="crudModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        $('#crudModal').remove();
        
        // Add new modal
        $('body').append(modal);
        
        // Populate form if editing
        if (data) {
            this.populateForm(data);
        }
        
        // Show modal
        $('#crudModal').modal('show');
    }

    closeModal() {
        $('#crudModal').modal('hide');
        setTimeout(() => {
            $('#crudModal').remove();
        }, 300);
    }

    // =====================================================
    // FORM HANDLING METHODS
    // =====================================================

    getFormTemplate() {
        const templates = {
            admins: this.getAdminForm(),
            company_info: this.getCompanyInfoForm(),
            features: this.getFeatureForm(),
            specialties: this.getSpecialtyForm(),
            industries: this.getIndustryForm(),
            projects: this.getProjectForm(),
            project_categories: this.getCategoryForm(),
            process: this.getProcessForm(),
            contacts: this.getContactForm(),
            settings: this.getSettingForm()
        };

        return templates[this.currentTable] || '';
    }

    getAdminForm() {
        return `
            <form class="crud-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" ${this.currentAction === 'add' ? 'required' : ''}>
                            ${this.currentAction === 'edit' ? '<small class="text-muted">Leave blank to keep current password</small>' : ''}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-control" name="role">
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="editor">Editor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }

    getCompanyInfoForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="form-control" name="company_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tagline</label>
                    <input type="text" class="form-control" name="tagline">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mission</label>
                    <textarea class="form-control" name="mission" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Vision</label>
                    <textarea class="form-control" name="vision" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">About Image</label>
                            <input type="file" class="form-control" name="about_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Logo Image</label>
                            <input type="file" class="form-control" name="logo_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Favicon Image</label>
                            <input type="file" class="form-control" name="favicon_image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getFeatureForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Feature Title</label>
                    <input type="text" class="form-control" name="feature_title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Feature Description</label>
                    <textarea class="form-control" name="feature_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Feature Image</label>
                            <input type="file" class="form-control" name="feature_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getSpecialtyForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Specialty Name</label>
                    <input type="text" class="form-control" name="specialty_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Specialty Description</label>
                    <textarea class="form-control" name="specialty_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Specialty Image</label>
                            <input type="file" class="form-control" name="specialty_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getIndustryForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Industry Name</label>
                    <input type="text" class="form-control" name="industry_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Industry Description</label>
                    <textarea class="form-control" name="industry_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Industry Image</label>
                            <input type="file" class="form-control" name="industry_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getProjectForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Project Title</label>
                    <input type="text" class="form-control" name="project_title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Project Description</label>
                    <textarea class="form-control" name="project_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Project Owner</label>
                            <input type="text" class="form-control" name="project_owner">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Project Location</label>
                            <input type="text" class="form-control" name="project_location">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Project Area (sqm)</label>
                            <input type="number" class="form-control" name="project_area" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Project Value (PHP)</label>
                            <input type="number" class="form-control" name="project_value" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Turnover Date</label>
                            <input type="date" class="form-control" name="turnover_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Project Category</label>
                            <select class="form-control" name="project_category_id" id="project_category_select">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 1</label>
                            <input type="file" class="form-control" name="project_image1" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 2</label>
                            <input type="file" class="form-control" name="project_image2" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 3</label>
                            <input type="file" class="form-control" name="project_image3" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 4</label>
                            <input type="file" class="form-control" name="project_image4" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 5</label>
                            <input type="file" class="form-control" name="project_image5" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Project Image 6</label>
                            <input type="file" class="form-control" name="project_image6" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }

    getCategoryForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="category_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category Description</label>
                    <textarea class="form-control" name="category_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" class="form-control" name="category_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getProcessForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Process Title</label>
                    <input type="text" class="form-control" name="process_title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Process Description</label>
                    <textarea class="form-control" name="process_description" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Process Image</label>
                            <input type="file" class="form-control" name="process_image" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
        `;
    }

    getContactForm() {
        return `
            <form class="crud-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Type</label>
                            <select class="form-control" name="contact_type" required>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="address">Address</option>
                                <option value="social_media">Social Media</option>
                                <option value="website">Website</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Value</label>
                            <input type="text" class="form-control" name="contact_value" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Label</label>
                            <input type="text" class="form-control" name="contact_label">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contact Icon</label>
                            <input type="text" class="form-control" name="contact_icon" placeholder="bi-telephone">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }

    getSettingForm() {
        return `
            <form class="crud-form">
                <div class="mb-3">
                    <label class="form-label">Setting Key</label>
                    <input type="text" class="form-control" name="setting_key" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Setting Value</label>
                    <textarea class="form-control" name="setting_value" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Setting Description</label>
                    <textarea class="form-control" name="setting_description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Setting Type</label>
                            <select class="form-control" name="setting_type">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }

    // =====================================================
    // FORM SUBMISSION AND DATA HANDLING
    // =====================================================

    handleFormSubmit() {
        const formData = new FormData($('.crud-form')[0]);
        formData.append('action', this.currentAction);

        if (this.currentAction === 'edit' && this.currentId) {
            formData.append(this.getIdField(), this.currentId);
        }

        // Load categories for project form
        if (this.currentTable === 'projects') {
            this.loadCategoriesForProject();
        }

        $.ajax({
            url: `app/api${this.getApiFile()}.php`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    this.showAlert('success', response.message);
                    this.closeModal();
                    this.loadData();
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to save data');
            }
        });
    }

    loadCategoriesForProject() {
        $.ajax({
            url: 'app/apiProjectCategories.php',
            type: 'POST',
            data: { action: 'get_categories' },
            success: (response) => {
                if (response.success) {
                    const select = $('#project_category_select');
                    select.empty();
                    select.append('<option value="">Select Category</option>');
                    
                    response.data.forEach(category => {
                        select.append(`<option value="${category.IdCategory}">${category.CategoryName}</option>`);
                    });
                }
            }
        });
    }

    populateForm(data) {
        const form = $('.crud-form');
        
        // Populate form fields based on data
        Object.keys(data).forEach(key => {
            const field = form.find(`[name="${this.camelToSnake(key)}"]`);
            if (field.length > 0) {
                if (field.attr('type') === 'file') {
                    // Handle file fields differently
                    if (data[key]) {
                        field.after(`<small class="text-muted">Current: ${data[key]}</small>`);
                    }
                } else {
                    field.val(data[key]);
                }
            }
        });
    }

    deleteRecord(id) {
        $.ajax({
            url: `app/api${this.getApiFile()}.php`,
            type: 'POST',
            data: {
                action: 'delete',
                [this.getIdField()]: id
            },
            success: (response) => {
                if (response.success) {
                    this.showAlert('success', response.message);
                    this.loadData();
                } else {
                    this.showAlert('error', response.message);
                }
            },
            error: () => {
                this.showAlert('error', 'Failed to delete record');
            }
        });
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    getApiFile() {
        const apiFiles = {
            admins: 'Admins',
            company_info: 'CompanyInfo',
            features: 'Features',
            specialties: 'Specialties',
            industries: 'Industries',
            projects: 'Projects',
            project_categories: 'ProjectCategories',
            process: 'Process',
            contacts: 'Contacts',
            settings: 'Settings'
        };

        return apiFiles[this.currentTable] || '';
    }

    getIdField() {
        const idFields = {
            admins: 'admin_id',
            company_info: 'company_id',
            features: 'feature_id',
            specialties: 'specialty_id',
            industries: 'industry_id',
            projects: 'project_id',
            project_categories: 'category_id',
            process: 'process_id',
            contacts: 'contact_id',
            settings: 'setting_id'
        };

        return idFields[this.currentTable] || 'id';
    }

    camelToSnake(str) {
        return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
    }

    renderTable(data, tableType) {
        const tableBody = $('#dataTable tbody');
        tableBody.empty();

        if (data.length === 0) {
            tableBody.append('<tr><td colspan="100%" class="text-center">No data available</td></tr>');
            return;
        }

        data.forEach(item => {
            const row = this.createTableRow(item, tableType);
            tableBody.append(row);
        });
    }

    createTableRow(item, tableType) {
        // This method should be customized based on your table structure
        // For now, returning a basic row structure
        return `
            <tr>
                <td>${item.Id || item.IdAdmin || item.IdCompany || item.IdFeature || item.IdSpecialty || item.IdIndustry || item.IdProject || item.IdCategory || item.IdProcess || item.IdContact || item.IdSetting || 'N/A'}</td>
                <td>${item.Name || item.Title || item.Username || item.CompanyName || item.FeatureTitle || item.SpecialtyName || item.IndustryName || item.ProjectTitle || item.CategoryName || item.ProcessTitle || item.ContactValue || item.SettingKey || 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-edit" data-id="${item.Id || item.IdAdmin || item.IdCompany || item.IdFeature || item.IdSpecialty || item.IdIndustry || item.IdProject || item.IdCategory || item.IdProcess || item.IdContact || item.IdSetting}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${item.Id || item.IdAdmin || item.IdCompany || item.IdFeature || item.IdSpecialty || item.IdIndustry || item.IdProject || item.IdCategory || item.IdProcess || item.IdContact || item.IdSetting}">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    }

    handleImagePreview(event) {
        const file = event.target.files[0];
        const preview = $(event.target).siblings('.image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview.length === 0) {
                    $(event.target).after(`<div class="image-preview mt-2"><img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;"></div>`);
                } else {
                    preview.find('img').attr('src', e.target.result);
                }
            };
            reader.readAsDataURL(file);
        }
    }

    showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert
        $('.container').first().prepend(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
}

// Initialize CRUD functionality when document is ready
$(document).ready(function() {
    window.adminCRUD = new AdminCRUD();
}); 