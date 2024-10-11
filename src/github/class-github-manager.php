<?php
/**
 * Github
 *
 * @package StaticSnap
 */

namespace StaticSnap\Github;

use StaticSnap\API\API;
use StaticSnapVendor\Github\AuthMethod;
use StaticSnapVendor\Github\Client;

use StaticSnap\Cache\Cache_Persister;
use StaticSnap\Connect\Connect;


/**
 * Class to manage GitHub authentication and operate on repositories.
 */
final class Github_Manager extends Cache_Persister {
	/**
	 * Static snap connect
	 *
	 * @var array
	 */
	protected $connect;


	/**
	 * Get installation access token
	 *
	 * @param int    $installation_id Installation ID.
	 * @param string $repo Repository name.
	 *
	 * @return \WP_Error|\WP_REST_Response|\array
	 */
	public function get_installation_access_token( $installation_id, $repo ) {
		$api      = new API();
		$response = $api->post( '/github/installations/create-installation-access-token/' . $installation_id, array( 'repository' => $repo ) );

		return $response;
	}


	/**
	 * Get current user github app installations
	 *
	 * @param bool $cache Use cache.
	 *
	 * @return \WP_Error|\WP_REST_Response|\array
	 */
	public function get_github_app_user_installations( $cache = true ) {
		if ( $cache ) {
			$cache_items = $this->get_cache();
			if ( ! empty( $cache_items ) ) {
				return $cache_items;
			}
		}

		$api           = new API();
		$installations = $api->get( '/github/installations/get-user-installations' );

		$this->set_cache( $installations );
		$this->persist_cache();

		return $installations;
	}



	/**
	 * Uses the access token to obtain the user's repositories.
	 *
	 * @param int  $installation Installation ID.
	 * @param int  $page Page number.
	 * @param bool $cache Use cache.
	 * @return \WP_Error|\WP_REST_Response|\array
	 * @throws \Exception If there is an error getting the repositories.
	 */
	public function get_user_repositories( $installation, $page = 1, $cache = true ) {
		if ( $cache ) {
			$cache_items = $this->get_cache( array( $installation, $page ) );
			if ( ! empty( $cache_items ) ) {
				return $cache_items;
			}
		}

		$api          = new API();
		$repositories = $api->get( '/github/installations/repositories/' . $installation );

		$this->set_cache( $repositories, array( $installation, $page ) );
		$this->persist_cache();

		return $repositories;
	}


