<?php
/**
 * Search templates
 *
 * @package StaticSnap
 */

namespace StaticSnap\Search;

use StaticSnap\Traits\OneTimeRenderable;


/**
 * Search templates
 */
final class Search_Result_Template {

	use OneTimeRenderable;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->allowed_html = array(
			// allow <script type="text/template" id="something">.
			'script' => array(
				'type' => true,
				'id'   => true,
			),
		);
	}
}
