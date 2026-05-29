/* FTB Donation Form – Admin scripts */
(function ($) {
    "use strict";

    /**
     * Show/hide conditional fields based on a select value.
     *
     * Fields wrapped in .ftb-conditional[data-show-when="fieldId=value"]
     * are shown only when the referenced select matches the expected value.
     */
    function updateConditionalFields() {
        $(".ftb-conditional").each(function () {
            var condition = $(this).data("show-when"); // e.g. "ftb_post_payment_behavior=message"
            if (!condition) {
                return;
            }

            var parts = condition.split("=");
            var fieldId = parts[0];
            var expected = parts[1];
            var $field = $("#" + fieldId);
            var actual = $field.is(":checkbox")
                ? $field.is(":checked")
                    ? "1"
                    : "0"
                : $field.val();
            var $row = $(this).closest("tr");

            if (actual === expected) {
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

        function onColor(hex) {
            var r = parseInt(hex.slice(1, 3), 16);
            var g = parseInt(hex.slice(3, 5), 16);
            var b = parseInt(hex.slice(5, 7), 16);
            return 0.299 * r + 0.587 * g + 0.114 * b > 128
                ? "#1a1a1a"
                : "#ffffff";
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

        // Re-run whenever any select, checkbox, or text/url input changes
        $('select, input[type="checkbox"]').on(
            "change",
            updateConditionalFields,
        );
        $('input[type="text"], input[type="url"]').on(
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
