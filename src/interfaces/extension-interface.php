<?php
/**
 * Environment Interface
 * This interface is used to create new environment type
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

interface Extension_Interface {

	/**
	 * Is available
	 *
	 * @return boolean
	 */
	public function is_available(): bool;

	/**
	 * Is enabled
	 *
	 * @return boolean
	 */
	public function is_enabled(): bool;
	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => type
	 * @example array
	 *  [
	 *      'api_key' => 'text',
	 *      'api_secret' => 'text',
	 *  ]
	 */
	public function get_settings_fields(): array;

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get type
	 *
	 * Extension type
	 * Valids: 'search', 'environment_type'
	 *
	 * @return string
	 */
	public function get_type(): string;


	/**
	 * Is configured
	 *
	 * @return bool
	 */
	public function is_configured(): bool;

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings(): array;

	/**
	 * Set settings
	 *
	 * @param array $settings settings.
	 * @return void
	 */
	public function set_settings( $settings );

	/**
	 * Add error
	 *
	 * @param string $field field name.
	 * @param string $message error message.
	 * @return void
	 */
	public function add_error( $field, $message );

	/**
	 * Get errors
	 *
	 * @return array
	 */
	public function get_errors(): array;

	/**
	 * Get build tasks
	 * Add extra tasks to the build process just before the build is done
	 *
	 * @return array
	 */
	public function get_build_tasks(): array;

	/**
	 * Get deployment tasks
	 * Add extra tasks to the deployment process just before the deployment is done
	 *
	 * @return array
	 */
	public function get_deployment_tasks(): array;
}
