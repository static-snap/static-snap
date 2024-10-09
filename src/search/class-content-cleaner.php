<?php
/**
 * Content cleaner
 *
 * @package StaticSnap
 */

namespace StaticSnap\Search;

use StaticSnap\Constants\Filters;

/**
 * This class is used to clean content.
 */
final class Content_Cleaner {

	/**
	 * Remove noise from content.
	 *
	 * @param string $content The HTML content to clean.
	 * @return string The cleaned content.
	 */
	public static function remove_content_noise( $content ) {
		if ( empty( $content ) ) {
			return 'empty';
		}
		// get WordPress encoding.
		$encoding = get_bloginfo( 'charset' );
		// Load HTML content into DOMDocument.
		$dom = new \DOMDocument( '1.0', $encoding );
		libxml_use_internal_errors( true ); // Suppress HTML parsing errors.
		$parent_wrapper_tmp = uniqid( 'staticsnap-wrapper-' );
		// set encoding.
		$dom->encoding       = $encoding;
		$enconding_force_tag = '<?xml encoding="' . $encoding . '">';
		$dom->loadHTML(
			$enconding_force_tag . "<$parent_wrapper_tmp>$content</$parent_wrapper_tmp>",
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		libxml_clear_errors();

		// Remove unwanted tags and comments.
		$tags_to_remove = array( 'script', 'style', 'code', 'pre', 'header', 'footer', 'nav', 'aside', '//comment()' );
		$tags_to_remove = apply_filters( Filters::SEARCH_CONTENT_TAGS_REMOVE, $tags_to_remove );

		foreach ( $tags_to_remove as $tag ) {
			self::remove_nodes( $dom, "//{$tag}" );
		}

		// Shortcodes to remove.
		$shortcodes_to_remove = array();
		$shortcodes_to_remove = apply_filters( Filters::SEARCH_CONTENT_SHORTCODES_REMOVE, $tags_to_remove );

		foreach ( $shortcodes_to_remove as $tag ) {
			self::remove_shortcodes( $dom, $tag );
		}

		$clean_content = $dom->saveHTML();

		// remove '<?xml encoding="' . $encoding . '">'  first chars.
		$clean_content = substr( $clean_content, strlen( $enconding_force_tag ) );

		// remove <staticsnap> and </staticsnap> tags.
		$clean_content = str_replace( array( "<$parent_wrapper_tmp>", "</$parent_wrapper_tmp>" ), '', $clean_content );

		// Replace non-breaking spaces with regular spaces.
		$clean_content = str_replace( '&nbsp;', ' ', $clean_content );

		// Prevent table content from being concatenated.
		$clean_content = str_replace( array( '</td>', '</th>' ), ' ', $clean_content );

		// Remove extra whitespace.
		$clean_content = preg_replace( '/\s+/', ' ', $clean_content );

		return html_entity_decode( trim( $clean_content ), ENT_QUOTES, $encoding );
	}


	/**
	 * Remove nodes matching an XPath query.
	 *
	 * @param \DOMDocument $dom The DOMDocument instance.
	 * @param string       $xpath_query The XPath query to match nodes.
	 */
	private static function remove_nodes( $dom, $xpath_query ) {
		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( $xpath_query );

		foreach ( $nodes as $node ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$node->parentNode->removeChild( $node );
		}
	}

	/**
	 * Remove shortcodes from the content.
	 *
	 * @param \DOMDocument $dom The DOMDocument instance.
	 * @param string       $shortcode The shortcode to remove.
	 */
	private static function remove_shortcodes( $dom, $shortcode ) {
		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( "//*[contains(text(), '[{$shortcode}')] | //*[contains(text(), '[/{$shortcode}')]" );

		foreach ( $nodes as $node ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$node->nodeValue = preg_replace( "/\[\/?{$shortcode}.*?\]/", '', $node->nodeValue );
		}
	}
}
