// Prevent any alerts for VAT validation - use toastr only
const originalAlert = window.alert;
window.alert = function(message) {
    // If the message contains VAT validation content, use toastr instead
    if (typeof message === 'string' && (message.includes('VAT') || message.includes('12%') || message.includes('exceed'))) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        }
        return;
    }
    // For all other alerts, use the original alert function
    return originalAlert.apply(this, arguments);
};

// Define displayExistingProofs in global scope
function displayExistingProofs(proofs) {
  const container = $("#editProofsOfPaymentContainer");
  container.empty();

  if (Array.isArray(proofs) && proofs.length > 0) {
    container.show();
    proofs.forEach((proof) => {
      const imgPath = "app/uploads/" + proof.filename;
      const imgElement = $("<img>")
        .attr("src", imgPath)
        .addClass("img-thumbnail")
        .css({
          width: "100px",
          height: "100px",
          objectFit: "cover",
          margin: "5px",
        });

      const viewButton = $("<button>")
        .addClass("btn bi bi-eye-fill position-absolute")
        .css({
          top: "5px",
          right: "5px",
          padding: "2px 5px",
          fontSize: "16px",
          color: "#121212",
          
        })
        .on("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          previewImage(imgPath);
        });

      const imgWrapper = $("<div>")
        .addClass("position-relative d-inline-block")
        .css({
          margin: "5px",
          pointerEvents: "auto",
        })
        .append(imgElement)
        .append(viewButton);

      container.append(imgWrapper);
    });
  } else {
    container.hide();
  }
}

// Add permission check functions
function hasUpdatePermission() {
  return window.userPermissions && window.userPermissions.includes('9D');
}

function hasAmountPermission() {
  return window.userPermissions && window.userPermissions.includes('9E');
}

function hasWithoutAmountPermission() {
  return window.userPermissions && window.userPermissions.includes('9F');
}

function hasSearchPermission() {
  return window.userPermissions && (window.userPermissions.includes('9D') || window.userPermissions.includes('9E') || window.userPermissions.includes('9F'));
}

function hasReportsPermission() {
  return window.userPermissions && window.userPermissions.includes('9B');
}

function hasHideCustomerDetailsPermission() {
  return window.userPermissions && window.userPermissions.includes('9G');
}

let orderDataTable = new DataTable(".order_table", {
  columnDefs: [{ orderable: false, targets: [-1] }],
  order: [[0, "desc"]],
  dom:
    "<'row'<'col-12 mb-3'tr>>" +
    "<'row'<'col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2'ip>>",
  processing: true,
  ajax: {
    url: "app/API/apiOrders.php?get_orders",
    type: "GET",
    data: function (d) {
      if (hasSearchPermission()) {
        d.searchOrder = $("#customerCustomSearch").val();
        d.orderStatus = $("#selectOrderStatusFilter").val();
        d.paymentStatus = $("#selectPaymentStatusFilter").val();
        d.approvalStatus = $("#selectApprovalStatusFilter").val();
      }
    },
    dataSrc: function (json) {
      return json.data || [];
    },
  },
  columns: [
    {
      data: "OrderNum",
      render: function (data) {
        return `<div class="text-start">${data}</div>`;
      },
    },
    {
      data: null,
      render: function (data) {
        let nameDisplay = "";
        if (data.CustomerName) {
          nameDisplay += data.CustomerName;
        }
        if (data.BusinessName) {
          if (nameDisplay) nameDisplay += " / ";
          nameDisplay += data.BusinessName;
        }
        
        // If user has hide customer details permission, only show name
        if (hasHideCustomerDetailsPermission()) {
          return `<div class="text-start">${nameDisplay || "N/A"}</div>`;
        }
        
        // Otherwise show full details
        let details = `<div class="text-start">`;
        details += `<div>${nameDisplay || "N/A"}</div>`;
        if (data.CustomerAddress) details += `<div class="small text-muted">${data.CustomerAddress}</div>`;
        if (data.ContactNum) details += `<div class="small text-muted">${data.ContactNum}</div>`;
        if (data.ContactEmail) details += `<div class="small text-muted">${data.ContactEmail}</div>`;
        details += `</div>`;
        return details;
      },
    },
    {
      data: "TotalItems",
      render: function (data) {
        return `<div class="text-start">${data}</div>`;
      },
    },
    {
      data: "TotalAmount",
      visible: hasAmountPermission(),
      render: function (data) {
        return `<div class="text-end">${parseFloat(data).toFixed(2)}</div>`;
      },
    },
    {
      data: "StatusName",
      render: function (data, type, row) {
        // Get status name from the server response
        const statusName = data || 'Unknown';
        let badgeClass = 'bg-secondary';
        
        // Determine badge class based on status name
        if (statusName.toLowerCase().includes('waiting')) {
          badgeClass = 'bg-warning';
        } else if (statusName.toLowerCase().includes('completed')) {
          badgeClass = 'bg-success';
        } else if (statusName.toLowerCase().includes('cancelled')) {
          badgeClass = 'bg-danger';
        } else if (statusName.toLowerCase().includes('graphics')) {
          badgeClass = 'bg-info';
        } else if (statusName.toLowerCase().includes('cutting')) {
          badgeClass = 'bg-primary';
        } else if (statusName.toLowerCase().includes('sewing')) {
          badgeClass = 'bg-secondary';
        } else if (statusName.toLowerCase().includes('printing')) {
          badgeClass = 'bg-info';
        } else if (statusName.toLowerCase().includes('quality control')) {
          badgeClass = 'bg-warning';
        } else if (statusName.toLowerCase().includes('packaging')) {
          badgeClass = 'bg-success';
        } else if (statusName.toLowerCase().includes('final checking')) {
          badgeClass = 'bg-danger';
        } else if (statusName.toLowerCase().includes('on-process')) {
          badgeClass = 'bg-primary';
        }
        
        return `<div class="text-start"><span class="badge ${badgeClass}">${statusName}</span></div>`;
      },
    },
    {
      data: "PaymentStatus",
      render: function (data) {
        const statusMap = {
          0: '<span class="badge bg-danger">Unpaid</span>',
          1: '<span class="badge bg-success">Paid</span>',
        };
        return `<div class="text-start">${statusMap[data] || "N/A"}</div>`;
      },
    },
    {
      data: "ApprovalStatus",
      render: function (data) {
        const statusMap = {
          0: '<span class="badge bg-warning">Pending</span>',
          1: '<span class="badge bg-success">Approved</span>',
        };
        return `<div class="text-start">${statusMap[data] || "N/A"}</div>`;
      },
    },
    {
      data: "OrderDate",
      render: function (data) {
        if (!data) return '<div class="text-start">N/A</div>';
        try {
          return `<div class="text-start">${moment(data).format(
            "YYYY-MM-DD HH:mm:ss"
          )}</div>`;
        } catch (e) {
          const date = new Date(data);
          return `<div class="text-start">${date
            .toISOString()
            .slice(0, 19)
            .replace("T", " ")}</div>`;
        }
      },
    },
    {
      data: "DateCompleted",
      render: function (data) {
        if (!data) return '<div class="text-start">N/A</div>';
        try {
          return `<div class="text-start">${moment(data).format(
            "YYYY-MM-DD HH:mm:ss"
          )}</div>`;
        } catch (e) {
          const date = new Date(data);
          return `<div class="text-start">${date
            .toISOString()
            .slice(0, 19)
            .replace("T", " ")}</div>`;
        }
      },
    },
    {
      data: "OrderAuthor",
      render: function (data) {
        return `<div class="text-start">${data}</div>`;
      },
    },
    {
      data: null,
      render: function (data, type, row) {
        let buttons = '<div class="text-start">';
        buttons += '<i class="bi bi-eye view_order" data-order-id="' + row.idOrder + '" title="View Order" style="cursor: pointer;"></i>';
        
        // Only show edit button if user has 9D permission
        if (hasUpdatePermission()) {
          buttons += ' <i class="bi bi-pen edit_order" data-order-id="' + row.idOrder + '" title="Edit Order" style="cursor: pointer;"></i>';
        }
        
        buttons += '</div>';
        return buttons;
      },
    },
  ],
  language: {
    processing: "Loading orders...",
    emptyTable: "No orders found",
    zeroRecords: "No matching orders found",
  },
});

