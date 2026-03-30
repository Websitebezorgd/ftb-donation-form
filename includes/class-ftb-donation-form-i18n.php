<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://for-the-better.nl
 * @since      1.0.0
 *
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 * @author     For The Better <info@for-the-better.nl>
 */
class FTB_Donation_Form_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'ftb-donation-form',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}