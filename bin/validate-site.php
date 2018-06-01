<?php
/**
 * Crawls the entire site and validates it for AMP compatibility.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

namespace AMP;

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$count_post_types_and_taxonomies = count( get_post_types( array( 'public' => true ), 'names' ) ) + count( get_taxonomies( array( 'public' => true ) ) );
		$wp_cli_progress                 = \WP_CLI\Utils\make_progress_bar( 'Validating the URLs of the entire site', $count_post_types_and_taxonomies );
		$validation_result               = \AMP_Site_Validation::validate_entire_site_urls( $wp_cli_progress );
		$wp_cli_progress->finish();
		\WP_CLI::success( sprintf( '%d URLs were validated for AMP compatibility.', count( $validation_result ) ) );
	} catch ( \Exception $e ) {
		\WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "This script should be run with WP-CLI via: wp eval-file bin/validate-site.php\n";
	exit( 1 );
}
