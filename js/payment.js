document.addEventListener("DOMContentLoaded", function () {
  const paymentMethods = document.querySelectorAll(
    'input[name="payment_method"]'
  );
  const paymentDetails = document.querySelectorAll(".payment-details");

  paymentMethods.forEach((method) => {
    method.addEventListener("change", function () {
      // Hide all payment details
      paymentDetails.forEach((detail) => {
        detail.style.display = "none";
      });

      // Show selected payment details
      const selectedDetails = document.getElementById(this.value + "-details");
      if (selectedDetails) {
        selectedDetails.style.display = "block";
      }

      // Update required fields
      const inputs = selectedDetails.querySelectorAll("input");
      inputs.forEach((input) => {
        if (this.value === "paypal") {
          input.required = true;
        } else {
          input.required = false;
        }
      });
    });
  });
});
