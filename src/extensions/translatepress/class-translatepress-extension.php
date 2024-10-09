<?php
/**
 * TranslatePress
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\TranslatePress;

use StaticSnap\Constants\Filters;
use StaticSnap\Deployment\URL;
use StaticSnap\Search\Content_Cleaner;

/**
 * TranslatePress class
 */
final class TranslatePress_Extension {

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( class_exists( '\TRP_Translate_Press' ) ) {
			add_filter( Filters::BEFORE_SAVE_POST_URLS, array( $this, 'add_translatepress_urls' ), 10 );
			add_filter( Filters::SEARCH_POST_TO_INDEX, array( $this, 'add_translatepress_index' ), 10, 2 );
			add_filter( Filters::FRONTEND_HAS_TRANSLATIONS, '__return_true', 10 );
		}
	}

	/**
	 * Add TranslatePress URLs
	 *
	 * @param array $urls The array of URLs.
	 * @return array
	 */
	public function add_translatepress_urls( $urls ) {

			$translated_urls = array();
			$languages       = \TRP_Translate_Press::get_trp_instance()->get_component( 'settings' )->get_settings();
			$url_converter   = \TRP_Translate_Press::get_trp_instance()->get_component( 'url_converter' );

		foreach ( $languages['url-slugs'] as $language_code => $language ) {
			// if is not the default language.
			if ( $language_code === $languages['default-language'] ) {
				continue;
			}
			foreach ( $urls as $url ) {
				try {

					$translated_url = $url_converter->get_url_for_language( $language_code, $url->get_url(), null );

					$translated_urls[] = new URL( $translated_url );
				} catch ( \Exception $e ) {
					continue;
				}
			}
		}

			$urls = array_merge( $urls, $translated_urls );

		return $urls;
	}

	/**
	 * Add TranslatePress index
	 *
	 * @param array    $index The array of index.
	 * @param \WP_Post $post The post object.
	 * @return array
	 */
	public function add_translatepress_index( $index, $post ) {

		$translated_index   = array();
		$languages          = \TRP_Translate_Press::get_trp_instance()->get_component( 'settings' )->get_settings();
		$translation_render = \TRP_Translate_Press::get_trp_instance()->get_component( 'translation_render' );
		$url_converter      = \TRP_Translate_Press::get_trp_instance()->get_component( 'url_converter' );

		$post_content = apply_filters( 'the_content', $post->post_content );

		// phpcs:ignore
		global $TRP_LANGUAGE;

		// phpcs:ignore
		$original_page_language = $TRP_LANGUAGE;

		$return_index = array();
		foreach ( $index as $object_index ) {
			$object_index['language'] = $languages['default-language'];
			$return_index[]           = $object_index;
			foreach ( $languages['url-slugs'] as $language_code => $language ) {
				// if is not the default language.
				if ( $language_code === $languages['default-language'] ) {
					continue;
				}

				// phpcs:ignore
				\trp_switch_language( $language_code );

				$title   = $translation_render->translate_page( $object_index['title'] );
				$content = $translation_render->translate_page( $post_content );
				$excerpt = $translation_render->translate_page( $object_index['excerpt'] );

				$relative_url = '';
				try {
					// phpcs:ignore
					$translated_url = @$url_converter->get_url_for_language( $language_code, $object_index['url'], null );
					$relative_url   = $translated_url ? wp_make_link_relative( $translated_url ) : '';
				} catch ( \Exception $e ) {
					$relative_url = '/' . $language . $object_index['url'];
				}

				// translate the index.
				$return_index[] = array(
					'objectID' => $object_index['objectID'] . '-' . $language_code,
					'post_id'  => $object_index['post_id'],
					'title'    => html_entity_decode( wp_kses_post( $title ) ) | '',
					'content'  => wp_strip_all_tags( Content_Cleaner::remove_content_noise( $content ), false ) | '',
					'excerpt'  => html_entity_decode( wp_kses_post( $excerpt ) ) | '',
					'url'      => $relative_url,
					'language' => $language_code,
				);

			}
		}

		\trp_switch_language( $original_page_language );

		return $return_index;
	}
}
