<?php
/**
 * Bulk Payment Shortcode Handler
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Shortcode
 */
class Bulk_Payment_Shortcode {

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
        // Register shortcode
        add_shortcode('bulk_payment_form', array($this, 'render_shortcode'));

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Handle AJAX add to cart
        add_action('wp_ajax_bulk_payment_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_bulk_payment_add_to_cart', array($this, 'ajax_add_to_cart'));

        // Handle AJAX direct checkout
        add_action('wp_ajax_bulk_payment_direct_checkout', array($this, 'ajax_direct_checkout'));
        add_action('wp_ajax_nopriv_bulk_payment_direct_checkout', array($this, 'ajax_direct_checkout'));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue if shortcode is present
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'bulk_payment_form')) {
            return;
        }

        wp_enqueue_style(
            'bulk-payment-frontend',
            BULK_PAYMENT_WC_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            BULK_PAYMENT_WC_VERSION
        );

        wp_enqueue_script(
            'bulk-payment-frontend',
            BULK_PAYMENT_WC_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            BULK_PAYMENT_WC_VERSION,
            true
        );

        wp_localize_script('bulk-payment-frontend', 'bulkPaymentData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bulk_payment_nonce'),
            'currency' => get_woocommerce_currency_symbol(),
            'i18n' => array(
                'pleaseEnterAmount' => __('Please enter an amount', 'bulk-payment-wc'),
                'invalidAmount' => __('Please enter a valid amount', 'bulk-payment-wc'),
                'minAmount' => __('Minimum amount is', 'bulk-payment-wc'),
                'maxAmount' => __('Maximum amount is', 'bulk-payment-wc'),
                'addingToCart' => __('Adding to cart...', 'bulk-payment-wc'),
                'addedToCart' => __('Added to cart!', 'bulk-payment-wc'),
                'error' => __('An error occurred. Please try again.', 'bulk-payment-wc'),
            ),
        ));
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        // Get checkout type from settings
        $checkout_type = get_option('bulk_payment_checkout_type', 'cart');

        // Parse attributes
        $atts = shortcode_atts(array(
            'layout' => 'default', // default, minimal, compact
            'show_image' => 'yes',
            'show_title' => 'yes',
            'show_description' => 'yes',
            'button_text' => $checkout_type === 'direct' ? __('Pay Now', 'bulk-payment-wc') : __('Add to Cart', 'bulk-payment-wc'),
            'image_position' => 'left', // left, right, top
            'checkout_type' => $checkout_type, // cart or direct
        ), $atts, 'bulk_payment_form');

        // Get bulk payment product
        $product = Bulk_Payment_Product_Creator::get_bulk_payment_product();

        if (!$product) {
            return '<p>' . __('Bulk payment product not found.', 'bulk-payment-wc') . '</p>';
        }

        $product_id = $product->get_id();
        $min_amount = get_post_meta($product_id, '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($product_id, '_bulk_payment_max_amount', true);
        $label = get_post_meta($product_id, '_bulk_payment_label', true);
        $placeholder = get_post_meta($product_id, '_bulk_payment_placeholder', true);
        $currency_symbol = get_woocommerce_currency_symbol();
        $currency_pos = get_option('woocommerce_currency_pos', 'left');

        // Start output buffering
        ob_start();

        ?>
        <div class="bulk-payment-form-container layout-<?php echo esc_attr($atts['layout']); ?> image-<?php echo esc_attr($atts['image_position']); ?>" data-product-id="<?php echo esc_attr($product_id); ?>">

            <?php if ($atts['show_image'] === 'yes' && has_post_thumbnail($product_id)): ?>
                <div class="bulk-payment-image">
                    <?php echo get_the_post_thumbnail($product_id, 'large'); ?>
                </div>
            <?php endif; ?>

            <div class="bulk-payment-content">

                <?php if ($atts['show_title'] === 'yes'): ?>
                    <h2 class="bulk-payment-title"><?php echo esc_html($product->get_name()); ?></h2>
                <?php endif; ?>

                <?php if ($atts['show_description'] === 'yes' && $product->get_description()): ?>
                    <div class="bulk-payment-description">
                        <?php echo wp_kses_post($product->get_description()); ?>
                    </div>
                <?php endif; ?>

                <form class="bulk-payment-form" method="post" data-checkout-type="<?php echo esc_attr($atts['checkout_type']); ?>">

                    <div class="bulk-payment-amount-wrapper">
                        <label for="bulk_payment_amount_input" class="bulk-payment-label">
                            <?php echo esc_html($label ? $label : __('Enter Amount', 'bulk-payment-wc')); ?>
                        </label>

                        <div class="bulk-payment-input-group">
                            <?php if ($currency_pos === 'left' || $currency_pos === 'left_space'): ?>
                                <span class="bulk-payment-currency-symbol currency-left">
                                    <?php echo esc_html($currency_symbol); ?>
                                </span>
                            <?php endif; ?>

                            <input
                                type="number"
                                id="bulk_payment_amount_input"
                                name="bulk_payment_amount"
                                class="bulk-payment-amount-input"
                                step="0.01"
                                min="<?php echo esc_attr($min_amount ? $min_amount : '0.01'); ?>"
                                <?php if ($max_amount) echo 'max="' . esc_attr($max_amount) . '"'; ?>
                                placeholder="<?php echo esc_attr($placeholder ? $placeholder : __('Enter your amount', 'bulk-payment-wc')); ?>"
                                required
                                data-min="<?php echo esc_attr($min_amount); ?>"
                                data-max="<?php echo esc_attr($max_amount); ?>"
                            >

                            <?php if ($currency_pos === 'right' || $currency_pos === 'right_space'): ?>
                                <span class="bulk-payment-currency-symbol currency-right">
                                    <?php echo esc_html($currency_symbol); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($min_amount || $max_amount): ?>
                            <div class="bulk-payment-amount-info">
                                <?php
                                if ($min_amount && $max_amount) {
                                    printf(
                                        __('Amount must be between %s and %s', 'bulk-payment-wc'),
                                        '<strong>' . wc_price($min_amount) . '</strong>',
                                        '<strong>' . wc_price($max_amount) . '</strong>'
                                    );
                                } elseif ($min_amount) {
                                    printf(
                                        __('Minimum amount: %s', 'bulk-payment-wc'),
                                        '<strong>' . wc_price($min_amount) . '</strong>'
                                    );
                                } elseif ($max_amount) {
                                    printf(
                                        __('Maximum amount: %s', 'bulk-payment-wc'),
                                        '<strong>' . wc_price($max_amount) . '</strong>'
                                    );
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($atts['checkout_type'] === 'direct'): ?>
                        <div class="bulk-payment-customer-fields">
                            <div class="bulk-payment-field">
                                <label for="customer_name">
                                    <?php _e('Full Name', 'bulk-payment-wc'); ?> <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="customer_name"
                                    name="customer_name"
                                    required
                                    placeholder="<?php esc_attr_e('Enter your full name', 'bulk-payment-wc'); ?>"
                                >
                            </div>

                            <div class="bulk-payment-field">
                                <label for="customer_email">
                                    <?php _e('Email Address', 'bulk-payment-wc'); ?> <span class="required">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="customer_email"
                                    name="customer_email"
                                    required
                                    placeholder="<?php esc_attr_e('Enter your email', 'bulk-payment-wc'); ?>"
                                >
                            </div>

                            <div class="bulk-payment-field">
                                <label for="customer_phone">
                                    <?php _e('Phone Number', 'bulk-payment-wc'); ?> <span class="required">*</span>
                                </label>
                                <input
                                    type="tel"
                                    id="customer_phone"
                                    name="customer_phone"
                                    required
                                    placeholder="<?php esc_attr_e('Enter your phone number', 'bulk-payment-wc'); ?>"
                                >
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bulk-payment-button-wrapper">
                        <button type="submit" class="bulk-payment-submit-button">
                            <?php echo esc_html($atts['button_text']); ?>
                        </button>
                    </div>

                    <div class="bulk-payment-message" style="display: none;"></div>

                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                    <input type="hidden" name="action" value="bulk_payment_add_to_cart">
                    <?php wp_nonce_field('bulk_payment_nonce', 'bulk_payment_nonce'); ?>

                </form>

            </div>

        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * AJAX add to cart handler
     */
    public function ajax_add_to_cart() {
        // Verify nonce
        if (!isset($_POST['bulk_payment_nonce']) || !wp_verify_nonce($_POST['bulk_payment_nonce'], 'bulk_payment_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'bulk-payment-wc')));
        }

        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

        if (!$product_id || $amount <= 0) {
            wp_send_json_error(array('message' => __('Invalid product or amount', 'bulk-payment-wc')));
        }

        // Get product
        $product = wc_get_product($product_id);

        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found', 'bulk-payment-wc')));
        }

        // Validate amount
        $min_amount = get_post_meta($product_id, '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($product_id, '_bulk_payment_max_amount', true);

        if ($min_amount && $amount < floatval($min_amount)) {
            wp_send_json_error(array(
                'message' => sprintf(__('Minimum amount is %s', 'bulk-payment-wc'), wc_price($min_amount))
            ));
        }

        if ($max_amount && $amount > floatval($max_amount)) {
            wp_send_json_error(array(
                'message' => sprintf(__('Maximum amount is %s', 'bulk-payment-wc'), wc_price($max_amount))
            ));
        }

        // Add to cart
        $cart_item_data = array(
            'bulk_payment_amount' => $amount,
            'unique_key' => md5(microtime() . rand()),
        );

        $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);

        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => __('Added to cart successfully!', 'bulk-payment-wc'),
                'cart_url' => wc_get_cart_url(),
                'cart_count' => WC()->cart->get_cart_contents_count(),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to add to cart', 'bulk-payment-wc')));
        }
    }

    /**
     * AJAX direct checkout handler
     */
    public function ajax_direct_checkout() {
        // Verify nonce
        if (!isset($_POST['bulk_payment_nonce']) || !wp_verify_nonce($_POST['bulk_payment_nonce'], 'bulk_payment_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'bulk-payment-wc')));
        }

        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $customer_data = isset($_POST['customer_data']) ? $_POST['customer_data'] : array();

        if (!$product_id || $amount <= 0) {
            wp_send_json_error(array('message' => __('Invalid product or amount', 'bulk-payment-wc')));
        }

        // Validate customer data
        if (empty($customer_data['name']) || empty($customer_data['email']) || empty($customer_data['phone'])) {
            wp_send_json_error(array('message' => __('Please provide all required customer information', 'bulk-payment-wc')));
        }

        // Validate email
        if (!is_email($customer_data['email'])) {
            wp_send_json_error(array('message' => __('Please provide a valid email address', 'bulk-payment-wc')));
        }

        // Get product
        $product = wc_get_product($product_id);

        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found', 'bulk-payment-wc')));
        }

        // Validate amount
        $min_amount = get_post_meta($product_id, '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($product_id, '_bulk_payment_max_amount', true);

        if ($min_amount && $amount < floatval($min_amount)) {
            wp_send_json_error(array(
                'message' => sprintf(__('Minimum amount is %s', 'bulk-payment-wc'), wc_price($min_amount))
            ));
        }

        if ($max_amount && $amount > floatval($max_amount)) {
            wp_send_json_error(array(
                'message' => sprintf(__('Maximum amount is %s', 'bulk-payment-wc'), wc_price($max_amount))
            ));
        }

        // Clear cart and add bulk payment product
        WC()->cart->empty_cart();

        $cart_item_data = array(
            'bulk_payment_amount' => $amount,
            'unique_key' => md5(microtime() . rand()),
        );

        $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);

        if (!$cart_item_key) {
            wp_send_json_error(array('message' => __('Failed to add to cart', 'bulk-payment-wc')));
        }

        // Store customer data in session for checkout
        WC()->session->set('bulk_payment_customer_data', array(
            'billing_first_name' => sanitize_text_field($customer_data['name']),
            'billing_last_name' => '',
            'billing_email' => sanitize_email($customer_data['email']),
            'billing_phone' => sanitize_text_field($customer_data['phone']),
        ));

        // Return checkout URL
        wp_send_json_success(array(
            'checkout_url' => wc_get_checkout_url(),
        ));
    }
}