// Hide search bar if user doesn't have permission
if (!hasSearchPermission()) {
  $('.search-section').hide();
}

// Hide reports button if user doesn't have permission
if (!hasReportsPermission()) {
  $('.reports-button').hide();
}

$("#customerSearch").on("keyup", function () {
  const searchTerm = $(this).val();
  if (searchTerm.length >= 2) {
    $.ajax({
      url: "app/API/apiOrders.php?search_customers",
      type: "GET",
      data: {
        term: searchTerm,
      },
      success: function (response) {
        if (response.status === 1) {
          let options =
            '<option value="" disabled selected>Select customer</option>';
          response.data.forEach((customer) => {
            const displayName =
              customer.CustomerType == 1
                ? customer.BusinessName
                : customer.CustomerName;
            options += `<option value="${customer.idCustomer}"
                            data-type="${customer.CustomerType}"
                            data-name="${customer.CustomerName}"
                            data-business="${customer.BusinessName}"
                            data-address="${customer.CustomerAddress}"
                            data-tin="${customer.CustomerTIN}"
                            data-contact="${customer.ContactNum}"
                            data-email="${customer.ContactEmail}">${displayName}</option>`;
          });
          $("#customerId").html(options);
        }
      },
    });
  }
});

$("#itemSearch").on("keyup", function () {
  const searchTerm = $(this).val();
  if (searchTerm.length >= 2) {
    $.ajax({
      url: "app/API/apiOrders.php?search_pricelists",
      type: "GET",
      data: {
        term: searchTerm,
      },
      success: function (response) {
        if (response.status === 1) {
          let options =
            '<option value="" disabled selected>Select item</option>';
          response.data.forEach((item) => {
            options += `<option value="${item.ItemCode}"
                            data-price="${item.UnitPrice}"
                            data-discount="${item.DiscountedPrice}"
                            data-category="${item.CategoryName}"
                            data-measure="${item.MeasureDesc}">${item.ProductDesc}</option>`;
          });
          $("#itemCode").html(options);
        }
      },
    });
  }
});

$("#customerId").on("change", function () {
  const selectedOption = $(this).find("option:selected");
  const customerType = selectedOption.data("type");

  if (customerType == 1) {
    $("#customerNameLabel").text("Business Name");
    $("#customerName").val(selectedOption.data("business"));
    $("#businessNameGroup").show();
    $("#contactPersonGroup").show();
    $("#personDesignationGroup").show();
  } else {
    $("#customerNameLabel").text("Customer Name");
    $("#customerName").val(selectedOption.data("name"));
    $("#businessNameGroup").hide();
    $("#contactPersonGroup").hide();
    $("#personDesignationGroup").hide();
  }

  $("#customerAddress").val(selectedOption.data("address"));
  $("#customerTIN").val(selectedOption.data("tin"));
  $("#customerContact").val(selectedOption.data("contact"));
  $("#customerEmail").val(selectedOption.data("email"));
});

$("#itemCode").on("change", function () {
  const selectedOption = $(this).find("option:selected");
  $("#unitPrice").val(selectedOption.data("price"));
  $("#discountedPrice").val(selectedOption.data("discount"));
  $("#itemCategory").val(selectedOption.data("category"));
  $("#itemMeasure").val(selectedOption.data("measure"));
});

$("#itemQuantity").on("input", function () {
  const quantity = parseFloat($(this).val()) || 0;
  const unitPrice = parseFloat($("#unitPrice").val()) || 0;
  const discountedPrice = parseFloat($("#discountedPrice").val()) || 0;
  const price = discountedPrice > 0 ? discountedPrice : unitPrice;
  const total = quantity * price;
  $("#itemTotal").val(total.toFixed(2));
});

// Function to generate 12-digit item code
function generateItemCode() {
    const allowedChars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // Excluding I, L, O, 0
    let code = '';
    for (let i = 0; i < 12; i++) {
        const randomIndex = Math.floor(Math.random() * allowedChars.length);
        code += allowedChars[randomIndex];
    }
    return code;
}

// Function to reset item form
function resetItemForm() {
    $("#itemCode").val('');
    $("#itemQuantity").val('1');
    $("#unitPrice").val('');
    $("#discountedPrice").val('');
    $("#itemTotal").val('');
    $("#itemCategory").val('');
    $("#itemMeasure").val('');
}

