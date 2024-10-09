<?php
/**
 * Assets
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Constants\Filters;
use StaticSnap\Environments\Environment;

/**
 * Assets class
 */
final class Assets {

	const IGNORED_FILES = array(
		'.git',
		'.gitignore',
		'.gitattributes',
		'.gitkeep',
		'.gitmodules',
		'.svn',
		'.hg',
		'.editorconfig',
		'.phpcs.xml',
		'.phpcs.xml.dist',
		'tmp',
		'node_modules',
		'package.json',
		'package-lock.json',
		'composer.json',
		'composer.lock',
		'.DS_Store',
	);

	const IGNORED_EXTENSIONS = array(
		'php',
		'inc',
		'phtml',
		'php3',
		'php4',
		'php5',
		'php7',
		'php8',
		'phpt',
		'phps',
		'phar',
		// typescript.
		'ts',
		'tsx',
		'mo',
		'po',
	);

	const POSSIBLE_EXTENSIONS_WITH_URLS = array(
		'css',
		'js',
		'json',
		'xml',
		'html',
		'xhtml',
		'rss',
		'atom',
		'rdf',
	);

	const IGNORED_PATTERNS = array(
		// hidden files start by .* .
		'/^\..*/',
		// webpack config files.
		'/webpack\.config\..*\.js/',
		// babel config files.
		'/babel\.config\..*\.js/',
		// eslint config files.
		'/\.eslintrc\..*\.js/',
		// stylelint config files.
		'/\.stylelintrc\..*\.js/',
		// composer files.
		'/composer\..*\.json/',
		// .env files.
		'/\.env\..*/',
		// sql files outside uploads directory.
		'#^(?!/wp-content/uploads).*\.sql$#',
		// All /wp-admin folder.
		'#^/wp-admin#',
		// All files inside static-snap plugin folder but ignore frontend.js file.
		// '#^/wp-content/plugins/static-snap/.*(?<!frontend\.js)$#',.

	);


	/**
	 * Copy assets recursively
	 *
	 * @param string $source Source.
	 * @return \RecursiveIteratorIterator
	 * @throws \Exception If directory could not be created.
	 */
	public static function get_assets( string $source ): \RecursiveIteratorIterator {

		$directory_iterator = new \RecursiveDirectoryIterator( $source, \RecursiveDirectoryIterator::SKIP_DOTS );
		$ignored_files      = apply_filters( Filters::IGNORED_FILES, self::IGNORED_FILES );
		$ignored_extensions = apply_filters( Filters::IGNORED_EXTENSIONS, self::IGNORED_EXTENSIONS );
		$patterns           = apply_filters( Filters::IGNORED_PATTERNS, self::IGNORED_PATTERNS );

		$filter = new \RecursiveCallbackFilterIterator(
			$directory_iterator,
			function ( $current ) use ( $source, $ignored_files, $ignored_extensions, $patterns ) {

				$current_path = $current->getRealPath();

				$relative_path = substr( $current->getRealPath(), strlen( rtrim( $source, DIRECTORY_SEPARATOR ) ) );

				// Skip hidden files and directories.
				if ( in_array( $relative_path, $ignored_files, true ) || in_array( $current->getFilename(), $ignored_files, true ) || in_array( $current->getExtension(), $ignored_extensions, true ) ) {
					return false;
				}

				foreach ( $patterns as $pattern ) {
					if ( preg_match( $pattern, $relative_path ) || preg_match( $pattern, $current->getFilename() ) ) {
						return false;
					}
				}
				return true;
			}
		);

		return new \RecursiveIteratorIterator(
			$filter,
			\RecursiveIteratorIterator::SELF_FIRST
		);
	}

	/**
	 * Copy assets recursively
	 *
	 * @param \SplFileInfo $file Iterator.
	 * @param object       $url Destination.
	 * @param Environment  $environment Environment.
	 *
	 * @return array
	 * @throws \Exception If directory could not be created.
	 */
	public static function copy( \SplFileInfo $file, object $url, Environment $environment ): array {
		$destination = $environment->get_build_path();
		if ( $file->isDir() ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			trigger_error( 'Directory should not be in the database ' . esc_html( $file->getRealPath() ) );
			// return true to remove the item from the queue.
			return array(
				'saved'                  => true,
				'local_path_destination' => $destination,
			);
		}
		$relative_path = substr( $file->getRealPath(), strlen( rtrim( ABSPATH, DIRECTORY_SEPARATOR ) ) );

		$destination       = $destination . $relative_path;
		$destination       = wp_normalize_path( $destination );
		$directory         = dirname( $destination );
		$directory_created = wp_mkdir_p( $directory );
		if ( false === $directory_created ) {
			throw new \Exception( 'Could not create directory: ' . esc_html( dirname( $destination ) ) );
		}

		$possible_extensions = self::get_content_assets_extensions();
		if ( in_array( $file->getExtension(), $possible_extensions, true ) ) {
			// check if WP_Filesystem is available.
			if ( ! function_exists( 'WP_Filesystem_Direct' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			}

			if ( ! defined( 'FS_CHMOD_FILE' ) ) {
				$chmod_dir = ( 0755 & ~ umask() );
				define( 'FS_CHMOD_FILE', $chmod_dir );
			}
			$filesystem = new \WP_Filesystem_Direct( true );
			$content    = $filesystem->get_contents( $file->getRealPath() );

			$content = apply_filters( Filters::POST_URL_CONTENT, $content, $url, $environment );

			$finished = $filesystem->put_contents( $destination, $content );

			return array(
				'saved'                  => false !== $finished,
				'local_path_destination' => $destination,
			);
		}

		$finished = copy( $file->getRealPath(), $destination );

		return array(
			'saved'                  => $finished,
			'local_path_destination' => $destination,
		);
	}

	/**
	 * Get content assests extensions
	 * Will return the extensions of the files that are considered as content assets.
	 * Assets which can contain urls that need to be replaced.
	 */
	public static function get_content_assets_extensions(): array {
		return apply_filters( Filters::POSSIBLE_EXTENSIONS_WITH_URLS, self::POSSIBLE_EXTENSIONS_WITH_URLS );
	}
}
