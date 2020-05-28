<?php
/**
 * Abstract seed base class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Seed;

use AmpProject\AmpWP\Tests\Cli\Seed;

final class Frontpage extends Seed {

	/**
	 * Get the name of the testable feature that is being seeded.
	 *
	 * @return string Name of the testable feature.
	 */
	public function get_feature_name() {
		return 'Front page';
	}

	/**
	 * Get the list of URLs that are meant to be tested for this seed.
	 *
	 * @return string[]
	 */
	public function get_urls() {
		return [ '/' ];
	}

	/**
	 * Process the seed.
	 *
	 * @return void
	 */
	public function process() {
		$frontpage_id = wp_insert_post( $this->get_frontpage_post_args() );
		update_option( 'page_on_front', $frontpage_id );
		update_option( 'show_on_front', 'page' );
	}

	/**
	 * Get the documentation fragment in markdown format.
	 *
	 * This is used to generate documentation for the entire seeded site.
	 *
	 * @return string Markdown fragment documenting the content that is seeded.
	 */
	public function get_documentation_markdown_fragment() {
		return <<<MARKDOWN
### Front page

A page with the slug 'frontpage' is added and WordPress is configured to use this page as the front page.

Accessible via the URL(s):
{$this->get_site_url()}/

Screenshot(s):
![Front page]({$this->get_screenshot_url( '/' )})
MARKDOWN;
	}

	/**
	 * Get the post arguments for the front page.
	 *
	 * @return array Associative array of post arguments.
	 */
	private function get_frontpage_post_args() {
		return [
			'name'    => 'frontpage',
			'content' => $this->get_frontpage_body(),
		];
	}

	/**
	 * Get the body of the front page.
	 *
	 * @return string HTML markup of the front page body.
	 */
	private function get_frontpage_body() {
		// @TODO Add meaningful front page test content.
		return <<<BODY
<p>Front page test content.</p>
BODY;
	}
}
