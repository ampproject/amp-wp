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
		update_option( 'blogname', $this->site_definition->get_name() );
		update_option( 'blogdescription', $this->site_definition->get_description() );

		return 2;
	}
}
