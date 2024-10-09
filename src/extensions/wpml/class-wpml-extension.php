<?php
/**
 * WPML Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\WPML;

use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Filters;

/**
 * WPML Extension
 */
final class WPML_Extension {

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( ! is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			return;
		}

		add_filter( Filters::POST_URL, array( $this, 'translate_post_url' ), 10, 2 );

		add_action( Actions::DEPLOYMENT_BEFORE_PERFORM_TASK, array( $this, 'before_perform_task' ), 1, 1 );
		add_filter( Filters::SEARCH_POST_TO_INDEX, array( $this, 'add_wpml_language_code_to_index' ), 10, 2 );
		add_filter( Filters::FRONTEND_HAS_TRANSLATIONS, '__return_true', 10 );
		add_filter( Filters::FRONTEND_LOCALIZE_DATA, array( $this, 'add_wpml_language_code_to_localize_data' ), 10, 1 );
		add_action( Actions::ADMIN_REMOVE_CONFLICTING_STYLES, array( $this, 'remove_conflicting_styles' ), 10 );
	}

	/**
	 * Before perform task
	 *
	 * @param string $task_name Task.
	 */
	public function before_perform_task( $task_name ) {
		if ( 'StaticSnap\Deployment\Build\Get_Terms_Urls_Task' === $task_name ) {

			global $sitepress;
			// remove get_terms_args_filter from WPML.
			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10 );

		}
	}
	/**
	 * Add WPML language code to localize data
	 *
	 * @param array $data Data.
	 */
	public function add_wpml_language_code_to_localize_data( $data ) {
		global $sitepress;
		$data['locale'] = $sitepress->get_current_language();
		return $data;
	}
	/**
	 * Add WPML language code to index
	 *
	 * @param array  $index Index.
	 * @param object $post Post.
	 */
	public function add_wpml_language_code_to_index( $index, $post ) {
		global $sitepress;
		foreach ( $index as $key => $object_index ) {
			// create language code in post object if not set.
			if ( ! isset( $post->language_code ) ) {
				$post->language_code = ( $sitepress->get_language_for_element( $post->ID, 'post_' . $post->post_type ) );
			}

			// Return early if WPML language code not set.
			if ( ! isset( $post->language_code ) ) {
				continue;
			}

			$index[ $key ]['language'] = $post->language_code;
		}
		return $index;
	}


	/**
	 * Translate post URL
	 *
	 * @param string $url URL.
	 * @param object $post Post object.
	 */
	public function translate_post_url( $url, $post ) {
		global $sitepress;
		// create language code in post object if not set.
		if ( ! isset( $post->language_code ) ) {
				$post->language_code = ( $sitepress->get_language_for_element( $post->ID, 'post_' . $post->post_type ) );
		}

			// Return early if WPML language code not set.
		if ( ! isset( $post->language_code ) ) {
			return $url;
		}

		$default_lang = $sitepress->get_default_language();
		if ( $post->language_code !== $default_lang ) {
			$url = apply_filters( 'wpml_permalink', $url, $post->language_code );
		}

		return $url;
	}

	/**
	 * Remove conflicting styles
	 */
	public function remove_conflicting_styles() {
		// this two styles are conflicting with the dashboard input fields styles.
		wp_deregister_style( 'wpml-tm-styles' );
		wp_deregister_style( 'sitepress-style' );
	}
}
