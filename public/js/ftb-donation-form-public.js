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
  const form = document.querySelector(".ftb-donation-form__form");

  const startStep = parseInt(form?.dataset.startStep ?? "1", 10) - 1;
  let currentStep = startStep;

  function goToStep(index) {
    steps.forEach((step, i) => {
      const active = i === index;
      step.classList.toggle("is-active", active);
      step.hidden = !active;
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
      "input, select, textarea, button",
    );
    if (firstField) firstField.focus();
  }

  // Initialise to the correct starting step without triggering focus
  steps.forEach((step, i) => {
    const active = i === startStep;
    step.classList.toggle("is-active", active);
    step.hidden = !active;
  });
  stepIndicators.forEach((indicator, i) => {
    indicator.classList.toggle("is-active", i === startStep);
    indicator.setAttribute(
      "aria-current",
      i === startStep ? "step" : "false",
    );
  });

  // ─────────────────────────────────────────────
  // ERROR SUMMARY MANAGEMENT
  // ─────────────────────────────────────────────

  function syncErrorSummary(stepIndex) {
    const activeStep = steps[stepIndex];
    const summary = document.querySelector(".ftb-donation-form__error-summary");
    const list = summary?.querySelector(".ftb-donation-form__error-list");

    if (!summary || !list) return;

    const visibleErrors = activeStep.querySelectorAll(
      ".ftb-donation-form__error:not([hidden])",
    );

    list.innerHTML = "";

    if (visibleErrors.length === 0) {
      summary.hidden = true;
      return;
    }

    visibleErrors.forEach((error) => {
      // Error IDs follow the pattern "ftb-{field}-error"; derive field ID by stripping "-error"
      const fieldId = error.id.replace(/-error$/, "");
      const li = document.createElement("li");
      li.innerHTML = `<a href="#${fieldId}">${error.textContent}</a>`;
      list.appendChild(li);
    });

    summary.hidden = false;
  }

  // ─────────────────────────────────────────────
  // VALIDATION
  // ─────────────────────────────────────────────

  function validateStep(stepIndex) {
    let isValid = true;
    const activeStep = steps[stepIndex];
    const errors = activeStep.querySelectorAll(".ftb-donation-form__error");

    // Reset all errors and aria-invalid on radios
    errors.forEach((e) => (e.hidden = true));
    activeStep.querySelectorAll('input[type="radio"]').forEach((r) => r.setAttribute("aria-invalid", "false"));

    // STEP 1
    if (stepIndex === 0) {
      const frequency = activeStep.querySelector(
        'input[name="ftb_frequency"]:checked',
      );
      const amount = activeStep.querySelector(
        'input[name="ftb_amount"]:checked',
      );
      const customAmount = activeStep.querySelector("#ftb-custom-amount");

      const frequencyError = activeStep.querySelector("#ftb-frequency-error");
      if (frequencyError && !frequency) {
        frequencyError.hidden = false;
        activeStep.querySelectorAll('input[name="ftb_frequency"]').forEach((r) => r.setAttribute("aria-invalid", "true"));
        isValid = false;
      }

      const amountError = activeStep.querySelector("#ftb-amount-error");
      const minAmount = ftbDonationForm?.minCustomAmount ?? 1;

      if (!amount) {
        amountError.textContent = ftbDonationForm?.i18n?.errorAmount ?? amountError.textContent;
        amountError.hidden = false;
        activeStep.querySelectorAll('input[name="ftb_amount"]').forEach((r) => r.setAttribute("aria-invalid", "true"));
        isValid = false;
      } else if (amount.value === "custom") {
        if (!customAmount?.value || Number(customAmount.value) < minAmount) {
          amountError.textContent = ftbDonationForm?.i18n?.errorCustom ?? amountError.textContent;
          amountError.hidden = false;
          activeStep.querySelectorAll('input[name="ftb_amount"]').forEach((r) => r.setAttribute("aria-invalid", "true"));
          isValid = false;
        }
      }
    }

    // STEP 2
    if (stepIndex === 1) {
      const name = activeStep.querySelector("#ftb-name");
      const email = activeStep.querySelector("#ftb-email");
      const gdpr = activeStep.querySelector("#ftb-gdpr");

      if (!name?.value.trim()) {
        activeStep.querySelector("#ftb-name-error").hidden = false;
        isValid = false;
      }

      if (!email?.value.trim() || !email.checkValidity()) {
        activeStep.querySelector("#ftb-email-error").hidden = false;
        isValid = false;
      }

      if (!gdpr?.checked) {
        activeStep.querySelector("#ftb-gdpr-error").hidden = false;
        isValid = false;
      }
    }

    syncErrorSummary(stepIndex);

    if (!isValid) {
      const summary = document.querySelector(".ftb-donation-form__error-summary");
      if (summary && !summary.hidden) summary.focus();
    }

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
  // AMOUNT + CUSTOM AMOUNT MODULE
  // ─────────────────────────────────────────────

  const amountRadios = document.querySelectorAll('input[name="ftb_amount"]');
  const customAmountBox = document.querySelector(
    ".ftb-donation-form__custom-amount",
  );
  const customAmountInput = document.querySelector("#ftb-custom-amount");
  const customRadio = document.querySelector("#ftb-amount-custom-radio");

  function setCustomAmountVisible(isVisible) {
    if (!customAmountBox || !customAmountInput || !customRadio) return;

    customAmountBox.classList.toggle(
      "ftb-donation-form__custom-amount--hidden",
      !isVisible,
    );

    customRadio.setAttribute("aria-expanded", String(isVisible));
    customAmountInput.setAttribute("aria-required", String(isVisible));
    customAmountInput.required = isVisible;

    if (isVisible) {
      setTimeout(() => customAmountInput.focus(), 0);
    } else {
      customAmountInput.value = "";
    }
  }

  if (customAmountInput && customRadio) {
    const isCustom = customRadio.checked;
    customAmountInput.required = isCustom;
    customAmountInput.setAttribute("aria-required", String(isCustom));
  }

  amountRadios.forEach((radio) => {
    radio.addEventListener("change", () => {
      setCustomAmountVisible(radio.value === "custom" && radio.checked);
      const amountError = document.querySelector("#ftb-amount-error");
      if (amountError) amountError.hidden = true;
      amountRadios.forEach((r) => r.setAttribute("aria-invalid", "false"));
      syncErrorSummary(currentStep);
    });
  });

  customAmountInput?.addEventListener("input", () => {
    const minAmount = ftbDonationForm?.minCustomAmount ?? 1;
    if (Number(customAmountInput.value) >= minAmount) {
      const amountError = document.querySelector("#ftb-amount-error");
      if (amountError) amountError.hidden = true;
      syncErrorSummary(currentStep);
    }
  });

  // ─────────────────────────────────────────────
  // LIVE ERROR CLEARING
  // ─────────────────────────────────────────────

  const frequencyRadios = document.querySelectorAll(
    'input[name="ftb_frequency"]',
  );
  const nameInput = document.querySelector("#ftb-name");
  const emailInput = document.querySelector("#ftb-email");
  const gdprCheckbox = document.querySelector("#ftb-gdpr");

  frequencyRadios.forEach((radio) => {
    radio.addEventListener("change", () => {
      const freqError = document.querySelector("#ftb-frequency-error");
      if (freqError) freqError.hidden = true;
      frequencyRadios.forEach((r) => r.setAttribute("aria-invalid", "false"));
      syncErrorSummary(currentStep);
    });
  });

  nameInput?.addEventListener("input", () => {
    if (nameInput.value.trim()) {
      const nameError = document.querySelector("#ftb-name-error");
      if (nameError) nameError.hidden = true;
      syncErrorSummary(currentStep);
    }
  });

  emailInput?.addEventListener("input", () => {
    if (emailInput.value.trim() && emailInput.checkValidity()) {
      const emailError = document.querySelector("#ftb-email-error");
      if (emailError) emailError.hidden = true;
      syncErrorSummary(currentStep);
    }
  });

  gdprCheckbox?.addEventListener("change", () => {
    if (gdprCheckbox.checked) {
      const gdprError = document.querySelector("#ftb-gdpr-error");
      if (gdprError) gdprError.hidden = true;
      syncErrorSummary(currentStep);
    }
  });

  // ─────────────────────────────────────────────
  // FORM SUBMIT
  // ─────────────────────────────────────────────

  form?.addEventListener("submit", (e) => {
    if (!validateStep(currentStep)) {
      e.preventDefault();
    }
  });
});
