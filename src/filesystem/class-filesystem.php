<?php
/**
 * Filesystem class
 *
 * @package StaticSnap
 */

namespace StaticSnap\Filesystem;

if ( ! function_exists( 'WP_Filesystem_Direct' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
}

if ( ! defined( 'FS_CHMOD_FILE' ) ) {
	$chmod_dir = ( 0755 & ~ umask() );
	define( 'FS_CHMOD_FILE', $chmod_dir );
}
if ( ! defined( 'FS_CHMOD_DIR' ) ) {
	$chmod_dir = ( 0755 & ~ umask() );
	define( 'FS_CHMOD_DIR', $chmod_dir );
}



/**
 * Filesystem class
 *
 * TODO: create a singleton for this class
 */
final class Filesystem extends \WP_Filesystem_Direct {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( true );
	}

	/**
	 * Is allowed path
	 *
	 * @param string $path Path.
	 * @return bool
	 */
	public function is_allowed_path( $path ) {

		$allowed_paths = strval( ini_get( 'open_basedir' ) );

		if ( ! $allowed_paths ) {
			return true;
		}

		$delimiter           = ( strpos( $allowed_paths, ':' ) !== false ) ? ':' : ';';
		$allowed_paths_array = explode( $delimiter, $allowed_paths );

		foreach ( $allowed_paths_array as $allowed_path ) {

			if ( strpos( $path, $allowed_path ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create directory recursively
	 *
	 * @param string $path Path.
	 */
	public function create_directory_recursive( $path ) {
		$dir = '';
		foreach ( explode( DIRECTORY_SEPARATOR, $path ) as $part ) {
			$dir .= $part . DIRECTORY_SEPARATOR;
			if ( $this->is_allowed_path( $dir ) && ! is_dir( $dir ) ) {
				$this->mkdir( $dir );
			}
		}
	}
}
