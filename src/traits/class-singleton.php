<?php
/**
 * Singleton
 *
 * @package StaticSnap
 */

declare(strict_types=1);

namespace StaticSnap\Traits;

/**
 * The singleton skeleton trait to instantiate the class only once
 */
trait Singleton {

	/**
	 * Instance of the class
	 *
	 * @var self
	 */
	private static $instances = array();

	/**
	 * Constructor
	 */
	final private function __construct() {
	}

	/**
	 * Get the instance of the class
	 *
	 * @return mixed the instance of the class
	 */
	final public static function instance() {
		$called_class = get_called_class();
		if ( ! isset( static::$instances[ $called_class ] ) ) {

			self::$instances[ $called_class ] = new $called_class();
			// call the init method if it exists.
			if ( method_exists( self::$instances[ $called_class ], 'init' ) ) {
				self::$instances[ $called_class ]->init();
			}
		}
		return self::$instances[ $called_class ];
	}
}
