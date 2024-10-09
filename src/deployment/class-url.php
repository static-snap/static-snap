<?php
/**
 * Interface  URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Constants\Filters;
use StaticSnap\Interfaces\Deployment_URL_Interface;
use StaticSnap\Constants\Mimetypes;
use StaticSnap\Database\Replacements_URLS_Database;
use StaticSnap\Environments\Environment;
use StaticSnap\Filesystem\Filesystem;

/**
 * Interface URL defines the methods that a URL object should implement.
 */
// phpcs:ignore
class URL implements Deployment_URL_Interface {

	/**
	 * Replacements
	 *
	 * @var array $url_replacements index the url to replace with the new url.
	 */
	private static $url_replacements = array();

	/**
	 * URL
	 *
	 * @var string $url
	 */
	private $url = null;

	/**
	 * Last modified
	 * we use string to compatibility with WordPress post object.
	 *
	 * @var string $last_modified
	 */
	private $last_modified = null;

	/**
	 * URL
	 *
	 * @var string $status
	 */
	private $status = null;

	/**
	 * Source
	 *
	 * @var string $source
	 */
	protected $source = 'Url::class';

	/**
	 * Priority
	 *
	 * @var int $priority
	 */
	protected $priority = 10;


	/**
	 * Local path
	 *
	 * @var string $local_path
	 */
	protected $local_path = null;

	/**
	 * Local path destination
	 *
	 * @var string $local_path_destination
	 */
	protected $local_path_destination = null;


	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 * @param string $last_modified Last modified.
	 * @param string $status Status.
	 * @param string $source Source.
	 */
	public function __construct( string $url, string $last_modified = null, string $status = 'published', string $source = 'Url::class' ) {
		$this->url           = $url;
		$this->last_modified = $last_modified ? $last_modified : current_time( 'mysql' );
		$this->status        = $status;
		$this->source        = $source;
	}


	/**
	 * Get  priority
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return $this->priority;
	}
	/**
	 * Set priority
	 *
	 * @param int $priority Priority.
	 * @return void
	 */
	public function set_priority( int $priority ) {
		$this->priority = $priority;
	}

	/**
	 * Get source
	 * Source is where the url is generated from.
	 */
	public function get_source(): string {
		return $this->source;
	}

	/**
	 * Set source
	 *
	 * @param string $source Source.
	 */
	public function set_source( string $source ) {
		$this->source = $source;
	}

	/**
	 * Get  URL
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}



	/**
	 * Get url hash
	 *
	 * @return string
	 */
	public function get_url_hash(): string {
		return md5( $this->get_url() );
	}

	/**
	 * Get local path
	 *
	 * @return string | null
	 */
	public function get_local_path() {
		return $this->local_path;
	}

	/**
	 * Set local path
	 *
	 * @param string $local_path Local path.
	 */
	public function set_local_path( string $local_path ) {
		$this->local_path = $local_path;
	}

	/**
	 * Get local path destination
	 *
	 * @return string | null
	 */
	public function get_local_path_destination() {
		return $this->local_path_destination;
	}

	/**
	 * Set local path destination
	 *
	 * @param string $local_path_destination Local path destination.
	 */
	public function set_local_path_destination( string $local_path_destination ) {
		$this->local_path_destination = $local_path_destination;
	}

