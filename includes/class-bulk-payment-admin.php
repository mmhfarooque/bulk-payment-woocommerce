<?php
/**
 * Bulk Payment Admin Handler
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Admin
 */
class Bulk_Payment_Admin {

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
        // Add settings menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));

        // Add custom column to products list
        add_filter('manage_product_posts_columns', array($this, 'add_product_column'));
        add_action('manage_product_posts_custom_column', array($this, 'render_product_column'), 10, 2);

        // Add admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . BULK_PAYMENT_WC_PLUGIN_BASENAME, array($this, 'add_plugin_action_links'));

        // Add quick edit support
        add_action('quick_edit_custom_box', array($this, 'add_quick_edit_fields'), 10, 2);
        add_action('save_post', array($this, 'save_quick_edit_data'));

        // Output dynamic CSS
        add_action('wp_head', array($this, 'output_custom_styles'), 100);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Bulk Payment Settings', 'bulk-payment-wc'),
            __('Bulk Payment', 'bulk-payment-wc'),
            'manage_woocommerce',
            'bulk-payment-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('bulk_payment_settings', 'bulk_payment_enable_for_all');
        register_setting('bulk_payment_settings', 'bulk_payment_default_min');
        register_setting('bulk_payment_settings', 'bulk_payment_default_max');
        register_setting('bulk_payment_settings', 'bulk_payment_default_label');
        register_setting('bulk_payment_settings', 'bulk_payment_default_placeholder');
        register_setting('bulk_payment_settings', 'bulk_payment_hide_regular_price');
        register_setting('bulk_payment_settings', 'bulk_payment_product_title');
        register_setting('bulk_payment_settings', 'bulk_payment_product_description');
        register_setting('bulk_payment_settings', 'bulk_payment_product_image_id');
        register_setting('bulk_payment_settings', 'bulk_payment_checkout_type');

