<?php
/**
 * Class OptimizerService.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Optimizer;

use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;

/**
 * Optimizer service that wraps the AMP Optimizer's TransformationEngine.
 *
 * @package AmpProject\AmpWP
 * @since 2.1.0
 * @internal
 */
final class OptimizerService implements Service {

	/**
	 * @var TransformationEngine
	 */
	private $transformation_engine;

	/**
	 * OptimizerService constructor.
	 *
	 * @param TransformationEngine $transformation_engine Transformation engine instance to use.
	 */
	public function __construct( TransformationEngine $transformation_engine ) {
		$this->transformation_engine = $transformation_engine;
	}

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document        $document DOM document to apply the transformations to.
	 * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
	 * @return void
	 */
	public function optimizeDom( Document $document, ErrorCollection $errors ) {
		$this->transformation_engine->optimizeDom( $document, $errors );
	}

	/**
	 * Apply transformations to the provided string of HTML markup.
	 *
	 * @param string          $html   HTML markup to apply the transformations to.
	 * @param ErrorCollection $errors Collection of errors that are collected during transformation.
	 * @return string Optimized HTML string.
	 */
	public function optimizeHtml( $html, ErrorCollection $errors ) {
		return $this->transformation_engine->optimizeHtml( $html, $errors );
	}
}
