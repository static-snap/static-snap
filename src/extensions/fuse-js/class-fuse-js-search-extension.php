<?php
/**
 * Page find search type
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Search\FuseJS;

use StaticSnap\Constants\Filters;
use StaticSnap\Extensions\Search\FuseJS\Rest\Rest_FUSE_Search;
use StaticSnap\Search\Search_Extension_Base;

/**
 * Pagefind search type
 */
final class Fuse_JS_Search_Extension extends Search_Extension_Base {

	/**
	 * Constructor
	 *
	 * @param array $params settings.
	 * @throws \Exception If invalid extension type.
	 */
	public function __construct( $params = array() ) {
		parent::__construct( $params );
		if ( $this->is_enabled() ) {
			add_filter( Filters::FRONTEND_LOCALIZE_DATA, array( $this, 'localize_frontend_data' ), 10, 1 );
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		$rest = new Rest_FUSE_Search();
		$rest->register_routes();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'fuse-js';
	}

	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => field definition
	 */
	public function get_settings_fields(): array {
		return array(
			'fuse_isCaseSensitive' => array(
				'required'   => false,
				'default'    => false,
				'type'       => 'boolean',
				'label'      => 'Case Sensitive',
				'helperText' => 'Indicates whether comparisons should be case sensitive.',
			),
			'fuse_includeScore' => array(
				'required'   => true,
				'default'    => false,
				'type'       => 'boolean',
				'label'      => 'Include Score',
				'helperText' => 'Indicates whether each result should include the score.',
			),
			'fuse_minMatchCharLength' => array(
				'required'   => false,
				'default'    => 1,
				'type'       => 'number',
				'label'      => 'Minimum Match Char Length',
				'helperText' => 'Minimum number of characters that must be matched before a result is returned.',
			),
			'fuse_threshold' => array(
				'required'   => false,
				'default'    => 0.55,
				'type'       => 'number',
				'label'      => 'Threshold',
				'helperText' => 'The minimum score a result must have to be returned.',
			),

		);
	}

	/**
	 * Index posts
	 *
	 * @param array $posts post array.
	 * @param mixed $build_path build path.
	 */
	public function index_posts( $posts, $build_path ) {
		Fuse_JS::index_posts( $posts, $build_path );
	}

	/**
	 * Prepare index
	 *
	 * @param mixed $build_path build path.
	 */
	public function prepare_index( $build_path ) {
		Fuse_JS::prepare_index( $build_path );
	}

	/**
	 * Test if pagefind is configured correctly
	 */
	public function is_configured(): bool {
		return true;
	}


	/**
	 * Localize frontend data
	 * This method is used to localize the data that will be used in the frontend.
	 *
	 * @param array $data Data to localize.
	 * @return array Localized data.
	 */
	public function localize_frontend_data( $data ) {
		$data['search_index_url'] = Fuse_JS::get_search_index_url();
		return $data;
	}
}
