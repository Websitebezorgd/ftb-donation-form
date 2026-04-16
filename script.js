// Step navigation logic for the donation form
const steps = document.querySelectorAll(".ftb-donation-form__step");
const nextBtn = document.querySelector(".ftb-donation-form__button--next");
const prevBtn = document.querySelector(".ftb-donation-form__button--previous");
const stepIndicators = document.querySelectorAll(".ftb-donation-form__steps li");

let currentStep = 0;

function goToStep(index) {
  steps.forEach((step, i) => {
    step.classList.toggle("is-active", i === index);
  });

  stepIndicators.forEach((indicator, i) => {
    indicator.classList.toggle("is-active", i === index);
    indicator.setAttribute("aria-current", i === index ? "step" : "false");
  });

  currentStep = index;

  focusFirstInput(index);
}

function focusFirstInput(stepIndex) {
  const activeStep = steps[stepIndex];

  const firstField = activeStep.querySelector(
    "input, select, textarea, button"
  );

  if (firstField) {
    firstField.focus();
  }
}

nextBtn.addEventListener("click", () => {
  const isValid = validateStep1();

  if (!isValid) {
    return; // Stop if validation fails
  }

  goToStep(currentStep + 1);
});

prevBtn.addEventListener("click", () => {
  if (currentStep > 0) {
    goToStep(currentStep - 1);
  }
});

// Check if the user has selected a frequency and amount, and if the custom amount (if selected) is valid
function validateStep1() {
  const frequency = document.querySelector('input[name="frequency"]:checked');
  const amount = document.querySelector('input[name="amount"]:checked');
  const customAmount = document.querySelector("#custom-amount");

  let isValid = true;

  // reset errors
  document.querySelector("#error-frequency").hidden = true;
  document.querySelector("#error-amount").hidden = true;

  // frequency check
  if (!frequency) {
    document.querySelector("#error-frequency").hidden = false;
    isValid = false;
  }

  // amount check
  if (!amount) {
    document.querySelector("#error-amount").hidden = false;
    isValid = false;
  }

  // custom amount check
  if (amount?.value === "other") {
    if (!customAmount.value || Number(customAmount.value) < 1) {
      isValid = false;
    }
  }

  return isValid;
}

const amountRadios = document.querySelectorAll('input[name="amount"]');
const customAmountBox = document.querySelector(".ftb-donation-form__custom-amount");

amountRadios.forEach((radio) => {
  radio.addEventListener("change", () => {
    if (radio.value === "other") {
      customAmountBox.classList.remove("ftb-donation-form__custom-amount--hidden");
    } else {
      customAmountBox.classList.add("ftb-donation-form__custom-amount--hidden");
    }
  });
});

// Hide error messages when the user changes their selection
document.querySelectorAll('input[name="frequency"]').forEach((radio) => {
  radio.addEventListener("change", () => {
    document.querySelector("#error-frequency").hidden = true;
  });
});

document.querySelectorAll('input[name="amount"]').forEach((radio) => {
  radio.addEventListener("change", () => {
    document.querySelector("#error-amount").hidden = true;
  });
});

const customAmount = document.querySelector("#custom-amount");

if (customAmount) {
  customAmount.addEventListener("input", () => {
    if (Number(customAmount.value) > 0) {
      document.querySelector("#error-amount").hidden = true;
    }
  });
};