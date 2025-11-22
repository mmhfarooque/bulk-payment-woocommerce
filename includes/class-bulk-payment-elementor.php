<?php
/**
 * Bulk Payment Elementor Widget
 *
 * @package Bulk_Payment_WC
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Bulk_Payment_Elementor
 */
class Bulk_Payment_Elementor {

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
        // Register widget
        add_action('elementor/widgets/register', array($this, 'register_widget'));

        // Register widget category
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_category'));
    }

    /**
     * Check if Elementor is active
     */
    public static function is_elementor_active() {
        return did_action('elementor/loaded');
    }

    /**
     * Add Elementor category
     */
    public function add_elementor_category($elements_manager) {
        $elements_manager->add_category(
            'bulk-payment',
            array(
                'title' => __('Bulk Payment', 'bulk-payment-wc'),
                'icon' => 'fa fa-money',
            )
        );
    }

    /**
     * Register widget
     */
    public function register_widget($widgets_manager) {
        // Widget class is defined below in this same file
        if (class_exists('\Bulk_Payment_Elementor_Widget')) {
            $widgets_manager->register(new \Bulk_Payment_Elementor_Widget());
        }
    }
}

/**
 * Elementor Widget Class
 */
if (class_exists('\Elementor\Widget_Base')) {

    class Bulk_Payment_Elementor_Widget extends \Elementor\Widget_Base {

        /**
         * Get widget name
         */
        public function get_name() {
            return 'bulk_payment_form';
        }

        /**
         * Get widget title
         */
        public function get_title() {
            return __('Bulk Payment Form', 'bulk-payment-wc');
        }

        /**
         * Get widget icon
         */
        public function get_icon() {
            return 'eicon-price-table';
        }

        /**
         * Get widget categories
         */
        public function get_categories() {
            return array('bulk-payment', 'general');
        }

        /**
         * Get widget keywords
         */
        public function get_keywords() {
            return array('payment', 'bulk', 'donation', 'money', 'woocommerce');
        }

        /**
         * Register widget controls
         */
        protected function register_controls() {

            // Content Section
            $this->start_controls_section(
                'content_section',
                array(
                    'label' => __('Content', 'bulk-payment-wc'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                )
            );

            $this->add_control(
                'show_image',
                array(
                    'label' => __('Show Image', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Show', 'bulk-payment-wc'),
                    'label_off' => __('Hide', 'bulk-payment-wc'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                )
            );

            $this->add_control(
                'image_position',
                array(
                    'label' => __('Image Position', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'left',
                    'options' => array(
                        'left' => __('Left', 'bulk-payment-wc'),
                        'right' => __('Right', 'bulk-payment-wc'),
                        'top' => __('Top', 'bulk-payment-wc'),
                    ),
                    'condition' => array(
                        'show_image' => 'yes',
                    ),
                )
            );

            $this->add_control(
                'show_title',
                array(
                    'label' => __('Show Title', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Show', 'bulk-payment-wc'),
                    'label_off' => __('Hide', 'bulk-payment-wc'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                )
            );

            $this->add_control(
                'show_description',
                array(
                    'label' => __('Show Description', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Show', 'bulk-payment-wc'),
                    'label_off' => __('Hide', 'bulk-payment-wc'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                )
            );

            $this->add_control(
                'button_text',
                array(
                    'label' => __('Button Text', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('Add to Cart', 'bulk-payment-wc'),
                    'placeholder' => __('Add to Cart', 'bulk-payment-wc'),
                )
            );

            $this->add_control(
                'layout',
                array(
                    'label' => __('Layout', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'default',
                    'options' => array(
                        'default' => __('Default', 'bulk-payment-wc'),
                        'minimal' => __('Minimal', 'bulk-payment-wc'),
                        'compact' => __('Compact', 'bulk-payment-wc'),
                    ),
                )
            );

            $this->end_controls_section();

            // Style Section - Title
            $this->start_controls_section(
                'title_style_section',
                array(
                    'label' => __('Title', 'bulk-payment-wc'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_control(
                'title_color',
                array(
                    'label' => __('Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-title' => 'color: {{VALUE}}',
                    ),
                )
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                array(
                    'name' => 'title_typography',
                    'selector' => '{{WRAPPER}} .bulk-payment-title',
                )
            );

            $this->end_controls_section();

            // Style Section - Button
            $this->start_controls_section(
                'button_style_section',
                array(
                    'label' => __('Button', 'bulk-payment-wc'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_control(
                'button_text_color',
                array(
                    'label' => __('Text Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-submit-button' => 'color: {{VALUE}}',
                    ),
                )
            );

            $this->add_control(
                'button_background_color',
                array(
                    'label' => __('Background Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-submit-button' => 'background-color: {{VALUE}}',
                    ),
                )
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                array(
                    'name' => 'button_typography',
                    'selector' => '{{WRAPPER}} .bulk-payment-submit-button',
                )
            );

            $this->add_control(
                'button_border_radius',
                array(
                    'label' => __('Border Radius', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => array('px', '%'),
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-submit-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                )
            );

            $this->add_control(
                'button_padding',
                array(
                    'label' => __('Padding', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => array('px', 'em', '%'),
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-submit-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                )
            );

            $this->end_controls_section();

            // Style Section - Input
            $this->start_controls_section(
                'input_style_section',
                array(
                    'label' => __('Input Field', 'bulk-payment-wc'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_control(
                'input_text_color',
                array(
                    'label' => __('Text Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-amount-input' => 'color: {{VALUE}}',
                    ),
                )
            );

            $this->add_control(
                'input_background_color',
                array(
                    'label' => __('Background Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-amount-input' => 'background-color: {{VALUE}}',
                    ),
                )
            );

            $this->add_control(
                'input_border_color',
                array(
                    'label' => __('Border Color', 'bulk-payment-wc'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} .bulk-payment-amount-input' => 'border-color: {{VALUE}}',
                    ),
                )
            );

            $this->end_controls_section();
        }

        /**
         * Render widget output
         */
        protected function render() {
            $settings = $this->get_settings_for_display();

            // Build shortcode attributes
            $atts = array(
                'layout' => $settings['layout'],
                'show_image' => $settings['show_image'],
                'show_title' => $settings['show_title'],
                'show_description' => $settings['show_description'],
                'button_text' => $settings['button_text'],
                'image_position' => isset($settings['image_position']) ? $settings['image_position'] : 'left',
            );

            // Render shortcode
            $shortcode = Bulk_Payment_Shortcode::get_instance();
            echo $shortcode->render_shortcode($atts);
        }
    }
}
