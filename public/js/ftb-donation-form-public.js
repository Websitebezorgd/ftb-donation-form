/* Public scripts for FTB Donation Form */
( function () {
    'use strict';

    const config = window.ftbDonationForm || {};
    const i18n   = config.i18n || {};

    document.addEventListener( 'DOMContentLoaded', function () {
        const form = document.getElementById( 'ftb-donation-form' );
        if ( ! form ) return;

        initCustomAmount();
        initStepNavigation();
        initClientValidation();
        focusErrorSummary();
    } );

    /* ── Custom amount toggle ────────────────────────────────────────────── */
    function initCustomAmount() {
        const customRadio   = document.getElementById( 'ftb-amount-custom-radio' );
        const customWrapper = document.getElementById( 'ftb-custom-amount-wrapper' );
        const customInput   = document.getElementById( 'ftb-custom-amount' );

        if ( ! customRadio || ! customWrapper || ! customInput ) return;

        const amountRadios = document.querySelectorAll( 'input[name="ftb_amount"]' );

        function updateCustomVisibility() {
            const isCustom = customRadio.checked;
            customWrapper.classList.toggle( 'donation-form__custom-amount--hidden', ! isCustom );
            customInput.setAttribute( 'aria-required', isCustom ? 'true' : 'false' );
            customRadio.setAttribute( 'aria-expanded', isCustom ? 'true' : 'false' );
            if ( isCustom ) {
                customInput.focus();
            }
        }

        amountRadios.forEach( function ( radio ) {
            radio.addEventListener( 'change', updateCustomVisibility );
        } );

        // When user types in custom amount, select the custom radio
        customInput.addEventListener( 'input', function () {
            if ( ! customRadio.checked ) {
                customRadio.checked = true;
                updateCustomVisibility();
            }
        } );

        // Toggle: clicking Anders again hides the custom amount field
        const customLabel = customRadio.parentElement;
        let wasChecked = false;

        customLabel.addEventListener( 'mousedown', function () {
            wasChecked = customRadio.checked;
        } );

        customLabel.addEventListener( 'click', function () {
            if ( wasChecked ) {
                setTimeout( function () {
                    customRadio.checked = false;
                    updateCustomVisibility();
                }, 0 );
            }
        } );

        customRadio.addEventListener( 'keydown', function ( e ) {
            if ( e.key === ' ' && customRadio.checked ) {
                e.preventDefault();
                customRadio.checked = false;
                updateCustomVisibility();
            }
        } );

        // Escape or Shift+Tab from the custom amount input closes the field
        customInput.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' ) {
                e.preventDefault();
                customRadio.checked = false;
                updateCustomVisibility();
                customRadio.focus();
            } else if ( e.key === 'Tab' && e.shiftKey ) {
                customRadio.checked = false;
                updateCustomVisibility();
            }
        } );

        // Run once on init (handles server-side re-render with 'custom' selected)
        updateCustomVisibility();
    }

    /* ── Step navigation ────────────────────────────────────────────────── */
    function initStepNavigation() {
        const step1   = document.getElementById( 'ftb-step-1' );
        const step2   = document.getElementById( 'ftb-step-2' );
        const nextBtn = document.getElementById( 'ftb-next-button' );
        const prevBtn = document.getElementById( 'ftb-previous-button' );

        if ( ! step1 || ! step2 || ! nextBtn || ! prevBtn ) return;

        nextBtn.addEventListener( 'click', function () {
            const errors = collectStep1Errors();
            if ( Object.keys( errors ).length > 0 ) {
                renderErrors( errors );
                return;
            }
            step1.hidden = true;
            step2.hidden = false;
            updateStepIndicator( 2 );
            ( step2.querySelector( '[tabindex="-1"]' ) || step2.querySelector( 'legend' ) ).focus();
        } );

        prevBtn.addEventListener( 'click', function () {
            step2.hidden = true;
            step1.hidden = false;
            updateStepIndicator( 1 );
            ( step1.querySelector( 'input, button, select, textarea' ) || step1 ).focus();
        } );

        const startStep = parseInt( form.dataset.startStep, 10 ) || 1;
        if ( startStep === 2 ) {
            step1.hidden = true;
            step2.hidden = false;
            updateStepIndicator( 2 );
        }
    }

    function collectStep1Errors() {
        const errors = {};

        const freqSelected = document.querySelector( 'input[name="ftb_frequency"]:checked' );
        if ( ! freqSelected ) {
            errors.frequency = i18n.errorFrequency || 'Kies een frequentie.';
        }

        const amountSelected = document.querySelector( 'input[name="ftb_amount"]:checked' );
        if ( ! amountSelected ) {
            errors.amount = i18n.errorAmount || 'Kies een bedrag.';
        } else if ( amountSelected.value === 'custom' ) {
            const customInput = document.getElementById( 'ftb-custom-amount' );
            const val = customInput ? parseFloat( customInput.value.replace( ',', '.' ) ) : NaN;
            if ( isNaN( val ) || val < 0.01 ) {
                errors.amount = i18n.errorCustom || 'Vul een bedrag in van minimaal €0,01.';
            }
        }

        return errors;
    }

    function updateStepIndicator( stepNumber ) {
        document.querySelectorAll( '[aria-current="step"]' ).forEach( function ( el ) {
            el.removeAttribute( 'aria-current' );
        } );
        const current = document.querySelector( '[data-step="' + stepNumber + '"]' );
        if ( current ) current.setAttribute( 'aria-current', 'step' );
    }

    /* ── Client-side validation ──────────────────────────────────────────── */
    function initClientValidation() {
        const form = document.getElementById( 'ftb-donation-form' );
        if ( ! form ) return;

        form.addEventListener( 'submit', function ( event ) {
            const errors = collectErrors();

            if ( Object.keys( errors ).length > 0 ) {
                event.preventDefault();
                renderErrors( errors );
                focusFirstError();
            }
        } );
    }

    function collectErrors() {
        const errors = {};

        // Frequency
        const freqSelected = document.querySelector( 'input[name="ftb_frequency"]:checked' );
        if ( ! freqSelected ) {
            errors.frequency = i18n.errorFrequency || 'Kies een frequentie.';
        }

        // Amount
        const amountSelected = document.querySelector( 'input[name="ftb_amount"]:checked' );
        if ( ! amountSelected ) {
            errors.amount = i18n.errorAmount || 'Kies een bedrag.';
        } else if ( amountSelected.value === 'custom' ) {
            const customInput = document.getElementById( 'ftb-custom-amount' );
            const val = customInput ? parseFloat( customInput.value.replace( ',', '.' ) ) : NaN;
            if ( isNaN( val ) || val < 0.01 ) {
                errors.amount = i18n.errorCustom || 'Vul een bedrag in van minimaal €0,01.';
            }
        }

        // Name
        const nameInput = document.getElementById( 'ftb-name' );
        if ( nameInput && nameInput.value.trim() === '' ) {
            errors.name = i18n.errorName || 'Vul je volledige naam in.';
        }

        // Email
        const emailInput = document.getElementById( 'ftb-email' );
        if ( emailInput ) {
            const email = emailInput.value.trim();
            if ( email === '' || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
                errors.email = i18n.errorEmail || 'Vul een geldig e-mailadres in.';
            }
        }

        // GDPR
        const gdprInput = document.getElementById( 'ftb-gdpr' );
        if ( gdprInput && ! gdprInput.checked ) {
            errors.gdpr = i18n.errorGdpr || 'Je moet akkoord gaan met de privacyverklaring om te doneren.';
        }

        return errors;
    }

    function renderErrors( errors ) {
        // Remove any previous JS-injected errors
        document.querySelectorAll( '.donation-form__error--js' ).forEach( function ( el ) {
            el.remove();
        } );
        const existingSummary = document.getElementById( 'ftb-error-summary' );
        if ( existingSummary ) existingSummary.remove();

        // Mark invalid fields
        const fieldMap = {
            frequency : { container: document.getElementById( 'ftb-frequency' ),      inputId: null },
            amount    : { container: document.getElementById( 'ftb-amount' ),          inputId: null },
            name      : { container: document.getElementById( 'ftb-name' ),            inputId: 'ftb-name' },
            email     : { container: document.getElementById( 'ftb-email' ),           inputId: 'ftb-email' },
            gdpr      : { container: document.getElementById( 'ftb-gdpr' ) ? document.getElementById( 'ftb-gdpr' ).closest( 'label' ) : null, inputId: 'ftb-gdpr' },
        };

        Object.keys( errors ).forEach( function ( field ) {
            const mapping = fieldMap[ field ];
            if ( ! mapping ) return;

            // Mark the input/container as invalid
            const input = mapping.inputId ? document.getElementById( mapping.inputId ) : null;
            if ( input ) {
                input.setAttribute( 'aria-invalid', 'true' );
                input.setAttribute( 'aria-describedby', 'ftb-' + field + '-error' );
                input.classList.add( 'donation-form__input--error' );
            }

            // Inject error paragraph after the container
            const target = mapping.container ? mapping.container.parentElement || mapping.container : null;
            if ( target ) {
                const errEl = document.createElement( 'p' );
                errEl.className  = 'donation-form__error donation-form__error--js';
                errEl.id         = 'ftb-' + field + '-error';
                errEl.textContent = errors[ field ];

                const afterEl = mapping.container.nextSibling;
                target.insertBefore( errEl, afterEl );
            }
        } );

        // Inject error summary at top of form
        const summary = buildErrorSummary( errors );
        const form    = document.getElementById( 'ftb-donation-form' );
        form.insertBefore( summary, form.firstChild );
        summary.focus();
    }

    function buildErrorSummary( errors ) {
        const div = document.createElement( 'div' );
        div.className  = 'donation-form__error-summary';
        div.id         = 'ftb-error-summary';
        div.setAttribute( 'role', 'alert' );
        div.setAttribute( 'tabindex', '-1' );

        const title = document.createElement( 'p' );
        title.className   = 'donation-form__error-summary-title';
        title.textContent  = i18n.errorSummary || 'Controleer de volgende fouten:';
        div.appendChild( title );

        const ul = document.createElement( 'ul' );
        ul.className = 'donation-form__error-list';
        Object.keys( errors ).forEach( function ( field ) {
            const li = document.createElement( 'li' );
            const a  = document.createElement( 'a' );
            a.href        = '#ftb-' + field;
            a.textContent = errors[ field ];
            li.appendChild( a );
            ul.appendChild( li );
        } );
        div.appendChild( ul );

        return div;
    }

    function focusFirstError() {
        const summary = document.getElementById( 'ftb-error-summary' );
        if ( summary ) {
            summary.focus();
        }
    }

    /* ── Focus error summary on server-side errors (page reload) ─────────── */
    function focusErrorSummary() {
        const summary = document.getElementById( 'ftb-error-summary' );
        if ( summary ) {
            summary.focus();
        }
    }

} )();
