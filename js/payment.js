document.addEventListener("DOMContentLoaded", function () {
  const paymentMethods = document.querySelectorAll(
    'input[name="payment_method"]'
  );
  const paymentDetails = document.querySelectorAll(".payment-details");
  
  function updatePaymentForm(selectedValue) {
    paymentDetails.forEach((detail) => {
      const inputs = detail.querySelectorAll("input, select");

      // Hide and disable all inputs
      detail.style.display = "none";
      inputs.forEach((input) => {
        input.disabled = true;
        input.required = false;
      });
    });

    const selectedDetails = document.getElementById(selectedValue + "-details");
    if (selectedDetails) {
      selectedDetails.style.display = "block";

      const inputs = selectedDetails.querySelectorAll("input, select");
      inputs.forEach((input) => {
        input.disabled = false;
        input.required = true;
      });
    }
  }

  paymentMethods.forEach((method) => {
    method.addEventListener("change", function () {
      updatePaymentForm(this.value);
    });
  });

  // Initialize on load (important for default selected method)
  const selected = document.querySelector(
    'input[name="payment_method"]:checked'
  );
  if (selected) {
    updatePaymentForm(selected.value);
  }
});
