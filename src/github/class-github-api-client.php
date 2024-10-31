<?php
/**
 * GitHub API Client
 *
 * @package StaticSnap
 */

namespace StaticSnap\Github;

use WP_Error;

/**
 * Class Github_Api_Client
 * Handles interactions with the GitHub API.
 */
final class Github_Api_Client {
	/**
	 * Token for authentication.
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Base URL for the GitHub API.
	 *
	 * @var string
	 */
	private $base_url = 'https://api.github.com';

	/**
	 * Constructor to initialize the client with an access token.
	 *
	 * @param string $token Access token for authentication.
	 */
	public function __construct( $token ) {
		$this->token = $token;
	}


		/**
		 * Gets the content of a file in the repository.
		 *
		 * @param string $owner Repository owner.
		 * @param string $repo Repository name.
		 * @param string $path File path.
		 * @param string $branch Branch name.
		 * @return array|WP_Error File content or WP_Error on failure.
		 */
	public function get_file_contents( $owner, $repo, $path, $branch = 'main' ) {
		return $this->get( "/repos/$owner/$repo/contents/$path?ref=$branch" );
	}



	/**
	 * Uploads or updates a file in the repository.
	 *
	 * @param string      $owner Repository owner.
	 * @param string      $repo Repository name.
	 * @param string      $branch Branch name.
	 * @param string      $path File path.
	 * @param string      $content File content.
	 * @param string|null $comments Commit message.
	 * @param string|null $sha SHA of the file to update.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function upload_file( $owner, $repo, $branch, $path, $content, $comments, $sha = null ) {
		$data = array(
			'message' => $comments,
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'content' => base64_encode( $content ),
			'branch'  => $branch,
		);

		if ( $sha ) {
			$data['sha'] = $sha;
		}

		return $this->put( "/repos/$owner/$repo/contents/$path", $data );
	}

	/**
	 * Creates a new file in the repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param string $filename Name of the file to create.
	 * @param string $content Content of the file.
	 * @param string $message Commit message.
	 * @param string $branch Branch name.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function create_file( $owner, $repo, $filename, $content, $message, $branch ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_content = base64_encode( $content );

		// Prepare the data for the request.
		$data = array(
			'message' => $message,
			'content' => $encoded_content,
			'branch'  => $branch,
		);

		// Make the request to create the file.
		return $this->put( "/repos/$owner/$repo/contents/$filename", $data );
	}

	/**
	 * Updates a file in the repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param string $path File path.
	 * @param string $content New file content.
	 * @param string $comments Commit message.
	 * @param string $sha SHA of the file to update.
	 * @param string $branch Branch name.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function update_file( $owner, $repo, $path, $content, $comments, $sha, $branch = 'main' ) {
		$data = array(
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'content' => base64_encode( $content ),
			'message' => $comments,
			'sha'     => $sha,
			'branch'  => $branch,
		);

		return $this->put( "/repos/$owner/$repo/contents/$path", $data );
	}



	/**
	 * Gets the branches of a repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @return array|WP_Error List of branches or WP_Error on failure.
	 */
	public function get_branches( $owner, $repo ) {
		$endpoint = "/repos/$owner/$repo/branches";
		return $this->get( $endpoint );
	}

	/**
	 * Creates a new release in the repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param array  $params Release parameters.
	 */
	public function create_release( $owner, $repo, $params = array() ) {

		return $this->post( "/repos/$owner/$repo/releases", $params );
	}

	/**
	 * Updates a release in the repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param int    $release_id Release ID.
	 * @param array  $data Data to update the release (e.g., draft, prerelease).
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function edit_release( $owner, $repo, $release_id, $data ) {
		return $this->patch( "/repos/$owner/$repo/releases/$release_id", $data );
	}


	/**
	 * Creates a reference (branch) in the repository.
	 *
	 * @param string $owner Repository owner.
	 * @param string $repo Repository name.
	 * @param array  $data Reference data.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function create_reference( $owner, $repo, $data ) {

		return $this->post( "/repos/$owner/$repo/git/refs", $data );
	}


	/**
	 * Makes a GET request to the GitHub API.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $params Query parameters.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function get( $endpoint, $params = array() ) {
		return $this->request( 'GET', $endpoint, $params );
	}

	/**
	 * Makes a POST request to the GitHub API.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $data Request data.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public function post( $endpoint, $data = array() ) {
		return $this->request( 'POST', $endpoint, $data );
	}

	/**
	 * Makes a PUT request to the GitHub API.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $data Request data.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	private function put( $endpoint, $data ) {
		return $this->request( 'PUT', $endpoint, $data );
	}

	/**
	 * Makes a PATCH request to the GitHub API.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $data Request data.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	private function patch( $endpoint, $data ) {
		return $this->request( 'PATCH', $endpoint, $data );
	}

	/**
	 * Makes a request to the GitHub API.
	 *
	 * @param string $method HTTP method.
	 * @param string $endpoint API endpoint.
	 * @param array  $data Request data or query parameters.
	 * @return array|WP_Error Response data or WP_Error on failure.
	 * @throws \Exception If the request fails.
	 */
	private function request( $method, $endpoint, $data = array() ) {
		$url  = $this->base_url . $endpoint;
		$args = array(
			'method'  => $method,
			'headers' => $this->get_headers(),
		);

		if ( ! empty( $data ) ) {
			if ( 'GET' === $method ) {
				$url .= '?' . http_build_query( $data );
			} else {
				$args['body']                    = wp_json_encode( $data );
				$args['headers']['Content-Type'] = 'application/json';
			}
		}

		$response = wp_remote_request( $url, $args );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code >= 200 && $status_code < 300 ) {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}

		throw new \Exception( 'github_api_error', \intval( $status_code ) );
	}

	/**
	 * Gets the headers for the request.
	 *
	 * @return array Request headers.
	 */
	private function get_headers() {
		return array(
			'Authorization' => 'token ' . $this->token,
			'User-Agent'    => 'StaticSnap',
		);
	}
}
