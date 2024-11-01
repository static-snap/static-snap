<?php
/**
 * Environment class
 *
 * @package StaticSnap
 */

namespace StaticSnap\Environments;

use StaticSnap\Application;
use StaticSnap\Constants\Build_Type;
use StaticSnap\Constants\Filters;
use StaticSnap\Deployment\Deployment_Task_Manager;
use StaticSnap\Filesystem\Filesystem;
use StaticSnap\Interfaces\Environment_Interface;
use StaticSnap\Interfaces\Environment_Type_Interface;


/**
 * This class is used to get all data from current environment.
 */
final class Environment implements Environment_Interface {
	/**
	 * Id
	 *
	 * @var int
	 */
	private $id = 0;
	/**
	 * Environment type
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * Environment name
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Environment settings
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Local build path
	 *
	 * @var string
	 */
	private $build_path = '';

	/**
	 * Destination type
	 *
	 * @var string
	 */
	private $destination_type = 'relative';

	/**
	 * Destination path
	 *
	 * @var string
	 */
	private $destination_path = '/';


	/**
	 * Constructor
	 *
	 * @param int    $id Environment id.
	 * @param string $type Environment type.
	 * @param string $name Environment name.
	 * @param string $destination_type Destination type.
	 * @param string $destination_path Destination path.
	 * @param array  $settings Environment settings.
	 */
	public function __construct( $id, $type, $name, $destination_type, $destination_path, $settings ) {
		$this->id               = $id;
		$this->type             = $type;
		$this->name             = $name;
		$this->destination_type = $destination_type;
		$this->destination_path = $destination_path;
		$this->settings         = $settings;
	}

	/**
	 * Get the environment id
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}


	/**
	 * Get the environment type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}
	/**
	 * Get type instance
	 *
	 * @return Environment_Type_Interface
	 * @throws \Exception Exception.
	 */
	public function get_type_instance(): Environment_Type_Interface {
		$valid_types = Application::instance()->get_extensions_by_type( 'environment_type' );

		if ( ! isset( $valid_types[ $this->type ] ) ) {
			throw new \Exception( 'Invalid environment type' );
		}

		$valid_types[ $this->type ]->set_settings( $this->settings );

		return $valid_types[ $this->type ];
	}

	/**
	 * Get the environment name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the environment settings
	 *
	 * @return array
	 */
	public function get_settings(): array {
		return $this->settings;
	}

	/**
	 * Delete build path
	 */
	public function delete_build_path() {
		$build_path = $this->get_build_path();
		if ( file_exists( $build_path ) ) {
			$filesystem = new Filesystem();
			$filesystem->delete( $build_path, true );
		}
	}

	/**
	 * Get local build path
	 *
	 * @return string
	 */
	public function get_build_path(): string {
		if ( ! empty( $this->build_path ) ) {
			return $this->build_path;
		}

		$name_slug        = sanitize_title( $this->name );
		$destination      = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'static-snap' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $name_slug;
		$destination      = wp_normalize_path( $destination );
		$destination      = apply_filters( Filters::BUILD_DIRECTORY_PATH, $destination, $this );
		$this->build_path = $destination;

		return $this->build_path;
	}

	/**
	 * Get destination type
	 *
	 * @return string
	 */
	public function get_destination_type(): string {
		return $this->destination_type;
	}

	/**
	 * Get destination path
	 *
	 * @return string
	 */
	public function get_destination_path(): string {
		return $this->destination_path;
	}

	/**
	 * Get Zip file name
	 *
	 * @return string
	 */
	public function get_zip_file_name(): string {

		if ( array_key_exists( 'zip_file_name', $this->settings ) ) {
			return $this->settings['zip_file_name'];
		}

		$name_slug = sanitize_title( $this->name );
		$zip_name  = $name_slug . '.zip';
		return $zip_name;
	}


	/** To Array
	 *
	 * @param bool $json_settings JSON settings.
	 * @return array
	 */
	public function to_array( $json_settings = true ): array {
		return array(
			'id'               => $this->id,
			'type'             => $this->type,
			'name'             => $this->name,
			'destination_type' => $this->destination_type,
			'destination_path' => $this->destination_path,
			// phpcs:ignore
			'settings' => $json_settings ? json_encode( $this->settings ) : $this->settings,
		);
	}

	/**
	 * To JSON
	 *
	 * @return string
	 */
	public function to_json(): string {
		// phpcs:ignore
		return json_encode( $this->to_array() );
	}

	/**
	 * To String
	 */
	public function __toString() {
		return $this->to_json();
	}

	/**
	 * From Array
	 *
	 * @param array $data Data.
	 * @param bool  $decode_settings Decode settings.
	 * @return Environment
	 */
	public static function from_array( array $data, $decode_settings = true ): Environment {
		return new Environment(
			$data['id'],
			$data['type'],
			$data['name'],
			$data['destination_type'],
			$data['destination_path'],
			$decode_settings ? json_decode( $data['settings'], true ) : $data['settings']
		);
	}

	/**
	 * Start deploy process
	 *
	 * @param string $build_type Build type.
	 *
	 * @return bool
	 */
	public function publish( $build_type = Build_Type::FULL ): bool {
		return Application::instance()->run_deployment( $this, $build_type );
	}

	/**
	 * Init deployment tasks
	 */
	public function init_deployment_tasks() {
		$type_tasks = $this->get_type_instance()->get_deployment_tasks();
		add_filter(
			Filters::DEPLOYMENT_TASKS,
			function ( $tasks ) use ( $type_tasks ) {
				// Add task just before StaticSnap\Deployment\Deploy\Done_Task if found. Otherwise add it at the end.
				$done_task_index = array_search( Deployment_Task_Manager::DONE_TASK, $tasks, true );
				if ( false !== $done_task_index ) {
					array_splice( $tasks, $done_task_index, 0, $type_tasks );
				} else {
					$tasks = array_merge( $tasks, $type_tasks );
				}

				return $tasks;
			}
		);
	}
}
