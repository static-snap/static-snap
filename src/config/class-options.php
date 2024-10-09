<?php
/**
 * Options
 *
 * @package StaticSnap
 */

namespace StaticSnap\Config;

use StaticSnap\Traits\Singleton;
use StaticSnap\Constants\Filters;

/**
 * This class is used to store all options for the plugin
 */
final class Options {

	use Singleton;

	/**
	 * Options array.
	 *
	 * @var array
	 */
	protected $options = array();




	/**
	 * Constructor
	 */
	private function __construct() {

		// get all options in the database Plugin::SLUG.
		$db_options = get_option( Plugin::SLUG );

		$options = apply_filters( 'static_snap_get_options', $db_options );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$this->options = $options;
	}

	/**
	 * Filter private options
	 *
	 * @param mixed $key The key of the options.
	 * @return bool
	 */
	private function filter_private_options_conditional( $key ): bool {
		return 0 !== strpos( $key, '_' );
	}

	/**
	 * Filter private options
	 *
	 * @param mixed $input The options to filter.
	 * @return bool
	 */
	private function filter_private_options( $input ): array {

		foreach ( $input as &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->filter_private_options( $value );
			}
		}

		return array_filter( $input, array( $this, 'filter_private_options_conditional' ), ARRAY_FILTER_USE_KEY );
	}

	/**
	 * Get options
	 *
	 * @param bool $only_public If true, only return public options.
	 * @return array
	 */
	public function get_options( $only_public = true ): array {
		if ( $only_public ) {
			return $this->filter_private_options( $this->options );

		}

		return $this->options;
	}

	/**
	 * Set the value of an option.
	 *
	 * @param string $name The name of the option. Use dot notation to set nested options.
	 * @param mixed  $value The value to set.
	 * @return void
	 */
	public function set( $name, $value ): void {
		// add filter to allow update value. We use the same filter for create and update.
		$value = apply_filters( Filters::SET_OPTIONS, $value, $name );
		// specific filter for each option.
		$value = apply_filters( Filters::SET_OPTIONS . '_' . $name, $value, $name );

		$names = explode( '.', $name );

		$option = &$this->options;
		$last   = array_pop( $names );

		foreach ( $names as $key ) {
			if ( ! isset( $option[ $key ] ) ) {
				$option[ $key ] = array();
			}
			$option = & $option[ $key ];
		}

		$option[ $last ] = $value;
	}

	/**
	 * Get the value of an option.
	 *
	 * @param string $name The name of the option. Use dot notation to get nested options.
	 * @param mixed  $default_value The default value to return if the option is not set.
	 * @return mixed The value of the option.
	 */
	public function get( $name, $default_value = null ) {
		$names = explode( '.', $name );

		$option = $this->options;

		foreach ( $names as $key ) {
			if ( ! isset( $option[ $key ] ) ) {
				return $default_value;
			}
			$option = $option[ $key ];
		}

		return $option;
	}

	/**
	 * Set options
	 *
	 * @param array $options The options to set.
	 * @return void
	 */
	public function set_options( $options ): void {
		$this->options = $options;
	}

	/**
	 * Delete an option.
	 *
	 * @param string $name The name of the option. Use dot notation to delete nested options.
	 * @return bool
	 */
	public function delete( $name ): bool {
		$names = explode( '.', $name );

		$option = &$this->options;

		$last = array_pop( $names );

		foreach ( $names as $key ) {
			if ( ! isset( $option[ $key ] ) ) {
				return false;
			}
			$option = & $option[ $key ];
		}

		if ( isset( $option[ $last ] ) ) {
			unset( $option[ $last ] );
			return true;
		}

		return false;
	}

	/**
	 * Save options
	 *
	 * @return bool
	 */
	public function save(): bool {
		$options = apply_filters( Filters::SAVE_OPTIONS, $this->options );
		return update_option( Plugin::SLUG, $options );
	}
}