$("#addItemBtn").on("click", function () {
    const itemCode = generateItemCode(); // Generate new item code
    const quantity = $("#itemQuantity").val();
    const unitPrice = $("#unitPrice").val();
    const discountedPrice = $("#discountedPrice").val();
    const total = $("#itemTotal").val();
    const productDesc = $("#productDesc").val(); // Add this input field to your form
    const categoryId = $("#itemCategory").val();
    const unitMeasureId = $("#itemMeasure").val();

    if (!productDesc || !quantity || !unitPrice || !categoryId || !unitMeasureId) {
        toastr.error("Please fill in all required fields");
        return;
    }

    // Create item data object
    const itemData = {
        ItemCode: itemCode,
        ProductDesc: productDesc,
        CategoryId: categoryId,
        UnitMeasureId: unitMeasureId,
        UnitPrice: unitPrice,
        DiscountedPrice: discountedPrice || unitPrice,
        Quantity: quantity,
        TotalAmount: total
    };

    // First, create the item in pricelists
    $.ajax({
        url: "app/API/apiOrders.php",
        type: "POST",
        data: {
            create_new_item: true,
            ...itemData
        },
        success: function(response) {
            if (response.status === 1) {
                // Add the item to the order table
                const itemRow = $(`
                    <tr>
                        <td>${productDesc}</td>
                        <td>${$("#itemCategory option:selected").text()}</td>
                        <td>${$("#itemMeasure option:selected").text()}</td>
                        <td>${quantity}</td>
                        <td>${unitPrice}</td>
                        <td>${discountedPrice || "-"}</td>
                        <td>${total}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);

                itemRow.attr("data-item-code", itemCode);
                itemRow.attr("data-product-desc", productDesc);
                itemRow.attr("data-category-id", categoryId);

                $("#orderItems tbody").append(itemRow);
                updateOrderTotals();
                resetItemForm();
                toastr.success("Item added successfully");
            } else {
                toastr.error(response.message || "Error adding item");
            }
        },
        error: function() {
            toastr.error("Error adding item");
        }
    });
});

$(document).on("click", ".remove-item", function () {
  $(this).closest("tr").remove();
  updateOrderTotals();
});

function updateOrderTotals() {
  let totalItems = 0;
  let totalAmount = 0;

  $("#orderItems tbody tr").each(function () {
    const amount = parseFloat($(this).find("td:eq(6)").text());

    if (!validateOrderAmount(amount)) {
      toastr.error("Invalid amount found. Please check your order items.");
      return false;
    }

    totalItems++;
    totalAmount += amount;
  });

  const vatAmount = calculateVAT(totalAmount);
  const deliveryFee = parseFloat($("#deliveryFee").val()) || 0;

  if (!validateDeliveryFee(deliveryFee)) {
    toastr.error("Please enter a valid delivery fee");
    return;
  }

  const dueAmount = calculateDueAmount(totalAmount, vatAmount, deliveryFee);

  $("#totalItems").val(totalItems);
  $("#totalAmount").val(formatCurrency(totalAmount));
  $("#vatAmount").val(formatCurrency(vatAmount));
  $("#dueAmount").val(formatCurrency(dueAmount));
}

function updateOrderAmounts(modalId) {
    const totalAmount = parseFloat($(`#${modalId}TotalAmount`).val()) || 0;
    const vatAmount = parseFloat($(`#${modalId}VatAmount`).val()) || 0;
    const deliveryFee = parseFloat($(`#${modalId}DeliveryFee`).val()) || 0;

    // Ensure VAT amount is never negative
    const validVatAmount = Math.max(0, vatAmount);
    if (vatAmount !== validVatAmount) {
        $(`#${modalId}VatAmount`).val(validVatAmount.toFixed(2));
    }

    // Calculate maximum allowed VAT (12% of total amount) - round to 2 decimal places
    const maxVAT = Math.round((totalAmount * 0.12) * 100) / 100;

    // Validate VAT amount - allow manual control but show warning if it exceeds 12%
    const vatInput = $(`#${modalId}VatAmount`);
    if (validVatAmount > maxVAT) {
        vatInput.addClass('is-invalid');
        // Show warning but don't prevent calculation
    } else {
        vatInput.removeClass('is-invalid');
    }

    // Calculate due amount: totalAmount + vatAmount + deliveryFee
    const dueAmount = calculateDueAmount(totalAmount, validVatAmount, deliveryFee);
    
    $(`#${modalId}DueAmount`).val(formatCurrency(dueAmount));
}

function validateVatAmount(input) {
    const totalAmount = parseFloat($('#editOrderTotalAmount').val()) || 0;
    const vatAmount = parseFloat(input.value) || 0;
    const maxVAT = Math.round((totalAmount * 0.12) * 100) / 100; // Round to 2 decimal places

    // Ensure VAT amount is never negative
    const validVatAmount = Math.max(0, vatAmount);
    if (vatAmount !== validVatAmount) {
        input.value = validVatAmount.toFixed(2);
    }

    // Block submission if VAT exceeds 12%
    if (validVatAmount > maxVAT) {
        input.classList.add('is-invalid');
        // Disable submit button
        $('#updateOrderBtn').prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
        return false; // Block submission
    } else {
        input.classList.remove('is-invalid');
        // Enable submit button
        $('#updateOrderBtn').prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
        return true;
    }
}

// VAT and delivery fee event listeners are now handled by OrderCalculator
// Event listeners removed to avoid conflicts

// VAT toggle functionality removed - VAT amount is now always editable

$("#saveOrderBtn").on("click", function () {
  const customerId = $("#customerId").val();
  const items = [];

  $("#orderItems tbody tr").each(function () {
    const itemCode = $(this).attr("data-item-code");
    const productDesc = $(this).attr("data-product-desc") || $(this).find("td:eq(0)").text();
    
    items.push({
      ItemCode: itemCode,
      ProductDesc: productDesc,
      Quantity: $(this).find("td:eq(3)").text(),
      UnitPrice: $(this).find("td:eq(4)").text(),
      DiscountedPrice:
        $(this).find("td:eq(5)").text() === "-"
          ? "0"
          : $(this).find("td:eq(5)").text(),
      TotalAmount: $(this).find("td:eq(6)").text(),
    });
  });

  if (!customerId || items.length === 0) {
    toastr.error("Please select a customer and add at least one item");
    return;
  }

  const postData = {
    create_order: true,
    CustomerId: customerId,
    DeliveryInstruction: $("#deliveryInstruction").val(),
    TotalItems: $("#totalItems").val(),
    TotalAmount: $("#totalAmount").val(),
    VATAmount: $("#vatAmount").val(),
    DeliveryFee: $("#deliveryFee").val(),
    DueAmount: $("#dueAmount").val(),
    OrderStatusId: 0,
    PaymentStatus: 0,
    ApprovalStatus: 0,
    OrderAuthor: $("#orderAuthor").val(),
    items: JSON.stringify(items),
  };

  $.ajax({
    url: "app/API/apiOrders.php",
    type: "POST",
    data: postData,
    success: function (response) {
      if (response.status === 1) {
        orderDataTable.ajax.reload();
        $("#orderForm")[0].reset();
        $("#orderItems tbody").empty();
        updateOrderTotals();
        toastr.success(response.message);
      } else {
        toastr.error(response.message);
      }
    },
    error: function () {
      toastr.error("Error creating order");
    },
  });
});

$(document).on("click", ".edit_order", function () {
  const orderId = $(this).data("order-id");
  $.ajax({
    url: "app/API/apiOrders.php?get_order",
    type: "GET",
    data: {
      get_order: true,
      idOrder: orderId,
    },
    success: function (response) {
      if (response.status === 1) {
        const order = response.data;

        // Check if order is completed, approved, and paid from database
        const isCompleted =
          order.StatusName && order.StatusName.toLowerCase().includes('completed') &&
          order.ApprovalStatus === 1 &&
          order.PaymentStatus === 1;

        // Disable form if order is completed
        if (isCompleted) {
          $(
            "#editOrderForm input:not(#closeEditOrderModal), #editOrderForm select, #editOrderForm textarea"
          ).prop("disabled", true);
          $("#updateOrderBtn").hide();
          $("#editOrderForm").addClass("form-locked");
          // Ensure close button and links remain clickable
          $("#closeEditOrderModal, #editOrderLink, #editOrderPdf").css({
            "pointer-events": "auto",
            opacity: "1",
            cursor: "pointer",
          });
          // Make payment proof images clickable
          $("#editProofDropZone").css({
            "pointer-events": "none",
            opacity: "0.7",
          });
          // Ensure payment proof container remains visible and interactive
          $("#editProofsOfPaymentContainer").css({
            "pointer-events": "auto",
            opacity: "1",
            display: "flex !important",
            visibility: "visible !important"
          });
        } else {
          $(
            "#editOrderForm input:not(#closeEditOrderModal), #editOrderForm select, #editOrderForm textarea"
          ).prop("disabled", false);
          $("#updateOrderBtn").show();
          $("#editOrderForm").removeClass("form-locked");
          $("#editProofDropZone").css({
            "pointer-events": "auto",
            opacity: "1",
          });
        }

        // Clear existing payment proof containers
        $("#editProofsOfPaymentContainer").empty();
        $("#editExistingProofs").empty();

        // Handle payment proofs
        if (order.PaymentProof) {
          try {
            const paymentProofs = JSON.parse(order.PaymentProof);
            displayExistingProofs(paymentProofs);
            if (Array.isArray(paymentProofs) && paymentProofs.length > 0) {
              $("#editPaymentStatus").val(1);
            }
          } catch (e) { 
          }
        }

        $("#editOrderNumber").text(order.OrderNum);
        $("#editOrderId").val(order.idOrder);
        $("#editCustomerId").val(order.CustomerId);

        $("#editOrderLink").attr(
          "href",
          `https://tool04.myracle.ph/order_form.php?orderNum=${order.OrderNum}`
        );
        $("#editOrderPdf").attr(
          "href",
          `https://tool04.myracle.ph/generate_pdf.php?orderNum=${order.OrderNum}`
        );

        $("#editOrderCustomerType").val(order.CustomerType).trigger("change");
        $("#editOrderCustomerTIN").val(order.CustomerTIN);

        if (order.CustomerType == 1) {
          $("#editOrderBusinessName")
            .val(order.BusinessName)
            .removeClass("d-none");
          $("#editOrderCustomerName").val("").addClass("d-none");
        } else {
          $("#editOrderBusinessName").val("").addClass("d-none");
          $("#editOrderCustomerName")
            .val(order.CustomerName)
            .removeClass("d-none");
        }

        $("#editOrderCustomerAddress").val(order.CustomerAddress);
        $("#editOrderContactPerson").val(order.ContactPerson);
        $("#editOrderPersonDesignation").val(order.PersonDesignation);
        $("#editOrderContactNum").val(order.ContactNum);
        $("#editOrderContactEmail").val(order.ContactEmail);

        $("#editDeliveryInstruction").val(order.DeliveryInstruction);
        $("#editOrderTotalAmount").val(order.TotalAmount);
        $("#editOrderVatableSales").val(order.VatableSales || 0);
        $("#editOrderZeroRatedSales").val(order.ZeroRatedSales || 0);
        $("#editOrderVatAmount").val(order.VATAmount);
        $("#editOrderEwtAmount").val(order.EWTAmount || 0);
        $("#editOrderDeliveryFee").val(order.DeliveryFee);
        $("#editOrderDueAmount").val(order.DueAmount);

        // Set approval and payment status first
        $("#editApprovalStatus").val(order.ApprovalStatus);
        $("#editPaymentStatus").val(order.PaymentStatus);
        
        // Set the order status from database (will be handled by loadOrderStatuses)
        // The automatic status logic is now handled in the PHP backend

        $("#editOrderAuthor").val(order.OrderAuthor);
        $("#editOrderDate").val(
          moment(order.OrderDate).format("YYYY-MM-DD HH:mm:ss")
        );
        $("#editDateCompleted").val(
          order.DateCompleted
            ? moment(order.DateCompleted).format("YYYY-MM-DD HH:mm:ss")
            : ""
        );

        $("#editOrderItemsTable tbody").empty();
        if (order.items && order.items.length > 0) {
          order.items.forEach((item) => {
            const itemRow = $(`
                            <tr data-item-code="${item.ItemCode}" data-product-desc="${item.ProductDesc}" data-category-id="${item.CategoryId || 1}" data-unit-measure-id="${item.UnitMeasureId || ''}">
                                <td class="text-start">
                                    <span class="product-desc-text">${item.ProductDesc}</span>
                                    <input type="text" class="form-control form-control-sm product-desc-edit d-none" value="${item.ProductDesc}">
                                </td>
                                <td class="text-center">${item.MeasureDesc}</td>
                                <td class="text-end">${item.UnitPrice}</td>
                                <td class="text-end">${item.DiscountedPrice || "-"}</td>
                                <td class="text-center">${item.Quantity}</td>
                                <td class="text-end">${item.TotalAmount}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success save-desc-btn d-none me-1" title="Save Description">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary cancel-desc-btn d-none me-1" title="Cancel Edit">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <button type="button" class="btn bi bi-pencil text-dark edit-item-btn me-1" title="Edit Item"></button>
                                    <button type="button" class="btn bi bi-trash text-danger remove-item" ${
                                      order.items.length === 1
                                        ? 'style="display: none;"'
                                        : ""
                                    }></button>
                                </td>
                            </tr>
                        `);

            $("#editOrderItemsTable tbody").append(itemRow);
          });
        }

        $("#originalOrderItems").val(JSON.stringify(order.items));
        $("#editCustomerStatus").val(order.CustomerStatus || 1);

        // Reset item form fields
        $("#editOrderProductDesc").val("");
        $("#editOrderItemCode").val("");
        $("#editOrderItemId").val("");
        $("#editOrderItemCategoryId").val("");
        $("#editOrderUOM").val("");
        $("#editOrderUnitPrice").val("");
        $("#editOrderDiscountedPrice").val("");
        $("#editOrderQuantity").val("1");
        $("#editOrderAmount").val("");

        // Load order statuses dynamically and preserve selected value
        loadOrderStatuses(order.OrderStatusId);

        $("#editOrderModal").modal("show");
        
        // Update items data after modal is shown
        $("#editOrderModal").on('shown.bs.modal', function() {
          if (window.editOrderProductHandler) {
            window.editOrderProductHandler.updateItemsData('editOrderModal');
          }
        });
      } else {
        toastr.error(response.message || "Error retrieving order details");
      }
    },
    error: function (xhr, status, error) {
      toastr.error("Error retrieving order details: " + error);
    },
  });
});

$("#editOrderForm").on("submit", function (e) {
  e.preventDefault();

  // Get items from the hidden input (updated by productSuggestions.js)
  const itemsInput = $("#editOrderModal input[name='items']");
  let items = [];
  
  if (itemsInput.length > 0 && itemsInput.val()) {
    try {
      items = JSON.parse(itemsInput.val());
    } catch (e) {
      toastr.error("Error processing order items");
      return;
    }
  } else {
    // Fallback: collect items from table if hidden input is not available
    $("#editOrderItemsTable tbody tr").each(function () {
      const row = $(this);
      const itemCode = row.attr("data-item-code");

      if (!itemCode) {
        toastr.error("Invalid item code found. Please remove and re-add the item.");
        return false;
      }

      const unitPrice = parseFloat(row.find("td:eq(2)").text()) || 0;
      const discountedPrice = row.find("td:eq(3)").text() === "-" ? unitPrice : parseFloat(row.find("td:eq(3)").text()) || unitPrice;
      const quantity = parseFloat(row.find("td:eq(4)").text()) || 0;

      if (isNaN(quantity) || quantity <= 0) {
        toastr.error("Invalid quantity for item: " + row.find("td:eq(0)").text());
        return false;
      }

      items.push({
        ItemCode: itemCode,
        ProductDesc: row.attr("data-product-desc") || row.find("td:eq(0)").text(),
        CategoryId: row.attr("data-category-id"),
        UnitMeasureId: row.attr("data-unit-measure-id"),
        UnitPrice: unitPrice,
        DiscountedPrice: discountedPrice,
        Quantity: quantity
      });
    });
  }

  if (items.length === 0) {
    toastr.error("Please add at least one item to the order");
    return;
  }

  // Validate VAT amount before submission
  const totalAmount = parseFloat($("#editOrderTotalAmount").val()) || 0;
  const vatAmount = parseFloat($("#editOrderVatAmount").val()) || 0;
  
  // Create FormData object
  const formData = new FormData(this);
  formData.append("items", JSON.stringify(items));
  formData.append("TotalItems", items.length);
  formData.append("TotalAmount", $("#editOrderTotalAmount").val());
  formData.append("VATAmount", $("#editOrderVatAmount").val());
  
  // Always include the current delivery fee value
  const currentDeliveryFee = $("#editOrderDeliveryFee").val();
  formData.append("DeliveryFee", currentDeliveryFee);
  
  // Calculate due amount based on current values
  const deliveryFee = parseFloat(currentDeliveryFee) || 0;
  const dueAmount = calculateDueAmount(totalAmount, vatAmount, deliveryFee);
  formData.append("DueAmount", dueAmount);

  // Handle payment proofs
  const paymentProofFiles = $("#editPaymentProof")[0].files;
  const originalOrderData = $("#originalOrderItems").val();
  let originalPaymentProofs = [];

  try {
    if (originalOrderData) {
      const orderData = JSON.parse(originalOrderData);
      if (orderData.PaymentProof) {
        originalPaymentProofs = JSON.parse(orderData.PaymentProof);
      }
    }
  } catch (e) {
  }

  if (paymentProofFiles.length > 0) {
    // If new files are uploaded, only send the new files
    for (let i = 0; i < paymentProofFiles.length; i++) {
      formData.append("payment_proofs[]", paymentProofFiles[i]);
    }
  } else if (originalPaymentProofs.length > 0) {
    // If no new files but we have original proofs, send those
    formData.append("PaymentProof", JSON.stringify(originalPaymentProofs));
  }

  // Submit the form
  $.ajax({
    url: "app/API/apiOrders.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status === 1) {
        toastr.success(response.message || "Order updated successfully");
        $("#editOrderModal").modal("hide");
        if (typeof orderDataTable !== "undefined") {
          orderDataTable.ajax.reload();
        }
      } else {
        toastr.error(response.message || "Error updating order");
      }
    },
    error: function (xhr, status, error) {
      toastr.error(
        "Error updating order: " + (xhr.responseJSON?.message || error)
      );
    },
  });
});

// Note: The newAddOrderDetailToList and editAddOrderDetailToList event handlers
// are now handled by productSuggestions.js to avoid conflicts

function updateEditOrderAmounts() {
  const modal = $("#editOrderModal");
  const tbody = modal.find("#editOrderItemsTable tbody");

  let totalAmount = 0;
  let totalItems = 0;
  tbody.find("tr").each(function () {
    const amount = parseFloat($(this).find("td:eq(5)").text()) || 0;

    if (!validateOrderAmount(amount)) {
      toastr.error("Invalid amount found. Please check your order items.");
      return false;
    }

    totalAmount += amount;
    totalItems++;
  });

  // Get current VAT amount from input (don't auto-calculate)
  const currentVATAmount = parseFloat(modal.find("#editOrderVatAmount").val()) || 0;
  const deliveryFee = parseFloat(modal.find("#editOrderDeliveryFee").val()) || 0;

  if (!validateDeliveryFee(deliveryFee)) {
    toastr.error("Please enter a valid delivery fee");
    return;
  }

  // Calculate due amount: totalAmount + vatAmount + deliveryFee
  const dueAmount = calculateDueAmount(totalAmount, currentVATAmount, deliveryFee);

  modal.find("#editOrderTotalAmount").val(formatCurrency(totalAmount));
  // Don't auto-update VAT amount - let user control it
  modal.find("#editOrderDueAmount").val(formatCurrency(dueAmount));
  modal.find("#editOrderTotalItems").val(totalItems);
}

$("#editOrderDeliveryFee").on("input", function () {
  updateEditOrderAmounts();
});

$(document).on("click", "#editOrderItemsTable .remove-item-btn", function () {
  const tbody = $("#editOrderItemsTable tbody");
  const rowCount = tbody.find("tr").length;

  if (rowCount <= 1) {
    return;
  }

  $(this).closest("tr").remove();

  if (tbody.find("tr").length === 1) {
    tbody.find("tr:last .remove-item-btn").hide();
  }

  updateEditOrderAmounts();
  
  // Update items data in hidden input
  if (window.editOrderProductHandler) {
    window.editOrderProductHandler.updateItemsData('editOrderModal');
  }
});

$("#updateOrderBtn").on("click", function () {
  // Ensure items data is up to date before submission
  if (window.editOrderProductHandler) {
    window.editOrderProductHandler.updateItemsData('editOrderModal');
  }
  // Get items from the hidden input (updated by productSuggestions.js)
  const itemsInput = $("#editOrderModal_items_input");
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
    $("#editOrderItemsTable tbody tr").each(function () {
      const item = {
        ItemCode: $(this).attr("data-item-code"),
        ProductDesc: $(this).attr("data-product-desc"),
        UnitPrice: $(this).find("td:eq(2)").text(),
        DiscountedPrice:
          $(this).find("td:eq(3)").text() === "-"
            ? "0"
            : $(this).find("td:eq(3)").text(),
        Quantity: $(this).find("td:eq(4)").text(),
        TotalAmount: $(this).find("td:eq(5)").text(),
      };
      items.push(item);
    });
  }

  // Create FormData object
  const formData = new FormData($("#editOrderForm")[0]);
  formData.append("items", JSON.stringify(items));
  formData.append("TotalItems", items.length);
  formData.append("TotalAmount", $("#editOrderTotalAmount").val());
  formData.append("VATAmount", $("#editOrderVatAmount").val());
  formData.append("DueAmount", $("#editOrderDueAmount").val());
  formData.append("update_order", true);

  // Handle payment proof files
  const paymentProofFiles = $("#editPaymentProof")[0].files;
  if (paymentProofFiles.length > 0) {
    for (let i = 0; i < paymentProofFiles.length; i++) {
      formData.append("payment_proofs[]", paymentProofFiles[i]);
    }
  }

  // Submit the form
  $.ajax({
    url: "app/API/apiOrders.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status === 1) {
        $("#editOrderModal").modal("hide");
        orderDataTable.ajax.reload();
      } else {
        toastr.error(response.message || "Error updating order");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error details:", {
        status: status,
        error: error,
        response: xhr.responseText,
      });
      toastr.error(
        "Error updating order: " + (xhr.responseJSON?.message || error)
      );
    },
  });
});

