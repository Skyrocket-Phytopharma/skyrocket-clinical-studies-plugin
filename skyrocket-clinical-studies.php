<?php
/**
 * Plugin Name: Skyrocket Clinical Studies Viewer
 * Description: Fetches and displays remote clinical studies JSON data from GitHub using a shortcode.
 * Version:     2.0.3
 * Author:      Skyrocket Digital Development
 * Text Domain: clinical-studies-viewer
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


// GitHub updater configuration.
$github_updater_config = [
    'username' => 'Skyrocket-Phytopharma',
    'repository' => 'skyrocket-clinical-studies',
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