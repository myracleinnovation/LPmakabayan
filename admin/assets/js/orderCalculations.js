(function() {
    'use strict';

    class OrderCalculator {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Listen for item additions/updates in both modals
            $(document).on('click', '#newAddOrderDetailToList, #editAddOrderDetailToList', () => {
                setTimeout(() => {
                    this.recalculateAllAmounts();
                }, 100);
            });

            // Listen for item removals
            $(document).on('click', '.remove-item', () => {
                setTimeout(() => {
                    this.recalculateAllAmounts();
                }, 100);
            });

            // Listen for quantity and discounted price changes
            $(document).on('input', '.item-qty, .item-dp', () => {
                setTimeout(() => {
                    this.recalculateAllAmounts();
                }, 100);
            });

            // Listen for delivery fee changes
            $(document).on('input', '#newOrderDeliveryFee, #editOrderDeliveryFee', (e) => {
                this.recalculateDueAmountOnly(e.target);
            });

            // Listen for VAT amount changes
            $(document).on('input', '#newOrderVatAmount, #editOrderVatAmount', (e) => {
                this.handleVatAmountChange(e.target);
            });

            // Listen for modal shown events to initialize calculations
            $(document).on('shown.bs.modal', '#newOrderModal', () => {
                setTimeout(() => {
                    this.recalculateAllAmounts();
                }, 200);
            });

            // For edit modal, don't auto-recalculate VAT - preserve existing values
            $(document).on('shown.bs.modal', '#editOrderModal', () => {
                setTimeout(() => {
                    this.recalculateDueAmountOnly();
                }, 200);
            });
        }

        /**
         * Recalculate all amounts (Total Amount, Vatable Sales, VAT Amount, Due Amount)
         * This is called when items are added, updated, or removed
         */
        recalculateAllAmounts() {
            const activeModal = this.getActiveModal();
            if (!activeModal) {
                return;
            }

            const modalId = activeModal.attr('id');
            const prefix = modalId === 'newOrderModal' ? 'new' : 'edit';

            // Calculate total amount from items table
            const totalAmount = this.calculateTotalAmountFromTable(modalId);
            
            // Calculate Vatable Sales (Total Amount / 1.12)
            const vatableSales = totalAmount > 0 ? Math.round((totalAmount / 1.12) * 100) / 100 : 0;
            
            // For new orders: auto-calculate VAT as 12% of total amount
            // For edit orders: preserve existing VAT amount
            let vatAmount;
            if (modalId === 'newOrderModal') {
                vatAmount = this.calculateVatAmount(totalAmount);
            } else {
                vatAmount = parseFloat(activeModal.find(`#${prefix}OrderVatAmount`).val()) || 0;
            }
            
            // Get current delivery fee
            const deliveryFee = parseFloat(activeModal.find(`#${prefix}OrderDeliveryFee`).val()) || 0;
            
            // Calculate due amount
            const dueAmount = this.calculateDueAmount(totalAmount, vatAmount, deliveryFee);

            // Update form fields
            activeModal.find(`#${prefix}OrderTotalAmount`).val(totalAmount.toFixed(2));
            activeModal.find(`#${prefix}OrderVatableSales`).val(vatableSales.toFixed(2));
            
            // Only update VAT amount for new orders
            if (modalId === 'newOrderModal') {
                activeModal.find(`#${prefix}OrderVatAmount`).val(vatAmount.toFixed(2));
            }
            
            activeModal.find(`#${prefix}OrderDueAmount`).val(dueAmount.toFixed(2));

            // Clear any validation errors
            activeModal.find(`#${prefix}OrderVatAmount`).removeClass('is-invalid');
            activeModal.find(`#${prefix}OrderVatAmount`).siblings('.invalid-feedback').hide();

        }

        /**
         * Recalculate only the due amount (when VAT or delivery fee changes manually)
         */
        recalculateDueAmountOnly(vatInput = null) {
            const activeModal = this.getActiveModal();
            if (!activeModal) return;

            const modalId = activeModal.attr('id');
            const prefix = modalId === 'newOrderModal' ? 'new' : 'edit';

            // If vatInput is provided, use that modal, otherwise use active modal
            const modal = vatInput ? $(vatInput).closest('.modal') : activeModal;

            const totalAmount = parseFloat(modal.find(`#${prefix}OrderTotalAmount`).val()) || 0;
            const vatAmount = parseFloat(modal.find(`#${prefix}OrderVatAmount`).val()) || 0;
            const deliveryFee = parseFloat(modal.find(`#${prefix}OrderDeliveryFee`).val()) || 0;

            const dueAmount = this.calculateDueAmount(totalAmount, vatAmount, deliveryFee);
            modal.find(`#${prefix}OrderDueAmount`).val(dueAmount.toFixed(2));

        }

        /**
         * Handle VAT amount manual changes
         */
        handleVatAmountChange(vatInput) {
            const modal = $(vatInput).closest('.modal');
            const modalId = modal.attr('id');
            const prefix = modalId === 'newOrderModal' ? 'new' : 'edit';

            const totalAmount = parseFloat(modal.find(`#${prefix}OrderTotalAmount`).val()) || 0;
            const vatAmount = parseFloat(vatInput.value) || 0;

            // Ensure VAT amount is never negative
            const validVatAmount = Math.max(0, vatAmount);
            if (vatAmount !== validVatAmount) {
                vatInput.value = validVatAmount.toFixed(2);
            }

            // Calculate maximum allowed VAT (12% of total amount)
            const maxVAT = Math.round((totalAmount * 0.12) * 100) / 100;

            // Block submission if VAT exceeds 12%
            if (validVatAmount > maxVAT) {
                $(vatInput).addClass('is-invalid');
                $(vatInput).siblings('.invalid-feedback').show();
                
                // Disable submit buttons
                const submitBtn = modalId === 'newOrderModal' ? '#saveNewOrder' : '#updateOrderBtn';
                modal.find(submitBtn).prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
            } else {
                $(vatInput).removeClass('is-invalid');
                $(vatInput).siblings('.invalid-feedback').hide();
                
                // Enable submit buttons
                const submitBtn = modalId === 'newOrderModal' ? '#saveNewOrder' : '#updateOrderBtn';
                modal.find(submitBtn).prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
            }

            // Recalculate due amount
            this.recalculateDueAmountOnly(vatInput);
        }

        /**
         * Calculate total amount from items table
         */
        calculateTotalAmountFromTable(modalId) {
            const prefix = modalId === 'newOrderModal' ? 'new' : 'edit';
            const tableSelector = `#${prefix}OrderItemsTable`;
            let totalAmount = 0;

            const table = $(tableSelector);
            
            if (table.length > 0) {
                const rows = table.find('tbody tr');
                
                rows.each(function(index) {
                    const amountText = $(this).find('td:eq(5)').text() || '0';
                    const amount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                    totalAmount += amount;
                });
            }

            return totalAmount;
        }

        /**
         * Calculate VAT amount as 12% of total amount
         */
        calculateVatAmount(totalAmount) {
            return totalAmount > 0 ? Math.round((totalAmount * 0.12) * 100) / 100 : 0;
        }

        /**
         * Calculate due amount
         */
        calculateDueAmount(totalAmount, vatAmount, deliveryFee) {
            return parseFloat(totalAmount) + parseFloat(vatAmount) + parseFloat(deliveryFee);
        }

        /**
         * Get the currently active modal
         */
        getActiveModal() {
            const newOrderModal = $('#newOrderModal');
            const editOrderModal = $('#editOrderModal');
            
            if (newOrderModal.hasClass('show') || newOrderModal.is(':visible')) {
                return newOrderModal;
            } else if (editOrderModal.hasClass('show') || editOrderModal.is(':visible')) {
                return editOrderModal;
            }
            return null;
        }

        /**
         * Reset all amount fields to 0
         */
        resetAmounts(modalId) {
            const prefix = modalId === 'newOrderModal' ? 'new' : 'edit';
            const modal = $(`#${modalId}`);

            modal.find(`#${prefix}OrderTotalAmount`).val('0.00');
            modal.find(`#${prefix}OrderVatableSales`).val('0.00');
            modal.find(`#${prefix}OrderVatAmount`).val('0.00');
            modal.find(`#${prefix}OrderDeliveryFee`).val('0.00');
            modal.find(`#${prefix}OrderDueAmount`).val('0.00');

            // Clear validation errors
            modal.find(`#${prefix}OrderVatAmount`).removeClass('is-invalid');
            modal.find(`#${prefix}OrderVatAmount`).siblings('.invalid-feedback').hide();
        }

        /**
         * Validate VAT amount against 12% rule
         */
        validateVatAmount(vatAmount, totalAmount) {
            const maxVAT = Math.round((totalAmount * 0.12) * 100) / 100;
            const isValid = vatAmount <= maxVAT;
            
            // Get active modal and disable/enable submit button
            const activeModal = this.getActiveModal();
            if (activeModal) {
                const modalId = activeModal.attr('id');
                const submitBtn = modalId === 'newOrderModal' ? '#saveNewOrder' : '#updateOrderBtn';
                
                if (!isValid) {
                    // Disable submit button
                    activeModal.find(submitBtn).prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
                } else {
                    // Enable submit button
                    activeModal.find(submitBtn).prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
                }
            }
            
            return {
                isValid: isValid,
                maxVAT: maxVAT,
                isExceeded: vatAmount > maxVAT
            };
        }
    }

    // Initialize the calculator when document is ready
    $(document).ready(function() {
        window.orderCalculator = new OrderCalculator();
    });

    // Export functions for global use
    window.OrderCalculator = OrderCalculator;

})(); 