$("#resetOrderForm").on("click", function () {
  $("#orderForm")[0].reset();
  $("#orderId").val("");
  $("#orderItems tbody").empty();
  updateOrderTotals();
  $("#saveOrderBtn").show();
  $("#updateOrderBtn").hide();
  $(".status-fields").hide();
});

$("#customerCustomSearch").on("keyup", function () {
  orderDataTable.ajax.reload();
});

$("#selectOrderStatusFilter").on("change", function () {
  orderDataTable.ajax.reload();
});

$("#selectPaymentStatusFilter").on("change", function () {
  orderDataTable.ajax.reload();
});

$("#selectApprovalStatusFilter").on("change", function () {
  orderDataTable.ajax.reload();
});

$("#searchFilter").on("click", function () {
  orderDataTable.ajax.reload();
});

$("#newOrderCustomerType")
  .on("change", function () {
    const isBusiness = $(this).val() == 1;

    if (isBusiness) {
      $("#newOrderCustomerNameLabel").text("Business Name");
      $("#contactPersonGroup, #personDesignationGroup").show();
      $("#newOrderContactPerson, #newOrderPersonDesignation").prop(
        "required",
        true
      );
    } else {
      $("#newOrderCustomerNameLabel").text("Customer Name");
      $("#contactPersonGroup, #personDesignationGroup").hide();
      $("#newOrderContactPerson, #newOrderPersonDesignation").prop(
        "required",
        false
      );
    }
  })
  .trigger("change");

