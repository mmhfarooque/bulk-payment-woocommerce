<?php
/**
 * Bulk Payment Product Handler
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Product
 */
class Bulk_Payment_Product {

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
        // Add product meta box
        add_action('add_meta_boxes', array($this, 'add_product_meta_box'));
        add_action('save_post', array($this, 'save_product_meta'));

        // Add custom amount field on product page
        add_action('woocommerce_before_add_to_cart_button', array($this, 'display_custom_amount_field'));

        // Handle custom amount when adding to cart
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_amount_to_cart'), 10, 3);
        add_action('woocommerce_add_to_cart_validation', array($this, 'validate_custom_amount'), 10, 3);

        // Hide price if bulk payment is enabled
        add_filter('woocommerce_get_price_html', array($this, 'custom_price_display'), 10, 2);
    }

    /**
     * Add product meta box
     */
    public function add_product_meta_box() {
        add_meta_box(
            'bulk_payment_product_options',
            __('Bulk Payment Options', 'bulk-payment-wc'),
            array($this, 'render_product_meta_box'),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render product meta box
     */
    public function render_product_meta_box($post) {
        wp_nonce_field('bulk_payment_product_meta', 'bulk_payment_product_meta_nonce');

        $enabled = get_post_meta($post->ID, '_bulk_payment_enabled', true);
        $min_amount = get_post_meta($post->ID, '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($post->ID, '_bulk_payment_max_amount', true);
        $placeholder = get_post_meta($post->ID, '_bulk_payment_placeholder', true);
        $label = get_post_meta($post->ID, '_bulk_payment_label', true);

        ?>
        <div class="bulk-payment-options">
            <p>
                <label>
                    <input type="checkbox" name="_bulk_payment_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                    <?php _e('Enable Bulk Payment', 'bulk-payment-wc'); ?>
                </label>
            </p>
            <p>
                <label><?php _e('Amount Field Label:', 'bulk-payment-wc'); ?></label>
                <input type="text" name="_bulk_payment_label" value="<?php echo esc_attr($label ? $label : __('Enter Amount', 'bulk-payment-wc')); ?>" style="width: 100%;">
            </p>
            <p>
                <label><?php _e('Placeholder Text:', 'bulk-payment-wc'); ?></label>
                <input type="text" name="_bulk_payment_placeholder" value="<?php echo esc_attr($placeholder ? $placeholder : __('Enter your amount', 'bulk-payment-wc')); ?>" style="width: 100%;">
            </p>
            <p>
                <label><?php _e('Minimum Amount:', 'bulk-payment-wc'); ?></label>
                <input type="number" name="_bulk_payment_min_amount" value="<?php echo esc_attr($min_amount); ?>" step="0.01" min="0" style="width: 100%;">
            </p>
            <p>
                <label><?php _e('Maximum Amount:', 'bulk-payment-wc'); ?></label>
                <input type="number" name="_bulk_payment_max_amount" value="<?php echo esc_attr($max_amount); ?>" step="0.01" min="0" style="width: 100%;">
                <small style="display: block; color: #666;"><?php _e('Leave empty for no limit', 'bulk-payment-wc'); ?></small>
            </p>
        </div>
        <?php
    }

    /**
     * Save product meta
     */
    public function save_product_meta($post_id) {
        // Check nonce
        if (!isset($_POST['bulk_payment_product_meta_nonce']) ||
            !wp_verify_nonce($_POST['bulk_payment_product_meta_nonce'], 'bulk_payment_product_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save enabled status
        $enabled = isset($_POST['_bulk_payment_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_bulk_payment_enabled', $enabled);

        // Save other options
        if (isset($_POST['_bulk_payment_min_amount'])) {
            update_post_meta($post_id, '_bulk_payment_min_amount', sanitize_text_field($_POST['_bulk_payment_min_amount']));
        }
        if (isset($_POST['_bulk_payment_max_amount'])) {
            update_post_meta($post_id, '_bulk_payment_max_amount', sanitize_text_field($_POST['_bulk_payment_max_amount']));
        }
        if (isset($_POST['_bulk_payment_placeholder'])) {
            update_post_meta($post_id, '_bulk_payment_placeholder', sanitize_text_field($_POST['_bulk_payment_placeholder']));
        }
        if (isset($_POST['_bulk_payment_label'])) {
            update_post_meta($post_id, '_bulk_payment_label', sanitize_text_field($_POST['_bulk_payment_label']));
        }
    }

    /**
     * Display custom amount field on product page
     */
    public function display_custom_amount_field() {
        global $product;

        if (!$product) {
            return;
        }

        $enabled = get_post_meta($product->get_id(), '_bulk_payment_enabled', true);

        if ($enabled !== 'yes') {
            return;
        }

        $min_amount = get_post_meta($product->get_id(), '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($product->get_id(), '_bulk_payment_max_amount', true);
        $placeholder = get_post_meta($product->get_id(), '_bulk_payment_placeholder', true);
        $label = get_post_meta($product->get_id(), '_bulk_payment_label', true);
        $currency_symbol = get_woocommerce_currency_symbol();

        ?>
        <div class="bulk-payment-amount-field" style="margin: 20px 0;">
            <label for="bulk_payment_amount" style="display: block; margin-bottom: 10px; font-weight: bold;">
                <?php echo esc_html($label ? $label : __('Enter Amount', 'bulk-payment-wc')); ?>
            </label>
            <div style="position: relative; display: inline-block; width: 100%;">
                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-weight: bold; color: #666;">
                    <?php echo esc_html($currency_symbol); ?>
                </span>
                <input
                    type="number"
                    id="bulk_payment_amount"
                    name="bulk_payment_amount"
                    step="0.01"
                    min="<?php echo esc_attr($min_amount ? $min_amount : '0.01'); ?>"
                    <?php if ($max_amount) echo 'max="' . esc_attr($max_amount) . '"'; ?>
                    placeholder="<?php echo esc_attr($placeholder ? $placeholder : __('Enter your amount', 'bulk-payment-wc')); ?>"
                    required
                    style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                >
            </div>
            <?php if ($min_amount || $max_amount): ?>
                <small style="display: block; margin-top: 5px; color: #666;">
                    <?php
                    if ($min_amount && $max_amount) {
                        printf(__('Amount must be between %s and %s', 'bulk-payment-wc'),
                            wc_price($min_amount), wc_price($max_amount));
                    } elseif ($min_amount) {
                        printf(__('Minimum amount: %s', 'bulk-payment-wc'), wc_price($min_amount));
                    } elseif ($max_amount) {
                        printf(__('Maximum amount: %s', 'bulk-payment-wc'), wc_price($max_amount));
                    }
                    ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Add custom amount to cart item data
     */
    public function add_custom_amount_to_cart($cart_item_data, $product_id, $variation_id) {
        $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

        if ($enabled === 'yes' && isset($_POST['bulk_payment_amount'])) {
            $custom_amount = floatval($_POST['bulk_payment_amount']);
            $cart_item_data['bulk_payment_amount'] = $custom_amount;
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }

        return $cart_item_data;
    }

    /**
     * Validate custom amount
     */
    public function validate_custom_amount($passed, $product_id, $quantity) {
        $enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);

        if ($enabled !== 'yes') {
            return $passed;
        }

        if (!isset($_POST['bulk_payment_amount']) || empty($_POST['bulk_payment_amount'])) {
            wc_add_notice(__('Please enter an amount.', 'bulk-payment-wc'), 'error');
            return false;
        }

        $custom_amount = floatval($_POST['bulk_payment_amount']);
        $min_amount = get_post_meta($product_id, '_bulk_payment_min_amount', true);
        $max_amount = get_post_meta($product_id, '_bulk_payment_max_amount', true);

        if ($custom_amount <= 0) {
            wc_add_notice(__('Please enter a valid amount.', 'bulk-payment-wc'), 'error');
            return false;
        }

        if ($min_amount && $custom_amount < floatval($min_amount)) {
            wc_add_notice(
                sprintf(__('Minimum amount is %s.', 'bulk-payment-wc'), wc_price($min_amount)),
                'error'
            );
            return false;
        }

        if ($max_amount && $custom_amount > floatval($max_amount)) {
            wc_add_notice(
                sprintf(__('Maximum amount is %s.', 'bulk-payment-wc'), wc_price($max_amount)),
                'error'
            );
            return false;
        }

        return $passed;
    }

    /**
     * Custom price display for bulk payment products
     */
    public function custom_price_display($price, $product) {
        $enabled = get_post_meta($product->get_id(), '_bulk_payment_enabled', true);

        if ($enabled === 'yes') {
            return '<span class="bulk-payment-price">' . __('Enter your amount', 'bulk-payment-wc') . '</span>';
        }

        return $price;
    }
}