	/**
	 * Get last modified
	 *
	 * @return string
	 */
	public function get_last_modified(): string {
		return $this->last_modified;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * To array
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'url'                    => $this->get_url(),
			'url_hash'               => $this->get_url_hash(),
			'last_modified'          => $this->get_last_modified(),
			'status'                 => $this->get_status(),
			'local_path'             => $this->get_local_path(),
			'local_path_destination' => $this->get_local_path_destination(),
			'priority'               => $this->get_priority(),
			'source'                 => $this->get_source(),

		);
	}

	/**
	 *
	 * Is valid url
	 * Valid url will be saved to the database by the deployment task.
	 * Invalid urls will be ignored.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		$url   = $this->get_url();
		$valid = filter_var( $url, FILTER_VALIDATE_URL );
		if ( $valid ) {
			// url should be in the same domain.
			// some plugins add external urls to the post permalink.
			$home_url = home_url();
			$valid    = ! empty( strval( $url ) ) && strpos( $url, $home_url ) === 0;
		}
		return $valid;
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'url';
	}
	/**
	 * Get type reference id
	 *
	 * @return int | null
	 */
	public function get_type_reference_id() {
		return null;
	}



	/**
	 * Default content filter
	 * will convert all site url to relative paths
	 *
	 * @param string      $content Content.
	 * @param object      $url URL.
	 * @param Environment $environment Environment.
	 *
	 * @return string
	 */
	public static function default_content_filter( string $content, object $url, Environment $environment ): string {

		foreach ( self::$url_replacements as $replacement_url => $local_url ) {
			$content = str_replace( $replacement_url, $local_url, $content );           // replace the escaped version of the url.
		}
		/**
		 * Default content filter will replace all site urls with relative paths.
		 */
		$home_url = home_url( '/' );

		$replace_path = $environment->get_destination_path();

		$replace_path = trailingslashit( $replace_path ?? '/' );

		$content                   = str_replace( $home_url, $replace_path, $content );
		$replace_path_without_port = preg_replace( '/:\d+/', '', $replace_path );
		$content                   = str_replace( rtrim( $home_url, '/' ), '/' === $replace_path ? '/' : rtrim( $replace_path_without_port, '/' ), $content );

		// some plugins like elementor encodes urls in elementorConfig object.
		$escaped_home_url = wp_json_encode( $home_url );
		// remove quotes with regex.

		$escaped_home_url = preg_replace( '/[\'"]/', '', $escaped_home_url );

		$escaped_replace_path = wp_json_encode( $replace_path );

		$escaped_replace_path = preg_replace( '/[\'"]/', '', $escaped_replace_path );

		$content = str_replace( $escaped_home_url, $escaped_replace_path, $content );
		return $content;
	}

	/**
	 * Add default filter
	 */
	public static function add_default_filter() {
		add_filter( Filters::POST_URL_CONTENT, array( __CLASS__, 'default_content_filter' ), 10, 3 );
	}
	/**
	 * Get remote url
	 *
	 * @param string      $destination Destination.
	 * @param object      $url URL.
	 * @param Environment $environment Environment.
	 *
	 * @return array [content, extension]
	 */
	public static function get_remote_url( string $destination, object $url, Environment $environment ): array {
		$args = array(
			'redirection' => 'fixed_url' === $url->type, // only redirect if the url is fixed, for example in favicon.ico.
			'blocking'    => true,
			'sslverify'   => false,
			'timeout'     => 10,
			'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',

		);

		$args = apply_filters( Filters::POST_URL_REMOTE_ARGS, $args, $url->url );

		$remote = wp_remote_get( $url->url, $args );

		if ( is_wp_error( $remote ) ) {
			return array( '', 'html' );
		}

		// detect 301 redirect.
		$redirect_url = wp_remote_retrieve_header( $remote, 'location' );

		if ( $redirect_url ) {
			// content will have a redirect script
			// that will redirect to the new url.
			$redirect_url = apply_filters( Filters::POST_URL_CONTENT, $redirect_url, $url, $environment );
			ob_start();
			include __DIR__ . '/templates/redirect.php';
			$content = ob_get_clean();
			return array( $content, 'html' );
		}

		$content_type = trim( wp_remote_retrieve_header( $remote, 'content-type' ) );

		// we want only the type part.
		if ( ! empty( strval( $content_type ) ) && false !== strpos( $content_type, ';' ) ) {
			$content_type = explode( ';', $content_type );
			$content_type = $content_type[0];
		}

		/**
		 * If the content type is not html, we will not save it.
		 */
		$extension = 'html';

		if ( 'text/html' !== $content_type ) {
			$assigned_extension = Mimetypes::get_extension( $content_type );
			if ( $assigned_extension ) {
				$extension = $assigned_extension[0];
			}
			$local_path                = self::get_file_local_destination( $destination, $url, $extension );
			$local_path_to_url         = str_replace( rtrim( $destination, DIRECTORY_SEPARATOR ), home_url(), $local_path );
			$url_replacements_database = Replacements_URLS_Database::instance();
			$url_replacements_database->insert( new URL_Replacement( $url->url, $local_path_to_url ) );
			self::$url_replacements[ $url->url ] = $local_path_to_url;

		}

		$content = wp_remote_retrieve_body( $remote );

		$content = apply_filters( Filters::POST_URL_CONTENT, $content, $url, $environment );

		return array( $content, $extension );
	}

	/**
	 * Get local destination
	 * Returns the local destination for the url in uploads directory.
	 *
	 * @param object      $url URL.
	 * @param Environment $environment Environment.
	 * @return array
	 * @throws \Exception If the directory could not be created.
	 */
	public static function save_url( $url, $environment ): array {
		$destination = $environment->get_build_path();

		$directory_created = wp_mkdir_p( $destination );
		if ( false === $directory_created ) {
			throw new \Exception( 'Could not create directory: ' . esc_html( $destination ) );
		}

		[$content, $extension] = self::get_remote_url( $destination, $url, $environment );

		$local_path = self::get_file_local_destination( $destination, $url, $extension );

		$filesystem = new Filesystem();

		wp_mkdir_p( dirname( $local_path ) );

		$bytes = $filesystem->put_contents( $local_path, $content );

		return array(
			'saved'                  => false !== $bytes,
			'local_path_destination' => $local_path,
		);
	}

	/**
	 * Get file local destination.
	 * example http://localhost/test-url/ will be saved to
	 * /var/www/html/wp-content/uploads/static-snap/tmp/test-url/index.html
	 *
	 * @param string $destination Destination.
	 * @param object $url URL.
	 * @param string $extension Extension.
	 */
	public static function get_file_local_destination( $destination, $url, $extension ): string {
		// add / to end of url.
		$url_string    = trailingslashit( $url->url );
		$relative_path = wp_parse_url( $url_string, PHP_URL_PATH );
		// fixed url don't need index.html.
		$needs_index_file = ! ( 'fixed_url' === $url->type );
		if ( $needs_index_file ) {
			$local_path = trailingslashit( $destination ) . ltrim( $relative_path, DIRECTORY_SEPARATOR ) . 'index.' . $extension;
		} else {
			$local_path = trailingslashit( $destination ) . ltrim( untrailingslashit( $relative_path ), DIRECTORY_SEPARATOR );
		}
		$local_path = apply_filters( Filters::URL_LOCAL_DESTINATION, $local_path, $url );

		$local_path = wp_normalize_path( $local_path );
		return $local_path;
	}
}
