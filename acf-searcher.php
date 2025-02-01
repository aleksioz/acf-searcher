<?php
/**
 * Plugin Name: acf-searcher
 * Description: A search plugin for ACF fields
 * Version: 1.2.1
 * Author: Aleksa
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin path
define( 'ACF_SEARCHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACF_SEARCHER_URL', plugin_dir_url( __FILE__ ) );

// Include the main plugin class
require_once ACF_SEARCHER_PATH . 'includes/class-init.php';

// Initialize the plugin
add_action( 'plugins_loaded', function() { 
    if ( class_exists( 'ACF' ) ) {
        Init::instance();
    }
}, 20 );

?>