        // Style customization settings
        register_setting('bulk_payment_settings', 'bulk_payment_primary_color');
        register_setting('bulk_payment_settings', 'bulk_payment_secondary_color');
        register_setting('bulk_payment_settings', 'bulk_payment_text_color');
        register_setting('bulk_payment_settings', 'bulk_payment_accent_color');
        register_setting('bulk_payment_settings', 'bulk_payment_font_family');
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        // Handle settings save
        if (isset($_POST['bulk_payment_save_settings'])) {
            check_admin_referer('bulk_payment_settings');

            update_option('bulk_payment_enable_for_all', isset($_POST['bulk_payment_enable_for_all']) ? 'yes' : 'no');
            update_option('bulk_payment_default_min', sanitize_text_field($_POST['bulk_payment_default_min']));
            update_option('bulk_payment_default_max', sanitize_text_field($_POST['bulk_payment_default_max']));
            update_option('bulk_payment_default_label', sanitize_text_field($_POST['bulk_payment_default_label']));
            update_option('bulk_payment_default_placeholder', sanitize_text_field($_POST['bulk_payment_default_placeholder']));
            update_option('bulk_payment_hide_regular_price', isset($_POST['bulk_payment_hide_regular_price']) ? 'yes' : 'no');
            update_option('bulk_payment_checkout_type', sanitize_text_field($_POST['bulk_payment_checkout_type']));

            // Save style settings
            update_option('bulk_payment_primary_color', sanitize_hex_color($_POST['bulk_payment_primary_color']));
            update_option('bulk_payment_secondary_color', sanitize_hex_color($_POST['bulk_payment_secondary_color']));
            update_option('bulk_payment_text_color', sanitize_hex_color($_POST['bulk_payment_text_color']));
            update_option('bulk_payment_accent_color', sanitize_hex_color($_POST['bulk_payment_accent_color']));
            update_option('bulk_payment_font_family', sanitize_text_field($_POST['bulk_payment_font_family']));

            // Update product settings
            if (isset($_POST['bulk_payment_product_title'])) {
                Bulk_Payment_Product_Creator::update_bulk_payment_product(array(
                    'title' => sanitize_text_field($_POST['bulk_payment_product_title']),
                    'description' => wp_kses_post($_POST['bulk_payment_product_description']),
                    'image_id' => absint($_POST['bulk_payment_product_image_id']),
                    'min_amount' => sanitize_text_field($_POST['bulk_payment_default_min']),
                    'max_amount' => sanitize_text_field($_POST['bulk_payment_default_max']),
                    'label' => sanitize_text_field($_POST['bulk_payment_default_label']),
                    'placeholder' => sanitize_text_field($_POST['bulk_payment_default_placeholder']),
                ));
            }

            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'bulk-payment-wc') . '</p></div>';
        }

        $enable_for_all = get_option('bulk_payment_enable_for_all', 'no');
        $default_min = get_option('bulk_payment_default_min', '');
        $default_max = get_option('bulk_payment_default_max', '');
        $default_label = get_option('bulk_payment_default_label', __('Enter Amount', 'bulk-payment-wc'));
        $default_placeholder = get_option('bulk_payment_default_placeholder', __('Enter your amount', 'bulk-payment-wc'));
        $hide_regular_price = get_option('bulk_payment_hide_regular_price', 'no');
        $product_title = get_option('bulk_payment_product_title', __('Make a Payment', 'bulk-payment-wc'));
        $product_description = get_option('bulk_payment_product_description', __('Enter any amount you wish to pay.', 'bulk-payment-wc'));
        $product_image_id = get_option('bulk_payment_product_image_id', '');
        $checkout_type = get_option('bulk_payment_checkout_type', 'cart');

        // Style settings (empty by default to use CSS defaults)
        $primary_color = get_option('bulk_payment_primary_color', '');
        $secondary_color = get_option('bulk_payment_secondary_color', '');
        $text_color = get_option('bulk_payment_text_color', '');
        $accent_color = get_option('bulk_payment_accent_color', '');
        $font_family = get_option('bulk_payment_font_family', '');

        // Get product ID
        $product_id = Bulk_Payment_Product_Creator::get_bulk_payment_product_id();

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="bulk-payment-admin-header" style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2c3e50;">
                <h2><?php _e('Bulk Payment for WooCommerce', 'bulk-payment-wc'); ?></h2>
                <p><?php _e('Allow customers to pay any amount of money for symbolic products without shipping information.', 'bulk-payment-wc'); ?></p>
                <p><strong><?php _e('Version:', 'bulk-payment-wc'); ?></strong> <?php echo BULK_PAYMENT_WC_VERSION; ?></p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('bulk_payment_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Global Settings', 'bulk-payment-wc'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="bulk_payment_enable_for_all" value="yes" <?php checked($enable_for_all, 'yes'); ?>>
                                <?php _e('Enable bulk payment for all products by default', 'bulk-payment-wc'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, bulk payment will be available for all products unless specifically disabled.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Default Amount Label', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_default_label" value="<?php echo esc_attr($default_label); ?>" class="regular-text">
                            <p class="description"><?php _e('Default label for the amount input field.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Default Placeholder', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_default_placeholder" value="<?php echo esc_attr($default_placeholder); ?>" class="regular-text">
                            <p class="description"><?php _e('Default placeholder text for the amount input field.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Default Minimum Amount', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="number" name="bulk_payment_default_min" value="<?php echo esc_attr($default_min); ?>" step="0.01" min="0" class="regular-text">
                            <p class="description"><?php echo sprintf(__('Default minimum amount in %s. Leave empty for no limit.', 'bulk-payment-wc'), get_woocommerce_currency()); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Default Maximum Amount', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="number" name="bulk_payment_default_max" value="<?php echo esc_attr($default_max); ?>" step="0.01" min="0" class="regular-text">
                            <p class="description"><?php echo sprintf(__('Default maximum amount in %s. Leave empty for no limit.', 'bulk-payment-wc'), get_woocommerce_currency()); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Display Options', 'bulk-payment-wc'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="bulk_payment_hide_regular_price" value="yes" <?php checked($hide_regular_price, 'yes'); ?>>
                                <?php _e('Hide regular price for bulk payment products', 'bulk-payment-wc'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, the regular product price will be hidden and replaced with custom text.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top: 40px;"><?php _e('Bulk Payment Product Configuration', 'bulk-payment-wc'); ?></h2>
                <p><?php _e('Configure the dedicated bulk payment product that will be used with the shortcode and Elementor widget.', 'bulk-payment-wc'); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Product Title', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_product_title" value="<?php echo esc_attr($product_title); ?>" class="regular-text">
                            <p class="description"><?php _e('Title for the bulk payment product.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Product Description', 'bulk-payment-wc'); ?></th>
                        <td>
                            <?php
                            wp_editor($product_description, 'bulk_payment_product_description', array(
                                'textarea_name' => 'bulk_payment_product_description',
                                'textarea_rows' => 5,
                                'media_buttons' => false,
                                'teeny' => true,
                            ));
                            ?>
                            <p class="description"><?php _e('Description shown on the bulk payment form.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Product Image', 'bulk-payment-wc'); ?></th>
                        <td>
                            <div class="bulk-payment-image-upload">
                                <div class="image-preview" style="margin-bottom: 10px;">
                                    <?php if ($product_image_id): ?>
                                        <?php echo wp_get_attachment_image($product_image_id, 'medium'); ?>
                                    <?php else: ?>
                                        <p><?php _e('No image selected', 'bulk-payment-wc'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="bulk_payment_product_image_id" id="bulk_payment_product_image_id" value="<?php echo esc_attr($product_image_id); ?>">
                                <button type="button" class="button bulk-payment-upload-image"><?php _e('Upload/Select Image', 'bulk-payment-wc'); ?></button>
                                <button type="button" class="button bulk-payment-remove-image" <?php if (!$product_image_id) echo 'style="display:none;"'; ?>><?php _e('Remove Image', 'bulk-payment-wc'); ?></button>
                            </div>
                            <p class="description"><?php _e('Image displayed on the left side of the bulk payment form.', 'bulk-payment-wc'); ?></p>

                            <script>
                            jQuery(document).ready(function($) {
                                var mediaUploader;

                                $('.bulk-payment-upload-image').on('click', function(e) {
                                    e.preventDefault();

                                    if (mediaUploader) {
                                        mediaUploader.open();
                                        return;
                                    }

                                    mediaUploader = wp.media({
                                        title: '<?php _e('Choose Image', 'bulk-payment-wc'); ?>',
                                        button: {
                                            text: '<?php _e('Use this image', 'bulk-payment-wc'); ?>'
                                        },
                                        multiple: false
                                    });

                                    mediaUploader.on('select', function() {
                                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                                        $('#bulk_payment_product_image_id').val(attachment.id);
                                        $('.image-preview').html('<img src="' + attachment.url + '" style="max-width: 300px;">');
                                        $('.bulk-payment-remove-image').show();
                                    });

                                    mediaUploader.open();
                                });

                                $('.bulk-payment-remove-image').on('click', function(e) {
                                    e.preventDefault();
                                    $('#bulk_payment_product_image_id').val('');
                                    $('.image-preview').html('<p><?php _e('No image selected', 'bulk-payment-wc'); ?></p>');
                                    $(this).hide();
                                });
                            });
                            </script>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Checkout Type', 'bulk-payment-wc'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="bulk_payment_checkout_type" value="cart" <?php checked($checkout_type, 'cart'); ?>>
                                    <strong><?php _e('Regular Checkout', 'bulk-payment-wc'); ?></strong>
                                    <p class="description"><?php _e('Add to cart → Checkout page with Name, Email, Phone (no shipping)', 'bulk-payment-wc'); ?></p>
                                </label>
                                <br><br>
                                <label>
                                    <input type="radio" name="bulk_payment_checkout_type" value="direct" <?php checked($checkout_type, 'direct'); ?>>
                                    <strong><?php _e('Direct Checkout', 'bulk-payment-wc'); ?></strong>
                                    <p class="description"><?php _e('Collect Name, Email, Phone on product page → Direct to payment', 'bulk-payment-wc'); ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top: 40px;"><?php _e('Style Customization', 'bulk-payment-wc'); ?></h2>
                <p><?php _e('Customize the colors and fonts for your bulk payment forms. Leave fields empty to use the default neutral theme.', 'bulk-payment-wc'); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Primary Color', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_primary_color" value="<?php echo esc_attr($primary_color); ?>" class="bulk-payment-color-picker" data-default-color="">
                            <p class="description"><?php _e('Used for buttons, borders, and primary elements. Default: #2c3e50', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Secondary Color', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_secondary_color" value="<?php echo esc_attr($secondary_color); ?>" class="bulk-payment-color-picker" data-default-color="">
                            <p class="description"><?php _e('Background color for forms and sections. Default: #f9f9f9', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Text Color', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_text_color" value="<?php echo esc_attr($text_color); ?>" class="bulk-payment-color-picker" data-default-color="">
                            <p class="description"><?php _e('Main text color throughout the form. Default: #333', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Accent Color', 'bulk-payment-wc'); ?></th>
                        <td>
                            <input type="text" name="bulk_payment_accent_color" value="<?php echo esc_attr($accent_color); ?>" class="bulk-payment-color-picker" data-default-color="">
                            <p class="description"><?php _e('Used for hover states and focus effects. Default: #3498db', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Font Family', 'bulk-payment-wc'); ?></th>
                        <td>
                            <select name="bulk_payment_font_family" class="regular-text">
                                <option value="" <?php selected($font_family, ''); ?>><?php _e('Default (System Fonts)', 'bulk-payment-wc'); ?></option>
                                <option value="Georgia, serif" <?php selected($font_family, 'Georgia, serif'); ?>>Georgia (Serif)</option>
                                <option value="'Times New Roman', Times, serif" <?php selected($font_family, "'Times New Roman', Times, serif"); ?>>Times New Roman (Serif)</option>
                                <option value="Arial, Helvetica, sans-serif" <?php selected($font_family, 'Arial, Helvetica, sans-serif'); ?>>Arial (Sans-serif)</option>
                                <option value="Verdana, Geneva, sans-serif" <?php selected($font_family, 'Verdana, Geneva, sans-serif'); ?>>Verdana (Sans-serif)</option>
                                <option value="'Trebuchet MS', sans-serif" <?php selected($font_family, "'Trebuchet MS', sans-serif"); ?>>Trebuchet MS (Sans-serif)</option>
                                <option value="'Courier New', Courier, monospace" <?php selected($font_family, "'Courier New', Courier, monospace"); ?>>Courier New (Monospace)</option>
                                <option value="'Baskerville Poster PT', Georgia, serif" <?php selected($font_family, "'Baskerville Poster PT', Georgia, serif"); ?>>Baskerville Poster PT (Serif)</option>
                            </select>
                            <p class="description"><?php _e('Choose a font family for the bulk payment forms.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top: 40px;"><?php _e('Usage', 'bulk-payment-wc'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Shortcode', 'bulk-payment-wc'); ?></th>
                        <td>
                            <code>[bulk_payment_form]</code>
                            <p class="description"><?php _e('Add this shortcode to any page or post to display the bulk payment form.', 'bulk-payment-wc'); ?></p>
                            <?php if ($product_id): ?>
                                <p><a href="<?php echo get_permalink($product_id); ?>" target="_blank"><?php _e('View Product →', 'bulk-payment-wc'); ?></a></p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Elementor Widget', 'bulk-payment-wc'); ?></th>
                        <td>
                            <p><?php _e('Search for "Bulk Payment Form" in the Elementor widget panel.', 'bulk-payment-wc'); ?></p>
                            <p class="description"><?php _e('Drag and drop the widget onto your page and customize the settings.', 'bulk-payment-wc'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="bulk_payment_save_settings" class="button-primary" value="<?php _e('Save Settings', 'bulk-payment-wc'); ?>">
                </p>
            </form>

            <div class="bulk-payment-usage-info" style="background: #f7f7f7; padding: 20px; margin: 20px 0; border-radius: 4px;">
                <h3><?php _e('How to Use', 'bulk-payment-wc'); ?></h3>
                <ol>
                    <li><?php _e('Edit any product in WooCommerce', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Find the "Bulk Payment Options" box in the sidebar', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Enable "Bulk Payment" for that product', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Configure minimum/maximum amounts and labels (optional)', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Save the product', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Customers will now see a custom amount field on the product page', 'bulk-payment-wc'); ?></li>
                </ol>

                <h3><?php _e('Features', 'bulk-payment-wc'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('Custom amount input on product pages', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Configurable minimum and maximum amounts', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Automatic shipping skip for bulk payment products', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Customizable labels and placeholders', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Works with all payment gateways', 'bulk-payment-wc'); ?></li>
                    <li><?php _e('Translation ready', 'bulk-payment-wc'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        // Show activation notice
        if (get_transient('bulk_payment_wc_activated')) {
            delete_transient('bulk_payment_wc_activated');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        __('Bulk Payment for WooCommerce is now active! Visit the %ssettings page%s to configure.', 'bulk-payment-wc'),
                        '<a href="' . admin_url('admin.php?page=bulk-payment-settings') . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Add custom column to products list
     */
    public function add_product_column($columns) {
        $columns['bulk_payment'] = __('Bulk Payment', 'bulk-payment-wc');
        return $columns;
    }

    /**
     * Render custom column content
     */
    public function render_product_column($column, $post_id) {
        if ($column === 'bulk_payment') {
            $enabled = get_post_meta($post_id, '_bulk_payment_enabled', true);
            if ($enabled === 'yes') {
                echo '<span class="dashicons dashicons-yes" style="color: #46b450;" title="' . __('Enabled', 'bulk-payment-wc') . '"></span>';
            } else {
                echo '<span class="dashicons dashicons-minus" style="color: #ccc;" title="' . __('Disabled', 'bulk-payment-wc') . '"></span>';
            }
        }
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        // Only load on product pages and settings page
        if (!in_array($hook, array('post.php', 'post-new.php', 'edit.php', 'woocommerce_page_bulk-payment-settings'))) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type !== 'product' && $hook !== 'woocommerce_page_bulk-payment-settings') {
            return;
        }

        wp_enqueue_style(
            'bulk-payment-admin',
            BULK_PAYMENT_WC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BULK_PAYMENT_WC_VERSION
        );

        // Enqueue color picker on settings page
        if ($hook === 'woocommerce_page_bulk-payment-settings') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_add_inline_script('wp-color-picker', '
                jQuery(document).ready(function($) {
                    $(".bulk-payment-color-picker").wpColorPicker();
                });
            ');
        }
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=bulk-payment-settings') . '">' . __('Settings', 'bulk-payment-wc') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add quick edit fields
     */
    public function add_quick_edit_fields($column_name, $post_type) {
        if ($column_name !== 'bulk_payment' || $post_type !== 'product') {
            return;
        }

        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Bulk Payment', 'bulk-payment-wc'); ?></span>
                    <span class="input-text-wrap">
                        <select name="_bulk_payment_enabled">
                            <option value="no"><?php _e('Disabled', 'bulk-payment-wc'); ?></option>
                            <option value="yes"><?php _e('Enabled', 'bulk-payment-wc'); ?></option>
                        </select>
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    /**
     * Save quick edit data
     */
    public function save_quick_edit_data($post_id) {
        if (!isset($_POST['_bulk_payment_enabled'])) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, '_bulk_payment_enabled', sanitize_text_field($_POST['_bulk_payment_enabled']));
    }

    /**
     * Output custom styles based on admin settings
     */
    public function output_custom_styles() {
        // Get style settings
        $primary_color = get_option('bulk_payment_primary_color', '');
        $secondary_color = get_option('bulk_payment_secondary_color', '');
        $text_color = get_option('bulk_payment_text_color', '');
        $accent_color = get_option('bulk_payment_accent_color', '');
        $font_family = get_option('bulk_payment_font_family', '');

        // Only output if at least one style setting is configured
        if (empty($primary_color) && empty($secondary_color) && empty($text_color) && empty($accent_color) && empty($font_family)) {
            return;
        }

        // Calculate hover color (darker version of primary color)
        $hover_color = '';
        if (!empty($primary_color)) {
            $hover_color = $this->darken_color($primary_color, 10);
        }

        ?>
        <style type="text/css" id="bulk-payment-custom-styles">
            /* Bulk Payment Custom Styles */
            <?php if (!empty($font_family)): ?>
            .bulk-payment-form-container,
            .bulk-payment-title,
            .bulk-payment-label,
            .bulk-payment-customer-fields label,
            .bulk-payment-customer-fields input,
            .bulk-payment-amount-input {
                font-family: <?php echo esc_attr($font_family); ?> !important;
            }
            <?php endif; ?>

            <?php if (!empty($text_color)): ?>
            .bulk-payment-form-container,
            .bulk-payment-label,
            .bulk-payment-amount-input,
            .bulk-payment-description,
            .bulk-payment-amount-info,
            .bulk-payment-customer-fields label,
            .bulk-payment-customer-fields input {
                color: <?php echo esc_attr($text_color); ?> !important;
            }
            <?php endif; ?>

            <?php if (!empty($primary_color)): ?>
            .bulk-payment-title,
            .bulk-payment-currency-symbol {
                color: <?php echo esc_attr($primary_color); ?> !important;
            }
            .bulk-payment-form,
            .bulk-payment-input-group,
            .bulk-payment-customer-fields {
                border-color: <?php echo esc_attr($primary_color); ?> !important;
            }
            .bulk-payment-input-group {
                border-width: 2px !important;
            }
            .bulk-payment-customer-fields input {
                border-color: <?php echo esc_attr($primary_color); ?> !important;
            }
            .bulk-payment-currency-symbol.currency-left,
            .bulk-payment-currency-symbol.currency-right {
                border-color: <?php echo esc_attr($primary_color); ?> !important;
            }
            .bulk-payment-submit-button {
                background: <?php echo esc_attr($primary_color); ?> !important;
                color: #fff !important;
            }
            <?php if (!empty($hover_color)): ?>
            .bulk-payment-submit-button:hover {
                background: <?php echo esc_attr($hover_color); ?> !important;
                color: #fff !important;
            }
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($secondary_color)): ?>
            .bulk-payment-form {
                background: <?php echo esc_attr($secondary_color); ?> !important;
            }
            .bulk-payment-currency-symbol {
                background: <?php echo esc_attr($secondary_color); ?> !important;
            }
            <?php endif; ?>

            <?php if (!empty($accent_color)): ?>
            .bulk-payment-input-group:focus-within {
                border-color: <?php echo esc_attr($accent_color); ?> !important;
                box-shadow: 0 0 0 3px <?php echo esc_attr($accent_color); ?>33 !important;
            }
            .bulk-payment-customer-fields input:focus {
                border-color: <?php echo esc_attr($accent_color); ?> !important;
                box-shadow: 0 0 0 3px <?php echo esc_attr($accent_color); ?>33 !important;
            }
            <?php endif; ?>
        </style>
        <?php
    }

    /**
     * Darken a hex color by a percentage
     */
    private function darken_color($hex, $percent) {
        // Remove # if present
        $hex = str_replace('#', '', $hex);

        // Convert to RGB
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        // Darken and cast to int to avoid PHP 8.1+ deprecation warnings
        $r = (int) max(0, min(255, $r - ($r * $percent / 100)));
        $g = (int) max(0, min(255, $g - ($g * $percent / 100)));
        $b = (int) max(0, min(255, $b - ($b * $percent / 100)));

        // Convert back to hex
        $r = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
        $g = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
        $b = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

        return '#' . $r . $g . $b;
    }
}
