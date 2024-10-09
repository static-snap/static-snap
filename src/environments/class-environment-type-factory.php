<?php
/**
 * File Environment
 * This class is used to create the file environment.
 *
 * @package StaticSnap
 */

namespace StaticSnap\Environments;

/**
 * This class is used to create the file environment.
 */
final class Environment_Type_Factory {

	/**
	 * Create environment
	 *
	 * @param array $params settings.
	 * @throws \Exception If invalid type.
	 * @return Environment_Type_Base
	 */
	public static function create( array $params ): Environment_Type_Base {
		$type = $params['type'];
		switch ( $type ) {
			case 'file':
				return new File_Environment( $params );
			case 'github':
				return new Github_Environment( $params );
			default:
				throw new \Exception( 'Invalid environment type' );
		}
	}
}
