<?php
/**
 * Pagefind integration
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Search\FuseJS;

use StaticSnap\Application;
use StaticSnap\Constants\Filters;
use StaticSnap\Filesystem\Filesystem;

/**
 * Pagefind class
 */
final class Fuse_JS {

	/**
	 * Constructor
	 */
	public function __construct() {
		new Fuse_JS_Search_Extension();
	}

	/**
	 * Get search file name
	 *
	 * @return string
	 */
	public static function get_search_file_name() {
		return 'search.json';
	}

	/**
	 * Get search file path
	 *
	 * @param string $build_path Build path.
	 * @return string
	 */
	public static function get_search_file_path( $build_path ) {
		return $build_path . DIRECTORY_SEPARATOR . self::get_search_file_name();
	}

	/**
	 * Get dynamic search index url
	 *
	 * @return string
	 */
	public static function get_dynamic_search_index_url() {
		$search_index_url = get_rest_url( null, '/static-snap/v1/fuse-search/index' );
		$search_index_url = apply_filters( Filters::FUSE_DYNAMIC_SEARCH_INDEX_URL, $search_index_url );
		return $search_index_url;
	}

	/**
	 * Get search index url
	 *
	 * @return string
	 */
	public static function get_search_index_url() {
		$filename = self::get_search_file_name();
		return Application::instance()->get_frontend()::is_static() ? "/$filename" : self::get_dynamic_search_index_url();
	}

	/**
	 * Prepare search file
	 *
	 * @param string $build_path File path.
	 */
	public static function prepare_index( $build_path ) {
		$file_path = self::get_search_file_path( $build_path );
		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
		}
		$filesystem = new Filesystem();
		// recreate the file.
		$filesystem->touch( $file_path );
	}



	/**
	 * Index post
	 *
	 * @param array  $posts posts array.
	 * @param string $build_path build path.
	 */
	public static function index_posts( $posts, $build_path ) {
		$json_file = self::get_search_file_path( $build_path );

		$filesystem = new Filesystem();

		// get all content from json_file.
		// phpcs:ignore
		$search_json        = $filesystem->get_contents( $json_file );
		$all_indexed_items = json_decode( $search_json, true ) ?? array();

		$all_indexed_items = array_merge( $all_indexed_items, $posts );

		// append index to search.json.
		$index_json = wp_json_encode( $all_indexed_items );

		// recreate the file.
		$filesystem->put_contents( $json_file, $index_json );
	}
}
