$(document).ready(function () {
    // Add validation for discounted price in new order modal
    $(document).on('input', '.item-dp', function () {
        const unitPrice = parseFloat($(this).closest('.row').find('.item-up').val()) || 0;
        const discountedPrice = parseFloat($(this).val()) || 0;
        const errorElement = $(this).siblings('.discount-error');

        if (discountedPrice > unitPrice) {
            errorElement.show();
            $(this).addClass('is-invalid');
            return false;
        } else {
            errorElement.hide();
            $(this).removeClass('is-invalid');
        }
    });

    // Add validation for discounted price in edit order modal
    $(document).on('input', '#editOrderModal .item-dp', function () {
        const unitPrice = parseFloat($(this).closest('.row').find('.item-up').val()) || 0;
        const discountedPrice = parseFloat($(this).val()) || 0;
        const errorElement = $(this).siblings('.discount-error');

        if (discountedPrice > unitPrice) {
            errorElement.show();
            $(this).addClass('is-invalid');
            return false;
        } else {
            errorElement.hide();
            $(this).removeClass('is-invalid');
        }
    });

    // --- VALIDATION: Due Amount ---
    function validateDueAmount(modalPrefix) {
        const vatableSales = parseFloat($(`#${modalPrefix}OrderVatableSales`).val()) || 0;
        const zeroRatedSales = parseFloat($(`#${modalPrefix}OrderZeroRatedSales`).val()) || 0;
        const vatAmount = parseFloat($(`#${modalPrefix}OrderVatAmount`).val()) || 0;
        const deliveryFee = parseFloat($(`#${modalPrefix}OrderDeliveryFee`).val()) || 0;
        const ewtAmount = parseFloat($(`#${modalPrefix}OrderEwtAmount`).val()) || 0;
        const dueAmount = parseFloat($(`#${modalPrefix}OrderDueAmount`).val()) || 0;
        const computedDue = vatableSales + zeroRatedSales + vatAmount + deliveryFee - ewtAmount;
        const isValid = Math.abs(dueAmount - computedDue) < 0.01;
        if (!isValid) {
            $(`#${modalPrefix}OrderDueAmount`).addClass('is-invalid');
            $(`#${modalPrefix}OrderDueAmount`).siblings('.invalid-feedback').remove();
            $(`#${modalPrefix}OrderDueAmount`).after('<div class="invalid-feedback">Due Amount does not match the sum of Vatable Sales, Zero-Rated Sales, VAT Amount, Delivery Fee minus EWT Amount.</div>');
        } else {
            $(`#${modalPrefix}OrderDueAmount`).removeClass('is-invalid');
            $(`#${modalPrefix}OrderDueAmount`).siblings('.invalid-feedback').remove();
        }
        return isValid;
    }

    // --- LIVE COMPUTATION: Due Amount ---
    function computeAndSetDueAmount(modalPrefix) {
        const vatableSales = parseFloat($(`#${modalPrefix}OrderVatableSales`).val()) || 0;
        const zeroRatedSales = parseFloat($(`#${modalPrefix}OrderZeroRatedSales`).val()) || 0;
        const vatAmount = parseFloat($(`#${modalPrefix}OrderVatAmount`).val()) || 0;
        const deliveryFee = parseFloat($(`#${modalPrefix}OrderDeliveryFee`).val()) || 0;
        const ewtAmount = parseFloat($(`#${modalPrefix}OrderEwtAmount`).val()) || 0;
        const computedDue = vatableSales + zeroRatedSales + vatAmount + deliveryFee - ewtAmount;
        $(`#${modalPrefix}OrderDueAmount`).val(computedDue.toFixed(2));
        validateDueAmount(modalPrefix);
    }

    // Attach live computation to relevant fields in new order modal
    $('#newOrderVatableSales, #newOrderZeroRatedSales, #newOrderVatAmount, #newOrderDeliveryFee').on('input', function() {
        computeAndSetDueAmount('new');
    });

    // Attach live computation to relevant fields in edit order modal (if present)
    $('#editOrderVatableSales, #editOrderZeroRatedSales, #editOrderVatAmount, #editOrderDeliveryFee').on('input', function() {
        computeAndSetDueAmount('edit');
    });

    // Optionally, run on modal show to initialize
    $('#newOrderModal').on('shown.bs.modal', function() {
        computeAndSetDueAmount('new');
    });
    $('#editOrderModal').on('shown.bs.modal', function() {
        computeAndSetDueAmount('edit');
    });

    // --- LIVE COMPUTATION: Vatable Sales ---
    function computeAndSetVatableSales(modalPrefix) {
        const totalAmount = parseFloat($(`#${modalPrefix}OrderTotalAmount`).val()) || 0;
        const computedVatable = totalAmount / 1.12;
        $(`#${modalPrefix}OrderVatableSales`).val(computedVatable > 0 ? computedVatable.toFixed(2) : '0.00');
    }

    // Attach live computation to Total Amount changes
    $('#newOrderTotalAmount').on('input', function() {
        computeAndSetVatableSales('new');
        computeAndSetDueAmount('new');
    });
    $('#editOrderTotalAmount').on('input', function() {
        computeAndSetVatableSales('edit');
        computeAndSetDueAmount('edit');
    });
    // Also run on modal show
    $('#newOrderModal').on('shown.bs.modal', function() {
        computeAndSetVatableSales('new');
        computeAndSetDueAmount('new');
    });
    $('#editOrderModal').on('shown.bs.modal', function() {
        computeAndSetVatableSales('edit');
        computeAndSetDueAmount('edit');
    });

    // Make Vatable Sales field readonly in both modals
    $('#newOrderVatableSales, #editOrderVatableSales').prop('readonly', true);

    // --- REMOVE: EWT Amount auto-compute and readonly ---
    // Remove the code that sets EWT Amount to readonly
    // $('#newOrderEwtAmount, #editOrderEwtAmount').prop('readonly', true);

    // Remove the code that auto-computes EWT Amount from Vatable Sales
    // function computeAndSetEwtAmount(modalPrefix) { ... }
    // $('#newOrderVatableSales').on('input', ...)
    // $('#editOrderVatableSales').on('input', ...)
    // $('#newOrderModal').on('shown.bs.modal', ...)
    // $('#editOrderModal').on('shown.bs.modal', ...)

    // Instead, just update Due Amount and validations when EWT Amount changes
    $('#newOrderEwtAmount').on('input', function() {
        computeAndSetDueAmount('new');
        validateOrderFields('new');
    });
    $('#editOrderEwtAmount').on('input', function() {
        computeAndSetDueAmount('edit');
        validateOrderFields('edit');
    });

    // --- LIVE DISPLAY: Vatable Sales Formula and Result ---
    function updateVatableSalesFormulaDisplay(modalPrefix) {
        const totalAmount = parseFloat($(`#${modalPrefix}OrderTotalAmount`).val()) || 0;
        const vatableSales = parseFloat($(`#${modalPrefix}OrderVatableSales`).val()) || 0;
        const formulaText = `Vatable Sales = ${totalAmount.toFixed(2)} / 1.12 = <b>${vatableSales.toFixed(2)}</b>`;
        if (modalPrefix === 'new') {
            $('#vatableSalesFormula').html(formulaText);
        } else if (modalPrefix === 'edit') {
            $('#editVatableSalesFormula').html(formulaText);
        }
    }

    // Update formula display whenever Vatable Sales or Total Amount changes
    $('#newOrderTotalAmount').on('input', function() {
        updateVatableSalesFormulaDisplay('new');
    });
    $('#newOrderVatableSales').on('input', function() {
        updateVatableSalesFormulaDisplay('new');
    });
    $('#newOrderModal').on('shown.bs.modal', function() {
        updateVatableSalesFormulaDisplay('new');
    });
    // (If you want for edit modal, add similar code for editOrderTotalAmount and editOrderVatableSales)

    // --- PATCH: Force update of computed fields after Total Amount changes programmatically ---
    function forceAllOrderComputations(modalPrefix) {
        computeAndSetVatableSales(modalPrefix);
        computeAndSetDueAmount(modalPrefix);
        updateVatableSalesFormulaDisplay(modalPrefix);
    }

    // Patch: If you have a function that updates totals, call this after setting Total Amount
    // Example for new order modal:
    window.updateOrderTotals = function() {
        // ... your code to compute totalAmount ...
        $('#newOrderTotalAmount').val(totalAmount.toFixed(2));
        forceAllOrderComputations('new');
    };
    // If you have a similar function for edit modal, do the same with 'edit'.

    // Handle new order form submission
    $('#newOrderForm').on('submit', function (e) {
        e.preventDefault();
        
        // Ensure items data is up to date before submission
        if (window.newOrderProductHandler) {
            window.newOrderProductHandler.updateItemsData('newOrderModal');
        }

        // Get customer data
        const customerData = {
            CustomerType: $('#newOrderCustomerType').val(),
            CustomerName: $('#newOrderCustomerType').val() == '0' ? $('#newOrderBusinessName').val() : '',
            BusinessName: $('#newOrderCustomerType').val() == '1' ? $('#newOrderBusinessName').val() : '',
            CustomerTIN: $('#newOrderCustomerTIN').val(),
            CustomerAddress: $('#newOrderCustomerAddress').val(),
            ContactPerson: $('#newOrderContactPerson').val(),
            PersonDesignation: $('#newOrderPersonDesignation').val(),
            ContactNum: $('#newOrderContactNum').val(),
            ContactEmail: $('#newOrderContactEmail').val(),
            CustomerStatus: 1
        };

        // Get order items from hidden input (updated by productSuggestions.js)
        const itemsInput = $('#newOrderModal_items_input');
        let items = [];
        
        if (itemsInput.length > 0 && itemsInput.val()) {
            try {
                items = JSON.parse(itemsInput.val());
            } catch (e) {
                console.error("Error parsing items:", e);
                toastr.error("Error processing order items");
                return;
            }
        } else {
            // Fallback: collect items from table if hidden input is not available
            $('#newOrderItemsTable tbody tr').each(function () {
                const row = $(this);
                const itemCode = row.attr('data-item-code');
                const productDesc = row.attr('data-product-desc');

                if (!itemCode) {
                    toastr.error('Invalid item code found. Please remove and re-add the item.');
                    return false; // Break the loop
                }

                items.push({
                    ItemCode: itemCode,
                    ProductDesc: productDesc,
                    UnitPrice: parseFloat(row.find('td:eq(2)').text()),
                    DiscountedPrice: row.find('td:eq(3)').text() === '-' ? 0 : parseFloat(row.find('td:eq(3)').text()),
                    Quantity: parseFloat(row.find('td:eq(4)').text()),
                    TotalAmount: parseFloat(row.find('td:eq(5)').text())
                });
            });
        }

        // Validate required fields
        if (!customerData.CustomerTIN || !customerData.CustomerAddress || !customerData.ContactNum || !customerData.ContactEmail) {
            toastr.error('Please fill in all required customer fields');
            return;
        }

        if (customerData.CustomerType == '1' && (!customerData.ContactPerson || !customerData.PersonDesignation)) {
            toastr.error('Please fill in all required business fields');
            return;
        }

        // Validate items array
        if (items.length === 0) {
            toastr.error('Please add at least one item to the order');
            return;
        }
        
        const totalAmount = parseFloat($('#newOrderTotalAmount').val()) || 0;
        const vatAmount = parseFloat($('#newOrderVatAmount').val()) || 0;
        const deliveryFee = parseFloat($('#newOrderDeliveryFee').val()) || 0;

        if (!validateDeliveryFee(deliveryFee)) {
            toastr.error('Please enter a valid delivery fee');
            return;
        }

        const dueAmount = calculateDueAmount(totalAmount, vatAmount, deliveryFee);

        // Prepare the data for submission
        const postData = {
            create_order: 1,
            customer_data: JSON.stringify(customerData),
            items: JSON.stringify(items),
            DeliveryInstruction: $('#newDeliveryInstruction').val(),
            TotalItems: items.length,
            TotalAmount: totalAmount.toFixed(2),
            VatableSales: (totalAmount / 1.12).toFixed(2),
            ZeroRatedSales: $('#newOrderZeroRatedSales').val() || '0.00',
            VATAmount: vatAmount.toFixed(2),
            EWTAmount: $('#newOrderEwtAmount').val() || '0.00',
            DeliveryFee: deliveryFee.toFixed(2),
            DueAmount: dueAmount,
            OrderStatusId: 1,
            PaymentStatus: 0,
            ApprovalStatus: 0,
            OrderAuthor: $('#newOrderForm input[name="OrderAuthor"]').val()
        };

        // Submit the form
        $.ajax({
            url: 'app/API/apiOrders.php',
            type: 'POST',
            data: postData,
            success: function (response) {
                if (response.status === 1) {
                    toastr.success('Order created successfully');
                    $('#newOrderModal').modal('hide');
                    $('#newOrderForm')[0].reset();
                    $('#newOrderItemsTable tbody').empty();
                    if (typeof orderDataTable !== 'undefined') {
                        orderDataTable.ajax.reload();
                    }
                } else {
                    toastr.error(response.message || 'Error creating order');
                }
            },
            error: function (xhr, status, error) {
                toastr.error('Error creating order: ' + error);
            }
        });
    });

    // --- ADVANCED VALIDATION BASED ON IMAGE RULES ---
    function validateOrderFields(modalPrefix) {
        let valid = true;
        const totalAmount = parseFloat($(`#${modalPrefix}OrderTotalAmount`).val()) || 0;
        const vatableSales = parseFloat($(`#${modalPrefix}OrderVatableSales`).val()) || 0;
        const zeroRatedSales = parseFloat($(`#${modalPrefix}OrderZeroRatedSales`).val()) || 0;
        const vatAmount = parseFloat($(`#${modalPrefix}OrderVatAmount`).val()) || 0;
        const ewtAmount = parseFloat($(`#${modalPrefix}OrderEwtAmount`).val()) || 0;
        const deliveryFee = parseFloat($(`#${modalPrefix}OrderDeliveryFee`).val()) || 0;
        const dueAmount = parseFloat($(`#${modalPrefix}OrderDueAmount`).val()) || 0;

        // 1. VATABLE SALES = TOTAL AMOUNT / 1.12 (or not more than computed 1.12)
        const computedVatable = totalAmount / 1.12;
        if (vatableSales > computedVatable + 0.01) {
            toastr.warning('Vatable Sales should not be more than Total Amount / 1.12 (' + computedVatable.toFixed(2) + ')');
            valid = false;
        }

        // 2. ZERO-RATED SALES = manual input, but not more than TOTAL AMOUNT
        if (zeroRatedSales > totalAmount + 0.01) {
            toastr.error('Zero-Rated Sales should not be more than Total Amount.');
            valid = false;
        }

        // 3. VAT AMOUNT = 12% of VATABLE SALES
        const computedVAT = vatableSales * 0.12;
        if (vatableSales > 0 && vatAmount > computedVAT + 0.01) {
            toastr.warning('VAT Amount should not be more than 12% of Vatable Sales (' + computedVAT.toFixed(2) + ')');
            valid = false;
        }

        // 4. EWT AMOUNT = 1% or 2% of VATABLE SALES
        const ewt1 = vatableSales * 0.01;
        const ewt2 = vatableSales * 0.02;
        if (ewtAmount > 0 && Math.abs(ewtAmount - ewt1) > 0.01 && Math.abs(ewtAmount - ewt2) > 0.01) {
            toastr.warning('EWT Amount should be 1% (' + ewt1.toFixed(2) + ') or 2% (' + ewt2.toFixed(2) + ') of Vatable Sales.');
            valid = false;
        }

        // 5. DUE AMOUNT = VATABLE SALES + ZERO-RATED SALES + VAT AMOUNT + DELIVERY FEE - EWT AMOUNT
        const computedDue = vatableSales + zeroRatedSales + vatAmount + deliveryFee - ewtAmount;
        if (Math.abs(dueAmount - computedDue) > 0.01) {
            toastr.error('Due Amount should be equal to Vatable Sales + Zero-Rated Sales + VAT Amount + Delivery Fee - EWT Amount (' + computedDue.toFixed(2) + ')');
            valid = false;
        }
        return valid;
    }

    // Attach advanced validation to new order form
    $('#newOrderForm').on('submit', function (e) {
        if (!validateOrderFields('new')) {
            e.preventDefault();
            return false;
        }
    });
    // Attach advanced validation to edit order form
    $('#editOrderForm').on('submit', function (e) {
        if (!validateOrderFields('edit')) {
            e.preventDefault();
            return false;
        }
    });
    // Live validation on input
    $('#newOrderVatableSales, #newOrderZeroRatedSales, #newOrderVatAmount, #newOrderEwtAmount, #newOrderDeliveryFee, #newOrderDueAmount').on('input', function() {
        validateOrderFields('new');
    });
    $('#editOrderVatableSales, #editOrderZeroRatedSales, #editOrderVatAmount, #editOrderEwtAmount, #editOrderDeliveryFee, #editOrderDueAmount').on('input', function() {
        validateOrderFields('edit');
    });

    // Handle TIN input formatting
    $('#newOrderCustomerTIN').on('input', function () {
        const tin = $(this).val();
        $(this).val(tin);
    });

    // Handle contact number input formatting
    $('#newOrderContactNum').on('input', function () {
        const number = $(this).val();
        $(this).val(number);
    });

    // Handle order form submission
    $('#orderForm').on('submit', function (e) {
        e.preventDefault();

        // Collect order items from the table
        const items = [];
        $('#orderItemsTable tbody tr').each(function() {
            const row = $(this);
            items.push({
                ItemCode: row.attr('data-item-code'),
                ProductDesc: row.attr('data-product-desc'),
                UnitPrice: row.find('.item-up').val(),
                DiscountedPrice: row.find('.item-dp').val(),
                Quantity: row.find('.item-qty').val(),
                TotalAmount: row.find('.item-amount').val()
            });
        });

        // Create FormData object
        const formData = new FormData(this);
        formData.append('items', JSON.stringify(items));
        formData.append('TotalItems', items.length);
        formData.append('CustomerStatus', $('#customerStatus').val());

        // Handle payment proof files
        const paymentProofFiles = $('#paymentProof')[0].files;
        if (paymentProofFiles.length > 0) {
            for (let i = 0; i < paymentProofFiles.length; i++) {
                formData.append('payment_proofs[]', paymentProofFiles[i]);
            }
        }

        // Submit the form
        $.ajax({
            url: 'app/API/apiOrders.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    }).then(() => {
                        $('#orderForm')[0].reset();
                        $('#orderItemsTable tbody').empty();
                        updateTotals();
                        if (typeof orderTable !== 'undefined') {
                            orderTable.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while creating the order.'
                });
            }
        });
    });
});