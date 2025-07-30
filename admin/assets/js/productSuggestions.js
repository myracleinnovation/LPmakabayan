(function () {
    class ProductSuggestionHandler {
        constructor(modalSelector, inputSelector, suggestionsId) {
            this.modal = document.querySelector(modalSelector);
            if (!this.modal) {
                console.error(`Modal with selector "${modalSelector}" not found.`);
                return;
            }

            this.input = this.modal.querySelector(inputSelector);
            this.suggestionsContainer = this.modal.querySelector('#' + suggestionsId);

            if (!this.input) {
                console.error(`Input with selector "${inputSelector}" not found within modal "${modalSelector}".`);
                return;
            }
            if (!this.suggestionsContainer) {
                console.error(`Suggestions container with ID "${suggestionsId}" not found within modal "${modalSelector}".`);
                return;
            }

            this.searchTimeout = null;
            this.init();
            
            // Refresh categories when modal is shown
            $(this.modal).on('shown.bs.modal', () => {
                this.refreshCategoryDropdowns();
            });
        }

        generateItemCode() {
            const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ123456789';
            return Array.from({
                length: 12
            }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
        }

        init() {
            this.input.addEventListener('input', this.handleInput.bind(this));
            this.suggestionsContainer.addEventListener('click', this.handleSuggestionClick.bind(this));
            this.boundHandleClickOutside = this.handleClickOutside.bind(this);
            document.addEventListener('click', this.boundHandleClickOutside);

            this.initGlobalEventListeners();
        }

        destroy() {
            this.input.removeEventListener('input', this.handleInput.bind(this));
            this.suggestionsContainer.removeEventListener('click', this.handleSuggestionClick.bind(this));
            document.removeEventListener('click', this.boundHandleClickOutside);
        }

        handleInput() {
            clearTimeout(this.searchTimeout);
            const searchTerm = this.input.value.trim();

            if (searchTerm.length === 0) {
                this.hideSuggestions();
                return;
            }

            this.searchTimeout = setTimeout(() => {
                this.searchProducts(searchTerm);
            }, 300);
        }

        handleSuggestionClick(e) {
            const suggestionItem = e.target.closest('.suggestion-item');
            if (!suggestionItem) return;

            try {
                const product = JSON.parse(suggestionItem.dataset.product);
                this.fillProductDetails(product);
                this.hideSuggestions();
            } catch (error) {
                console.error('Error parsing product data from suggestion item:', error);
            }
        }

        handleClickOutside(e) {
            const isClickInsideInput = this.input.contains(e.target);
            const isClickInsideSuggestions = this.suggestionsContainer.contains(e.target);
            const isClickInsideModal = this.modal.contains(e.target);

            if (!isClickInsideInput && !isClickInsideSuggestions && !isClickInsideModal) {
                this.hideSuggestions();
            }
        }

        searchProducts(searchTerm) {
            if (!searchTerm || searchTerm.length < 1) {
                this.hideSuggestions();
                return;
            }

            $.ajax({
                url: 'app/API/apiOrders.php',
                type: 'POST',
                data: {
                    search_item_suggestions: 1,
                    term: searchTerm
                },
                dataType: 'json',
                success: (res) => {
                    if (res.status === 1 && Array.isArray(res.data)) {
                        this.displaySuggestions(res.data);
                    } else {
                        console.warn('No suggestions found or invalid response format:', res);
                        this.hideSuggestions();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error searching products:', error);
                    console.error('Response:', xhr.responseText);
                    this.hideSuggestions();
                },
            });
        }

        displaySuggestions(products) {
            this.suggestionsContainer.innerHTML = '';
            if (products.length === 0) {
                this.hideSuggestions();
                return;
            }

            products.forEach(product => {
                const item = document.createElement('div');
                item.classList.add('suggestion-item', 'p-2', 'border-bottom', 'cursor-pointer');
                item.dataset.product = JSON.stringify(product);

                const discountedPrice = parseFloat(product.DiscountedPrice).toFixed(2);
                const unitPrice = parseFloat(product.UnitPrice).toFixed(2);

                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark">${product.ProductDesc}</strong>
                            <div class="small text-muted">
                                ${product.ItemCode} | ${product.CategoryName}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">&#x20B1;${discountedPrice} / ${product.MeasureDesc}</div>
                            <div class="small text-muted">Unit Price: &#x20B1;${unitPrice}</div>
                        </div>
                    </div>
                `;
                this.suggestionsContainer.appendChild(item);
            });
            this.suggestionsContainer.classList.remove('d-none');
        }

        hideSuggestions() {
            this.suggestionsContainer.classList.add('d-none');
            this.suggestionsContainer.innerHTML = '';
        }

        fillProductDetails(product) {
            const modal = $(this.modal);
            const itemDesc = modal.find('.item-desc');
            const itemCode = modal.find('.item-code');
            const itemId = modal.find('.item-id');
            const itemCategory = modal.find('.item-category');
            const itemUOM = modal.find('.item-uom');
            const itemUP = modal.find('.item-up');
            const itemDP = modal.find('.item-dp');
            const itemQty = modal.find('.item-qty');
            const itemAmount = modal.find('.item-amount');

            // Set the product description
            if (product.ProductDesc) {
                itemDesc.val(product.ProductDesc);
            } else {
                console.error('Product description is missing:', product);
                toastr.error('Product description is missing');
                return;
            }

            // Set the item code
            itemCode.val(product.ItemCode);

            // Set the item ID if it exists
            if (product.idPriceList) {
                itemId.val(product.idPriceList);
            }

            // Set the category
            if (product.CategoryId) {
                itemCategory.val(product.CategoryId);
            }

            // Set the unit of measure
            if (product.UnitMeasureId) {
                itemUOM.val(product.UnitMeasureId);
            }

            // Set prices
            itemUP.val(product.UnitPrice);
            itemDP.val(product.DiscountedPrice || product.UnitPrice);

            // Set quantity to 1 by default
            itemQty.val(1);

            // Calculate amount
            const amount = (parseFloat(product.DiscountedPrice || product.UnitPrice) * 1).toFixed(2);
            itemAmount.val(amount);

            // Hide suggestions
            this.hideSuggestions();

            // Update order totals
            this.updateOrderAmounts(modal.attr('id'));
        }

        updateOrderAmounts(modalId) {
            // Delegate to the new OrderCalculator
            if (window.orderCalculator) {
                window.orderCalculator.recalculateAllAmounts();
            }
        }

        // New method for updating only Due Amount (for VAT and Delivery Fee changes)
        updateDueAmountOnly(modalId) {
            // Delegate to the new OrderCalculator
            if (window.orderCalculator) {
                const activeModal = window.orderCalculator.getActiveModal();
                if (activeModal) {
                    const vatInput = activeModal.find(`#${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderVatAmount`)[0];
                    window.orderCalculator.recalculateDueAmountOnly(vatInput);
                }
            }
        }

        resetItemForm(modalId) {
            const modal = $(`#${modalId}`);
            modal.find('.item-desc').val('');
            modal.find('.item-code').val('');
            modal.find('.item-uom').val('');
            modal.find('.item-up').val('');
            modal.find('.item-dp').val('');
            modal.find('.item-qty').val('1');
            modal.find('.item-amount').val('');
            modal.find('.item-id').val('');
            modal.find('.item-category').val('');
            
            // Clear editing state
            modal.removeAttr('data-editing-item-code');
            modal.find('#newAddOrderDetailToList, #editAddOrderDetailToList').text('Add');
            modal.find('#newResetOrderDetailFields, #editResetOrderDetailFields').text('Cancel');
            
            // Remove the edit note
            modal.find('.edit-note').remove();
            
            // Remove visual indicators
            modal.find('.item-desc, .item-code, .item-category, .item-uom, .item-up, .item-dp, .item-qty, .item-amount').removeClass('border-warning');
        }

        cancelEdit(modalId) {
            const modal = $(`#${modalId}`);
            this.resetItemForm(modalId);
            toastr.info('Edit cancelled');
        }

        loadItemIntoForm(modalId, itemCode, rowData = null) {
            const modal = $(`#${modalId}`);
            const tableId = `${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderItemsTable`;
            
            // If rowData is provided, use it directly
            if (rowData) {
                this.fillFormFromRowData(modal, rowData);
                return;
            }
            
            // First, let's see what tables exist in the modal
            const allTables = modal.find('table');
            allTables.each(function(index) {
            });
            
            let row = modal.find(`#${tableId} tbody tr[data-item-code="${itemCode}"]`);
            
            if (row.length === 0) {
                // Fallback: try to find by item code in ALL tables in the modal
                const allRows = modal.find('table tbody tr');
                
                allRows.each(function(index) {
                    const rowItemCode = $(this).attr('data-item-code');
                    const tableId = $(this).closest('table').attr('id');
                });
                
                row = modal.find('table tbody tr').filter(function() {
                    const rowItemCode = $(this).attr('data-item-code');
                    return rowItemCode && rowItemCode === itemCode;
                });
                
                if (row.length === 0) {
                    toastr.error('Item not found');
                    return;
                }
            }

            // Get data from the row
            const itemDesc = row.attr('data-product-desc');
            const itemCategory = row.attr('data-category-id');
            const itemUOMId = row.attr('data-unit-measure-id');
            const itemUOM = row.find('td:eq(1)').text();
            const itemUP = row.find('td:eq(2)').text();
            const itemDP = row.find('td:eq(3)').text();
            const itemQty = row.find('td:eq(4)').text();
            const itemAmount = row.find('td:eq(5)').text();

            // Load data into form fields
            modal.find('.item-desc').val(itemDesc);
            modal.find('.item-code').val(itemCode);
            modal.find('.item-category').val(itemCategory);
            modal.find('.item-uom').val(itemUOMId);
            modal.find('.item-up').val(itemUP);
            modal.find('.item-dp').val(itemDP === '-' ? itemUP : itemDP);
            modal.find('.item-qty').val(itemQty);
            modal.find('.item-amount').val(itemAmount);

            // Set editing state
            modal.attr('data-editing-item-code', itemCode);
            modal.find('#newAddOrderDetailToList, #editAddOrderDetailToList').text('Update');
            modal.find('#newResetOrderDetailFields, #editResetOrderDetailFields').text('Cancel');

            // Focus on the description field
            modal.find('.item-desc').focus();
            
        }

        fillFormFromRowData(modal, rowData) {
            // Load data into form fields
            modal.find('.item-desc').val(rowData.productDesc);
            modal.find('.item-code').val(rowData.itemCode);
            modal.find('.item-category').val(rowData.categoryId);
            modal.find('.item-uom').val(rowData.uomId);
            modal.find('.item-up').val(rowData.unitPrice);
            modal.find('.item-dp').val(rowData.discountedPrice === '-' ? rowData.unitPrice : rowData.discountedPrice);
            modal.find('.item-qty').val(rowData.quantity);
            modal.find('.item-amount').val(rowData.amount);

            // Set editing state
            modal.attr('data-editing-item-code', rowData.itemCode);
            modal.find('#newAddOrderDetailToList, #editAddOrderDetailToList').text('Update');
            modal.find('#newResetOrderDetailFields, #editResetOrderDetailFields').text('Cancel');

            // Show a note that this only updates the order item, not the master pricelist
            if (modal.find('.edit-note').length === 0) {
                modal.find('.modal-body').prepend(`
                    <div class="alert alert-info edit-note" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> You are editing this item for this order only. 
                        To update the master pricelist, use the Pricelist management section.
                    </div>
                `);
            }

            // Focus on the description field
            modal.find('.item-desc').focus();
        }

        initGlobalEventListeners() {
            $(document).off('click', '#newResetOrderDetailFields, #editResetOrderDetailFields');
            $(document).off('input', '.item-qty, .item-dp');
            $(document).off('click', '#newAddOrderDetailToList, #editAddOrderDetailToList');
            $(document).off('click', '.remove-item');
            $(document).off('input', '#newOrderDeliveryFee, #editOrderDeliveryFee');
            $(document).off('shown.bs.modal', '#newOrderModal, #editOrderModal');

            // Add event listener for category updates
            $(document).on('categoryUpdated', () => {
                this.refreshCategoryDropdowns();
            });

            $(document).on('click', '#newResetOrderDetailFields, #editResetOrderDetailFields', (e) => {
                const modalId = $(e.target).closest('.modal').attr('id');
                const modal = $(`#${modalId}`);
                const isEditing = modal.attr('data-editing-item-code');
                
                if (isEditing) {
                    // If editing, cancel the edit
                    this.cancelEdit(modalId);
                } else {
                    // If not editing, just reset the form
                    this.resetItemForm(modalId);
                }
            });

            $(document).on('input', '.item-qty, .item-dp', (e) => {
                const modal = $(e.target).closest('.modal');
                const modalId = modal.attr('id');
                const qty = parseFloat(modal.find('.item-qty').val()) || 0;
                const dp = parseFloat(modal.find('.item-dp').val()) || 0;
                const amount = (qty * dp).toFixed(2);
                modal.find('.item-amount').val(amount);
                
                // Update order totals when quantity or discounted price changes
                this.updateOrderAmounts(modalId);
            });

            $(document).on('click', '#newAddOrderDetailToList, #editAddOrderDetailToList', (e) => {
                const modal = $(e.target).closest('.modal');
                const modalId = modal.attr('id');
                const itemDesc = modal.find('.item-desc').val();
                let itemCode = modal.find('.item-code').val();
                const itemUOM = modal.find('.item-uom option:selected').text();
                const itemUOMId = modal.find('.item-uom').val();
                const itemUP = modal.find('.item-up').val();
                const itemDP = modal.find('.item-dp').val();
                const itemQty = parseFloat(modal.find('.item-qty').val()) || 0;
                const itemAmount = modal.find('.item-amount').val();
                const itemCategory = modal.find('.item-category').val();
                
                if (!itemDesc || !itemUOM || !itemUP || itemQty <= 0 || parseFloat(itemAmount) <= 0 || !itemCategory || !itemUOMId) {
                    toastr.error('Please fill in all required fields and ensure quantity and amount are valid and greater than zero.');
                    return;
                }

                // If we're editing and the description has changed, we need to look up the correct ItemCode
                const currentEditingItemCode = modal.attr('data-editing-item-code');
                if (currentEditingItemCode) {
                    // Check if the description has changed from the original
                    const originalRow = modal.find(`#${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderItemsTable tbody tr[data-item-code="${currentEditingItemCode}"]`);
                    if (originalRow.length > 0) {
                        const originalDesc = originalRow.attr('data-product-desc');
                        if (itemDesc !== originalDesc) {
                            // Look up the correct ItemCode for the new description
                            this.lookupItemCodeByDescription(itemDesc, (correctItemCode) => {
                                if (correctItemCode) {
                                    modal.find('.item-code').val(correctItemCode);
                                    // Continue with the update process using the correct ItemCode
                                    this.processItemUpdate(modal, modalId, correctItemCode, itemDesc, itemUOM, itemUOMId, itemUP, itemDP, itemQty, itemAmount, itemCategory, currentEditingItemCode);
                                } else {
                                    // Continue with the update process using the current ItemCode
                                    this.processItemUpdate(modal, modalId, itemCode, itemDesc, itemUOM, itemUOMId, itemUP, itemDP, itemQty, itemAmount, itemCategory, currentEditingItemCode);
                                }
                            });
                            return; // Exit early, the callback will handle the rest
                        }
                    }
                }

                // If we reach here, no description change or not editing, proceed normally
                this.processItemUpdate(modal, modalId, itemCode, itemDesc, itemUOM, itemUOMId, itemUP, itemDP, itemQty, itemAmount, itemCategory, currentEditingItemCode);
            });

            $(document).on('click', '.remove-item', (e) => {
                const modal = $(e.target).closest('.modal');
                const modalId = modal.attr('id');
                $(e.target).closest('tr').remove();
                this.updateOrderAmounts(modalId);
                this.updateItemsData(modalId);
                
                // Verify the update was successful
                const updatedInput = modal.find(`#${modalId}_items_input`);
                if (updatedInput.length > 0) {
                } else {
                    console.error('Failed to update items data after removal');
                }
            });

            // Edit item functionality
            $(document).on('click', '.edit-item-btn', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const button = $(e.target).closest('.edit-item-btn');
                const row = button.closest('tr');
                
                // Find the modal that contains this row
                let modal = row.closest('.modal');
                if (!modal.length) {
                    // Try to find modal by looking for parent with modal class
                    modal = row.parents('.modal');
                }
                
                if (!modal.length) {
                    // Last resort: check which modal is currently visible/open
                    const newOrderModal = $('#newOrderModal');
                    const editOrderModal = $('#editOrderModal');
                    
                    if (newOrderModal.hasClass('show') || newOrderModal.is(':visible')) {
                        modal = newOrderModal;
                    } else if (editOrderModal.hasClass('show') || editOrderModal.is(':visible')) {
                        modal = editOrderModal;
                    } else {
                        // Try to find any visible modal
                        const visibleModal = $('.modal.show, .modal:visible');
                        if (visibleModal.length > 0) {
                            modal = visibleModal.first();
                        }
                    }
                }
                
                const modalId = modal.attr('id');
                const itemCode = row.attr('data-item-code');
                
                if (!modalId) {
                    toastr.error('Could not find modal');
                    return;
                }
                
                if (!itemCode) {
                    toastr.error('Could not find item code');
                    return;
                }
                
                // Extract data from the row
                const rowData = {
                    itemCode: itemCode,
                    productDesc: row.attr('data-product-desc'),
                    categoryId: row.attr('data-category-id'),
                    uomId: row.attr('data-unit-measure-id'),
                    unitPrice: row.find('td:eq(2)').text().trim(),
                    discountedPrice: row.find('td:eq(3)').text().trim(),
                    quantity: row.find('td:eq(4)').text().trim(),
                    amount: row.find('td:eq(5)').text().trim()
                };
                
                this.loadItemIntoForm(modalId, itemCode, rowData);
            });

            // Delivery fee and VAT amount changes are now handled by OrderCalculator
            // Event listeners removed to avoid conflicts

            // Inline edit functionality for product descriptions
            $(document).on('click', '.edit-desc-btn', (e) => {
                const row = $(e.target).closest('tr');
                const descText = row.find('.product-desc-text');
                const descInput = row.find('.product-desc-edit');
                const editBtn = row.find('.edit-desc-btn');
                const saveBtn = row.find('.save-desc-btn');
                const cancelBtn = row.find('.cancel-desc-btn');

                // Store original value for cancel functionality
                row.attr('data-original-desc', descText.text());

                // Show edit mode
                descText.addClass('d-none');
                descInput.removeClass('d-none').focus();
                editBtn.addClass('d-none');
                saveBtn.removeClass('d-none');
                cancelBtn.removeClass('d-none');
            });

            $(document).on('click', '.save-desc-btn', (e) => {
                const row = $(e.target).closest('tr');
                const descText = row.find('.product-desc-text');
                const descInput = row.find('.product-desc-edit');
                const editBtn = row.find('.edit-desc-btn');
                const saveBtn = row.find('.save-desc-btn');
                const cancelBtn = row.find('.cancel-desc-btn');
                const modal = row.closest('.modal');
                const modalId = modal.attr('id');

                const newDesc = descInput.val().trim();
                if (!newDesc) {
                    toastr.error('Product description cannot be empty');
                    return;
                }

                // Update the display and data attributes
                descText.text(newDesc);
                row.attr('data-product-desc', newDesc);

                // Hide edit mode
                descText.removeClass('d-none');
                descInput.addClass('d-none');
                editBtn.removeClass('d-none');
                saveBtn.addClass('d-none');
                cancelBtn.addClass('d-none');

                // Update the items data
                this.updateItemsData(modalId);

                toastr.success('Product description updated successfully');
            });

            $(document).on('click', '.cancel-desc-btn', (e) => {
                const row = $(e.target).closest('tr');
                const descText = row.find('.product-desc-text');
                const descInput = row.find('.product-desc-edit');
                const editBtn = row.find('.edit-desc-btn');
                const saveBtn = row.find('.save-desc-btn');
                const cancelBtn = row.find('.cancel-desc-btn');

                // Restore original value
                const originalDesc = row.attr('data-original-desc');
                descInput.val(originalDesc);

                // Hide edit mode
                descText.removeClass('d-none');
                descInput.addClass('d-none');
                editBtn.removeClass('d-none');
                saveBtn.addClass('d-none');
                cancelBtn.addClass('d-none');
            });

            // Handle Enter key in edit input
            $(document).on('keydown', '.product-desc-edit', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(e.target).closest('tr').find('.save-desc-btn').click();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    $(e.target).closest('tr').find('.cancel-desc-btn').click();
                }
            });

            $(document).on('shown.bs.modal', '#newOrderModal, #editOrderModal', (e) => {
                const modalId = $(e.target).attr('id');
                this.updateOrderAmounts(modalId);
                
                // Only update items data if there are items in the table
                const modal = $(e.target);
                const tableId = `${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderItemsTable`;
                const hasItems = modal.find(`#${tableId} tbody tr`).length > 0;
                
                if (hasItems) {
                    this.updateItemsData(modalId);
                }
                
                // Ensure VAT Amount is always displayed (even if 0.00)
                const vatInput = modal.find(`#${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderVatAmount`);
                if (!vatInput.val() || vatInput.val() === '') {
                    vatInput.val('0.00');
                }
            });

            // Clear editing state when modal is shown to ensure clean state
            $(document).on('show.bs.modal', '#newOrderModal, #editOrderModal', (e) => {
                const modalId = $(e.target).attr('id');
                const modal = $(`#${modalId}`);
                if (modal.attr('data-editing-item-code')) {
                    this.resetItemForm(modalId);
                }
            });

            // Clear editing state when modal is hidden
            $(document).on('hidden.bs.modal', '#newOrderModal, #editOrderModal', (e) => {
                const modalId = $(e.target).attr('id');
                const modal = $(`#${modalId}`);
                if (modal.attr('data-editing-item-code')) {
                    this.resetItemForm(modalId);
                }
            });
        }

        // Add new method to refresh category dropdowns
        refreshCategoryDropdowns() {
            $.ajax({
                url: 'app/apiProjectCategories.php',
                type: 'POST',
                data: { action: 'get_categories' },
                success: response => {
                    if (response.status === 1) {
                        const options = ['<option value="" disabled selected>Select category</option>']
                            .concat(response.data.map(cat => `<option value="${cat.IdCategory}">${cat.CategoryName}</option>`))
                            .join('');
                        
                        // Update category dropdowns in both modals
                        $('#newOrderModal select[name="category[]"]').html(options);
                        $('#editOrderModal select[name="category[]"]').html(options);
                    } else {
                        toastr.error('Error loading categories');
                    }
                },
                error: () => toastr.error('Error loading categories')
            });
        }

        // Process item update (moved from click handler)
        processItemUpdate(modal, modalId, itemCode, itemDesc, itemUOM, itemUOMId, itemUP, itemDP, itemQty, itemAmount, itemCategory, currentEditingItemCode) {
            if (!itemCode) {
                itemCode = this.generateItemCode(); // dapat unique per item
                modal.find('.item-code').val(itemCode);
            }

            const tableId = `${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderItemsTable`;
            
            // Check if we're editing an existing item
            if (currentEditingItemCode) {
                // Try to find the existing item using multiple selectors
                let existingItem = modal.find(`#${tableId} tbody tr[data-item-code="${currentEditingItemCode}"]`);
                
                // If not found with the specific table selector, try a broader search
                if (existingItem.length === 0) {
                    existingItem = modal.find('table tbody tr').filter(function() {
                        return $(this).attr('data-item-code') === currentEditingItemCode;
                    });
                }
                
                if (existingItem.length > 0) {
                    // Update the existing row
                    const unitPrice = parseFloat(itemUP);
                    const discountedPrice = parseFloat(itemDP) || unitPrice;
                    const newAmount = (itemQty * discountedPrice).toFixed(2);

                    // Update data attributes FIRST
                    existingItem.attr('data-item-code', itemCode); // Update the item code!
                    existingItem.attr('data-product-desc', itemDesc);
                    existingItem.attr('data-category-id', itemCategory);
                    existingItem.attr('data-unit-measure-id', itemUOMId);

                    // Update display
                    existingItem.find('.product-desc-text').text(itemDesc);
                    existingItem.find('.product-desc-edit').val(itemDesc);
                    existingItem.find('td:eq(1)').text(itemUOM);
                    existingItem.find('td:eq(2)').text(itemUP);
                    existingItem.find('td:eq(3)').text(itemDP || '-');
                    existingItem.find('td:eq(4)').text(itemQty);
                    existingItem.find('td:eq(5)').text(newAmount);
                    
                    // Update order amounts and items data immediately
                    this.updateOrderAmounts(modalId);
                    this.updateItemsData(modalId);
                        
                    this.resetItemForm(modalId);
                    toastr.success('Order item updated successfully');
                    return;
                } else {
                    // If we can't find the existing item, clear the editing state and add as new
                    modal.removeAttr('data-editing-item-code');
                    currentEditingItemCode = null;
                }
            }

            // Check for duplicate item code (for new items only, exclude the item being edited)
            const duplicateItem = modal.find(`#${tableId} tbody tr`).filter(function () {
                const rowItemCode = $(this).attr('data-item-code');
                return rowItemCode === itemCode && rowItemCode !== currentEditingItemCode;
            });

            if (duplicateItem.length > 0) {
                toastr.error('An item with this code already exists. Please use a different code or edit the existing item.');
                return;
            }

            // Add new item
            const newRow = `
                <tr data-item-code="${itemCode}" data-product-desc="${itemDesc}" data-category-id="${itemCategory}" data-unit-measure-id="${itemUOMId}">
                    <td class="text-start">
                        <span class="product-desc-text">${itemDesc}</span>
                        <input type="text" class="form-control form-control-sm product-desc-edit d-none" value="${itemDesc}">
                    </td>
                    <td class="text-center">${itemUOM}</td>
                    <td class="text-end">${itemUP}</td>
                    <td class="text-end">${itemDP || '-'}</td>
                    <td class="text-center">${itemQty}</td>
                    <td class="text-end">${itemAmount}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-success save-desc-btn d-none me-1" title="Save Description">
                            <i class="bi bi-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary cancel-desc-btn d-none me-1" title="Cancel Edit">
                            <i class="bi bi-x"></i>
                        </button>
                        <button type="button" class="btn bi bi-pencil text-dark edit-item-btn me-1" title="Edit Item"></button>
                        <button type="button" class="btn bi bi-trash text-danger remove-item"></button>
                    </td>
                </tr>
            `;
            modal.find(`#${tableId} tbody`).append(newRow);
            this.updateOrderAmounts(modalId);
            this.updateItemsData(modalId);
            this.resetItemForm(modalId);
            toastr.success('Item added successfully');
        }

        // Look up ItemCode by product description
        lookupItemCodeByDescription(description, callback) {
            $.ajax({
                url: 'app/API/apiOrders.php',
                type: 'POST',
                data: {
                    search_item_suggestions: 1,
                    term: description
                },
                dataType: 'json',
                success: (res) => {
                    if (res.status === 1 && Array.isArray(res.data)) {
                        // Find exact match for the description
                        const exactMatch = res.data.find(item => 
                            item.ProductDesc.toLowerCase() === description.toLowerCase()
                        );
                        
                        if (exactMatch) {
                            callback(exactMatch.ItemCode);
                        } else {
                            callback(null);
                        }
                    } else {
                        callback(null);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error looking up ItemCode:', error);
                    callback(null);
                }
            });
        }

        // Update items data in hidden input
        updateItemsData(modalId) {
            const modal = $(`#${modalId}`);
            const tableId = `${modalId === 'newOrderModal' ? 'new' : 'edit'}OrderItemsTable`;
            
            const items = [];
            modal.find(`#${tableId} tbody tr`).each(function (index) {
                const row = $(this);
                const itemCode = row.attr('data-item-code');
                const productDesc = row.attr('data-product-desc');
                
                // Only add items that have valid data
                if (itemCode && productDesc) {
                    const unitPrice = parseFloat(row.find('td:eq(2)').text()) || 0;
                    const discountedPrice = row.find('td:eq(3)').text() === '-' ? unitPrice : parseFloat(row.find('td:eq(3)').text()) || unitPrice;
                    const quantity = parseFloat(row.find('td:eq(4)').text()) || 0;
                    const totalAmount = parseFloat(row.find('td:eq(5)').text()) || 0;
                    
                    items.push({
                        ItemCode: itemCode,
                        ProductDesc: productDesc,
                        CategoryId: row.attr('data-category-id') || '1',
                        UnitMeasureId: row.attr('data-unit-measure-id') || '1',
                        UnitPrice: unitPrice,
                        DiscountedPrice: discountedPrice,
                        Quantity: quantity,
                        TotalAmount: totalAmount
                    });
                }
            });

            // Remove ALL existing hidden inputs with name="items" first
            modal.find('input[name="items"]').remove();
            
            // Create new hidden input with the correct ID format that orderFormHandler.js expects
            const hiddenInputId = `${modalId}_items_input`;
            const itemsJson = JSON.stringify(items);
            modal.append(`<input type="hidden" id="${hiddenInputId}" name="items" value='${itemsJson}'>`);
        }
    }

    window.ProductSuggestionHandler = ProductSuggestionHandler;

    document.addEventListener('DOMContentLoaded', function () {
        const newOrderProductHandler = new ProductSuggestionHandler(
            '#newOrderModal',
            '.item-desc',
            'newSuggestProductDesc'
        );

        const editOrderProductHandler = new ProductSuggestionHandler(
            '#editOrderModal',
            '.item-desc',
            'editSuggestProductDesc'
        );
        
        // Make handlers globally available for debugging
        window.newOrderProductHandler = newOrderProductHandler;
        window.editOrderProductHandler = editOrderProductHandler;
    });
})();