<?php
/**
 * Class Filters
 *
 * @package StaticSnap
 */

namespace StaticSnap\Constants;

use StaticSnap\Config\Plugin;

/**
 * This class is used to define all the filters used in the plugin
 * The main purpose of this class is to avoid using hard coded strings
 * and to create a single point of reference for all the filters used in the plugin
 * to help developers to easily find and modify the filters.
 */
abstract class Filters {

	/**
	 * Static Snap API url filter
	 * This filter is used to define the API url used in the plugin
	 * Example usage:
	 * add_filter( Filters::API_URL, array( $this, 'api_url' ) );
	 *
	 * @var string
	 */
	const API_URL = Plugin::BASE_NAME . '_api_url';

	/**
	 * Static Snap Website url filter
	 * This filter is used to define the API url used in the plugin
	 * Example usage:
	 * add_filter( Filters::API_URL, array( $this, 'api_url' ) );
	 *
	 * @var string
	 */
	const WEBSITE_URL = Plugin::BASE_NAME . '_website_url';

	/**
	 * Deployment tasks filter
	 * This filter is used to define the deployment tasks used in the plugin
	 *
	 * @var string
	 */
	const DEPLOYMENT_TASKS = Plugin::BASE_NAME . '_deployment_tasks';

	/**
	 * Post types filter
	 * This filter is used to define the post types used in the plugin
	 * Example usage:
	 * add_filter( Filters::POST_TYPES, array( $this, 'post_types' ) );
	 * By this you can add or remove post types from the plugin
	 *
	 * @var string
	 */
	const POST_TYPES = Plugin::BASE_NAME . '_post_types';

	/**
	 * Archive post types filter
	 * This filter is used to define the archive post types used in the plugin
	 */
	const ARCHIVE_POST_TYPES = Plugin::BASE_NAME . '_post_types';

	/**
	 * Before save post urls filter
	 * This filter can be used to modify the urls before saving them to the database
	 */
	const BEFORE_SAVE_POST_URLS = Plugin::BASE_NAME . '_before_save_post_urls';


	/**
	 * Before save post archives urls filter
	 * This filter can be used to modify the post archives urls before saving them to the database
	 */
	const BEFORE_SAVE_POST_ARCHIVES_URLS = Plugin::BASE_NAME . '_before_save_post_archives_urls';

	/**
	 * Before save authors urls filter
	 * This filter can be used to modify the authors urls before saving them to the database
	*/
	const BEFORE_SAVE_AUTHORS_URLS = Plugin::BASE_NAME . '_before_save_authors_urls';

	/**
	 * Before save term urls filter
	 * This filter can be used to modify the term urls before saving them to the database
	 */
	const BEFORE_SAVE_TERM_URLS = Plugin::BASE_NAME . '_before_save_term_urls';

	/**
	 * Ignored patterns filter
	 * This filter is used to define the ignored patterns used in the plugin
	 */
	const IGNORED_PATTERNS = Plugin::BASE_NAME . '_ignored_patterns';
	/**
	 * Ignored files filter
	 * This filter is used to define the ignored files used in the plugin
	 */
	const IGNORED_FILES = Plugin::BASE_NAME . '_ignored_files';

	/**
	 * Ignored extensions filter
	 * This filter is used to define the ignored extensions used in the plugin
	 */
	const IGNORED_EXTENSIONS = Plugin::BASE_NAME . '_ignored_extensions';

	/**
	 * Possible extensions with urls filter
	 * This filter is used to define the possible extensions with urls used in the plugin
	 */
	const POSSIBLE_EXTENSIONS_WITH_URLS = Plugin::BASE_NAME . '_possible_extensions_with_urls';

	/**
	 * Build directory path filter
	 * This filter is used to define the build directory path used in the plugin
	 * Its contains the environment path where the build files are stored
	 */
	const BUILD_DIRECTORY_PATH = Plugin::BASE_NAME . '_build_directory_path';

	/**
	 * Post URL filter
	 */
	const POST_URL = Plugin::BASE_NAME . '_post_url';

