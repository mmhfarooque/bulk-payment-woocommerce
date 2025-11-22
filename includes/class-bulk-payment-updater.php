<?php
/**
 * GitHub Auto-Updater for Bulk Payment WooCommerce
 *
 * Checks for updates from GitHub releases and notifies WordPress
 * when new versions are available.
 *
 * @package Bulk_Payment_WC
 * @since 1.0.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GitHub Updater Class
 */
class Bulk_Payment_Updater {

    /**
     * GitHub username
     */
    private $username = 'mmhfarooque';

    /**
     * GitHub repository name
     */
    private $repository = 'bulk-payment-woocommerce';

    /**
     * Plugin basename
     */
    private $basename;

    /**
     * Plugin data
     */
    private $plugin_data;

    /**
     * GitHub API data
     */
    private $github_data;

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
        $this->basename = BULK_PAYMENT_WC_PLUGIN_BASENAME;

        // Set plugin data
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->basename);

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check for updates
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));

        // Plugin information
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);

        // After update
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

        // Add settings link to admin
        add_filter('plugin_action_links_' . $this->basename, array($this, 'add_update_link'));

        // Clear cache on admin init
        if (isset($_GET['bulk_payment_clear_cache'])) {
            add_action('admin_init', array($this, 'clear_cache'));
        }
    }

    /**
     * Get GitHub data from API
     */
    private function get_github_data() {
        if (!empty($this->github_data)) {
            return $this->github_data;
        }

        // Check transient
        $transient_key = 'bulk_payment_wc_github_data';
        $github_data = get_transient($transient_key);

        if (false !== $github_data) {
            $this->github_data = $github_data;
            return $this->github_data;
        }

        // Fetch from GitHub API
        $api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->username,
            $this->repository
        );

        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return false;
        }

        // Store in transient (check every 12 hours)
        set_transient($transient_key, $data, 12 * HOUR_IN_SECONDS);

        $this->github_data = $data;
        return $this->github_data;
    }

    /**
     * Check for updates
     */
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $github_data = $this->get_github_data();

        if (!$github_data) {
            return $transient;
        }

        // Get version from tag (remove 'v' prefix if present)
        $github_version = ltrim($github_data['tag_name'], 'v');
        $current_version = $this->plugin_data['Version'];

        // Compare versions
        if (version_compare($github_version, $current_version, '>')) {
            // Find the plugin ZIP asset
            $download_url = $this->get_download_url($github_data);

            if ($download_url) {
                $plugin_data = array(
                    'slug' => dirname($this->basename),
                    'new_version' => $github_version,
                    'url' => $github_data['html_url'],
                    'package' => $download_url,
                    'tested' => $this->plugin_data['RequiresWP'] ?? '6.0',
                    'requires_php' => $this->plugin_data['RequiresPHP'] ?? '7.4',
                );

                $transient->response[$this->basename] = (object) $plugin_data;
            }
        }

        return $transient;
    }

    /**
     * Get download URL from release assets
     */
    private function get_download_url($github_data) {
        if (empty($github_data['assets'])) {
            return false;
        }

        // Look for ZIP file
        foreach ($github_data['assets'] as $asset) {
            if (strpos($asset['name'], '.zip') !== false) {
                return $asset['browser_download_url'];
            }
        }

        // Fallback to zipball if no asset found
        return $github_data['zipball_url'] ?? false;
    }

    /**
     * Plugin information popup
     */
    public function plugin_info($false, $action, $response) {
        // Check if this is for our plugin
        if ($action !== 'plugin_information') {
            return $false;
        }

        if (empty($response->slug) || $response->slug !== dirname($this->basename)) {
            return $false;
        }

        $github_data = $this->get_github_data();

        if (!$github_data) {
            return $false;
        }

        $github_version = ltrim($github_data['tag_name'], 'v');

        $plugin_info = new stdClass();
        $plugin_info->name = $this->plugin_data['Name'];
        $plugin_info->slug = dirname($this->basename);
        $plugin_info->version = $github_version;
        $plugin_info->author = $this->plugin_data['Author'];
        $plugin_info->homepage = $this->plugin_data['PluginURI'];
        $plugin_info->requires = $this->plugin_data['RequiresWP'] ?? '5.8';
        $plugin_info->tested = '6.4';
        $plugin_info->requires_php = $this->plugin_data['RequiresPHP'] ?? '7.4';
        $plugin_info->download_link = $this->get_download_url($github_data);
        $plugin_info->sections = array(
            'description' => $this->plugin_data['Description'],
            'changelog' => $this->parse_changelog($github_data['body']),
        );
        $plugin_info->banners = array();
        $plugin_info->last_updated = $github_data['published_at'];

        return $plugin_info;
    }

    /**
     * Parse changelog from release notes
     */
    private function parse_changelog($body) {
        // Convert markdown to HTML (basic)
        $body = wp_kses_post($body);
        $body = wpautop($body);
        return $body;
    }

    /**
     * After plugin install
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        // Check if this is our plugin
        if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->basename) {
            return $response;
        }

        // Move files if needed
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->basename);
        $wp_filesystem->move($result['destination'], $plugin_folder);
        $result['destination'] = $plugin_folder;

        // Clear cache
        $this->clear_cache();

        // Activate plugin if it was active before
        if ($this->is_plugin_active()) {
            activate_plugin($this->basename);
        }

        return $response;
    }

    /**
     * Check if plugin is active
     */
    private function is_plugin_active() {
        return is_plugin_active($this->basename);
    }

    /**
     * Add update check link to plugin actions
     */
    public function add_update_link($links) {
        $check_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('plugins.php?bulk_payment_clear_cache=1'),
            __('Check for Updates', 'bulk-payment-wc')
        );
        array_unshift($links, $check_link);
        return $links;
    }

    /**
     * Clear update cache
     */
    public function clear_cache() {
        delete_transient('bulk_payment_wc_github_data');
        delete_site_transient('update_plugins');

        if (isset($_GET['bulk_payment_clear_cache'])) {
            wp_safe_redirect(admin_url('plugins.php?cache-cleared=1'));
            exit;
        }
    }

    /**
     * Get current version
     */
    public function get_current_version() {
        return $this->plugin_data['Version'];
    }

    /**
     * Get latest version from GitHub
     */
    public function get_latest_version() {
        $github_data = $this->get_github_data();

        if (!$github_data) {
            return false;
        }

        return ltrim($github_data['tag_name'], 'v');
    }

    /**
     * Check if update is available
     */
    public function is_update_available() {
        $current = $this->get_current_version();
        $latest = $this->get_latest_version();

        if (!$latest) {
            return false;
        }

        return version_compare($latest, $current, '>');
    }
}
