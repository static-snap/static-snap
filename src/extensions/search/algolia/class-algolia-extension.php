<?php
/**
 * Algolia Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Search\Algolia;

use StaticSnap\Config\Options;
use StaticSnap\Search\Search_Extension_Base;

use StaticSnapVendor\Algolia\AlgoliaSearch\SearchClient;
use StaticSnap\Constants\Filters;
// fix for the issue with the Algolia client if stream_for is not defined.
if ( ! function_exists( '\StaticSnapVendor\Algolia\AlgoliaSearch\Http\Psr7\stream_for' ) ) {
	require_once STATIC_SNAP_PLUGIN_DIR . '/vendor_prefixed/algolia/algoliasearch-client-php/src/Http/Psr7/functions.php';
}

/**
 * Algolia Extension
 */
final class Algolia_Extension extends Search_Extension_Base {

	/**
	 * Search client
	 *
	 * @var SearchClient
	 */
	protected static $search_client = null;
	/**
	 * Default index name
	 *
	 * @var string
	 */
	const DEFAULT_INDEX_NAME = 'static-snap';

	/**
	 * Constructor
	 *
	 * @param array $params Parameters.
	 */
	public function __construct( $params = array() ) {
		parent::__construct( $params );
		// add algolia.js to the footer on frontend.
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ), 10 );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$search_type = Options::instance()->get( 'search.type' );
		if ( 'algolia' !== $search_type ) {
			return;
		}
		$asset_file = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/algolia.asset.php';
		wp_enqueue_script( 'static-snap-algolia', STATIC_SNAP_PLUGIN_URL . '/assets/js/algolia.js', $asset_file['dependencies'], $asset_file['version'], true );
	}

	/**
	 * Get client
	 *
	 * @return SearchClient
	 */
	public static function get_client() {
		if ( null === self::$search_client ) {
			$search_options      = Options::instance()->get(
				'search.settings',
				array(
					'enabled'                => false,
					'type'                   => 'algolia',
					'algolia_application_id' => '',
					'_algolia_admin_key'     => '',
				)
			);
			self::$search_client = SearchClient::create( $search_options['algolia_application_id'], $search_options['_algolia_admin_key'] );
		}
		return self::$search_client;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'algolia';
	}

	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => field definition
	 */
	public function get_settings_fields(): array {
		return array(
			'algolia_application_id' => array(
				'required'   => true,
				'default'    => '',
				'type'       => 'text',
				'label'      => 'Application ID',
				'helperText' => __( 'The application ID for your Algolia account.', 'static-snap' ),
			),
			'algolia_search_key' => array(
				'required'   => true,
				'default'    => '',
				'type'       => 'text',
				'label'      => 'Search Key',
				'helperText' => __( 'The search key for your Algolia account.', 'static-snap' ),
			),
			'_algolia_admin_key' => array(
				'required'   => true,
				'default'    => '',
				'type'       => 'text',
				'label'      => 'Admin Key',
				'helperText' => __( 'The admin key for your Algolia account.', 'static-snap' ),
			),
			'algolia_index_name' => array(
				'required'   => true,
				'default'    => sanitize_title( get_bloginfo( 'name' ) ),
				'type'       => 'text',
				'label'      => 'Index Name',
				'helperText' => __( 'The index name for your Algolia account.', 'static-snap' ),
			),
		);
	}

	/**
	 * Get settings fields
	 *
	 * @return int $max_post_size Max post size.
	 */
	public function get_max_post_size() {
		// 8k
		return 8000;
	}

	/**
	 * Prepare index
	 *
	 * @param string $build_path Build path.
	 */
	public function prepare_index( $build_path ) {
		$index = self::get_client()->initIndex( Options::instance()->get( 'search.settings.algolia_index_name', self::DEFAULT_INDEX_NAME ) );
		$index->clearObjects();
		$search_settings = array(
			'searchableAttributes'  => array( 'title', 'content', 'excerpt' ),
			'attributesForFaceting' => array( 'post_id', 'post_type', 'language' ),
		);
		apply_filters( Filters::ALGOLIA_SEARCH_SETTINGS, $search_settings );
		// add search settings.
		$index->setSettings(
			$search_settings
		);
	}

	/**
	 * Post to index
	 *
	 * @param \WP_Post $post Post object.
	 * @param string   $url URL.
	 */
	public function post_to_index( $post, $url = null ) {
		$parent_post_to_index = parent::post_to_index( $post, $url );
		$index                = array();
		foreach ( $parent_post_to_index as $post_to_index ) {

			// split content in chunks if needed.
			$chunks = str_split( $post_to_index['content'], $this->get_max_post_size() );

			foreach ( $chunks as $key => $chunk ) {
				$index[] = array_merge(
					$post_to_index,
					// override objectID to include the chunk number.
					array(
						'objectID' => $post_to_index['objectID'] . '-' . $key,
						'content'  => $chunk,
					)
				);
			}
		}

		return $index;
	}


	/**
	 * Index posts
	 *
	 * @param array  $posts post array.
	 * @param string $build_path Build path.
	 */
	public function index_posts( $posts, $build_path ) {
		$index = self::get_client()->initIndex( Options::instance()->get( 'search.settings.algolia_index_name', self::DEFAULT_INDEX_NAME ) );
		$index->saveObjects( $posts );
	}
	/**
	 * Test if pagefind is configured correctly
	 */
	public function is_configured(): bool {
		return true;
	}
}
