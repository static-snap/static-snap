<?php
/**
 * Create Release File
 *
 * @package StaticSnap
 */

namespace StaticSnap\Github\Tasks;

use StaticSnap\Deployment\Task;
use StaticSnap\Github\Github_Manager;

/**
 * Class Create_Release_File_Task
 *
 * @package StaticSnap\Github\Tasks
 */
final class Create_Release_Task extends Task {

	/**
	 * Task description
	 *
	 * @var string
	 */
	protected $description = 'Uploading Release File';

	/**
	 * Perform task
	 *
	 * @return bool
	 */
	public function perform(): bool {
		$github_manager = new Github_Manager();

		$settings        = $this->deployment_process->get_environment()->get_settings();
		$installation_id = $settings['installation'];
		$repository      = $settings['repository'];
		$branch          = $settings['branch'];

		$build_path   = $this->deployment_process->get_environment()->get_build_path();
		$release_file = $build_path . '/' . $this->deployment_process->get_environment()->get_zip_file_name();

		$github_manager->create_release( $installation_id, $repository, $branch, $release_file );

		return true;
	}
}
