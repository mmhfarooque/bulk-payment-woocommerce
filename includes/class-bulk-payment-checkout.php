<?php
/**
 * Bulk Payment Checkout Handler
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Checkout
 */
class Bulk_Payment_Checkout {

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
        // Remove shipping fields if cart has only bulk payment products
        add_filter('woocommerce_checkout_fields', array($this, 'maybe_remove_shipping_fields'), 10, 1);

        // Hide shipping methods for bulk payment products
        add_filter('woocommerce_package_rates', array($this, 'maybe_hide_shipping_methods'), 10, 2);

        // Add custom CSS for checkout
        add_action('wp_head', array($this, 'add_checkout_styles'));

        // Customize checkout button text
        add_filter('woocommerce_order_button_text', array($this, 'custom_order_button_text'));

        // Display bulk payment info on checkout
        add_action('woocommerce_review_order_before_payment', array($this, 'display_bulk_payment_info'));

        // Pre-fill checkout fields from session
        add_filter('woocommerce_checkout_get_value', array($this, 'prefill_checkout_fields'), 10, 2);
    }

    /**
     * Remove shipping fields if cart contains only bulk payment products
     */
    public function maybe_remove_shipping_fields($fields) {
        // Check if cart has only bulk payment products
        if (Bulk_Payment_Cart::cart_has_only_bulk_payment_products()) {
            // Remove ALL shipping fields
            if (isset($fields['shipping'])) {
                unset($fields['shipping']);
            }

            // REMOVE address fields from billing (not just make optional)
            if (isset($fields['billing']['billing_address_1'])) {
                unset($fields['billing']['billing_address_1']);
            }
            if (isset($fields['billing']['billing_address_2'])) {
                unset($fields['billing']['billing_address_2']);
            }
            if (isset($fields['billing']['billing_city'])) {
                unset($fields['billing']['billing_city']);
            }
            if (isset($fields['billing']['billing_state'])) {
                unset($fields['billing']['billing_state']);
            }
            if (isset($fields['billing']['billing_postcode'])) {
                unset($fields['billing']['billing_postcode']);
            }
            if (isset($fields['billing']['billing_country'])) {
                unset($fields['billing']['billing_country']);
            }
            if (isset($fields['billing']['billing_company'])) {
                unset($fields['billing']['billing_company']);
            }

            // Keep only: first_name, last_name, email, phone
            // These are already in the billing array by default

            // Add a filter to allow customization
            $fields = apply_filters('bulk_payment_checkout_fields', $fields);
        }

        return $fields;
    }

    /**
     * Hide shipping methods for bulk payment products
     */
    public function maybe_hide_shipping_methods($rates, $package) {
        // Check if cart has only bulk payment products
        if (Bulk_Payment_Cart::cart_has_only_bulk_payment_products()) {
            return array();
        }

        return $rates;
    }

    /**
     * Add custom CSS for checkout page
     */
    public function add_checkout_styles() {
        if (!is_checkout()) {
            return;
        }

        if (!Bulk_Payment_Cart::cart_has_bulk_payment_products()) {
            return;
        }

        ?>
        <style type="text/css">
            /* Hide shipping section if not needed */
            <?php if (Bulk_Payment_Cart::cart_has_only_bulk_payment_products()): ?>
            .woocommerce-shipping-fields,
            .woocommerce-checkout #ship-to-different-address,
            #shipping_method {
                display: none !important;
            }

            /* Hide ALL address-related billing fields */
            .woocommerce-billing-fields .form-row-address-1,
            .woocommerce-billing-fields .form-row-address-2,
            .woocommerce-billing-fields .form-row-city,
            .woocommerce-billing-fields .form-row-state,
            .woocommerce-billing-fields .form-row-postcode,
            .woocommerce-billing-fields .form-row-country,
            .woocommerce-billing-fields .form-row-company,
            #billing_address_1_field,
            #billing_address_2_field,
            #billing_city_field,
            #billing_state_field,
            #billing_postcode_field,
            #billing_country_field,
            #billing_company_field,
            .woocommerce-billing-fields__field-wrapper .address-field,
            .woocommerce-billing-fields h3 {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            <?php endif; ?>

            /* Style for bulk payment info */
            .bulk-payment-checkout-info {
                background: #f7f7f7;
                padding: 15px;
                margin: 20px 0;
                border-left: 4px solid #2c3e50;
                border-radius: 4px;
            }

            .bulk-payment-checkout-info h3 {
                margin-top: 0;
                color: #2c3e50;
                font-size: 18px;
            }

            .bulk-payment-checkout-info p {
                margin-bottom: 0;
                color: #666;
            }

            /* Style for bulk payment amount in order review */
            .bulk-payment-amount-info {
                color: #666;
                font-style: italic;
            }
        </style>
        <?php
    }

    /**
     * Custom order button text
     */
    public function custom_order_button_text($button_text) {
        if (Bulk_Payment_Cart::cart_has_only_bulk_payment_products()) {
            return __('Complete Payment', 'bulk-payment-wc');
        }
        return $button_text;
    }

    /**
     * Display bulk payment info on checkout
     */
    public function display_bulk_payment_info() {
        if (!Bulk_Payment_Cart::cart_has_bulk_payment_products()) {
            return;
        }

        $is_only_bulk_payment = Bulk_Payment_Cart::cart_has_only_bulk_payment_products();

        ?>
        <div class="bulk-payment-checkout-info">
            <h3><?php _e('Payment Information', 'bulk-payment-wc'); ?></h3>
            <?php if ($is_only_bulk_payment): ?>
                <p><?php _e('This is a direct payment. No shipping information is required.', 'bulk-payment-wc'); ?></p>
            <?php else: ?>
                <p><?php _e('Your order contains bulk payment items which do not require shipping.', 'bulk-payment-wc'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get bulk payment products from order
     */
    public static function order_has_bulk_payment_products($order) {
        if (!$order) {
            return false;
        }

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

            if ($enabled === 'yes') {
                return true;
            }
        }

        return false;
    }

    /**
     * Format bulk payment amount for display
     */
    public static function format_bulk_payment_amount($amount) {
        return wc_price($amount);
    }

    /**
     * Pre-fill checkout fields from session data
     */
    public function prefill_checkout_fields($value, $input) {
        // Check if WooCommerce session is available (not in admin or non-frontend contexts)
        if (!function_exists('WC') || !WC()->session) {
            return $value;
        }

        // Get customer data from session
        $customer_data = WC()->session->get('bulk_payment_customer_data');

        if (!$customer_data || !is_array($customer_data)) {
            return $value;
        }

        // Pre-fill fields if they exist in session data
        if (isset($customer_data[$input])) {
            return $customer_data[$input];
        }

        return $value;
    }
}
