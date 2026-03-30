<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://for-the-better.nl
 * @since      1.0.0
 *
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/admin
 * @author     For The Better <info@for-the-better.nl>
 */
class FTB_Donation_Form_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ftb-donation-form-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ftb-donation-form-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Add plugin admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('FTB Donation Form', 'ftb-donation-form'),
            __('Donation Form', 'ftb-donation-form'),
            'manage_options',
            'ftb-donation-form',
            array($this, 'display_plugin_setup_page'),
            'dashicons-heart',
            30
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        include_once 'partials/ftb-donation-form-admin-display.php';
    }
}