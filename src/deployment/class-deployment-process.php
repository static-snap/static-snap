<?php
/**
 * Deployment Process
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use DateTime;
use StaticSnapVendor\WP_Background_Process;

use StaticSnap\Database\Environments_Database;
use StaticSnap\Interfaces\Deployment_Interface;
use StaticSnap\Config\Options;
use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Build_Type;
use StaticSnap\Constants\Filters;
use StaticSnap\Database\Deployment_History_Database;
use StaticSnap\Interfaces\Environment_Interface;
use StaticSnap\Interfaces\Task_Interface;

/**
 * Deployment Process
 */
final class Deployment_Process extends WP_Background_Process implements Deployment_Interface {

	/**
	 * Tasks added
	 *
	 * @var bool
	 */
	public static $tasks_added = false;

	/**
	 * Task Manager intance
	 *
	 * @var Deployment_History_Database
	 */
	private $history = null;
	/**
	 * Task Manager intance
	 *
	 * @var Deployment_Task_Manager
	 */
	private $task_manager = null;
	/**
	 * Feeds
	 *
	 * @var Feeds
	 */
	private $feeds = null;
	/**
	 * Error Handler
	 *
	 * @var Error_Handler
	 */
	private $error_handler = null;

	/**
	 * Current Environment
	 *
	 * @var Environment_Interface
	 */
	private $current_environment = null;

	/**
	 * Last build date. Store it to avoid multiple queries.
	 *
	 * @var DateTime
	 */
	private $last_build_date = null;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options;
	/**
	 * Url finder
	 *
	 * @var Url_Finder
	 */
	private $url_finder;


	/**
	 * WordPress Head utility to disable unnecessary Head features.
	 *
	 * @var Head
	 */
	private $head;

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = 'static_snap';

	/**
	 * Build type
	 *
	 * @var string
	 */
	private $build_type = Build_Type::FULL;

	/**
	 * Constructor
	 *
	 * @param array $options Options.
	 */
	public function __construct( array $options = array() ) {
		$this->options      = Options::instance();
		$this->task_manager = Deployment_Task_Manager::instance( $this );

		$this->history = Deployment_History_Database::instance();
		$this->feeds   = Feeds::instance();

		$this->head = Head::instance();

		$this->url_finder = URL_Finder::instance();

		Posts::default_post_filters();
		// set error handler.
		$this->error_handler = new Error_Handler( $this );

		do_action( Actions::DEPLOYMENT_PROCESS_INIT_EXTENSIONS, $this );

		$this->add_environment_type_tasks();

		parent::__construct( $options );
	}

	/**
	 * Add Environment type tasks
	 */
	private function add_environment_type_tasks() {
		// Protect against double adding.
		if ( self::$tasks_added ) {
			return;
		}
		self::$tasks_added = true;
		try {
			$current_environment = $this->get_environment();
			add_filter(
				Filters::DEPLOYMENT_TASKS,
				function ( $tasks ) use ( $current_environment ) {

					// Add task just before StaticSnap\Deployment\Deploy\Done_Task if found. Otherwise add it at the end.
					$type_tasks      = $current_environment->get_type_instance()->get_deployment_tasks();
					$done_task_index = array_search( Deployment_Task_Manager::DONE_TASK, $tasks, true );
					if ( false !== $done_task_index ) {
						array_splice( $tasks, $done_task_index, 0, $type_tasks );
					} else {
						$tasks = array_merge( $tasks, $type_tasks );
					}
					return $tasks;
				},
				10
			);
		// phpcs:ignore
		} catch ( \Exception $e ) {
			// do nothing.
		}
	}

	/**
	 * Calculate progress percentage based on the current task.
	 * This is a helper method to calculate the progress percentage
	 * based on the current task.
	 *
	 * @param Task_Interface $task Task.
	 *
	 * @return void
	 */
	public function calculate_progress( Task_Interface $task ) {
		$all_tasks_count = count( $this->task_manager->get_tasks() );
		$current_index   = $this->task_manager->get_task_index( get_class( $task ) );

		// current percentage by count / index.
		$percentage = ( $current_index / $all_tasks_count ) * 100;

		// update the history.
		$last_history                       = $this->history->get_last_history();
		$last_history['status_information'] = array(
			'current_task'             => get_class( $task ),
			'build_type'               => $this->build_type,
			'current_task_description' => $task->get_description(),
			'percentage'               => $percentage,
		);
		$this->history->update_history( $last_history );
	}

