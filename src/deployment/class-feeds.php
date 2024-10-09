<?php
/**
 * Feeds
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Filters;
use StaticSnap\Traits\Singleton;

/**
 * Feeds class
 * This class will generate feeds url for each post / taxonomy / author
 */
final class Feeds {

	use Singleton;


	/**
	 * Default feed options
	 *
	 * @var array
	 */
	private $default_feed_options = array(
		'enable_feed'                  => true,

		'enable_rss_feed_atom'         => false,
		'enable_rss_feed_rdf'          => false,
		'enable_rss_feed_rss'          => true, // it includes rss2 and rss.

		'enable_rss_feed_taxonomy'     => false,
		'enable_rss_feed_author'       => false,
		'enable_rss_feed_post_comment' => false,



	);

	/**
	 * Feed options
	 *
	 * @var array
	 */
	private $feed_options = array();

	/**
	 * Init
	 */
	public function init() {
		$this->feed_options = Options::instance()->get( 'build_options', $this->default_feed_options );
		// just in case build_options doesnt have all default feed options key merge with default.
		$this->feed_options = array_merge( $this->default_feed_options, $this->feed_options );

		if ( ! $this->feed_options['enable_feed'] ) {
			return;
		}
		// before save post urls we add the post feeds and root feeds.
		add_filter( Filters::BEFORE_SAVE_POST_URLS, array( $this, 'get_post_feeds' ) );

		// before save taxonomy urls we add the taxonomy feeds.
		if ( $this->feed_options['enable_rss_feed_taxonomy'] ) {
			add_filter( Filters::BEFORE_SAVE_TERM_URLS, array( $this, 'get_taxonomy_feeds' ) );
		}

		// before save author urls we add the author feeds.
		if ( $this->feed_options['enable_rss_feed_author'] ) {
			add_filter( Filters::BEFORE_SAVE_AUTHORS_URLS, array( $this, 'get_author_feeds' ) );
		}
	}

	/**
	 * Get enabled feed types
	 *
	 * @return array
	 */
	protected function get_enabled_feed_types() {
		return array(
			'rss'  => $this->feed_options['enable_rss_feed_rss'],
			'atom' => $this->feed_options['enable_rss_feed_atom'],
			'rdf'  => $this->feed_options['enable_rss_feed_rdf'],
		);
	}

