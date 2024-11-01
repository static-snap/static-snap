<?php
/**
 * Interface for environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

use StaticSnap\Constants\Build_Type;
use StaticSnap\Interfaces\Environment_Type_Interface;

/**
 * Interface for environments
 */

interface Environment_Interface {

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function get_id(): int;

	/**
	 * Get the environment type
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Get type instance
	 *
	 * @return Environment_Type_Interface
	 */
	public function get_type_instance(): Environment_Type_Interface;



	/**
	 * Get the environment name
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get the environment settings
	 *
	 * @return array
	 */
	public function get_settings(): array;


	/**
	 * Get Zip file name
	 */
	public function get_zip_file_name(): string;

	/**
	 * Get local build path
	 */
	public function get_build_path(): string;

	/**
	 * Publish
	 *
	 * @param string $build_type Build type.
	 *
	 * @return bool
	 */
	public function publish( $build_type = Build_Type::FULL ): bool;

	/**
	 * Init deployment tasks
	 */
	public function init_deployment_tasks();
}
