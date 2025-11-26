<?php
/**
 * Plugin Name: Bulk Payment for WooCommerce
 * Plugin URI: https://jezweb.com
 * Description: Allow customers to pay any amount of money for a symbolic product without shipping information. Perfect for donations, custom payments, and flexible pricing.
 * Version: 1.0.9
 * Author: Jezweb
 * Author URI: https://jezweb.com
 * Developer: Mahmud Farooque
 * Text Domain: bulk-payment-wc
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BULK_PAYMENT_WC_VERSION', '1.0.9');
define('BULK_PAYMENT_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BULK_PAYMENT_WC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BULK_PAYMENT_WC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Bulk_Payment_WC {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Check if WooCommerce is active
        add_action('plugins_loaded', array($this, 'init'));

        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Admin notice for WooCommerce requirement
        add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Load text domain on init action (WordPress 6.7+ requirement)
        add_action('init', array($this, 'load_textdomain'));

        // Include required files
        $this->includes();

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('bulk-payment-wc', false, dirname(BULK_PAYMENT_WC_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-product.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-cart.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-checkout.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-admin.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-product-creator.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-shortcode.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-elementor.php';
        require_once BULK_PAYMENT_WC_PLUGIN_DIR . 'includes/class-bulk-payment-updater.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize classes
        Bulk_Payment_Product::get_instance();
        Bulk_Payment_Cart::get_instance();
        Bulk_Payment_Checkout::get_instance();
        Bulk_Payment_Admin::get_instance();
        Bulk_Payment_Product_Creator::get_instance();
        Bulk_Payment_Shortcode::get_instance();

        // Initialize Elementor widget if Elementor is active
        if (Bulk_Payment_Elementor::is_elementor_active()) {
            Bulk_Payment_Elementor::get_instance();
        }

        // Initialize GitHub auto-updater
        Bulk_Payment_Updater::get_instance();
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Admin notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        if (!$this->is_woocommerce_active()) {
            $class = 'notice notice-error';
            $message = sprintf(
                __('Bulk Payment for WooCommerce requires WooCommerce to be installed and active. You can download WooCommerce %shere%s.', 'bulk-payment-wc'),
                '<a href="' . admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') . '">',
                '</a>'
            );
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(BULK_PAYMENT_WC_PLUGIN_BASENAME);
            wp_die(__('Bulk Payment for WooCommerce requires PHP version 7.4 or higher.', 'bulk-payment-wc'));
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.8', '<')) {
            deactivate_plugins(BULK_PAYMENT_WC_PLUGIN_BASENAME);
            wp_die(__('Bulk Payment for WooCommerce requires WordPress version 5.8 or higher.', 'bulk-payment-wc'));
        }

        // Set activation flag
        set_transient('bulk_payment_wc_activated', true, 30);
    }
}

/**
 * Initialize the plugin
 */
function bulk_payment_wc() {
    return Bulk_Payment_WC::get_instance();
}

// Start the plugin
bulk_payment_wc();
