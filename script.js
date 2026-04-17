// ─────────────────────────────────────────────
// STEP NAVIGATION
// ─────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  const steps = document.querySelectorAll(".ftb-donation-form__step");
  const nextBtn = document.querySelector(".ftb-donation-form__button--next");
  const prevBtn = document.querySelector(
    ".ftb-donation-form__button--previous",
  );
  const stepIndicators = document.querySelectorAll(
    ".ftb-donation-form__steps li",
  );

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

    const summary = document.querySelector(".ftb-donation-form__error-summary");
    if (summary) summary.hidden = true;
  }

  function focusFirstInput(stepIndex) {
    const activeStep = steps[stepIndex];
    const firstField = activeStep.querySelector(
      "input, select, textarea, button",
    );
    if (firstField) firstField.focus();
  }

  // ─────────────────────────────────────────────
  // VALIDATION
  // ─────────────────────────────────────────────

  function validateStep(stepIndex) {
    let isValid = true;

    const activeStep = steps[stepIndex];
    const errors = activeStep.querySelectorAll(".ftb-donation-form__error");

    errors.forEach((e) => (e.hidden = true));

    // STEP 1
    if (stepIndex === 0) {
      const frequency = activeStep.querySelector(
        'input[name="frequency"]:checked',
      );
      const amount = activeStep.querySelector('input[name="amount"]:checked');
      const customAmount = activeStep.querySelector("#custom-amount");

      if (!frequency) {
        activeStep.querySelector("#error-frequency").hidden = false;
        isValid = false;
      }

      if (!amount) {
        activeStep.querySelector("#error-amount").hidden = false;
        isValid = false;
      }

      if (amount?.value === "other") {
        if (!customAmount.value || Number(customAmount.value) < 1) {
          activeStep.querySelector("#error-amount").hidden = false;
          isValid = false;
        }
      }
    }

    // STEP 2
    if (stepIndex === 1) {
      console.log("STEP 2 VALIDATION");

      const name = activeStep.querySelector("#name");
      const email = activeStep.querySelector("#email");
      const privacy = activeStep.querySelector("#privacy");

      console.log("Values:", {
        name: name?.value,
        email: email?.value,
        privacy: privacy?.checked,
      });

      const errorName = activeStep.querySelector("#error-name");
      const errorEmail = activeStep.querySelector("#error-email");
      const errorPrivacy = activeStep.querySelector("#error-privacy");

      if (!name?.value.trim()) {
        console.log("NAME INVALID");
        errorName.hidden = false;
        isValid = false;
      }

      if (!email?.value.trim() || !email.checkValidity()) {
        console.log("EMAIL INVALID");
        errorEmail.hidden = false;
        isValid = false;
      }

      if (!privacy?.checked) {
        console.log("PRIVACY INVALID");
        errorPrivacy.hidden = false;
        isValid = false;
      }
    }

    updateErrorSummary(stepIndex);

    return isValid;
  }

  // ─────────────────────────────────────────────
  // STEP BUTTONS
  // ─────────────────────────────────────────────

  nextBtn?.addEventListener("click", () => {
    if (!validateStep(currentStep)) return;
    goToStep(currentStep + 1);
  });

  prevBtn?.addEventListener("click", () => {
    if (currentStep > 0) goToStep(currentStep - 1);
  });

  // ─────────────────────────────────────────────
  // AMOUNT + CUSTOM AMOUNT MODULE (clean + a11y-safe)
  // ─────────────────────────────────────────────

  const amountRadios = document.querySelectorAll('input[name="amount"]');
  const customAmountBox = document.querySelector(
    ".ftb-donation-form__custom-amount",
  );
  const customAmountInput = document.querySelector("#custom-amount");
  const customRadio = document.querySelector("#amount-4");

  function setCustomAmountVisible(isVisible) {
    if (!customAmountBox || !customAmountInput || !customRadio) return;

    customAmountBox.classList.toggle(
      "ftb-donation-form__custom-amount--hidden",
      !isVisible,
    );

    customRadio.setAttribute("aria-expanded", String(isVisible));
    customAmountInput.setAttribute("aria-required", String(isVisible));

    if (isVisible) {
      setTimeout(() => customAmountInput.focus(), 0);
    } else {
      customAmountInput.value = "";
    }
  }

  const isCustom = customRadio.checked;

  customAmountInput.required = isCustom;
  customAmountInput.setAttribute("aria-required", isCustom ? "true" : "false");

  // radio change
  amountRadios.forEach((radio) => {
    radio.addEventListener("change", () => {
      setCustomAmountVisible(radio.value === "other" && radio.checked);

      document.querySelector("#error-amount").hidden = true;
    });
  });

  // typing in custom amount
  customAmountInput?.addEventListener("input", () => {
    if (Number(customAmountInput.value) > 0) {
      document.querySelector("#error-amount").hidden = true;
    }
  });

  // ─────────────────────────────────────────────
  // FREQUENCY + PRIVACY LIVE ERRORS
  // ─────────────────────────────────────────────

const nameInput = document.querySelector("#name");
const emailInput = document.querySelector("#email");
const privacyCheckbox = document.querySelector("#privacy");

// NAME
nameInput?.addEventListener("input", () => {
  if (nameInput.value.trim()) {
    document.querySelector("#error-name").hidden = true;
  }
});

// EMAIL (belangrijk: checkValidity)
emailInput?.addEventListener("input", () => {
  if (emailInput.value.trim() && emailInput.checkValidity()) {
    document.querySelector("#error-email").hidden = true;
  }
});

// PRIVACY
privacyCheckbox?.addEventListener("change", () => {
  if (privacyCheckbox.checked) {
    document.querySelector("#error-privacy").hidden = true;
  }
});

  // ─────────────────────────────────────────────
  // ERROR SUMMARY
  // ─────────────────────────────────────────────

  function updateErrorSummary(stepIndex) {
    const activeStep = steps[stepIndex];
    const summary = document.querySelector(".ftb-donation-form__error-summary");
    const list = summary?.querySelector(".ftb-donation-form__error-list");

    if (!summary || !list) return;

    list.innerHTML = "";

    const errors = activeStep.querySelectorAll(
      ".ftb-donation-form__error:not([hidden])",
    );

    if (errors.length === 0) {
      summary.hidden = true;
      return;
    }

    errors.forEach((error) => {
      const id = error.id.replace("error-", "");

      const li = document.createElement("li");
      li.innerHTML = `<a href="#${id}">${error.textContent}</a>`;

      list.appendChild(li);
    });

    summary.hidden = false;
  }

  const form = document.querySelector(".ftb-donation-form__form");

form?.addEventListener("submit", (e) => {
  console.log("SUBMIT FIRED");

  const isValid = validateStep(currentStep);

  if (!isValid) {
    e.preventDefault();
  }
});
});