$("#editOrderCustomerType")
  .on("change", function () {
    const isBusiness = $(this).val() == 1;

    if (isBusiness) {
      $("#editOrderCustomerNameLabel").text("Business Name");
      $(".business-fields").show();
      $("#editOrderContactPerson, #editOrderPersonDesignation").prop(
        "required",
        true
      );
      $("#editOrderBusinessName").removeClass("d-none");
      $("#editOrderCustomerName").addClass("d-none");
    } else {
      $("#editOrderCustomerNameLabel").text("Customer Name");
      $(".business-fields").hide();
      $("#editOrderContactPerson, #editOrderPersonDesignation").prop(
        "required",
        false
      );
      $("#editOrderBusinessName").addClass("d-none");
      $("#editOrderCustomerName").removeClass("d-none");
    }
  })
  .trigger("change");

$(document).ready(function () {
  $("#editBrowseFiles").on("click", function (e) {
    e.preventDefault();
    $("#editPaymentProof").click();
  });

  $("#editPaymentProof").on("change", function (e) {
    const files = e.target.files;
    handleEditFiles(files);
  });

  const editDropZone = document.getElementById("editProofDropZone");

  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    editDropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ["dragenter", "dragover"].forEach((eventName) => {
    editDropZone.addEventListener(eventName, highlight, false);
  });

  ["dragleave", "drop"].forEach((eventName) => {
    editDropZone.addEventListener(eventName, unhighlight, false);
  });

  function highlight(e) {
    editDropZone.classList.add("border-primary");
  }

  function unhighlight(e) {
    editDropZone.classList.remove("border-primary");
  }

  editDropZone.addEventListener("drop", handleEditDrop, false);

  function handleEditDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleEditFiles(files);
  }

  function handleEditFiles(files) {
    const container = $("#editProofsOfPaymentContainer");
    container.empty();

    Array.from(files).forEach((file) => {
      if (!file.type.match("image.*")) {
        toastr.error("Only image files are allowed");
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        const preview = $(`
          <div class="position-relative">
            <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
            <div class="position-absolute top-0 end-0 m-1 d-flex gap-1">
              <button type="button" class="btn btn-sm btn-info view-proof" onclick="previewImage('${e.target.result}')">
                <i class="bi bi-eye"></i>
              </button>
              <button type="button" class="btn btn-sm btn-danger remove-proof">
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>
        `);
        container.append(preview);
      };
      reader.readAsDataURL(file);
    });

    // Update payment status if files are uploaded
    if (files.length > 0) {
      $("#editPaymentStatus").val(1);
      
      // Check if order is already approved, if so set to Completed
      const approvalStatus = parseInt($("#editApprovalStatus").val());
      if (approvalStatus === 1) {
        $("#editOrderStatus").val(2); // Completed (idOrderStatus=2)
        const currentDate = new Date().toISOString().slice(0, 19).replace("T", " ");
        $("#editDateCompleted").val(currentDate);
        toastr.success(
          "Payment proof uploaded successfully. Payment status set to Paid. Order status set to Completed."
        );
      } else {
        toastr.success(
          "Payment proof uploaded successfully. Payment status set to Paid."
        );
      }
    }
  }

  $(document).on("click", ".remove-proof", function () {
    const filename = $(this).data("filename");
    const container = $(this).closest(".position-relative").parent();
    $(this).closest(".position-relative").remove();

    // If this is an existing proof, we need to update the PaymentProof JSON
    if (filename) {
      const existingProofs = JSON.parse($("#originalOrderItems").val() || "[]");
      const updatedProofs = existingProofs.filter(
        (proof) => proof.filename !== filename
      );
      $("#originalOrderItems").val(JSON.stringify(updatedProofs));
    }

    // Check if there are any remaining payment proofs
    const hasRemainingProofs =
      $("#editProofsOfPaymentContainer img").length > 0 ||
      $("#editExistingProofs img").length > 0;

    if (!hasRemainingProofs) {
      const approvalStatus = parseInt($("#editApprovalStatus").val());
      $("#editPaymentStatus").val(0);
      $("#editExistingProofContainer").hide();
      
          // Update order status based on approval status
    if (approvalStatus === 1) {
      $("#editOrderStatus").val(5); // On Process (idOrderStatus=5)
      toastr.info("Payment status set to Unpaid. Order status set to On Process.");
    } else {
      $("#editOrderStatus").val(4); // Waiting (idOrderStatus=4)
      toastr.info("Payment status set to Unpaid. Order status set to Waiting.");
    }
      $("#editDateCompleted").val("");
    }
  });

  window.previewImage = function (src) {
    $("#previewImage").attr("src", src);
    $("#imagePreviewModal").modal("show");
  };

  $("#deleteProofBtn").on("click", function () {
    const filename = $(this).data("filename");
    if (filename) {
      $(
        `#editExistingProofs .remove-proof[data-filename="${filename}"]`
      ).click();
    }
    $("#imagePreviewModal").modal("hide");
  });

  $(document).on("click", ".view-proof", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const src = $(this).closest(".position-relative").find("img").attr("src");
    $("#previewImage").attr("src", src);
    $("#imagePreviewModal").modal("show");

    // Only show delete button for new uploads
    if ($(this).closest("#editProofsOfPaymentContainer").length > 0) {
      $("#deleteProofBtn").show();
    } else {
      $("#deleteProofBtn").hide();
    }
  });
});

