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
 */
abstract class Actions {

	/**
	 * Init action
	 */
	const INIT = Plugin::BASE_NAME . '_init';


	/**
	 * Remove conflicting styles action in admin
	 *
	 * This is util because some plugins add styles in the admin that conflict with the plugin styles
	 * for example, the WPML plugin adds styles that conflict with input fields styles
	 */
	const ADMIN_REMOVE_CONFLICTING_STYLES = Plugin::BASE_NAME . '_remove_conflicting_styles';


	/**
	 *  Before create post url
	 *  This action is fired before creating a post url object
	 */
	const BEFORE_CREATE_POST_URL = Plugin::BASE_NAME . '_before_create_post_url';
	/**
	 * After create post url
	 *  This action is fired after creating a post url object
	 */
	const AFTER_CREATE_POST_URL = Plugin::BASE_NAME . '_after_create_post_url';

	/**
	 * Before create term url
	 *  This action is fired before creating a term url object
	 */
	const BEFORE_CREATE_TERM_URL = Plugin::BASE_NAME . '_before_create_term_url';
	/**
	 * After create term url
	 *  This action is fired after creating a term url object
	 */
	const AFTER_CREATE_TERM_URL = Plugin::BASE_NAME . '_after_create_term_url';

	/**
	 * Before fetch post url
	 */
	const BEFORE_FETCH_POST_URL = Plugin::BASE_NAME . '_before_fetch_post_url';

	/**
	 * After fetch post url
	 */
	const AFTER_FETCH_POST_URL = Plugin::BASE_NAME . '_after_fetch_post_url';

	/**
	 * Deployment process init integrations
	 *
	 * Used to init integrations
	 */
	const DEPLOYMENT_PROCESS_INIT_EXTENSIONS = Plugin::BASE_NAME . '_DEPLOYMENT_PROCESS_INIT_EXTENSIONS';

	/**
	 * Before perform task action
	 */
	const DEPLOYMENT_BEFORE_PERFORM_TASK = Plugin::BASE_NAME . '_DEPLOYMENT_BEFORE_PERFORM_TASK';
	/**
	 * After perform task action
	 */
	const DEPLOYMENT_AFTER_PERFORM_TASK = Plugin::BASE_NAME . '_DEPLOYMENT_BEFORE_AFTER_TASK';


	/**
	 * Register environment types action
	 * This action is used to define the environment types used in the plugin
	 */
	const REGISTER_ENVIRONMENT_EXTENSIONS = Plugin::BASE_NAME . '_register_environment_types';

	/**
	 * Register search types action
	 * This action is used to define the search types used in the plugin
	 */
	const REGISTER_SEARCH_EXTENSIONS = Plugin::BASE_NAME . '_register_search_types';

	/**
	 * Register form types action
	 * This action is used to define the form types used in the plugin
	 */
	const REGISTER_FORM_EXTENSIONS = Plugin::BASE_NAME . '_register_form_types';

	/**
	 * Remove head links
	 */
	const REMOVE_HEAD_LINKS = Plugin::BASE_NAME . '_remove_head_links';
}
