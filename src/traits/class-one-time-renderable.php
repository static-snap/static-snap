<?php
/**
 * Renderable
 *
 * @package StaticSnap
 */

declare(strict_types=1);

namespace StaticSnap\Traits;

/**
 * The renderable trait to render a view
 */
trait OneTimeRenderable {
	use Renderable {
		render as protected renderable_render;
	}


	/**
	 * Rendered
	 *
	 * @var bool
	 */
	protected $rendered = false;

	/**
	 * Render the view
	 *
	 * @return string
	 */
	final public function render(): void {
		if ( $this->rendered ) {
			return;
		}

		$this->rendered = true;
		$this->renderable_render();
	}
}
