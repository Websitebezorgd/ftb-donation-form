/* FTB Donation Form – Admin scripts */
(function ($) {
    "use strict";

    /**
     * Show/hide conditional fields based on a select value.
     *
     * Fields wrapped in .ftb-conditional[data-show-when="fieldId=value"]
     * are shown only when the referenced select matches the expected value.
     * Separate several conditions with "|" to show the field when any of them
     * matches, e.g. "field_a=1|field_b=1".
     */
    function matchesCondition(condition) {
        var parts = condition.split("=");
        var $field = $("#" + parts[0]);
        // Checkboxes and radio buttons count as "1" when checked, "0" otherwise.
        var actual = $field.is(":checkbox, :radio")
            ? $field.is(":checked")
                ? "1"
                : "0"
            : $field.val();

        return actual === parts[1];
    }

    function updateConditionalFields() {
        $(".ftb-conditional").each(function () {
            var condition = $(this).data("show-when"); // e.g. "ftb_post_payment_behavior=message"
            if (!condition) {
                return;
            }

            var $row = $(this).closest("tr");

            if (condition.split("|").some(matchesCondition)) {
                $(this).addClass("is-visible").prop("hidden", false);
                $row.removeClass("ftb-row-hidden");
            } else {
                $(this).removeClass("is-visible").prop("hidden", true);
                $row.addClass("ftb-row-hidden");
            }
        });
    }

    function initEmailPreview(
        subjectInputId,
        bodyTextareaId,
        previewSubjectId,
        previewBodyId,
    ) {
        var $subjectInput = $("#" + subjectInputId);
        var $bodyTextarea = $("#" + bodyTextareaId);
        var $previewSubject = $("#" + previewSubjectId);
        var $previewBody = $("#" + previewBodyId);

        if (!$previewBody.length) {
            return;
        }

        var details = $previewBody.data("details");
        var fallback = $subjectInput.attr("placeholder") || "";

        function update() {
            $previewSubject.text($subjectInput.val() || fallback);
            var custom = $.trim($bodyTextarea.val());
            $previewBody.text(custom ? custom + "\n\n" + details : details);
        }

        $subjectInput.on("input", update);
        $bodyTextarea.on("input", update);
        update();
    }

    function initPrivacySuggestion() {
        var $urlInput = $("#ftb_privacy_url");
        var $suggestion = $("#ftb-privacy-suggestion");
        var $copyBtn = $("#ftb-copy-privacy-text");

        if (!$suggestion.length) {
            return;
        }

        function updateVisibility() {
            $suggestion.prop("hidden", !$urlInput.val().trim());
        }

        $urlInput.on("input", updateVisibility);

        $copyBtn.on("click", function () {
            var label = $copyBtn.data("labelCopied");
            var original = $copyBtn.text();
            var text = document.getElementById("ftb-privacy-copy-text").value;

            function onSuccess() {
                $copyBtn.text(label);
                setTimeout(function () {
                    $copyBtn.text(original);
                }, 2000);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(onSuccess);
            } else {
                var $temp = $("<textarea>")
                    .val(text)
                    .css({ position: "fixed", top: 0, left: "-9999px" })
                    .appendTo("body");
                $temp[0].select();
                try {
                    document.execCommand("copy");
                    onSuccess();
                } catch (e) {}
                $temp.remove();
            }
        });
    }

    function initStylePicker() {
        var $colorInput = $("#ftb_form_primary_color");
        var $preview = $("#ftb-style-preview");
        var defaultColor = "#c42e31";

        if (!$colorInput.length || !$preview.length) {
            return;
        }

        // Same rule as FTB_Donation_Form_Public::primary_color_tokens(): white or black,
        // whichever has the higher WCAG contrast (one of the two always reaches 4.5:1).
        function onColor(hex) {
            var luminance = [1, 3, 5]
                .map(function (i) {
                    var v = parseInt(hex.slice(i, i + 2), 16) / 255;
                    return v <= 0.03928
                        ? v / 12.92
                        : Math.pow((v + 0.055) / 1.055, 2.4);
                })
                .reduce(function (sum, v, i) {
                    return sum + v * [0.2126, 0.7152, 0.0722][i];
                }, 0);
            var onWhite = 1.05 / (luminance + 0.05);
            var onBlack = (luminance + 0.05) / 0.05;
            return onWhite >= onBlack ? "#ffffff" : "#000000";
        }

        function applyColor(color) {
            $preview[0].style.setProperty("--ftb-preview-color", color);
            $preview[0].style.setProperty(
                "--ftb-preview-on-color",
                onColor(color),
            );
        }

        $colorInput.wpColorPicker({
            defaultColor: defaultColor,
            change: function (event, ui) {
                applyColor(ui.color.toString());
            },
            clear: function () {
                applyColor(defaultColor);
            },
        });

        // Iris generates empty <a> drag handles — hide them from screen readers.
        $colorInput
            .closest(".wp-picker-container")
            .find(".iris-square-value, .iris-strip-value")
            .attr("aria-hidden", "true")
            .attr("tabindex", "-1")
            .attr("role", "presentation");

        $(document).on("click", ".ftb-color-swatch", function () {
            var color = $(this).data("color");
            $colorInput.wpColorPicker("color", color);
            applyColor(color);
        });

        applyColor($colorInput.val() || defaultColor);
    }

    $(document).ready(function () {
        // Run on page load to reflect saved value
        updateConditionalFields();

        // Re-run whenever any select, checkbox, radio, or text/url/email input changes
        $('select, input[type="checkbox"], input[type="radio"]').on(
            "change",
            updateConditionalFields,
        );
        $('input[type="text"], input[type="url"], input[type="email"]').on(
            "input",
            updateConditionalFields,
        );

        // Email preview — live update as you type
        initEmailPreview(
            "ftb_email_donor_subject",
            "ftb_email_donor_body",
            "ftb_donor_preview_subject",
            "ftb_donor_preview_body",
        );

        // Privacy suggestion — show when URL is filled in
        initPrivacySuggestion();

        // Stijlkiezer — live preview
        initStylePicker();

        // API key — toggle password visibility
        $(".ftb-toggle-password").on("click", function () {
            var $btn = $(this);
            var $input = $("#" + $btn.attr("aria-controls"));
            var $icon = $btn.find(".dashicons");
            var isHidden = $input.attr("type") === "password";

            $input.attr("type", isHidden ? "text" : "password");
            $icon
                .toggleClass("dashicons-visibility", !isHidden)
                .toggleClass("dashicons-hidden", isHidden);
            $btn
                .attr("aria-pressed", isHidden ? "true" : "false")
                .attr(
                    "aria-label",
                    isHidden
                        ? ftbAdmin.hideApiKey
                        : ftbAdmin.showApiKey,
                );
        });
    });
})(jQuery);
