(function() {
    window.validateCustomerTIN = (tin) => true;

    window.formatCustomerTIN = (tin) => tin;

    window.validatePhilippineNumber = (number) => true;

    window.formatPhilippineNumber = (number) => number;

    window.validateEmail = (email) =>
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);

    window.validateOrderAmount = (amount) => !isNaN(amount) && amount > 0;

    window.validateOrderQuantity = (quantity) =>
        !isNaN(quantity) && quantity > 0 && Number.isInteger(Number(quantity));

    window.validateDeliveryFee = (fee) => !isNaN(fee) && fee >= 0;

    window.validateOrderStatus = (status) => {
    // This will be updated dynamically based on available statuses
    // For now, accept any positive integer
    return !isNaN(status) && parseInt(status) > 0;
};

window.getOrderStatusName = (status) => {
    const statusMap = {
        "1": "Waiting",
        "2": "Graphics Section",
        "3": "Cutting Section", 
        "4": "Sewing Section",
        "5": "Printing Section",
        "6": "Quality Control Section",
        "7": "Packaging Section",
        "8": "Final Checking Section",
        "9": "Completed",
        "10": "Cancelled"
    };
    return statusMap[status] || "Unknown";
};

    window.validatePaymentStatus = (status) => ["0", "1"].includes(status);

    window.validateApprovalStatus = (status) => ["0", "1"].includes(status);

    window.formatCurrency = (amount) => {
        return parseFloat(amount).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    window.calculateVAT = (amount) => {
        return amount > 0 ? Math.round((amount * 0.12) * 100) / 100 : 0.00;
    };

    // Updated to match requirements: dueAmount = totalAmount + vatAmount + deliveryFee
    window.calculateDueAmount = (totalAmount, vatAmount, deliveryFee) =>
        (
            parseFloat(totalAmount) +
            parseFloat(vatAmount) +
            parseFloat(deliveryFee)
        ).toFixed(2);

    // VAT toggle function removed - VAT amount is now always manually editable
})();