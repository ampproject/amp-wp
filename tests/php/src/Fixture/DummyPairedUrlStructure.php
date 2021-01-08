<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\PairedUrlStructure;

final class DummyPairedUrlStructure extends PairedUrlStructure {

	/**
	 * Add amp subdomain to a URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with amp subdomain.
	 */
	public function add_endpoint( $url ) {
		if ( $this->has_endpoint( $url ) ) {
			return $url;
		}
		$slug = amp_get_slug();
		return preg_replace(
			'#^((\w+:)?//[^/]+)?/#',
			"$1/{$slug}/",
			$url
		);
	}

	/**
	 * Remove AMP path prefix from a URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL without amp subdomain.
	 */
	public function remove_endpoint( $url ) {
		return preg_replace(
			sprintf(
				'#^((\w+:)?//[^/]+)?/%s/#',
				preg_quote( amp_get_slug(), '#' )
			),
			'$1/',
			$url
		);
	}
}
