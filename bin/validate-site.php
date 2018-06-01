<?php
/**
 * Crawls the entire site and validates it for AMP compatibility.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

namespace AMP;

/**
 * Crawl the entire site to validate it, and get the results.
 *
 * @return array[] {
 *     @type int  $number_crawled The number of URLs visited, regardless of the validation results.
 *     @type int  $number_invalid The number of URLs that have AMP validation error(s).
 * }
 */
function crawl_site() {
	\WP_CLI::log( 'Crawling the entire site to test for AMP validity. This might take a while...' );
	$count_post_types_and_taxonomies = count( get_post_types( array( 'public' => true ), 'names' ) ) + count( get_taxonomies( array( 'public' => true ) ) );
	$wp_cli_progress                 = \WP_CLI\Utils\make_progress_bar( 'Validating URLs...', $count_post_types_and_taxonomies );
	$number_crawled                  = count( \AMP_Site_Validation::validate_entire_site_urls( $wp_cli_progress ) );
	$wp_cli_progress->finish();

	$query_invalid_urls = new \WP_Query( array(
		'post_type'      => \AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
		'posts_per_page' => $number_crawled,
		'fields'         => 'ids',
	) );
	$number_invalid     = count( $query_invalid_urls->posts );

	return compact( 'number_crawled', 'number_invalid' );
}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$validation_counts = crawl_site();
		$url_more_details  = add_query_arg(
			'post_type',
			\AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			admin_url( 'edit.php' )
		);

		\WP_CLI::success(
			sprintf(
				"%d URLs were crawled, and %d have AMP validation issue(s).\nFor more details, please see: \n%s",
				$validation_counts['number_crawled'],
				$validation_counts['number_invalid'],
				$url_more_details
			)
		);
	} catch ( \Exception $e ) {
		\WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "Please run this script with WP-CLI via: wp eval-file bin/validate-site.php\n";
	exit( 1 );
}
