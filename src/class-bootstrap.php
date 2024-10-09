<?php
/**
 * Bootstrap
 * This class is used to initialize the plugin
 *
 * @package StaticSnap
 */

namespace StaticSnap;

use StaticSnap\Application;
use StaticSnap\Cli\Cli_Commands;

use Composer\Autoload\ClassLoader;

/**
 * Bootstrap class
 */
final class Bootstrap {

	/**
	 * Application instance.
	 *
	 * @var \StaticSnap\Application
	 */
	private $app;
	/**
	 *  Autoloader instance.
	 *
	 * @var ClassLoader $autoloader Autoloader.
	 */
	private $autoloader;

	/**
	 * Bootstrap constructor.
	 *
	 * @param ClassLoader $autoloader Autoloader.
	 */
	public function __construct( ClassLoader $autoloader ) {
		$this->autoloader = $autoloader;

		$this->init();
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		Application::instance();
		if ( \defined( 'WP_CLI' ) && \WP_CLI ) {
			new Cli_Commands();
		}
	}
}
