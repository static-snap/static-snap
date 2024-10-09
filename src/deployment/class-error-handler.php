<?php
/**
 * ErrorHandler
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Config\Plugin;
use StaticSnap\Interfaces\Deployment_Interface;

/**
 * Error Handler class
*/
final class Error_Handler {
	/**
	 * Option name
	 *
	 * @var string
	 */
	private static $option_name = Plugin::OPTION_GROUP . '_deployment_error';

	/**
	 * Deployment interface
	 *
	 * @var Deployment_Interface
	 */
	private $deployment = null;

	/**
	 * Constructor
	 *
	 * @param Deployment_Interface $deployment Deployment interface.
	 */
	public function __construct( Deployment_Interface $deployment ) {
		$this->deployment = $deployment;
	}
	/**
	 *  Start error handling
	 */
	public function start() {
		// phpcs:ignore
		set_error_handler( array( $this, 'handle_error' ), /** No deprecated errors */ E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
		set_exception_handler( array( $this, 'handle_exception' ) );
		register_shutdown_function( array( $this, 'handle_shutdown' ) );
	}

	/**
	 * Stop error handling
	 */
	public function stop() {
		// remove the error handler created option.
		update_option( self::$option_name, null );
		restore_error_handler();
		restore_exception_handler();
	}
	/**
	 * Handle error
	 *
	 * @param int    $severity Severity.
	 * @param string $message Message.
	 * @param string $file File.
	 * @param int    $line Line.
	 *
	 * @throws \ErrorException Error exception.
	 *
	 * @return void
	 */
	public function handle_error( $severity, $message, $file, $line ) {
		throw new \ErrorException( esc_html( $message ), 0, esc_html( $severity ), esc_html( $file ), esc_html( $line ) );
	}
	/**
	 * Handle exception
	 *
	 * @param \Exception $exception Exception.
	 *
	 * @return void
	 */
	public function handle_exception( $exception ) {
		// if we are in WP_CLI show the error message in the console.
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::error( $exception->getMessage() );
		}
		// stop the deployment process.
		$error = array(
			'code'    => $exception->getCode(),
			'message' => $exception->getMessage(),
			'file'    => $exception->getFile(),
			'line'    => $exception->getLine(),
			'stack'   => $exception->getTraceAsString(),
		);
		$this->deployment->failed( $error );
	}
	/**
	 * Handle shutdown
	 *
	 * @return void
	 */
	public function handle_shutdown() {
		$error = error_get_last();
		if ( null !== $error ) {
			$this->handle_error( $error['type'], $error['message'], $error['file'], $error['line'] );
		}
	}



	// remove the error handler.
	// phpcs:ignore
	public function __destruct() {
		restore_error_handler();
		restore_exception_handler();
	}
}
