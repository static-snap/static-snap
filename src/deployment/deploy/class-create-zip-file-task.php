<?php
/**
 * Create ZIP File Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Deployment\Task;
use StaticSnap\Database\URLS_Database;


/**
 * Create ZIP File Task class
 */
final class Create_ZIP_File_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Creating ZIP File';
	/**
	 * Perform task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @return bool
	 */
	public function perform(): bool {

		if ( ! $this->deployment_process->get_environment()->get_type_instance()->needs_zip() ) {
			return true;
		}

		// save all urls database as a zip file.
		$database = URLS_Database::instance();
		$urls     = $database->get_all( 'deployed' );

		$zip_filename = $this->deployment_process->get_environment()->get_build_path() . '/' . $this->deployment_process->get_environment()->get_zip_file_name();
		$zip          = new \ZipArchive();
		$zip->open( $zip_filename, \ZipArchive::CREATE );

		while ( $urls ) {

			foreach ( $urls as $url ) {
				$file = new \SplFileInfo( $url->local_path_destination );
				// remove ROOT path from the file path.
				$file_entry_name = str_replace( $this->deployment_process->get_environment()->get_build_path() . DIRECTORY_SEPARATOR, '', $url->local_path_destination );
				$zip->addFile( $file->getRealPath(), $file_entry_name );

				// mark the url as deployed.
				$database->set_deployed( $url->id );
			}

			$urls = $database->get_all( 'deployed' );
		}

		$zip->close();

		return true;
	}
}
