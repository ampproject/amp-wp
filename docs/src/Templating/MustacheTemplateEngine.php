<?php
/**
 * Abstract class TemplateEngine.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

use AmpProject\AmpWP\Documentation\Model\Leaf;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use RuntimeException;

/**
 * Mustache-based implementation of the templating engine.
 */
final class MustacheTemplateEngine implements TemplateEngine {

	/**
	 * Root folder that contains the template files.
	 *
	 * @var string
	 */
	const TEMPLATES_ROOT = AMP__DIR__ . '/docs/templates/';

	/**
	 * @var Mustache_Engine
	 */
	private $engine;

	/**
	 * MustacheTemplateEngine constructor.
	 */
	public function __construct() {
		$this->engine = new Mustache_Engine(
			[
				'entity_flags' => ENT_QUOTES,
				'loader'       => new Mustache_Loader_FilesystemLoader(
					self::TEMPLATES_ROOT
				),
			]
		);
	}

	/**
	 * Render a specific template.
	 *
	 * @param string     $template_name Name of the template to use.
	 * @param array|Leaf $data          Associative array of data to use for rendering.
	 * @return string Rendered result.
	 * @throws RuntimeException If the template file could not be located.
	 * @throws RuntimeException If the template file could not be loaded.
	 */
	public function render( $template_name, $data ) {
		return $this->engine->render( $template_name, $data );
	}
}