	/**
	 * Authenticate the client using the access token.
	 *
	 * @param Client $client GitHub client.
	 * @param string $token Access token.
	 * @throws \Exception If there is an error authenticating.
	 */
	private function authenticate_client( $client, $token ) {
		try {
			$client->authenticate( $token, null, AuthMethod::ACCESS_TOKEN );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Error authenticating: ' . esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Get the content of the action file.
	 *
	 * @return string
	 */
	private function get_action_file_content() {
		ob_start();
		include __DIR__ . '/action/github-action-template.yml';
		return ob_get_clean();
	}

	/**
	 * Update or create the action file in the repository.
	 *
	 * @param Client $client GitHub client.
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param string $branch Branch name.
	 * @param string $filename File name.
	 * @param string $action_file_content Content of the action file.
	 * @param array  $contents Existing file contents.
	 * @throws \Exception If there is an error updating the file.
	 */
	private function update_or_create_file( $client, $owner, $repo, $branch, $filename, $action_file_content, $contents ) {
		// phpcs:ignore
		$existing_content = base64_decode( $contents['content'] );

		if ( $existing_content === $action_file_content ) {
			return true;
		}

		try {
			$client->api( 'repo' )->contents()->update( $owner, $repo, $filename, $action_file_content, __( 'Update Static Snap action file', 'static-snap' ), $contents['sha'], $branch );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Error updating file: ' . esc_html( $e->getMessage() ) );
		}
	}


	/**
	 * Handle file not found
	 *
	 * @param Client $client GitHub client.
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param string $branch Branch name.
	 * @param string $filename File name.
	 * @param string $action_file_content Content of the action file.
	 * @throws \Exception If there is an error creating the file.
	 */
	private function handle_file_not_found( $client, $owner, $repo, $branch, $filename, $action_file_content ) {
		try {
			$client->api( 'repo' )->contents()->create( $owner, $repo, $filename, $action_file_content, __( 'Add Static Snap action file', 'static-snap' ), $branch );
		} catch ( \Exception $e ) {
			if ( $e->getCode() === 404 ) {
				$this->create_branch_and_file( $client, $owner, $repo, $branch, $filename, $action_file_content );
			} else {
				throw new \Exception( 'Error creating file: ' . esc_html( $e->getMessage() ) );
			}
		}
	}

	/**
	 * Create a branch and file in the repository.
	 *
	 * @param Client $client GitHub client.
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param string $branch Branch name.
	 * @param string $filename File name.
	 * @param string $action_file_content Content of the action file.
	 * @throws \Exception If there is an error creating the branch and file.
	 */
	private function create_branch_and_file( $client, $owner, $repo, $branch, $filename, $action_file_content ) {
		$branches = $client->api( 'repo' )->branches( $owner, $repo );
		$base_sha = $branches[0]['commit']['sha'];

		try {
			$client->api( 'git' )->references()->create(
				$owner,
				$repo,
				array(
					'ref' => 'refs/heads/' . $branch,
					'sha' => $base_sha,
				)
			);
			$client->api( 'repo' )->contents()->create( $owner, $repo, $filename, $action_file_content, __( 'Add Static Snap action file', 'static-snap' ), $branch );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Error creating branch and file: ' . esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Upload action file to the repository.
	 * This file is used to deploy the site using Static Snap.
	 *
	 * @param int    $installation_id Installation ID.
	 * @param string $repo  Repository name.
	 * @param string $branch Branch name.
	 * @throws \Exception If there is an error uploading the file.
	 * @return bool
	 */
	public function upload_action_file( $installation_id, $repo, $branch = 'main' ) {
		$connect      = Connect::instance();
		$connect_data = $connect->get_connect_data();
		if ( empty( $connect_data['installation_id'] ) ) {
			return false;
		}

		$response = $this->get_installation_access_token( $installation_id, $repo );
		$token    = $response['data']['token'];
		$owner    = $response['data']['login'];

		$client = new Client();

		$this->authenticate_client( $client, $token );

		$filename            = '.github/workflows/static-snap-site-deploy.yml';
		$action_file_content = $this->get_action_file_content();

		try {
			$contents = $client->api( 'repo' )->contents()->show( $owner, $repo, $filename, $branch );
			$this->update_or_create_file( $client, $owner, $repo, $branch, $filename, $action_file_content, $contents );
		} catch ( \Exception $e ) {
			if ( $e->getCode() === 404 ) {
				$this->handle_file_not_found( $client, $owner, $repo, $branch, $filename, $action_file_content );
			} else {
				throw new \Exception( 'Error accessing GitHub API: ' . esc_html( $e->getMessage() ) );
			}
		}

		return true;
	}

	/**
	 * Create release
	 * with a zip file
	 *
	 * @param int    $installation_id Installation ID.
	 * @param string $repo Repository name.
	 * @param string $branch Branch name.
	 * @param string $zip_file Full path to the zip file.
	 * @throws \Exception If there is an error creating the release.
	 * @return bool
	 */
	public function create_release( $installation_id, $repo, $branch, $zip_file ) {
		$connect      = Connect::instance();
		$connect_data = $connect->get_connect_data();
		if ( empty( $connect_data['installation_id'] ) ) {
			return false;
		}

		$response = $this->get_installation_access_token( $installation_id, $repo );
		$token    = $response['data']['token'];
		$owner    = $response['data']['login'];

		$client = new Client();

		$this->authenticate_client( $client, $token );

		/**
		 * Create a release
		 * with a zip file
		 */

		$tag_name = uniqid();
		// translators: %s is the branch name.
		$name = sprintf( __( 'Static Snap Release for branch %s', 'static-snap' ), $branch );
		$body = __( 'ZIP release from Static Snap.', 'static-snap' );

		$release = $client->api( 'repo' )->releases()->create(
			$owner,
			$repo,
			array(
				'tag_name'         => $tag_name,
				'target_commitish' => $branch,
				'name'             => $name,
				'body'             => $body,
				'draft'            => true,
				'prerelease'       => false,
			)
		);

		$release_id = $release['id'];

		// Upload asset using cURL.

		// Initialize GuzzleHttp Client.
		$guzzle_client = new \StaticSnapVendor\GuzzleHttp\Client();
		$upload_url    = str_replace( '{?name,label}', '?name=release.zip', $release['upload_url'] );

		try {
			$response = $guzzle_client->request(
				'POST',
				$upload_url,
				array(
					'headers' => array(
						'Authorization' => 'token ' . $token,
						'Content-Type'  => 'application/zip',
						'Accept'        => 'application/vnd.github.v3+json',
					),
					// phpcs:ignore
					'body'    => fopen( $zip_file, 'rb' ),
				)
			);
			$status_code = $response->getStatusCode();
			$body        = $response->getBody()->getContents();

			// Check if upload was successful.
			if ( 201 !== $status_code ) {
				// translators: %s is the status_code.
				throw new \Exception( sprintf( __( 'Failed to upload asset, status code: %s' ), $status_code ) );
			}
		} catch ( \Exception $e ) {
			return false;
		}

		$edit = $client->api( 'repo' )->releases()->edit(
			$owner,
			$repo,
			$release_id,
			array(
				'draft'      => false,
				'prerelease' => false,
			)
		);

		return true;
	}

	/**
	 * Get deployment tasks
	 *
	 * @return array
	 */
	public static function get_deployment_tasks(): array {
		return array(
			'StaticSnap\Github\Tasks\Upload_Action_File_Task',
			'StaticSnap\Github\Tasks\Create_Release_Task',
		);
	}
}
