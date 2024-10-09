<?php
/**
 * Pagefind Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Application;
use StaticSnap\Config\Options;
use StaticSnap\Deployment\Task;
use StaticSnap\Search\Search_Extension_Base;

/**
 * Pagefind Task class
 * Just to start the task
 */
final class Search_Prepare_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Preparing Search';


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

		// check if search is enabled.
		$search_options = Options::instance()->get(
			'search',
			array(
				'enabled' => false,
				'type'    => 'fuse-js',
			)
		);

		if ( ! $search_options['enabled'] ) {
			return true;
		}

		$search_extension = Application::instance()->get_extensions_by_type( 'search' )[ $search_options['type'] ];
		// check if $search_extension is an instance of Search_Extension_Base.
		if ( ! $search_extension instanceof Search_Extension_Base ) {
			return true;
		}
		$build_path = $this->deployment_process->get_environment()->get_build_path();

		$search_extension->prepare_index( $build_path );

		return true;
	}
}
