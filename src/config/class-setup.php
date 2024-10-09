<?php
/**
 * Setup
 *
 * @package StaticSnap
 */

namespace StaticSnap\Config;

use StaticSnap\Database\Environments_Database;
use StaticSnap\Environments\Environment;


/**
*
* Default environments, used when the plugin is activated
*
* @var array
*/
const DEFAULT_ENVIRONMENTS = array(
	array(
		'name'             => 'Production',
		'type'             => 'file',
		'destination_type' => 'relative',
		'destination_path' => '/',
		'settings'         => array(
			'path'            => '{static-snap_tmp_dir}/{environment_name}',
			'create_zip_file' => true,
		),
	),
);

/**
 * Setup
 */
final class Setup {



	/**
	 * Run only once after plugin is activated
	 *
	 * @docs https://developer.wordpress.org/reference/functions/register_activation_hook/
	 */
	public static function activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$environments = Environments_Database::instance()->get_all();
		$environments = array();
		if ( empty( $environments ) ) {
			foreach ( DEFAULT_ENVIRONMENTS as $environment_array ) {
				$environment = new Environment(
					0,
					$environment_array['type'],
					$environment_array['name'],
					$environment_array['destination_type'],
					$environment_array['destination_path'],
					$environment_array['settings']
				);
				Environments_Database::instance()->insert( $environment );
			}
		}

		/**
		 * Use this to add a database table after the plugin is activated for example
		 */

		// Clear the permalinks.
		flush_rewrite_rules();
	}

	/**
	 * Run only once after plugin is deactivated
	 *
	 * @docs https://developer.wordpress.org/reference/functions/register_deactivation_hook/
	 */
	public static function deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		/**
		 * Use this to register a function which will be executed when the plugin is deactivated
		 */

		// Clear the permalinks.
		flush_rewrite_rules();

		// Uncomment the following line to see the function in action
		// exit( var_dump( $_GET ) );.
	}

	/**
	 * Run only once after plugin is uninstalled
	 *
	 * @docs https://developer.wordpress.org/reference/functions/register_uninstall_hook/
	 */
	public static function uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		/**
		 * Use this to remove plugin data and residues after the plugin is uninstalled for example
		 */

		// Uncomment the following line to see the function in action.
		// exit( var_dump( $_GET ) );.
	}
}
