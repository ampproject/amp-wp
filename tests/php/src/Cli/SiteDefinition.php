<?php
/**
 * Site definition value object.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

final class SiteDefinition {

	/**
	 * Associative array of site definition data.
	 *
	 * @var array
	 */
	private $site_definition;

	/**
	 * SiteDefinition constructor.
	 *
	 * @param array $site_definition Associative array of site definition data.
	 */
	public function __construct( $site_definition ) {
		$this->site_definition = $this->validate( $site_definition );
	}

	/**
	 * Validate the site definition data and return its validated form.
	 *
	 * @param array $site_definition Associative array of site definition data.
	 * @return array Validated associative array of site definition data.
	 */
	private function validate( $site_definition ) {
		// @TODO: Add validation.
		return $site_definition;
	}

	/**
	 * Get the steps to perform for importing the site.
	 *
	 * @return mixed[] Associative array of steps to perform.
	 */
	public function get_import_steps() {
		return $this->site_definition['import-steps'];
	}

	/**
	 * Get the name of the reference site.
	 *
	 * @return string Name of the reference site.
	 */
	public function get_name() {
		return $this->site_definition['name'];
	}

	/**
	 * Get the description of the reference site.
	 *
	 * @return string Description of the reference site.
	 */
	public function get_description() {
		return $this->site_definition['description'];
	}

	/**
	 * Get the version of the reference site.
	 *
	 * @return string Version of the reference site.
	 */
	public function get_version() {
		return $this->site_definition['version'];
	}

	/**
	 * Get the tests that this reference site is supporting.
	 *
	 * @return string[] Tests supported by the reference site.
	 */
	public function get_tests() {
		return $this->site_definition['tests'];
	}

	/**
	 * Get the attributions for this reference site.
	 *
	 * @return string[] Attributions for the reference site.
	 */
	public function get_attributions() {
		return $this->site_definition['attributions'];
	}
}
