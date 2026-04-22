/* FTB Donation Form – Admin scripts */
(function ($) {
    'use strict';

    /**
     * Show/hide conditional fields based on a select value.
     *
     * Fields wrapped in .ftb-conditional[data-show-when="fieldId=value"]
     * are shown only when the referenced select matches the expected value.
     */
    function updateConditionalFields() {
        $('.ftb-conditional').each(function () {
            var condition = $(this).data('show-when'); // e.g. "ftb_post_payment_behavior=message"
            if ( ! condition ) return;

            var parts    = condition.split('=');
            var fieldId  = parts[0];
            var expected = parts[1];
            var $field   = $('#' + fieldId);
            var actual   = $field.is(':checkbox') ? ( $field.is(':checked') ? '1' : '0' ) : $field.val();
            var $row     = $(this).closest('tr');

            if ( actual === expected ) {
                $(this).addClass('is-visible');
                $row.removeClass('ftb-row-hidden');
            } else {
                $(this).removeClass('is-visible');
                $row.addClass('ftb-row-hidden');
            }
        });
    }

    $(document).ready(function () {
        // Run on page load to reflect saved value
        updateConditionalFields();

        // Re-run whenever any select or checkbox changes
        $('select, input[type="checkbox"]').on('change', updateConditionalFields);
    });

}(jQuery));
