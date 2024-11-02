<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Constants\Filters;
use StaticSnap\Deployment\Task;
use StaticSnap\Deployment\Asset_URL;
use StaticSnap\Deployment\Assets;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Deployment_Process;

/**
 * Get Urls Task class
 */
final class Get_Assets_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Getting Assets';


	/**
	 * Constructor
	 *
	 * @param Deployment_Process $deployment_process Deployment process.
	 */
	public function __construct( Deployment_Process $deployment_process ) {
		$this->ignore_other_themes_folder();
		parent::__construct( $deployment_process );
	}

	/**
	 * Ignore other themes folders
	 */
	protected function ignore_other_themes_folder() {
		$current_theme = wp_get_theme();
		$themes        = wp_get_themes();

		add_filter(
			Filters::IGNORED_FILES,
			function ( $ignored_files ) use ( $current_theme, $themes ) {
				foreach ( $themes as $theme ) {

					if ( $theme->get_template_directory() !== $current_theme->get_template_directory() ) {
						$ignored_files[] = str_replace( rtrim( ABSPATH, DIRECTORY_SEPARATOR ), '', $theme->get_template_directory() );
					}
				}
				return $ignored_files;
			},
			1
		);
	}

	/**
	 * Perform task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @return bool
	 */
	public function perform(): bool {
		$wordpress_root_directory = ABSPATH;
		$bulk_insert              = 10;
		$count                    = 0;
		$this->deployment_process->get_environment();

		$build_type = $this->deployment_process->get_build_type();

		if ( 'incremental' === $build_type ) {
			$last_build_time = $this->deployment_process->get_last_build_date()->getTimestamp();
			add_filter( Filters::IGNORE_CURRENT_ASSET, function ( $ignore_current, $current ) use ( $last_build_time ) {
				$last_modified_time = $current->getMTime();
				return $last_modified_time < $last_build_time;

			}, 10, 2 );
		}


		$iterator      = Assets::get_assets( $wordpress_root_directory );
		$urls          = array();
		$urls_database = URLS_Database::instance();
		foreach ( $iterator as $item ) {

			if ( is_dir( $item ) ) {
				continue;
			}

			$urls[] = new Asset_URL( $item, 'Get_Assets_Task::perform' );
			// save the last processed file.
			update_option( 'static_snap_last_processed_file', $item->getRealPath() );

			if ( $count++ >= $bulk_insert ) {
				$urls_database->insert_many( $urls );
				$urls  = array();
				$count = 0;
			}
		}
		if ( ! empty( $urls ) ) {
			$urls_database->insert_many( $urls );
		}

		return true;
	}
}
