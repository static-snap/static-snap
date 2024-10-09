<?php
/**
 * File Environment
 * This class is used to create the file environment.
 *
 * @package StaticSnap
 */

namespace StaticSnap\Environments;

use StaticSnap\Environments\Environment_Type_Base;


/**
 * This class is used to create the file environment.
 */
final class File_Environment extends Environment_Type_Base {


	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'file';
	}

	/**
	 * Get settings fields
	 *
	 * @return array of settings fields name => field definition
	 * @example array
	 *  [
	 *      'api_key' => array( 'type' => 'text' ),
	 *      'api_secret' => array( 'type' => 'text' ),
	 *  ]
	 */
	public function get_settings_fields(): array {
		return array(
			'path' => array(
				'required'   => true,
				'type'       => 'text',
				'label'      => 'Path',
				'default'    => '{static-snap_tmp_dir}/{environment_name}',
				'helperText' => 'The path to the build. Example: /var/www/html/builds. You can use the following placeholders: {static-snap_tmp_dir}, {environment_name}, {upload_dir}',
			),
			'create_zip_file' => array(
				'required'   => false,
				'type'       => 'boolean',
				'label'      => 'Create Zip File',
				'default'    => false,
				'helperText' => 'Create a zip file of the build.',
			),
		);
	}

	/**
	 * Needs zip file
	 *
	 * @return bool
	 */
	public function needs_zip(): bool {
		$settings = $this->get_settings();
		return ! empty( $settings['create_zip_file'] );
	}


	/**
	 * Get real path
	 */
	protected function get_real_path(): string {
		/**
		 * Replace variables
		 * accepted variables are
		 * {environment_name}
		 * {upload_dir}
		 */
		$settings = $this->get_settings();
		$name     = sanitize_title( $this->params['name'] );
		$path     = str_replace( '{environment_name}', $name, $settings['path'] );
		$path     = str_replace( '{upload_dir}', wp_upload_dir()['basedir'], $path );
		$path     = str_replace( '{static-snap_tmp_dir}', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'static-snap' . DIRECTORY_SEPARATOR . 'tmp', $path );

		if ( ! is_dir( $path ) ) {
			// try to create the directory.
			WP_Filesystem();
			global $wp_filesystem;
			$created = $wp_filesystem->mkdir( $path, 0755, false );
			if ( ! $created ) {
				$this->add_error( 'path', 'Cannot create directory:  ' . $path );
				return '';
			}
		}
		$real_path = realpath( $path );
		return $real_path;
	}

		/**
		 * Test if the environment is configured correctly
		 */
	public function is_configured(): bool {
		$settings   = $this->get_settings();
		$valid_path = ! empty( $settings['path'] );
		if ( ! $valid_path ) {
			$this->add_error( 'path', 'Path is empty' );
			return false;
		}

		$this->get_real_path();
		return empty( $this->errors );
	}

	/**
	 * This method is called when a build is published
	 *
	 * @param string $path path to the build.
	 * @return bool true if the build is published.
	 */
	public function on_publish( string $path ): bool {
		return true;
	}
}
