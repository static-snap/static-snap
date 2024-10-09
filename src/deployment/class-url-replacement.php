<?php
/**
 * Replacements
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

/**
 * Replacements class
 */
final class URL_Replacement extends URL {
	/**
	 * Replacements
	 *
	 * @var string $url_replacement index the url to replace with the new url.
	 */
	private string $url_replacement;

	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 * @param string $replacement Replacement.
	 */
	public function __construct( $url, $replacement ) {
		$this->url_replacement = $replacement;
		parent::__construct( $url );
	}



	/**
	 * Get replacements
	 *
	 * @return string
	 */
	public function get_url_replacement(): string {
		return $this->url_replacement;
	}
}
