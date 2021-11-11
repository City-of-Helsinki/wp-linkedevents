<?php
/**
 * Plugin Name: WP LinkedEvents
 * Plugin URI: https://github.com/devgeniem/wp-linked-events
 * Description: LinkedEvents plugin
 * Version: 0.1.0
 * Requires PHP: 7.4
 * Author: Geniem Oy
 * Author URI: https://geniem.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-linked-events
 * Domain Path: /languages
 */

use WPLinkedEvents\LinkedEventsPlugin;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' );
$plugin_version = $plugin_data['Version'];

LinkedEventsPlugin::boot(
    $plugin_version,
    plugin_dir_url( __FILE__ ),
    plugin_dir_path( __FILE__ )
);
