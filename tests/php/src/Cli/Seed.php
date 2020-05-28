<?php
/**
 * Abstract seed base class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

abstract class Seed {

	/**
	 * Get the name of the testable feature that is being seeded.
	 *
	 * @return string Name of the testable feature.
	 */
	abstract public function get_feature_name();

	/**
	 * Get the dependencies of the testable feature.
	 *
	 * The dependencies are an array of FQCNs to other testable feature seeds.
	 *
	 * @return string[]
	 */
	public function get_dependencies() {
		return [];
	}

	/**
	 * Get the list of URLs that are meant to be tested for this seed.
	 *
	 * @return string[]
	 */
	abstract public function get_urls();

	/**
	 * Process the seed.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Get the documentation fragment in markdown format.
	 *
	 * This is used to generate documentation for the entire seeded site.
	 *
	 * @return string Markdown fragment documenting the content that is seeded.
	 */
	abstract public function get_documentation_markdown_fragment();

	/**
	 * Get the site URL.
	 *
	 * The URL does not include the trailing slash.
	 *
	 * This is meant to be used within the documentation markdown fragments.
	 *
	 * @return string URL pointing to the root of the site.
	 */
	protected function get_site_url() {
		// @TODO Generate and return site URL.
		return '';
	}

	/**
	 * Get the URL of a screenshot to be used in markdown for a given page URL.
	 *
	 * This is meant to be used within the documentation markdown fragments.
	 *
	 * @param string $url Page URL To get the screenshot URL for.
	 * @return string URL pointing to the screenshot for the requested page URL.
	 */
	protected function get_screenshot_url( $url ) {
		// @TODO Generate and return screenshot URL.
		return $url;
	}
}