$("#editPaymentStatus").on("change", function () {
  const paymentStatus = parseInt($(this).val());
  const approvalStatus = parseInt($("#editApprovalStatus").val());
  const hasPaymentProof =
    $("#editProofsOfPaymentContainer img").length > 0 ||
    $("#editExistingProofs img").length > 0;

  if (paymentStatus === 1) {
    if (!hasPaymentProof) {
      toastr.warning("Please upload payment proof first");
      $(this).val(0);
      return;
    }
    
    // If order is approved and payment proof is uploaded, set to Completed
    if (approvalStatus === 1) {
      $("#editOrderStatus").val(2); // Completed (idOrderStatus=2)
      const currentDate = new Date().toISOString().slice(0, 19).replace("T", " ");
      $("#editDateCompleted").val(currentDate);
      toastr.success("Payment status set to Paid. Order status set to Completed.");
    }
  } else if (paymentStatus === 0) {
    if (hasPaymentProof) {
      toastr.warning("Cannot set to Unpaid when payment proof exists");
      $(this).val(1);
      return;
    }
    
    // If payment status is set to unpaid, revert order status based on approval
    if (approvalStatus === 1) {
      $("#editOrderStatus").val(5); // On Process (idOrderStatus=5)
      toastr.info("Payment status set to Unpaid. Order status set to On Process.");
    } else {
      $("#editOrderStatus").val(4); // Waiting (idOrderStatus=4)
      toastr.info("Payment status set to Unpaid. Order status set to Waiting.");
    }
    $("#editDateCompleted").val("");
  }
});

$("#editApprovalStatus").on("change", function () {
  const approvalStatus = parseInt($(this).val());
  const paymentStatus = parseInt($("#editPaymentStatus").val());
  const orderStatus = parseInt($("#editOrderStatus").val());

  // Automatically set order status to Completed when order is approved and payment proof exists
  if (approvalStatus === 1 && paymentStatus === 1) {
    $("#editOrderStatus").val(2); // Completed (idOrderStatus=2)
    const currentDate = new Date().toISOString().slice(0, 19).replace("T", " ");
    $("#editDateCompleted").val(currentDate);
    toastr.success("Order approved and payment received. Order status set to Completed.");
  } else if (approvalStatus === 1 && paymentStatus === 0) {
    // If approved but no payment proof, set to On Process
    $("#editOrderStatus").val(5); // On Process (idOrderStatus=5)
    toastr.info("Order approved. Please upload payment proof to complete the order.");
  } else if (approvalStatus === 0) {
    $("#editOrderStatus").val(4); // Waiting (idOrderStatus=4)
    $("#editDateCompleted").val("");
    toastr.info("Order approval revoked. Order status set to Waiting.");
  }
});

$("#editOrderStatus").on("change", function () {
  const orderStatus = parseInt($(this).val());
  const paymentStatus = parseInt($("#editPaymentStatus").val());
  const approvalStatus = parseInt($("#editApprovalStatus").val());

  // Get the status name to check if it's completed
  const statusName = $("#editOrderStatus option:selected").text().toLowerCase();
  
  // Prevent manual selection of Completed status
  if (statusName.includes('completed')) {
    $(this).val($("#editOrderStatus option:first").val()); // Set back to first option
    toastr.warning(
      "Order status will be automatically set to Completed when payment proof is uploaded and order is approved"
    );
    return;
  }

  // Update DateCompleted when order is cancelled
  if (statusName.includes('cancelled')) {
    const currentDate = new Date().toISOString().slice(0, 19).replace("T", " ");
    $("#editDateCompleted").val(currentDate);
  } else {
    $("#editDateCompleted").val("");
  }
});

$(document).ready(function () {
  // ProductSuggestionHandler is initialized in productSuggestions.js
  // Hide Completed option from order status dropdown
  $('#editOrderStatus option:contains("Completed")').hide();
});

$("#newOrderCustomerTIN, #editOrderCustomerTIN").on("input", function () {
  const tin = $(this).val();
  $(this).val(tin);
});

$("#newOrderContactNum, #editOrderContactNum").on("input", function () {
  const number = $(this).val();
  $(this).val(number);
});

$("#newOrderContactEmail, #editOrderContactEmail").on("input", function () {
  const email = $(this).val();
  if (validateEmail(email)) {
    $(this).removeClass("is-invalid");
    $(this).next(".invalid-feedback").text("");
  } else {
    $(this).addClass("is-invalid");
    $(this)
      .next(".invalid-feedback")
      .text("Please enter a valid email address");
  }
});

