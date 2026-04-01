<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://for-the-better.nl
 * @since      1.0.0
 *
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/public
 * @author     For The Better <info@for-the-better.nl>
 */
class FTB_Donation_Form_Public {

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
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ftb-donation-form-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ftb-donation-form-public.js', array('jquery'), $this->version, false);
    }

    /**
     * Register shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('ftb_donation_form', array($this, 'render_donation_form'));
    }

    /**
     * Render the donation form.
     *
     * @since    1.0.0
     * @return   string    The HTML for the donation form.
     */
    public function render_donation_form() {
        ob_start();
        include 'partials/ftb-donation-form-public-display.php';
        return ob_get_clean();
    }
}