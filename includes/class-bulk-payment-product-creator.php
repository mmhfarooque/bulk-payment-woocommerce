<?php
/**
 * Bulk Payment Product Creator
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Product_Creator
 */
class Bulk_Payment_Product_Creator {

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
        // Create product on activation
        add_action('init', array($this, 'maybe_create_bulk_payment_product'));
    }

    /**
     * Maybe create bulk payment product if it doesn't exist
     */
    public function maybe_create_bulk_payment_product() {
        // Check if product already exists
        $product_id = get_option('bulk_payment_product_id');

        if ($product_id && get_post($product_id)) {
            return;
        }

        // Create the product
        $this->create_bulk_payment_product();
    }

    /**
     * Create bulk payment product
     */
    public function create_bulk_payment_product() {
        // Get settings or use defaults
        $product_title = get_option('bulk_payment_product_title', __('Make a Payment', 'bulk-payment-wc'));
        $product_description = get_option('bulk_payment_product_description', __('Enter any amount you wish to pay. This is a flexible payment product that does not require shipping information.', 'bulk-payment-wc'));

        // Create product
        $product = new WC_Product_Simple();
        $product->set_name($product_title);
        $product->set_slug('bulk-payment-product');
        $product->set_description($product_description);
        $product->set_short_description($product_description);
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price('0');
        $product->set_regular_price('0');
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_reviews_allowed(false);

        // Save product
        $product_id = $product->save();

        if ($product_id) {
            // Enable bulk payment for this product
            update_post_meta($product_id, '_bulk_payment_enabled', 'yes');
            update_post_meta($product_id, '_bulk_payment_label', get_option('bulk_payment_default_label', __('Enter Amount', 'bulk-payment-wc')));
            update_post_meta($product_id, '_bulk_payment_placeholder', get_option('bulk_payment_default_placeholder', __('Enter your amount', 'bulk-payment-wc')));
            update_post_meta($product_id, '_bulk_payment_min_amount', get_option('bulk_payment_default_min', ''));
            update_post_meta($product_id, '_bulk_payment_max_amount', get_option('bulk_payment_default_max', ''));

            // Save product ID
            update_option('bulk_payment_product_id', $product_id);

            // Set default image if provided
            $image_id = get_option('bulk_payment_product_image_id');
            if ($image_id) {
                set_post_thumbnail($product_id, $image_id);
            }
        }

        return $product_id;
    }

    /**
     * Get bulk payment product ID
     */
    public static function get_bulk_payment_product_id() {
        $product_id = get_option('bulk_payment_product_id');

        if (!$product_id || !get_post($product_id)) {
            // Create product if it doesn't exist
            $creator = self::get_instance();
            $product_id = $creator->create_bulk_payment_product();
        }

        return $product_id;
    }

    /**
     * Get bulk payment product
     */
    public static function get_bulk_payment_product() {
        $product_id = self::get_bulk_payment_product_id();
        return $product_id ? wc_get_product($product_id) : null;
    }

    /**
     * Update bulk payment product
     */
    public static function update_bulk_payment_product($data) {
        $product_id = self::get_bulk_payment_product_id();

        if (!$product_id) {
            return false;
        }

        $product = wc_get_product($product_id);

        if (!$product) {
            return false;
        }

        // Update title
        if (isset($data['title'])) {
            $product->set_name(sanitize_text_field($data['title']));
            update_option('bulk_payment_product_title', sanitize_text_field($data['title']));
        }

        // Update description
        if (isset($data['description'])) {
            $description = wp_kses_post($data['description']);
            $product->set_description($description);
            $product->set_short_description($description);
            update_option('bulk_payment_product_description', $description);
        }

        // Update image
        if (isset($data['image_id'])) {
            set_post_thumbnail($product_id, absint($data['image_id']));
            update_option('bulk_payment_product_image_id', absint($data['image_id']));
        }

        // Update min amount
        if (isset($data['min_amount'])) {
            update_post_meta($product_id, '_bulk_payment_min_amount', sanitize_text_field($data['min_amount']));
        }

        // Update max amount
        if (isset($data['max_amount'])) {
            update_post_meta($product_id, '_bulk_payment_max_amount', sanitize_text_field($data['max_amount']));
        }

        // Update label
        if (isset($data['label'])) {
            update_post_meta($product_id, '_bulk_payment_label', sanitize_text_field($data['label']));
        }

        // Update placeholder
        if (isset($data['placeholder'])) {
            update_post_meta($product_id, '_bulk_payment_placeholder', sanitize_text_field($data['placeholder']));
        }

        $product->save();

        return true;
    }

    /**
     * Delete bulk payment product
     */
    public static function delete_bulk_payment_product() {
        $product_id = get_option('bulk_payment_product_id');

        if ($product_id) {
            wp_delete_post($product_id, true);
            delete_option('bulk_payment_product_id');
            delete_option('bulk_payment_product_title');
            delete_option('bulk_payment_product_description');
            delete_option('bulk_payment_product_image_id');
        }
    }

    /**
     * Reset bulk payment product (delete and recreate)
     */
    public static function reset_bulk_payment_product() {
        self::delete_bulk_payment_product();
        $creator = self::get_instance();
        return $creator->create_bulk_payment_product();
    }
}
