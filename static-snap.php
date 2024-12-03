<?php
/**
 * Static Snap
 *
 * @package StaticSnap
 * @copyright Copyright (c) 2024 Leandro Emanuel Lopez
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

/**
 *
 * Plugin Name:     Static Snap
 * Plugin URI:      https://staticsnap.com/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Description:     Static Snap transforms WordPress into a powerful tool for creating static websites.
 * Version:         0.2.7
 * Author:          Static Snap
 * Author URI:      https://staticsnap.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * License:         GPLv3
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 * Plugin URI:      https://staticsnap.com
 * Text Domain:     static-snap
 * Requires PHP:    7.4
 * Requires WP:     6.5.0
 * Namespace:       StaticSnap
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



/**
 * Define the default root file of the plugin
 *
 * @since 1.0.0
 */
define( 'STATIC_SNAP_PLUGIN_FILE', __FILE__ );
define( 'STATIC_SNAP_PLUGIN_DIR', __DIR__ );
define( 'STATIC_SNAP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Load PSR4 autoloader
 *
 * @since 1.0.0
 */
$staticsnap_autoloader = require plugin_dir_path( STATIC_SNAP_PLUGIN_FILE ) . 'vendor/autoload.php';

/**
 * Setup hooks (activation, deactivation, uninstall)
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, array( 'StaticSnap\Config\Setup', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'StaticSnap\Config\Setup', 'deactivation' ) );
register_uninstall_hook( __FILE__, array( 'StaticSnap\Config\Setup', 'uninstall' ) );

/**
 * Bootstrap the plugin
 *
 * @since 1.0.0
 */
if ( ! class_exists( '\StaticSnap\Bootstrap' ) ) {
	wp_die( esc_html__( 'Static Snap is unable to find the Bootstrap class.', 'static-snap' ) );
}
add_action(
	'plugins_loaded',
	static function () use ( $staticsnap_autoloader ) {
		/**
		 * Bootstrap the plugin
		 *
		 * @see \StaticSnap\Bootstrap
		 */
		try {
			load_plugin_textdomain( 'static-snap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			new \StaticSnap\Bootstrap( $staticsnap_autoloader );
		} catch ( Exception $e ) {
			wp_die( esc_html__( 'Static Snap is unable to run the Bootstrap class.', 'static-snap' ) );
		}
	},
	100
);