	/**
	 * URL Local Destination filter
	 */
	const URL_LOCAL_DESTINATION = Plugin::BASE_NAME . '_post_url_local_destination';

	/**
	 * Post Url Content filter
	 * This filter is used to define the post url content used in the plugin
	 */
	const POST_URL_CONTENT = Plugin::BASE_NAME . '_post_url_content';

	/**
	 * Post Url Remote Permalink filter
	 */
	const POST_URL_REMOTE_PERMALINK = Plugin::BASE_NAME . '_post_url_remote_permalink';

	/**
	 * Post url remote args filter
	 * This filter is used to define the remote args used in the plugin
	 */
	const POST_URL_REMOTE_ARGS = Plugin::BASE_NAME . '_post_url_remote_args';
	/**
	 *  Search post to index filter
	 * This filter can be used to modify the post index before saving it to the database
	 * for example you can add more fields to the index
	 *
	 * @param mixed $index
	 * @param \WP_Post $post
	 */
	const SEARCH_POST_TO_INDEX = Plugin::BASE_NAME . '_search_post_to_index';


	/**
	 *  Search Content tag filter
	 *  This filter can be used to modify tags that will be deleted from the content
	 */
	const SEARCH_CONTENT_TAGS_REMOVE = Plugin::BASE_NAME . '_search_content_tags_remove';

	/**
	 *  Search Content shortcodes filter
	 *  This filter can be used to modify shortcodes that will be deleted from the content
	 */
	const SEARCH_CONTENT_SHORTCODES_REMOVE = Plugin::BASE_NAME . '_search_content_shortcodes_remove';

	/**
	 * Algolia search settings filter
	 */
	const ALGOLIA_SEARCH_SETTINGS = Plugin::BASE_NAME . '_algolia_search_settings';

	/**
	 * Frontend
	 */

	/**
	 * Frontend has translations
	 * This filter is used to define if the site has translations
	 */
	const FRONTEND_HAS_TRANSLATIONS = Plugin::BASE_NAME . '_frontend_has_translations';

	/**
	 * Frontend localize data filter
	 * This filter is used to define the frontend localize data used in the plugin
	 */
	const FRONTEND_LOCALIZE_DATA = Plugin::BASE_NAME . '_frontend_localize_data';


	/**
	 * FUSE Filters
	 */

	/**
	 * Dynamic search index url filter
	 * This filter is used to define the dynamic search index url used in the plugin to get the search index in WordPress
	 */
	const FUSE_DYNAMIC_SEARCH_INDEX_URL = Plugin::BASE_NAME . 'fuse_search_index_url';

	/**
	 * WP Installation md5 filter
	 * This filter can be used to define the WordPress installation md5 used in the plugin,
	 * this will be useful to identify the site as unique in your theme
	 * It should be a 32 character long string, you can create an md5 string using the following code
	 * md5( 'your-unique-string' );
	 * Example usage:
	 * add_filter( Filters::WP_INSTALLATION_MD5, array( $this, 'wp_installation_md5' ) );
	 *
	 * Remember that if you change the database name or other settings the site will be identified as a new site
	 * and you will need to reconnect the site to the app before deploying
	 */
	const WP_INSTALLATION_MD5 = Plugin::BASE_NAME . '_wp_installation_md5';

	/**
	 * Save options filter
	 */
	const SAVE_OPTIONS = Plugin::BASE_NAME . '_save_options';

	/**
	 * Set options filter
	 * We also have a filter called UPDATE_OPTIONS . '_' . $option_name that can be used to modify the value of an option
	 * Example usage:
	 * add_filter( Filters::UPDATE_OPTIONS . '_my_option', array( $this, 'update_my_option' ), 10, 2 );
	 * function update_my_option( $value, $name ) {
	 *   // modify the value
	 *   // can also check and throw an exception if the value is not valid
	 *   // if ( ! is_valid( $value ) ) {
	 *   //    throw new \Exception( 'Invalid value' );
	 *   // }
	 *   return $value;
	 */
	const SET_OPTIONS = Plugin::BASE_NAME . '_set_options';
}
