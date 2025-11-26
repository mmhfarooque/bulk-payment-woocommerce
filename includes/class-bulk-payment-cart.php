<?php
/**
 * Bulk Payment Cart Handler
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Cart
 */
class Bulk_Payment_Cart {

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
        // Make bulk payment products always purchasable (even with $0 price)
        add_filter('woocommerce_is_purchasable', array($this, 'make_bulk_payment_purchasable'), 10, 2);

        // Update cart item price based on custom amount
        add_action('woocommerce_before_calculate_totals', array($this, 'update_cart_item_price'), 10, 1);

        // Display custom amount in cart
        add_filter('woocommerce_cart_item_name', array($this, 'display_custom_amount_in_cart'), 10, 3);
        add_filter('woocommerce_get_item_data', array($this, 'display_custom_amount_meta'), 10, 2);

        // Make bulk payment products virtual to skip shipping
        add_filter('woocommerce_cart_needs_shipping', array($this, 'maybe_disable_shipping'), 10, 1);
        add_filter('woocommerce_product_needs_shipping', array($this, 'product_needs_shipping'), 10, 2);

        // Save custom amount to order item meta
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'save_custom_amount_to_order_item'), 10, 4);
    }

    /**
     * Make bulk payment products always purchasable
     * WooCommerce considers $0 products as not purchasable by default
     *
     * @param bool $purchasable Whether the product is purchasable
     * @param WC_Product $product The product object
     * @return bool
     */
    public function make_bulk_payment_purchasable($purchasable, $product) {
        if (!$product) {
            return $purchasable;
        }

        $enabled = get_post_meta($product->get_id(), '_bulk_payment_enabled', true);

        if ($enabled === 'yes') {
            return true;
        }

        return $purchasable;
    }

    /**
     * Update cart item price based on custom amount
     */
    public function update_cart_item_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['bulk_payment_amount'])) {
                $custom_amount = floatval($cart_item['bulk_payment_amount']);
                $cart_item['data']->set_price($custom_amount);
            }
        }
    }

    /**
     * Display custom amount in cart item name
     */
    public function display_custom_amount_in_cart($product_name, $cart_item, $cart_item_key) {
        if (isset($cart_item['bulk_payment_amount'])) {
            $custom_amount = floatval($cart_item['bulk_payment_amount']);
            $product_name .= '<br><small class="bulk-payment-amount-info">' .
                sprintf(__('Custom Amount: %s', 'bulk-payment-wc'), wc_price($custom_amount)) .
                '</small>';
        }
        return $product_name;
    }

    /**
     * Display custom amount in cart item meta
     */
    public function display_custom_amount_meta($item_data, $cart_item) {
        if (isset($cart_item['bulk_payment_amount'])) {
            $item_data[] = array(
                'key'     => __('Amount', 'bulk-payment-wc'),
                'value'   => wc_price($cart_item['bulk_payment_amount']),
                'display' => '',
            );
        }
        return $item_data;
    }

    /**
     * Check if cart needs shipping (disable for bulk payment only carts)
     */
    public function maybe_disable_shipping($needs_shipping) {
        if (!WC()->cart) {
            return $needs_shipping;
        }

        $has_non_bulk_payment = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

            if ($enabled !== 'yes') {
                $has_non_bulk_payment = true;
                break;
            }
        }

        // If all items are bulk payment products, disable shipping
        if (!$has_non_bulk_payment) {
            return false;
        }

        return $needs_shipping;
    }

    /**
     * Product needs shipping check
     */
    public function product_needs_shipping($needs_shipping, $product) {
        if (!$product) {
            return $needs_shipping;
        }

        $product_id = $product->get_id();
        $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

        if ($enabled === 'yes') {
            return false;
        }

        return $needs_shipping;
    }

    /**
     * Save custom amount to order item meta
     */
    public function save_custom_amount_to_order_item($item, $cart_item_key, $values, $order) {
        if (isset($values['bulk_payment_amount'])) {
            $item->add_meta_data(__('Bulk Payment Amount', 'bulk-payment-wc'), wc_price($values['bulk_payment_amount']), true);
            $item->add_meta_data('_bulk_payment_amount', $values['bulk_payment_amount'], false);
        }
    }

    /**
     * Check if cart contains only bulk payment products
     */
    public static function cart_has_only_bulk_payment_products() {
        if (!WC()->cart) {
            return false;
        }

        $has_bulk_payment = false;
        $has_regular = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

            if ($enabled === 'yes') {
                $has_bulk_payment = true;
            } else {
                $has_regular = true;
            }
        }

        return $has_bulk_payment && !$has_regular;
    }

    /**
     * Check if cart contains any bulk payment products
     */
    public static function cart_has_bulk_payment_products() {
        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

            if ($enabled === 'yes') {
                return true;
            }
        }

        return false;
    }
}