// Add styles for locked form
$("<style>")
  .text(
    `
        .form-locked {
            opacity: 0.8;
            pointer-events: none;
        }
        .form-locked input,
        .form-locked select,
        .form-locked textarea {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        /* Ensure close button and links remain clickable */
        .form-locked #closeEditOrderModal,
        .form-locked #editOrderLink,
        .form-locked #editOrderPdf,
        .form-locked #editExistingProofs .position-relative,
        .form-locked #editProofsOfPaymentContainer,
        .form-locked #editProofsOfPaymentContainer .position-relative,
        .form-locked #imagePreviewModal,
        .form-locked #imagePreviewModal .modal-content,
        .form-locked #imagePreviewModal .modal-header,
        .form-locked #imagePreviewModal .modal-body,
        .form-locked #imagePreviewModal .modal-footer,
        .form-locked #imagePreviewModal .btn-close,
        .form-locked #imagePreviewModal .btn-light {
            pointer-events: auto !important;
            opacity: 1 !important;
            cursor: pointer !important;
        }
        /* Force payment proof container to be visible */
        .form-locked #editProofsOfPaymentContainer {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            position: relative !important;
            z-index: 1 !important;
        }
        .form-locked #editProofsOfPaymentContainer .position-relative {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        /* Ensure image preview modal is always visible and clickable */
        #imagePreviewModal {
            z-index: 1060 !important;
        }
        #imagePreviewModal .modal-content {
            background-color: rgba(0, 0, 0, 0.9);
        }
        #imagePreviewModal .modal-header,
        #imagePreviewModal .modal-footer {
            background-color: #212529;
            border-color: rgba(255, 255, 255, 0.1);
        }
        #imagePreviewModal .modal-title {
            color: white;
        }
        #imagePreviewModal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #previewImage {
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        #imagePreviewModal .modal-body {
            padding: 0;
            background-color: #000;
        }
        .view-proof {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .view-proof i {
            font-size: 0.875rem;
        }
        /* Only apply hover effect to payment proof images */
        #editProofsOfPaymentContainer .position-relative,
        #editExistingProofs .position-relative {
            transition: all 0.3s ease;
        }
        #editProofsOfPaymentContainer .position-relative:hover,
        #editExistingProofs .position-relative:hover {
            transform: scale(1.05);
        }
    `
  )
  .appendTo("head");

// Add the image preview modal HTML at the end of the file
$("body").append(`
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" class="img-fluid" style="max-height: 80vh; width: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="deleteProofBtn" style="display: none;">Delete Image</button>
                </div>
            </div>
        </div>
    </div>
  `);

// Add styles for the modal
$("<style>")
  .text(
    `
        #imagePreviewModal .modal-dialog {
            max-width: 90vw;
        }
        #imagePreviewModal .modal-content {
            background-color: rgba(0, 0, 0, 0.9);
        }
        #imagePreviewModal .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        #imagePreviewModal .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        #imagePreviewModal .modal-title {
            color: white;
        }
        #imagePreviewModal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #previewImage {
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        #imagePreviewModal .modal-body {
            padding: 0;
            background-color: black;
        }
        #imagePreviewModal .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        #imagePreviewModal .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    `
  )
  .appendTo("head");

// OLD EDIT ITEM HANDLER - DISABLED - Now handled by productSuggestions.js
// Add a variable to store the previously edited row
let previouslyEditedRow = null;

// $(document).on("click", ".edit-item-btn", function () {
//   const row = $(this).closest("tr");
//   const modal = $(this).closest(".modal");
//   const modalId = modal.attr("id");

//   // If there was a previously edited row, restore it to the table
//   if (previouslyEditedRow) {
//     $("#editOrderItemsTable tbody").append(previouslyEditedRow);
//     previouslyEditedRow = null;
//   }

//   // Get item data from the row
//   const itemDesc = row.find("td:eq(0)").text();
//   const itemUOMText = row.find("td:eq(1)").text();
//   const itemUP = parseFloat(
//     row
//       .find("td:eq(2)")
//       .text()
//       .replace(/[^0-9.-]+/g, "")
//   );
//   const itemDP = parseFloat(
//     row
//       .find("td:eq(3)")
//       .text()
//       .replace(/[^0-9.-]+/g, "")
//   );
//   const itemQty = parseFloat(row.find("td:eq(4)").text());
//   const itemCode = row.attr("data-item-code");
//   const itemCategoryId = row.attr("data-category-id");
//   const itemUOMId = row.attr("data-unit-measure-id");

//   // Store the current row before removing it
//   previouslyEditedRow = row.clone(true);

//   // Populate form fields
//   modal.find(".item-desc").val(itemDesc);
//   modal.find(".item-code").val(itemCode);
//   modal.find(".item-category-id").val(itemCategoryId);
//   modal.find(".item-category").val(itemCategoryId);

//   // Fetch unit measures and populate the dropdown
//   $.ajax({
//     url: "app/API/apiUnitMeasures.php",
//     type: "GET",
//     data: {
//       get_unit_measures: true
//     },
//     success: function(response) {
//       if (response.status === 1) {
//         let options = '<option value="" disabled>Select unit measure</option>';
//         response.data.forEach(unit => {
//           const selected = unit.IdMeasure == itemUOMId ? 'selected' : '';
//           options += `<option value="${unit.IdMeasure}" ${selected}>${unit.MeasureDesc}</option>`;
//         });
//         modal.find(".item-uom").html(options);
//       }
//     }
//   });

//   modal.find(".item-up").val(itemUP);
//   modal.find(".item-dp").val(itemDP);
//   modal.find(".item-qty").val(itemQty);
//   modal.find(".item-amount").val(itemQty * itemDP);

//   // Remove the row
//   row.remove();

//   // Update totals
//   updateOrderAmounts(modalId);
// });

// OLD CANCEL HANDLER - DISABLED - Now handled by productSuggestions.js
// Add handler for the Cancel button to restore the previously edited row
// $("#editResetOrderDetailFields").on("click", function() {
//   if (previouslyEditedRow) {
//     $("#editOrderItemsTable tbody").append(previouslyEditedRow);
//     previouslyEditedRow = null;
    
//             // Reset form fields
//         $("#editOrderProductDesc").val("");
//         $("#editOrderItemCode").val("");
//         $("#editOrderItemId").val("");
//         $("#editOrderItemCategoryId").val("");
//         $("#editOrderCategory").val("");
//         $("#editOrderUOM").val("");
//         $("#editOrderUnitPrice").val("");
//         $("#editOrderDiscountedPrice").val("");
//         $("#editOrderQuantity").val("1");
//         $("#editOrderAmount").val("");
    
//     // Update totals
//     updateOrderAmounts("editOrderModal");
//   }
// });

// Note: The editAddOrderDetailToList event is handled by productSuggestions.js
// This prevents conflicts between the two implementations

// OLD MODAL CLOSE HANDLER - DISABLED - Now handled by productSuggestions.js
// Clear the previously edited row when the modal is closed
// $("#editOrderModal").on("hidden.bs.modal", function() {
//   if (previouslyEditedRow) {
//     $("#editOrderItemsTable tbody").append(previouslyEditedRow);
//     previouslyEditedRow = null;
//   }
// });

// OLD INLINE EDIT FUNCTIONALITY - DISABLED - Now handled by productSuggestions.js
// Inline edit functionality for product descriptions in edit order modal
// $(document).on('click', '.edit-desc-btn', (e) => {
//   const row = $(e.target).closest('tr');
//   const descText = row.find('.product-desc-text');
//   const descInput = row.find('.product-desc-edit');
//   const editBtn = row.find('.edit-desc-btn');
//   const saveBtn = row.find('.save-desc-btn');
//   const cancelBtn = row.find('.cancel-desc-btn');

//   // Store original value for cancel functionality
//   row.attr('data-original-desc', descText.text());

//   // Show edit mode
//   descText.addClass('d-none');
//   descInput.removeClass('d-none').focus();
//   editBtn.addClass('d-none');
//   saveBtn.removeClass('d-none');
//   cancelBtn.removeClass('d-none');
// });

