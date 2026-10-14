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
  // Plain-text step indicator used by the Minimal style.
  const stepTexts = document.querySelectorAll(
    ".ftb-donation-form__step-text [data-step]",
  );
  const form = document.querySelector(".ftb-donation-form__form");

  const startStep = parseInt(form?.dataset.startStep ?? "1", 10) - 1;
  let currentStep = startStep;

  function goToStep(index, moveFocus = true) {
    steps.forEach((step, i) => {
      const active = i === index;
      step.classList.toggle("is-active", active);
      step.hidden = !active;
    });

    stepIndicators.forEach((indicator, i) => {
      indicator.classList.toggle("is-active", i === index);
      indicator.setAttribute("aria-current", i === index ? "step" : "false");
    });
    stepTexts.forEach((text, i) => {
      text.hidden = i !== index;
    });

    currentStep = index;
    if (moveFocus) focusFirstInput(index);
  }

  function focusFirstInput(stepIndex) {
    const activeStep = steps[stepIndex];
    const introEl = activeStep.querySelector('[tabindex="-1"]');
    if (introEl) {
      introEl.focus();
      return;
    }
    const firstField = activeStep.querySelector("input, select, textarea, button");
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
  stepTexts.forEach((text, i) => {
    text.hidden = i !== startStep;
  });

  // ─────────────────────────────────────────────
  // ERROR SUMMARY MANAGEMENT
  // ─────────────────────────────────────────────

  // Radio groups have no element with the field's own ID, so error summary links
  // point to the control the user has to change. Error IDs are "ftb-{field}-error".
  function errorTargetId(errorId) {
    const fieldId = errorId.replace(/-error$/, "");

    if (fieldId === "ftb-frequency") {
      const radio =
        document.querySelector('input[name="ftb_frequency"]:checked') ??
        document.querySelector('input[name="ftb_frequency"]');
      return radio?.id ?? "";
    }

    if (fieldId === "ftb-amount") {
      const custom = document.querySelector("#ftb-custom-amount");
      const customBox = document.querySelector("#ftb-custom-amount-wrapper");
      if (custom && !customBox?.hidden) return custom.id;
      const radio =
        document.querySelector('input[name="ftb_amount"]:checked') ??
        document.querySelector('input[type="radio"][name="ftb_amount"]');
      return radio?.id ?? "";
    }

    return fieldId;
  }

  function syncErrorSummary(stepIndex) {
    const activeStep = steps[stepIndex];
    const summary = document.querySelector(".ftb-donation-form__error-summary");
    const list = summary?.querySelector(".ftb-donation-form__error-list");

    if (!summary || !list) return;

    const visibleErrors = activeStep.querySelectorAll(
      ".ftb-donation-form__error:not([hidden])",
    );

    list.replaceChildren();

    if (visibleErrors.length === 0) {
      summary.hidden = true;
      return;
    }

    visibleErrors.forEach((error) => {
      const li = document.createElement("li");
      const targetId = errorTargetId(error.id);
      if (targetId && document.getElementById(targetId)) {
        const link = document.createElement("a");
        link.href = `#${targetId}`;
        link.textContent = error.textContent;
        li.appendChild(link);
      } else {
        li.textContent = error.textContent;
      }
      list.appendChild(li);
    });

    summary.hidden = false;
  }

  // Error summary links: open the step the field is on and move focus to it.
  // A plain anchor jump would only scroll, and cannot reach a hidden step.
  document
    .querySelector(".ftb-donation-form__error-summary")
    ?.addEventListener("click", (e) => {
      const link = e.target.closest("a[href^='#']");
      const target = link && document.getElementById(link.hash.slice(1));
      if (!target) return;

      e.preventDefault();
      const stepIndex = Array.from(steps).indexOf(
        target.closest(".ftb-donation-form__step"),
      );
      if (stepIndex !== -1 && stepIndex !== currentStep) {
        goToStep(stepIndex, false);
      }
      target.focus();
      target.scrollIntoView({ block: "center" });
    });

  // ─────────────────────────────────────────────
  // VALIDATION
  // ─────────────────────────────────────────────

  function validateStep(stepIndex) {
    let isValid = true;
    const activeStep = steps[stepIndex];
    const errors = activeStep.querySelectorAll(".ftb-donation-form__error");

    // Reset all errors and aria-invalid
    errors.forEach((e) => (e.hidden = true));
    activeStep.querySelectorAll("input").forEach((el) => el.setAttribute("aria-invalid", "false"));

    // STEP 1
    if (stepIndex === 0) {
      const showPresets = ftbDonationForm?.showPresetAmounts ?? true;
      const frequency = activeStep.querySelector(
        'input[name="ftb_frequency"]:checked',
      );
      const customAmount = activeStep.querySelector("#ftb-custom-amount");
      const amountError = activeStep.querySelector("#ftb-amount-error");
      const minAmount = ftbDonationForm?.minCustomAmount ?? 1;

      const frequencyError = activeStep.querySelector("#ftb-frequency-error");
      if (frequencyError && !frequency) {
        frequencyError.hidden = false;
        activeStep.querySelectorAll('input[name="ftb_frequency"]').forEach((r) => r.setAttribute("aria-invalid", "true"));
        isValid = false;
      }

      if (!showPresets) {
        // Only custom amount visible — validate the input directly
        if (!customAmount?.value || Number(customAmount.value) < minAmount) {
          amountError.textContent = ftbDonationForm?.i18n?.errorCustom ?? amountError.textContent;
          amountError.hidden = false;
          customAmount?.setAttribute("aria-invalid", "true");
          isValid = false;
        }
      } else {
        const amount = activeStep.querySelector('input[name="ftb_amount"]:checked');
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
            customAmount?.setAttribute("aria-invalid", "true");
            isValid = false;
          }
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
        name.setAttribute("aria-invalid", "true");
        isValid = false;
      }

      if (!email?.value.trim() || !email.checkValidity()) {
        activeStep.querySelector("#ftb-email-error").hidden = false;
        email.setAttribute("aria-invalid", "true");
        isValid = false;
      }

      if (!gdpr?.checked) {
        activeStep.querySelector("#ftb-gdpr-error").hidden = false;
        gdpr.setAttribute("aria-invalid", "true");
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

  const showPresets = ftbDonationForm?.showPresetAmounts ?? true;

  function setCustomAmountVisible(isVisible) {
    if (!customAmountBox || !customAmountInput) return;
    if (!showPresets) return; // always visible when no presets

    customAmountBox.classList.toggle(
      "ftb-donation-form__custom-amount--hidden",
      !isVisible,
    );
    customAmountBox.hidden = !isVisible;

    customAmountInput.setAttribute("aria-required", String(isVisible));
    customAmountInput.required = isVisible;

    if (isVisible) {
      customAmountInput.setAttribute("aria-invalid", "false");
      setTimeout(() => customAmountInput.focus(), 0);
    } else {
      customAmountInput.value = "";
    }
  }

  if (customAmountInput) {
    if (!showPresets) {
      // Custom amount is always required when it's the only option
      customAmountInput.required = true;
      customAmountInput.setAttribute("aria-required", "true");
    } else if (customRadio) {
      const isCustom = customRadio.checked;
      customAmountInput.required = isCustom;
      customAmountInput.setAttribute("aria-required", String(isCustom));
    }
  }

  amountRadios.forEach((radio) => {
    radio.addEventListener("change", () => {
      if (showPresets) {
        setCustomAmountVisible(radio.value === "custom" && radio.checked);
      }
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
      customAmountInput.setAttribute("aria-invalid", "false");
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
      nameInput.setAttribute("aria-invalid", "false");
      syncErrorSummary(currentStep);
    }
  });

  emailInput?.addEventListener("input", () => {
    if (emailInput.value.trim() && emailInput.checkValidity()) {
      const emailError = document.querySelector("#ftb-email-error");
      if (emailError) emailError.hidden = true;
      emailInput.setAttribute("aria-invalid", "false");
      syncErrorSummary(currentStep);
    }
  });

  gdprCheckbox?.addEventListener("change", () => {
    if (gdprCheckbox.checked) {
      const gdprError = document.querySelector("#ftb-gdpr-error");
      if (gdprError) gdprError.hidden = true;
      gdprCheckbox.setAttribute("aria-invalid", "false");
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

  // ─────────────────────────────────────────────
  // FOCUS ON PAGE LOAD
  // ─────────────────────────────────────────────

  // After a reload with server errors or a return from Mollie, move focus to the
  // message so screen readers read it (role="alert" is not announced on page load).
  const loadSummary = document.querySelector(".ftb-donation-form__error-summary");
  const paymentMessage = document.querySelector(".ftb-donation-form__payment-message");
  if (loadSummary && !loadSummary.hidden) {
    loadSummary.focus();
  } else if (paymentMessage) {
    paymentMessage.focus();
  }
});
