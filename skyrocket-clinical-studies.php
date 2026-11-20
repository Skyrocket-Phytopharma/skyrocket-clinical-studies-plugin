<?php
/**
 * Plugin Name: Skyrocket Clinical Studies Viewer
 * Description: Fetches and displays remote clinical studies JSON data from GitHub using a shortcode.
 * Version:     2.0.6
 * Author:      Skyrocket Digital Development
 * Text Domain: clinical-studies-viewer
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


// GitHub updater configuration.
$github_updater_config = [
    'username' => 'Skyrocket-Phytopharma',
    'repository' => 'skyrocket-clinical-studies-plugin',
    'plugin_file' => __FILE__,
];

// Updater code
require_once __DIR__ . '/vendor/lavamitts/skyrocket-plugin-updater/src/plugin-updater.php';


// Define plugin constants
define('SKYROCKET_STUDIES_PATH', plugin_dir_path(__FILE__));

// Require the core class file
require_once SKYROCKET_STUDIES_PATH . 'includes/class-clinical-studies.php';

// Initialize the plugin
if (class_exists('Skyrocket_Clinical_Studies')) {
    new Skyrocket_Clinical_Studies();
}

/**
 * Add a "JS Snippets" link on the Plugins screen for Skyrocket Script Manager.
 *
 * Replace __FILE__ with the path to your main plugin file if placed elsewhere.
 */
add_filter(
    'plugin_action_links_' . plugin_basename(__FILE__),
    static function (array $links): array {
        $snippets_url = admin_url('edit.php?post_type=js_snippet');
        $snippets_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url($snippets_url),
            esc_html__('JS Snippets', 'skyrocket-script-manager')
        );

        // Prepend before "Deactivate" (or use $links[] = $snippets_link to append at the end).
        array_unshift($links, $snippets_link);

        return $links;
    }
);