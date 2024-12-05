<?php
/**
 * Task Manager
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Constants\Filters;
use StaticSnap\Deployment\Task;
use StaticSnap\Deployment\Deployment_Process;
use StaticSnap\Traits\Singleton;


/**
 * Task Manager class
 */
final class Deployment_Task_Manager {

	use Singleton {
		instance as protected trait_instance;
	}


	public const DONE_TASK = 'StaticSnap\Deployment\Deploy\Done_Task';
	public const ZIP_TASK  = 'StaticSnap\Deployment\Deploy\Create_ZIP_File_Task';

	public const SEARCH_PREPARE_TASK = 'StaticSnap\Deployment\Deploy\Search_Prepare_Task';
	public const SEARCH_TASK         = 'StaticSnap\Deployment\Deploy\Search_Task';

	public const FORMS_TASK = 'StaticSnap\Deployment\Deploy\Forms_Task';

	/**
	 * Current task
	 *
	 * @var string
	 */
	private $current_task = null;

	/**
	 * Deployment process
	 *
	 * @var Deployment_Process
	 */
	private $deployment_process = null;

	/**
	 * Instance
	 *
	 * @param Deployment_Process $deployment_process Deployment process.
	 */
	// phpcs:ignore
	public static function instance( Deployment_Process $deployment_process ) {
		$instance = self::trait_instance();
		$instance->set_deployment_process( $deployment_process );
		return $instance;
	}

	/**
	 * Set deployment process
	 *
	 * @param Deployment_Process $deployment_process Deployment process.
	 * @return void
	 */
	public function set_deployment_process( Deployment_Process $deployment_process ): void {
		$this->deployment_process = $deployment_process;
	}

	/**
	 * Get task instance
	 *
	 * @param string $task Task.
	 * @return Task | false
	 */
	public function get_task( string $task ) {
		// check if $task is a class.
		if ( ! class_exists( $task ) ) {
			return false;
		}
		$task = new $task( $this->deployment_process );
		return $task;
	}

	/**
	 * Set current task
	 *
	 * @param string $task Task.
	 * @return void
	 */
	public function set_current_task( string $task ): void {
		$this->current_task = $task;
	}
	/**
	 * Get current task
	 *
	 * @return string | null
	 */
	public function get_current_task() {
		return $this->current_task;
	}

	/**
	 * Get task index
	 *
	 * @param string $task Task.
	 * @return int
	 */
	public function get_task_index( string $task ) {
		$tasks = $this->get_tasks();
		$index = array_search( $task, $tasks, true );
		return $index;
	}

	/**
	 * Get next task
	 *
	 * @return string | null
	 */
	public function get_next_task() {
		$tasks        = $this->get_tasks();
		$current_task = $this->get_current_task();
		if ( ! $current_task ) {
			$this->set_current_task( $tasks[0] );
			return $tasks[0];
		}
		// find current task index.
		$index     = array_search( $current_task, $tasks, true );
		$next      = $index + 1;
		$next_task = $tasks[ $next ] ?? false;

		if ( $next_task ) {
			$this->set_current_task( $next_task );
		}

		return $next_task;
	}



	/**
	 * Get tasks list to run
	 *
	 * @return array
	 */
	public function get_tasks(): array {
		$tasks = array(
			'StaticSnap\Deployment\Build\Start_Task',
			// Clean.
			'StaticSnap\Deployment\Build\Prepare_Task',
			// Build.
			'StaticSnap\Deployment\Build\Get_Posts_Urls_Task',
			'StaticSnap\Deployment\Build\Get_Post_Archives_Urls_Task',
			'StaticSnap\Deployment\Build\Get_Terms_Urls_Task',
			'StaticSnap\Deployment\Build\Get_Authors_Urls_Task',
			'StaticSnap\Deployment\Build\Fetch_Posts_Task',
			'StaticSnap\Deployment\Build\Get_Assets_Task',
			'StaticSnap\Deployment\Build\Copy_Content_Assets_Task',
			'StaticSnap\Deployment\Deploy\Copy_Assets_Task',
			// Deploy.
			self::ZIP_TASK,
			self::SEARCH_PREPARE_TASK,
			self::SEARCH_TASK,
			self::FORMS_TASK,
		);
		// add plugin tasks.
		$tasks = apply_filters( Filters::DEPLOYMENT_TASKS, $tasks );

		// Done task will always be the last task.
		$tasks[] = self::DONE_TASK;

		return $tasks;
	}
}
