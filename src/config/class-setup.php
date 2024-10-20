<?php
/**
 * Setup
 *
 * @package StaticSnap
 */

namespace StaticSnap\Config;

use StaticSnap\Database\Deployment_History_Database;
use StaticSnap\Database\Environments_Database;
use StaticSnap\Database\Replacements_URLS_Database;
use StaticSnap\Database\URLS_Database;
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

		Replacements_URLS_Database::instance()->drop_table();
		Deployment_History_Database::instance()->drop_table();
		URLS_Database::instance()->drop_table();
		Environments_Database::instance()->drop_table();

		// remove all options
		delete_option( Plugin::SLUG );


	}
}
