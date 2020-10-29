<?php
/**
 * Reference site import site meta information step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ImportStep;
use AmpProject\AmpWP\Tests\Cli\SiteDefinition;

final class ImportSiteMeta implements ImportStep {

	/**
	 * Site definition to use.
	 *
	 * @var SiteDefinition
	 */
	private $site_definition;

	/**
	 * ImportSiteMeta constructor.
	 *
	 * @param SiteDefinition $site_definition Site definition to use.
	 */
	public function __construct( SiteDefinition $site_definition ) {
		$this->site_definition = $site_definition;
	}

	/**
	 * Process the import step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		$count = 0;

		$options = [
			'blogname'        => $this->site_definition->get_name(),
			'blogdescription' => $this->site_definition->get_description(),
		];

		foreach ( $options as $key => $value ) {
			if ( update_option( $key, $value ) ) {
				++$count;
			}
		}

		return $count;
	}
}
