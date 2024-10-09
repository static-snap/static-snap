<?php
/**
 * Base class for Extensions
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extension;

use JsonSerializable;
use StaticSnap\Application;
use StaticSnap\Base;
use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Extensions_Types;
use StaticSnap\Interfaces\Extension_Interface;

/**
 * This class is used to create the base environment.
 */
abstract class Extension_Base extends Base implements Extension_Interface, JsonSerializable {
	/**
	 * Params for current environment
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Configuration errors
	 *
	 * @var array
	 */
	protected $errors = array();


	/**
	 * Constructor
	 *
	 * @param array $params settings.
	 * @throws \Exception If invalid extension type.
	 */
	public function __construct( $params = array() ) {
		parent::__construct();

		if ( ! array_key_exists( $this->get_type(), Extensions_Types::ALL ) ) {
			throw new \Exception( 'Invalid extension type' );
		}

		// Register the environment.
		add_action( Extensions_Types::ALL[ $this->get_type() ], array( $this, 'register' ), 10 );

		$this->params = $params;
	}
	/**
	 * Is available
	 *
	 * @return boolean
	 */
	public function is_available(): bool {
		return true;
	}
	/**
	 * Is enabled
	 *
	 * @return boolean
	 */
	public function is_enabled(): bool {
		return true;
	}

	/**
	 * Register the environment
	 *
	 * @param \StaticSnap\Application $app application.
	 */
	public function register( Application $app ) {
		$app->register_extension( $this );
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	public function __toString() {
		return ucfirst( $this->get_name() );
	}

	/**
	 * Json serialize
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {

		return array(
			'type'      => $this->get_type(),
			'name'      => $this->get_name(),
			'available' => $this->is_available(),
			'settings'  => $this->get_settings_fields(),
		);
	}



	/**
	 * Get type
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => type
	 * @example array
	 *  [
	 *      'api_key' => 'text',
	 *      'api_secret' => 'text',
	 *  ]
	 */
	abstract public function get_settings_fields(): array;

	/**
	 * Add error
	 *
	 * @param string $field field name.
	 * @param string $message error message.
	 * @return void
	 */
	public function add_error( $field, $message ) {
		$this->errors[ "settings.$field" ] = $message;
	}

	/**
	 * Get errors
	 *
	 * @return array
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings(): array {

		return $this->params['settings'] ?? array();
	}

	/**
	 * Set settings
	 *
	 * @param array $settings settings.
	 * @return void
	 */
	public function set_settings( $settings ) {
		$this->params['settings'] = $settings;
	}


	/**
	 * Test if the environment is configured correctly
	 */
	abstract public function is_configured(): bool;
	/**
	 * Get deployment tasks
	 *
	 * @return array
	 */
	public function get_build_tasks(): array {
		return array();
	}
	/**
	 * Get deployment tasks
	 *
	 * @return array
	 */
	public function get_deployment_tasks(): array {
		return array();
	}
}