	/**
	 * Perform task with queued item.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		// start error handler.
		$this->error_handler->start();

		$this->task_manager->set_current_task( $item );

		$task = $this->task_manager->get_task( $item );

		if ( ! $task ) {
			return false;
		}

		$this->calculate_progress( $task );
		// Get current task.

		if ( ! isset( $current_task['task'] ) ) {
			$current_task['task']    = $item;
			$current_task['retries'] = 0;
		}

		if ( $current_task['task'] !== $item ) {
			$current_task['task']    = $item;
			$current_task['retries'] = 0;
		}

		++$current_task['retries'];
		do_action( Actions::DEPLOYMENT_BEFORE_PERFORM_TASK, $item, $task );
		$done = $task->perform();

		if ( ! $done ) {
			// item not done, requeue it.
			return $item;
		}
		do_action( Actions::DEPLOYMENT_AFTER_PERFORM_TASK, $item, $task );

		$next_task = $this->task_manager->get_next_task();
		if ( ! $next_task ) {
			// no more tasks.
			$this->error_handler->stop();
			return false;
		}

		return $next_task;
	}

	/**
	 * Get environment
	 *
	 * @return Environment_Interface
	 * @throws \Exception If environment not found.
	 */
	public function get_environment(): Environment_Interface {
		if ( $this->current_environment ) {
			return $this->current_environment;
		}
		$last_history = $this->history->get_last_history();
		// get environment from transient.
		$environment_id = $last_history['environment_id'] ?? null;
		if ( ! $environment_id ) {
			throw new \Exception( 'Environment not found' );
		}
		$this->current_environment = Environments_Database::instance()->get_by_id( $environment_id );
		if ( ! $this->current_environment ) {
			throw new \Exception( 'Invalid Environment' );
		}

		// set build type.
		$this->build_type = $last_history['build_type'] ?? Build_Type::FULL;

		return $this->current_environment;
	}

	/**
	 * Complete the process
	 *
	 * @param Environment_Interface $environment Environment.
	 * @param string                $build_type build_type.
	 * @return bool
	 */
	public function run( Environment_Interface $environment, $build_type = Build_Type::FULL ): bool {
		$this->current_environment = $environment;
		$this->build_type          = $build_type;

		// if already active return.
		if ( $this->is_active() ) {
			return false;
		}

		$task = $this->task_manager->get_next_task();

		if ( ! $task ) {
			return false;
		}
		$this->history->start_history( $environment->get_id(), $build_type );
		$this->push_to_queue( $task )->save()->dispatch();

		return true;
	}


	/**
	 * Get the number of items to be processed in a single batch.
	 *
	 * @param Environment_Interface $environment Environment.
	 * @param string                $build_type build_type.
	 *
	 * @return bool
	 *
	 * @throws \Exception If a task is not found.
	 */
	public function run_cli( Environment_Interface $environment, $build_type = Build_Type::FULL ): bool {
		// if already active return.
		if ( $this->is_active() ) {
			return false;
		}

		$this->build_type = $build_type;

		$all_tasks = $this->task_manager->get_tasks();

		$this->history->start_history( $environment->get_id(), $build_type );

		set_site_transient( $this->identifier . '_cli_process_lock', true );
		// start error handler .
		$this->error_handler->start();

		ob_start();

		foreach ( $all_tasks as $string_stask ) {
			try {

				$task = $this->task_manager->get_task( $string_stask );
				if ( ! $task ) {
					// trow exception.
					throw new \Exception( sprintf( 'Task not found: %s', $string_stask ) );
				}
				$this->calculate_progress( $task );
				do_action( Actions::DEPLOYMENT_BEFORE_PERFORM_TASK, $string_stask, $task );
				$task->perform();
				do_action( Actions::DEPLOYMENT_AFTER_PERFORM_TASK, $string_stask, $task );
			} catch ( \Exception $e ) {
				$this->history->end_history(
					Deployment_History_Database::FAILED,
					array(
						'error' => $e->getMessage(),
					)
				);
				return false;
			}
		}

		// stop error handler.
		$this->error_handler->stop();
		delete_site_transient( $this->identifier . '_cli_process_lock' );
		return true;
	}

	/**
	 * Is the background process currently running?
	 *
	 * @return bool
	 */
	public function is_processing() {
		$parent_processing = parent::is_processing();

		$cli_processing = get_site_transient( $this->identifier . '_cli_process_lock' );

		return $parent_processing || $cli_processing;
	}


	/**
	 * Cancel the process
	 *
	 * @return void
	 */
	public function cancel(): void {
		$this->delete_all();
		$this->unlock_process();
		$this->history->end_history( Deployment_History_Database::CANCELED );
		delete_site_transient( $this->identifier . '_cli_process_lock' );
		parent::cancel();
	}


	/**
	 * Deployment failed
	 *
	 * @param mixed $error Error.
	 * @return void
	 */
	public function failed( $error ): void {
		$this->delete_all();
		$this->unlock_process();
		$this->history->end_history( Deployment_History_Database::FAILED, $error );
		parent::cancel();
	}

	/**
	 * Pause the process
	 *
	 * @return void
	 */
	public function pause(): void {
		if ( ! $this->is_active() ) {
			return;
		}
		$this->history->pause_history( 0 );
		// Do something with the data.
		parent::pause();
	}

	/**
	 * Get build type
	 *
	 * @return string
	 */
	public function get_build_type(): string {
		return $this->build_type;
	}

	/**
	 * Get last build date
	 *
	 * @return DateTime
	 */
	public function get_last_build_date(): DateTime {
		if ( $this->last_build_date ) {
			return $this->last_build_date;
		}
		$environment            = $this->get_environment();
		$last_completed_history = $this->history->get_last_completed_history_by_environment( $environment->get_id() );
		$end_timestamp          = $last_completed_history['end_time'];
		$this->last_build_date  = new DateTime( '@' . $end_timestamp );

		return $this->last_build_date;
	}
}
