<?php
/**
 * Interface TemplateEngine.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

use AmpProject\AmpWP\Documentation\Model\Leaf;
use RuntimeException;

interface TemplateEngine {

	/**
	 * Render a specific template.
	 *
	 * @param string     $template Name of the template to use.
	 * @param array|Leaf $data     Associative array of data to use for rendering.
	 * @return string Rendered result.
	 * @throws RuntimeException If the template file could not be located.
	 * @throws RuntimeException If the template file could not be loaded.
	 */
	public function render( $template, $data );
}
