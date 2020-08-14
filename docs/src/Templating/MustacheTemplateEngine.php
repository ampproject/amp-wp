<?php
/**
 * Abstract class TemplateEngine.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

use Mustache_Engine;
use RuntimeException;

final class MustacheTemplateEngine implements TemplateEngine {

	/**
	 * Root folder that contains the template files.
	 *
	 * @var string
	 */
	const TEMPLATES_ROOT = AMP__DIR__ . '/templates/';

	/**
	 * @var Mustache_Engine
	 */
	private $engine;

	/**
	 * MustacheTemplateEngine constructor.
	 */
	public function __construct() {
		$this->engine = new Mustache_Engine( [ 'entity_flags' => ENT_QUOTES ] );
	}

	/**
	 * Render a specific template.
	 *
	 * @param string $template_name Name of the template to use.
	 * @param mixed  $data          Associative array of data to use for rendering.
	 * @return string Rendered result.
	 * @throws RuntimeException If the template file could not be located.
	 * @throws RuntimeException If the template file could not be loaded.
	 */
	public function render( $template_name, $data ) {
		if ( substr( $template_name, -9 ) !== '.mustache' ) {
			$template_name .= '.mustache';
		}

		$template_path = realpath( self::TEMPLATES_ROOT . $template_name );

		if ( false === $template_path || ! file_exists( $template_path ) ) {
			throw new RuntimeException(
				"Could not locate template file '{$template_name}'."
			);
		}

		$template = file_get_contents( $template_path );

		if ( false === $template ) {
			throw new RuntimeException(
				"Could not load template file '{$template_name}'."
			);
		}

		return $this->engine->render( $template, $data );
	}
}
