<?php
/**
 * Base class for all classes
 *
 * @package StaticSnap
 */

declare(strict_types=1);

namespace StaticSnap;

use StaticSnap\Config\Plugin;
use StaticSnap\Application;

/**
 * The Base class which can be extended by other classes to load in default methods
 *
 * @package ThePluginName\Common\Abstracts
 * @since 1.0.0
 */
abstract class Base {

	/**
	 * The plugin instance
	 *
	 * @var Plugin : will be filled with data from the plugin config class.
	 *
	 * @see Plugin
	 */
	protected $plugin = null;

	/**
	 * The application instance
	 *
	 * @var Application : will be filled with data from the application class.
	 *
	 * @see Application
	 */
	protected $app = null;

	/**
	 * Base constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin = Plugin::instance();
		$this->app    = Application::instance();
	}
}
