<?php
/**
 * File Environment
 * This class is used to create the file environment.
 *
 * @package StaticSnap
 */

namespace StaticSnap\Environments;

use StaticSnap\Config\Options;
use StaticSnap\Environments\Environment_Type_Base;
use StaticSnap\Github\Github_Manager;


/**
 * This class is used to create the file environment.
 */
final class Github_Environment extends Environment_Type_Base {


	/**
	 * Is ready to use extra setup url
	 *
	 * @var string
	 */
	protected $is_ready_to_use_extra_setup_url = '/github';


	/**
	 * Is ready to use
	 *
	 * @return boolean
	 */
	public function is_ready_to_use(): bool {
		// on ready to use check if the github app is installed. We don't need the cache here.
		$installations = $this->get_github_app_user_installations( false );
		if ( empty( $installations ) ) {
			$this->is_ready_to_use_disabled_message = __( 'There is no GitHub installation connected to Static Snap. Please connect Static Snap to GitHub to use this environment. You will be redirected to the GitHub connection page.', 'static-snap' );
			return false;
		}
		return true;
	}

	/**
	 * Is available
	 * It's require Connect and Github Static Snap App installed on the github account to be available
	 * Github App: https://github.com/apps/static-snap
	 *
	 * @return boolean
	 */
	public function is_available(): bool {
		// get connected option.
		$options = Options::instance()->get_options();
		if ( empty( $options['connect']['installation_id'] ) ) {
			$this->disabled_reason = __( 'Connect is not available', 'static-snap' );
			return false;
		}

		return true;
	}
	/**
	 * Needs Static Snap connect to be available
	 *
	 * @return boolean
	 */
	public function needs_connect(): bool {
		return true;
	}

	/**
	 * Needs zip file
	 *
	 * @return bool
	 */
	public function needs_zip(): bool {
		return true;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'github';
	}

	/**
	 * Get github app user installations
	 *
	 * @param bool $cache use cache.
	 */
	public static function get_github_app_user_installations( $cache = true ) {
		$github            = new Github_Manager();
		$raw_installations = $github->get_github_app_user_installations( $cache );

		$installations = array();

		foreach ( $raw_installations['data'] as $installation ) {
			$installations[] = array(
				'label' => $installation['git_installation_owner'],
				'value' => $installation['git_installation_id'],
			);
		}

		return $installations;
	}

	/**
	 * Get repositories from GitHub
	 *
	 * @param int  $installation installation id.
	 * @param int  $page page number.
	 * @param bool $cache use cache.
	 */
	public static function get_github_repositories( $installation, $page = 1, $cache = true ) {
		$github = new Github_Manager();
		$repos  = $github->get_user_repositories( $installation, $page, $cache );

		$repositories = array();

		foreach ( $repos['data'] as $repo ) {
			$repositories[] = array(
				'label' => $repo['git_repository_name'],
				'value' => $repo['git_repository_name'],
			);
		}

		return $repositories;
	}

	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => field definition
	 * @example array
	 *  [
	 *      'api_key' => array( 'type' => 'text' ),
	 *      'api_secret' => array( 'type' => 'text' ),
	 *  ]
	 */
	public function get_settings_fields(): array {

		$installations = self::get_github_app_user_installations();

		return array(

			'installation' => array(
				'type'           => 'array',
				'label'          => 'Account',
				'required'       => true,
				'items'          => $installations,
				// reload items wp-json url.
				'reloadItemsUrl' => 'static-snap/v1/github-environment/installations?cache=0',
				'helperText'     => __( 'The installation that owns the repository. This is usually the account or organization name.', 'static-snap' ),
			),

			'repository' => array(
				'type'           => 'array',
				'label'          => 'Repository',
				'required'       => true,
				'dependsOn'      => 'settings.installation',
				'items'          => array(),
				// reload items wp-json url.
				'reloadItemsUrl' => 'static-snap/v1/github-environment/repositories?cache=0',
				// translators: %s is the link to create a new repository.
				'helperText'     => sprintf( __( 'If you need to create a new repository, you can do so on GitHub. Simply go to GitHub’s new repository page at %s and then click on reload.', 'static-snap' ), 'https://github.com/new' ),
			),
			'branch' => array(
				'type'       => 'text',
				'label'      => 'Branch',
				'required'   => true,
				'helperText' => __( 'Specify the branch to deploy to. If the branch doesn’t exist, Static Snap will create it. If it does exist, all contents will be replaced by the static site.​', 'static-snap' ),
			),

		);
	}

	/**
	 * Test if the environment is configured correctly
	 */
	public function is_configured(): bool {
		$settings = $this->get_settings();

		if ( empty( $settings['repository'] ) ) {

			$this->add_error( 'repository', 'Repository is required' );
		}
		if ( empty( $settings['branch'] ) ) {
			$this->add_error( 'branch', 'Branch is required' );
		}
		return empty( $this->errors );
	}
	/**
	 * This method is called when a build is published
	 *
	 * @param string $path path to the build.
	 * @return bool true if the build is published.
	 */
	public function on_publish( string $path ): bool {
		// TODO: Implement on_publish() method.
		return true;
	}

	/**
	 * Get deployment tasks
	 *
	 * @return array
	 */
	public function get_deployment_tasks(): array {
		return Github_Manager::get_deployment_tasks();
	}
}
