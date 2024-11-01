<?php
/**
 * URL Finder
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Filters;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Traits\Singleton;


/**
 * URL Finder class
 *
 * This class is responsible for finding URLs in the content and saving them to the URLs database.
 * it uses the POST_URL_CONTENT filter to find URLs in the content and a simple regular expression to find anchor link URLs in the content.
 */
final class URL_Finder {
	use Singleton;


	/**
	 * Init
	 */
	protected function init() {
		$options = Options::instance();
		/**
		 * We allow to desactivate the url finder in the content to improve performance .
		 */
		if ( ! $options->get( 'build_options.enable_url_finder', true ) ) {
			return;
		}
		// run the url finder in the content before other filters.
		add_filter( Filters::POST_URL_CONTENT, array( $this, 'find_urls' ), 1, 2 );
	}
	/**
	 * Find urls in the content and save them to the urls database.
	 *
	 * @param string $content Content.
	 * @param object $content_url URL.
	 * @return string
	 */
	public function find_urls( string $content, $content_url ): string {
		// find anchors links in the content that are in the same WordPress domain or are relative.
		// example url http://example.com/2023/12/07/.
		$domain   = wp_parse_url( home_url() )['host'];
		$patterns = array(
			'/<a\s+(?:[^>]*?\s+)?href=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// links tags.
			'/<link\s+(?:[^>]*?\s+)?href=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// scripts.
			'/<script\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Image tags.
			'/<img\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Iframe tags.
			'/<iframe\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Video tags.
			'/<video\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			'/<video\s+(?:[^>]*?\s+)?poster=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Audio tags.
			'/<audio\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Source tags (for video and audio).
			'/<source\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Embed tags.
			'/<embed\s+(?:[^>]*?\s+)?src=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
			// Object tags.
			'/<object\s+(?:[^>]*?\s+)?data=(["\'])(?:https?:\/\/' . preg_quote( $domain, '/' ) . '|\/)(.*?)\1/',
		);
		foreach ( $patterns as $pattern ) {
			// save all urls to urls database.
			if ( preg_match_all( $pattern, $content, $matches ) ) {
				$urls = array();
				foreach ( $matches[2] as $url ) {
					// check if the url is a full url or relative.
					$full_url = $url;
					if ( strpos( $url, 'http' ) !== 0 ) {
						// full url and remove query string.
						$full_url   = home_url( $url );
						$parsed_url = wp_parse_url( $full_url );
						if ( ! isset( $parsed_url['scheme'] ) || ! isset( $parsed_url['host'] ) || ! isset( $parsed_url['path'] ) ) {
							continue;
						}
						if ( pathinfo( $parsed_url['path'], PATHINFO_EXTENSION ) ) {
							continue;
						}

						$full_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];

						$full_url = trailingslashit( $full_url );
					}

					$urls[] = new URL( $full_url, null, 'published', 'URL_Finder::find_urls::' . $content_url->id );
				}
				$urls_database = URLS_Database::instance();
				$urls          = apply_filters( Filters::BEFORE_SAVE_POST_URLS, $urls );
				$urls_database->insert_many( $urls );
			}
		}

		return $content;
	}
}