	/**
	 * Check if we have feed types enabled
	 *
	 * @return bool
	 */
	protected function has_feed_types() {
		$feed_types = $this->get_enabled_feed_types();
		foreach ( $feed_types as $feed_type ) {
			if ( $feed_type ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get author feeds
	 *
	 * @param array $urls URLs.
	 */
	public function get_author_feeds( array $urls ): array {
		if ( ! $this->has_feed_types() ) {
			return $urls;
		}
		$feed_types = $this->get_enabled_feed_types();

		foreach ( $feed_types as $feed_type ) {
			if ( $feed_type ) {
				$all_disabled = false;
				break;
			}
		}

		$feeds_urls = $urls;
		foreach ( $urls as $url ) {
			if ( ! $url instanceof Author_URL ) {
				continue;
			}
			$author = $url->get_author();
			$feeds  = array();
			if ( $feed_types['rss'] ) {
				$feeds[] = get_author_feed_link( $author->ID );
				$feeds[] = get_author_feed_link( $author->ID, 'rss2' );
			}
			if ( $feed_types['atom'] ) {
				$feeds[] = get_author_feed_link( $author->ID, 'atom' );
			}
			if ( $feed_types['rdf'] ) {
				$feeds[] = get_author_feed_link( $author->ID, 'rdf' );
			}
			foreach ( $feeds as $feed ) {
				$feed_url = new URL( $feed, $url->get_last_modified(), $url->get_status(), 'Feeds::get_author_feeds' );
				// We need the feeds to be indexed first, because we will replace all the urls in the feeds with the local urls.
				$feed_url->set_priority( 1 );
				if ( $feed_url->is_valid() ) {
					$feeds_urls[] = $feed_url;
				}
			}
		}

		return $feeds_urls;
	}

	/**
	 * Get post feeds
	 * TODO: root feeds logic should be separate from post feeds.
	 *
	 * @param array $urls URLs.
	 * @return array
	 */
	public function get_post_feeds( array $urls ): array {
		if ( ! $this->has_feed_types() ) {
			return $urls;
		}
		$feed_types = $this->get_enabled_feed_types();

		$root_feeds = $this->get_root_feeds();
		$feeds_urls = array();
		if ( $this->feed_options['enable_rss_feed_post_comment'] ) {
			foreach ( $urls as $url ) {
				$feeds = array();
				// check url instance is Post_URL.
				if ( ! $url instanceof Post_URL ) {
					continue;
				}
				$post = $url->get_post();

				if ( $feed_types['rss'] ) {
					$feeds[] = get_post_comments_feed_link( $post->ID );
					$feeds[] = get_post_comments_feed_link( $post->ID, 'rss2' );
				}

				if ( $feed_types['atom'] ) {
					$feeds[] = get_post_comments_feed_link( $post->ID, 'atom' );
				}

				if ( $feed_types['rdf'] ) {
					$feeds[] = get_post_comments_feed_link( $post->ID, 'rdf' );
				}

				foreach ( $feeds as $feed ) {
					$feed_url = new URL( $feed, $url->get_last_modified(), $url->get_status(), 'Feeds::get_post_feeds' );
					// We need the feeds to be indexed first, because we will replace all the urls in the feeds with the local urls.
					$feed_url->set_priority( 1 );
					if ( $feed_url->is_valid() ) {
						$feeds_urls[] = $feed_url;
					}
				}
			}
		}
		// add post feeds to the urls.
		return array_merge( $urls, $feeds_urls, $root_feeds );
	}

	/**
	 * Get Root feeds
	 *
	 * @return array
	 */
	public function get_root_feeds(): array {
		if ( ! $this->has_feed_types() ) {
			return array();
		}

		$feed_types = $this->get_enabled_feed_types();

		$feeds = array();
		if ( $feed_types['rss'] ) {
			$feeds[] = get_bloginfo( 'rss_url' );
			$feeds[] = get_bloginfo( 'rss2_url' );
			$feeds[] = get_bloginfo( 'comments_rss2_url' );
		}
		if ( $feed_types['atom'] ) {
			$feeds[] = get_bloginfo( 'atom_url' );
			$feeds[] = get_bloginfo( 'comments_atom_url' );
		}

		if ( $feed_types['rdf'] ) {
			$feeds[] = get_bloginfo( 'rdf_url' );
		}

		$home_page_type = get_option( 'show_on_front' ) || 'posts';

		$home_post             = get_post( get_option( 'page' === $home_page_type ? 'page_on_front' : 'page_for_posts' ) );
		$home_post_date_string = gmdate( 'Y - m - d H:i:s', strtotime( 'now' ) );
		if ( ! empty( $home_post ) ) {
			$home_post_date_string = $home_post->post_modified;
		}

		$urls = array();
		foreach ( $feeds as $feed ) {
			$feed_url = new URL( $feed, $home_post_date_string, 'published', 'Feeds::get_root_feeds' );
			$feed_url->set_priority( 1 );
			if ( $feed_url->is_valid() ) {
				$urls[] = $feed_url;
			}
		}

		return $urls;
	}

	/**
	 * Get taxonomy feeds
	 *
	 * @param array $urls URLs.
	 * @return array
	 */
	public function get_taxonomy_feeds( array $urls ): array {
		if ( ! $this->has_feed_types() ) {
			return $urls;
		}

		$feed_types = $this->get_enabled_feed_types();

		$feeds_urls = $urls;
		foreach ( $urls as $url ) {
			if ( ! $url instanceof Term_URL ) {
				continue;
			}
			$term  = $url->get_term();
			$feeds = array();
			if ( $feed_types['rss'] ) {
				$feeds[] = get_term_feed_link( $term->term_id, $term->taxonomy );
				$feeds[] = get_term_feed_link( $term->term_id, $term->taxonomy, 'rss2' );
			}
			if ( $feed_types['atom'] ) {
				$feeds[] = get_term_feed_link( $term->term_id, $term->taxonomy, 'atom' );
			}
			if ( $feed_types['rdf'] ) {
				$feeds[] = get_term_feed_link( $term->term_id, $term->taxonomy, 'rdf' );
			}
			foreach ( $feeds as $feed ) {
				$feed_url = new URL( $feed, $url->get_last_modified(), $url->get_status(), 'Feeds::get_taxonomy_feeds' );
				// We need the feeds to be indexed first, because we will replace all the urls in the feeds with the local urls.
				$feed_url->set_priority( 1 );
				if ( $feed_url->is_valid() ) {
					$feeds_urls[] = $feed_url;
				}
			}
		}

		return $feeds_urls;
	}
}
