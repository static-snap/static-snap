<?php
/**
 * Cli Commands
 *
 * @package StaticSnap
 */

namespace StaticSnap\Cli;

use StaticSnap\Deployment\Deployment_Process;
use StaticSnap\Application;
use StaticSnap\Constants\Actions;
use WP_CLI;

use function Clue\StreamFilter\register;

/**
 * Cli Commands
 */
final class Cli_Commands {

	/**
	 * Deployment
	 *
	 * @var Deployment_Process $deployment
	 */
	protected $deployment = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$app              = Application::instance();
		$this->deployment = $app->get_deployment();
		add_action( Actions::DEPLOYMENT_BEFORE_PERFORM_TASK, array( $this, 'before_perform_task' ), 1, 2 );
		$this->register_commands();
	}

	/**
	 * Register commands
	 *
	 * @return void
	 */
	public function register_commands(): void {
		WP_CLI::add_command( 'static-snap deploy', array( $this, 'deploy' ) );
		WP_CLI::add_command( 'static-snap cancel', array( $this, 'cancel' ) );
	}
	/**
	 * Before perform task, show a message to the user.
	 *
	 * @param string $_task_name Task name.
	 * @param object $task Task.
	 */
	public function before_perform_task( $_task_name, $task ) {
		WP_CLI::log( $task->get_description() . '...' );
	}



	/**
	 * Deploy
	 *
	 * @param array $args Args.
	 * @return void
	 */
	public function deploy( array $args ): void {
		// get id from args.
		$name = (string) $args[0];
		$e    = \StaticSnap\Database\Environments_Database::instance()->get_by_name( $name );
		if ( ! $e ) {
			WP_CLI::error( 'Environment not found' );
			return;
		}
		$this->deployment->run_cli( $e );
	}

	/**
	 * Cancel
	 */
	public function cancel(): void {
		$this->deployment->cancel();
	}
}
