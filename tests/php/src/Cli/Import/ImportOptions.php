<?php
/**
 * Reference site import options step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImporter;
use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class ImportOptions implements ImportStep {

	/**
	 * Associative array of options to process.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * ImportOptions constructor.
	 *
	 * @param array $options Associative array of options to process.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		$count = 0;
		foreach ( $this->options as $key => $value ) {
			if ( null === $value ) {
				WP_CLI::log(
					WP_CLI::colorize(
						"Skipping empty option %G'{$key}'%n..."
					)
				);

				continue;
			}

			WP_CLI::log(
				WP_CLI::colorize(
					"Updating option %G'{$key}'%n..."
				)
			);

			switch ( $key ) {
				case 'woocommerce_shop_page_title':
				case 'woocommerce_cart_page_title':
				case 'woocommerce_checkout_page_title':
				case 'woocommerce_myaccount_page_title':
				case 'woocommerce_edit_address_page_title':
				case 'woocommerce_view_order_page_title':
				case 'woocommerce_change_password_page_title':
				case 'woocommerce_logout_page_title':
					// TODO: Translate WooCommerce page IDs.
					break;

				case 'page_for_posts':
				case 'page_on_front':
					if ( $this->update_page_id_option( $key, $value ) ) {
						++$count;
					}
					break;

				case 'woocommerce_product_cat':
					// TODO: Translate WooCommerce product category taxonomies.
					break;

				default:
					if ( update_option( $key, $value ) ) {
						++$count;
					}
					break;
			}
		}

		return $count;
	}

	/**
	 * Update option pointing to a page.
	 *
	 * This adapts the page ID as needed.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return bool Whether the update was successful.
	 */
	private function update_page_id_option( $key, $value ) {
		$page = get_page_by_title( $value );

		if ( ! is_object( $page ) ) {
			return false;
		}

		return update_option( $key, $page->ID );
	}

	/**
	 * Download image by URL.
	 *
	 * @param string $image_url Image URL to download.
	 * @return int|false Attachment ID of the image, or false if failed.
	 */
	private function download_image( $image_url = '' ) {
		$data = (object) ReferenceSiteImporter::sideload_image( $image_url );

		if ( is_wp_error( $data ) ) {
			WP_CLI::warning(
				WP_CLI::colorize(
					"Failed to download image %G'{$image_url}'%n."
				)
			);
			return false;
		}

		if ( empty( $data->attachment_id ) ) {
			WP_CLI::warning(
				WP_CLI::colorize(
					"Failed to retrieve attachment ID for downloaded image %G'{$image_url}'%n."
				)
			);
			return false;
		}

		return $data->attachment_id;
	}
}
