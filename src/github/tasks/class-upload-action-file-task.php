<?php
/**
 * Upload Action File Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Github\Tasks;

use StaticSnap\Deployment\Task;
use StaticSnap\Github\Github_Manager;


/**
 * Class Upload_Action_File_Task
 *
 * @package StaticSnap\Github\Tasks
 */
final class Upload_Action_File_Task extends Task {
	/**
	 * Task description
	 *
	 * @var string
	 */
	protected $description = 'Syncing Github Action File';

	/**
	 * Perform the task
	 *
	 * @return bool
	 */
	public function perform(): bool {

		$github_manager = new Github_Manager();

		$settings        = $this->deployment_process->get_environment()->get_settings();
		$installation_id = $settings['installation'];
		$repository      = $settings['repository'];
		$branch          = $settings['branch'];

		$github_manager->upload_action_file( $installation_id, $repository, $branch );

		return true;
	}
}