// $(document).on('click', '.save-desc-btn', (e) => {
//   const row = $(e.target).closest('tr');
//   const descText = row.find('.product-desc-text');
//   const descInput = row.find('.product-desc-edit');
//   const editBtn = row.find('.edit-desc-btn');
//   const saveBtn = row.find('.save-desc-btn');
//   const cancelBtn = row.find('.cancel-desc-btn');

//   const newDesc = descInput.val().trim();
//   if (!newDesc) {
//     toastr.error('Product description cannot be empty');
//     return;
//   }

//   // Update the display and data attributes
//   descText.text(newDesc);
//   row.attr('data-product-desc', newDesc);

//   // Hide edit mode
//   descText.removeClass('d-none');
//   descInput.addClass('d-none');
//   editBtn.removeClass('d-none');
//   saveBtn.addClass('d-none');
//   cancelBtn.addClass('d-none');

//   toastr.success('Product description updated successfully');
// });

// $(document).on('click', '.cancel-desc-btn', (e) => {
//   const row = $(e.target).closest('tr');
//   const descText = row.find('.product-desc-text');
//   const descInput = row.find('.product-desc-edit');
//   const editBtn = row.find('.edit-desc-btn');
//   const saveBtn = row.find('.save-desc-btn');
//   const cancelBtn = row.find('.cancel-desc-btn');

//   // Restore original value
//   const originalDesc = row.attr('data-original-desc');
//   descInput.val(originalDesc);

//   // Hide edit mode
//   descText.removeClass('d-none');
//   descInput.addClass('d-none');
//   editBtn.removeClass('d-none');
//   saveBtn.addClass('d-none');
//   cancelBtn.addClass('d-none');
// });

// Handle Enter key in edit input
// $(document).on('keydown', '.product-desc-edit', (e) => {
//   if (e.key === 'Enter') {
//     e.preventDefault();
//     $(e.target).closest('tr').find('.save-desc-btn').click();
//   } else if (e.key === 'Escape') {
//     e.preventDefault();
//     $(e.target).closest('tr').find('.cancel-desc-btn').click();
//   }
// });

// Function to populate categories dropdown
function populateCategories() {
    $.ajax({
        url: "app/API/apiCategory.php",
        type: "GET",
        data: { get_categories: true },
        success: function(response) {
            if (response.status === 1 && response.data && response.data.data) {
                let options = '<option value="" disabled selected>Select category</option>';
                response.data.data.forEach(category => {
                    options += `<option value="${category.idCategory}">${category.CategoryName}</option>`;
                });
                $("#itemCategory").html(options);
            }
        }
    });
}

// Function to populate unit measures dropdown
function populateUnitMeasures() {
    $.ajax({
        url: "app/API/apiUnitMeasures.php",
        type: "GET",
        data: { get_units: true },
        success: function(response) {
            if (response.status === 1) {
                let options = '<option value="" disabled selected>Select unit measure</option>';
                response.data.forEach(unit => {
                    options += `<option value="${unit.IdMeasure}">${unit.MeasureDesc}</option>`;
                });
                $("#itemMeasure").html(options);
            }
        }
    });
}

// Populate dropdowns when modal opens
$("#newOrderModal").on("show.bs.modal", function() {
    populateCategories();
    populateUnitMeasures();
    
    // Initialize VAT amount to 0.00 (VAT amount is now always editable)
    $("#newOrderVatAmount").val('0.00');
});

// Reset form when new order modal is hidden
$("#newOrderModal").on("hidden.bs.modal", function() {
    // Form will be reset when modal is shown again
});

// Calculate total amount when quantity or prices change
$("#itemQuantity, #unitPrice, #discountedPrice").on("input", function() {
    const quantity = parseFloat($("#itemQuantity").val()) || 0;
    const unitPrice = parseFloat($("#unitPrice").val()) || 0;
    const discountedPrice = parseFloat($("#discountedPrice").val()) || 0;
    const price = discountedPrice > 0 ? discountedPrice : unitPrice;
    const total = quantity * price;
    $("#itemTotal").val(total.toFixed(2));
});

// Reset form when cancel button is clicked
$("#resetItemBtn").on("click", function() {
    resetItemForm();
});

$(document).on("click", ".view_order", function () {
  const orderId = $(this).data("order-id");
  $.ajax({
    url: "app/API/apiOrders.php?get_order",
    type: "GET",
    data: {
      get_order: true,
      idOrder: orderId,
    },
    success: function (response) {
      if (response.status === 1) {
        const order = response.data;
        const pdfUrl = `https://tool04.myracle.ph/generate_pdf.php?orderNum=${order.OrderNum}`;
        window.open(pdfUrl, '_blank');
      } else {
        toastr.error(response.message || "Error retrieving order details");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      toastr.error("Error retrieving order details: " + error);
    },
  });
});

// Function to load order statuses dynamically
function loadOrderStatuses(selectedStatusId = null) {
  $.ajax({
    url: "app/API/apiOrderStatuses.php",
    type: "GET",
    data: { get_order_statuses: true },
    success: function(response) {
      if (response.status === 1 && response.data && response.data.data) {
        let options = '';
        response.data.data.forEach(status => {
          const classAttr = status.StatusName.toLowerCase() === 'completed' ? 'class="completed-status"' : '';
          const selectedAttr = (selectedStatusId && status.idOrderStatus == selectedStatusId) ? 'selected' : '';
          options += `<option value="${status.idOrderStatus}" ${classAttr} ${selectedAttr}>${status.StatusName}</option>`;
        });
        $("#editOrderStatus").html(options);
      } else {
        console.error("Error loading order statuses:", response.message);
        $("#editOrderStatus").html('<option value="" disabled>Error loading statuses</option>');
      }
    },
    error: function(xhr, status, error) {
      console.error("Error loading order statuses:", error);
      $("#editOrderStatus").html('<option value="" disabled>Error loading statuses</option>');
    }
  });
}

// --- VALIDATION: Due Amount ---
function validateDueAmountEdit() {
    const vatableSales = parseFloat($("#editOrderVatableSales").val()) || 0;
    const zeroRatedSales = parseFloat($("#editOrderZeroRatedSales").val()) || 0;
    const vatAmount = parseFloat($("#editOrderVatAmount").val()) || 0;
    const deliveryFee = parseFloat($("#editOrderDeliveryFee").val()) || 0;
    const ewtAmount = parseFloat($("#editOrderEwtAmount").val()) || 0;
    const dueAmount = parseFloat($("#editOrderDueAmount").val()) || 0;
    const computedDue = vatableSales + zeroRatedSales + vatAmount + deliveryFee - ewtAmount;
    const isValid = Math.abs(dueAmount - computedDue) < 0.01; // allow small rounding error
    if (!isValid) {
        $("#editOrderDueAmount").addClass('is-invalid');
        $("#editOrderDueAmount").siblings('.invalid-feedback').remove();
        $("#editOrderDueAmount").after('<div class="invalid-feedback">Due Amount does not match the sum of Vatable Sales, Zero-Rated Sales, VAT Amount, Delivery Fee minus EWT Amount.</div>');
    } else {
        $("#editOrderDueAmount").removeClass('is-invalid');
        $("#editOrderDueAmount").siblings('.invalid-feedback').remove();
    }
    return isValid;
}

// Attach validation to edit order form submission
$('#editOrderForm').on('submit', function (e) {
    if (!validateDueAmountEdit()) {
        toastr.error('Due Amount does not match the sum of Vatable Sales, Zero-Rated Sales, VAT Amount, Delivery Fee minus EWT Amount.');
        e.preventDefault();
        return false;
    }
});

// Optionally, validate live on input change
$('#editOrderVatableSales, #editOrderZeroRatedSales, #editOrderVatAmount, #editOrderDeliveryFee, #editOrderEwtAmount, #editOrderDueAmount').on('input', function() {
    validateDueAmountEdit();
});