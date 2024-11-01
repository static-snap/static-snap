<?php
/**
 * Application
 *
 * @package StaticSnap
 */

namespace StaticSnap;

use StaticSnap\Dashboard\Admin_Bar;
use StaticSnap\Frontend\Frontend;
use StaticSnap\Local_Dev\Local_Dev;
use StaticSnap\Rest\Rest;
use StaticSnap\Traits\Singleton;
use StaticSnap\Config\Options;
use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Extensions_Types;
use StaticSnap\Constants\Filters;

use StaticSnap\Dashboard\Settings;
use StaticSnap\Database\Deployment_History_Database;
use StaticSnap\Environments\File_Environment;
use StaticSnap\Environments\Github_Environment;

use StaticSnap\Deployment\Deployment_Process;
use StaticSnap\Interfaces\Environment_Interface;
use StaticSnap\Interfaces\Environment_Type_Interface;
use StaticSnap\Interfaces\Extension_Interface;
use StaticSnap\Interfaces\Search_Extension_Interface;


/**
 * Class Application
 */
final class Application {

	use Singleton;


	/**
	 * Deployment
	 *
	 * @var Deployment_Process $deployment
	 */
	protected $deployment = null;
	/**
	 * Search types
	 *
	 * @var array
	 */
	private $extensions = array();

	/**
	 * Options
	 *
	 * @var Options $options
	 */
	protected $options = null;

	/**
	 * Frontend
	 *
	 * @var Frontend $frontend
	 */
	protected $frontend = null;

	/**
	 * Local Dev
	 *
	 * @var Local_Dev $local_dev
	 */
	protected $local_dev = null;


	/**
	 * Initialize the application.
	 */
	private function init() {
		$this->frontend = new Frontend();
		new Settings();
		new Admin_Bar();
		new Rest();
		$this->options = Options::instance();

		do_action( Actions::INIT, $this );

		new File_Environment();
		new Github_Environment();

		// expand environments with new types using do_action.

		// bootstrapping built-in extensions.
		require_once __DIR__ . '/extensions/extensions-bootstrap.php';

		$this->deployment = new Deployment_Process();

		// Run extensions at the end so we can allow INIT_DEPLOYMENT_INTEGRATIONS action to add new extensions.
		foreach ( Extensions_Types::ALL as $action ) {
			do_action( $action, $this );
		}

		// init local dev for dev purposes.
		$this->local_dev = Local_Dev::instance();
	}

	/**
	 * Get options instance
	 */
	public function options(): Options {
		return $this->options;
	}



	/**
	 * Register extension.
	 *
	 * @param Extension_Interface $extension extension.
	 */
	public function register_extension( Extension_Interface $extension ) {
		$this->extensions[ $extension->get_type() ][ $extension->get_name() ] = $extension;
	}

	/**
	 * Get extensions by type
	 *
	 * @param string $type extension type.
	 */
	public function get_extensions_by_type( string $type ): array {
		return $this->extensions[ $type ] ?? array();
	}
	/**
	 * Get extensions
	 *
	 * @return array
	 */
	public function get_extensions(): array {
		return $this->extensions;
	}

	/**
	 * Get environments
	 *
	 * @return array
	 */
	public function get_environments(): array {
		$environments = $this->options->get_options() ['environments'] ?? array();
		ksort( $environments );
		return array_values( $environments );
	}

	/**
	 * Update environments
	 *
	 * @param array $environments environments.
	 */
	public function update_environments( array $environments ) {
		$this->options->set( 'environments', $environments );
		$this->options->save();
	}

	/**
	 * Run deployment
	 *
	 * @param Environment_Interface $environment environment.
	 * @param string                $build_type build type.
	 * @return bool
	 */
	public function run_deployment( Environment_Interface $environment, $build_type = Deployment_Process::FULL_BUILD ): bool {

		return $this->deployment->run( $environment, $build_type );
	}


	/**
	 * Pause deployment
	 *
	 * @return bool
	 */
	public function pause_deployment() {
		$this->deployment->pause();
		return true;
	}

	/**
	 * Cancel deployment
	 *
	 * @return bool
	 */
	public function cancel_deployment() {
		$this->deployment->cancel();
		return true;
	}

	/**
	 * Get deployments
	 *
	 * @return array
	 */
	public function get_deployments(): array {
		return Deployment_History_Database::instance()->get_all() ?? array();
	}

	/**
	 * Get status
	 *
	 * @return array
	 */
	public function get_status(): array {
		$last_deployment = Deployment_History_Database::instance()->get_last_history() ?? array();
		$is_done         = false;

		if ( ! empty( $last_deployment ) && Deployment_History_Database::DONE === (int) $last_deployment['status'] ) {
			$is_done = true;
			Deployment_History_Database::instance()->end_history( Deployment_History_Database::COMPLETED );
		}

		return array(
			'last_deployment' => $last_deployment,
			'is_running'      => $this->deployment->is_active(),
			'is_done'         => $is_done,
			'is_processing'   => $this->deployment->is_processing(),
			'is_cancelled'    => $this->deployment->is_cancelled() || Deployment_History_Database::CANCELED === (int) $last_deployment['status'],
		);
	}

	/**
	 * Get deployment
	 *
	 * @return Deployment_Process
	 */
	public function get_deployment(): Deployment_Process {
		return $this->deployment;
	}


	/**
	 * Get API URL
	 *
	 * @param string $path path.
	 * @param string $scope scope. This is used to identify the request source and for dev purposes.
	 * @return string
	 */
	public function get_static_snap_website_url( string $path, $scope = 'backend' ): string {
		$website_url = 'https://staticsnap.com';
		$website_url = apply_filters( Filters::WEBSITE_URL, $website_url, $scope );
		return $website_url . $path;
	}

	/**
	 * Get API URL
	 *
	 * @param string $path path.
	 * @param string $scope scope. This is used to identify the request source and for dev purposes.
	 * @return string
	 */
	public function get_static_snap_api_url( string $path, $scope = 'backend' ): string {
		$api_url = 'https://api.staticsnap.com';
		$api_url = apply_filters( Filters::API_URL, $api_url, $scope );
		return $api_url . $path;
	}

	/**
	 * Get WordPress installation md5
	 * its will be used to identify the site as unique
	 * Because we allow to use localhost sites we need to use database name and other settings as part of the identification
	 * Be careful with this method, if you change the database name or other settings the site will be identified as a new site
	 *
	 * @return string
	 */
	public function get_wp_installation_md5(): string {
		$identity_string = sprintf( 'static-snap%s%s%s%s-identity_string', home_url(), DB_NAME, DB_USER, DB_HOST );
		$identity_string = apply_filters( Filters::WP_INSTALLATION_MD5, $identity_string );
		return md5( $identity_string );
	}

	/**
	 * Get Frontend
	 *
	 * @return Frontend
	 */
	public function get_frontend(): Frontend {
		return $this->frontend;
	}
}
