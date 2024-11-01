<?php
/**
 * Class Build type
 *
 * @package StaticSnap
 */

namespace StaticSnap\Constants;

/**
 * This class is used to define all build types used in the plugin
 */
abstract class Build_Type {
	/**
	 * Full build
	 */
	const FULL = 'full';
	/**
	 * Incremental build (quick build)
	 */
	const INCREMENTAL = 'incremental';


	/**
	 * Is valid build type
	 *
	 * @param string $build_type Build type.
	 *
	 * @return bool
	 */
	public static function is_valid_build_type( $build_type ) {
		return in_array( $build_type, array( self::FULL, self::INCREMENTAL ), true );
	}
}
