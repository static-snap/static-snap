<?php
/**
 * Class Post URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

/**
 * Post URL class
 */
final class Asset_URL extends URL {

	/**
	 * File
	 *
	 * @var \SplFileInfo $file
	 */
	private \SplFileInfo $file;




	/**
	 * Constructor
	 *
	 * @param \SplFileInfo $file Post URL.
	 * @param string       $source Source.
	 */
	public function __construct( \SplFileInfo $file, $source = 'Asset_URL::class' ) {
		$this->file = $file;
		$this->set_source( $source );
		$this->set_priority( 11 );
	}



	/**
	 * Get post URL
	 *
	 * @return string
	 */
	public function get_url(): string {
		$local_path = $this->get_local_path();
		$local_path = str_replace( ABSPATH, '', $local_path );
		$local_path = str_replace( '\\', '/', $local_path );
		return home_url( $local_path );
	}

	/**
	 * Get local path
	 *
	 * @return string
	 */
	public function get_local_path(): string {
		return $this->file->getRealPath();
	}


	/**
	 * Get last modified date time
	 *
	 * @return string
	 */
	public function get_last_modified(): string {
		$last_modified_for_mysql = gmdate( 'Y-m-d H:i:s', $this->file->getMTime() );

		return $last_modified_for_mysql;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return 'published';
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'asset';
	}
}
