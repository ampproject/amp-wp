<?php
/**
 * Reference site import WXR file step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImporter;
use AmpProject\AmpWP\Tests\Cli\ImportStep;
use RuntimeException;
use stdClass;
use WP_CLI;
use WP_User;

final class ImportWxrFile implements ImportStep {

	/**
	 * File path to the WXR file to import.
	 *
	 * @var string
	 */
	private $wxr_file;

	/**
	 * ImportWxrFile constructor.
	 *
	 * @param string $wxr_file File path to the WXR file to import.
	 */
	public function __construct( $wxr_file ) {
		$this->wxr_file = $wxr_file;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		$importer    = new ReferenceSiteImporter();
		$import_data = $importer->parse( $this->wxr_file );

		if ( is_wp_error( $import_data ) ) {
			throw new RuntimeException( $import_data );
		}

		// Prepare the data to be used in process_author_mapping();
		$importer->get_authors_from_import( $import_data );

		// We no longer need the original data, so unset to avoid using excess
		// memory.
		unset( $import_data );

		$author_data = [];
		foreach ( $importer->authors as $wxr_author ) {
			$author = new stdClass();
			// Always in the WXR
			$author->user_login = $wxr_author['author_login'];

			// Should be in the WXR; no guarantees
			if ( isset( $wxr_author['author_email'] ) ) {
				$author->user_email = $wxr_author['author_email'];
			}
			if ( isset( $wxr_author['author_display_name'] ) ) {
				$author->display_name = $wxr_author['author_display_name'];
			}
			if ( isset( $wxr_author['author_first_name'] ) ) {
				$author->first_name = $wxr_author['author_first_name'];
			}
			if ( isset( $wxr_author['author_last_name'] ) ) {
				$author->last_name = $wxr_author['author_last_name'];
			}

			$author_data[] = $author;
		}

		// Build the author mapping
		$author_mapping = $this->create_authors_for_mapping( $author_data );
		if ( is_wp_error( $author_mapping ) ) {
			throw new RuntimeException( $author_mapping );
		}

		$author_in  = wp_list_pluck( $author_mapping, 'old_user_login' );
		$author_out = wp_list_pluck( $author_mapping, 'new_user_login' );
		unset( $author_mapping, $author_data );

		// $user_select needs to be an array of user IDs
		$user_select         = [];
		$invalid_user_select = [];
		foreach ( $author_out as $author_login ) {
			$user = get_user_by( 'login', $author_login );
			if ( $user ) {
				$user_select[] = $user->ID;
			} else {
				$invalid_user_select[] = $author_login;
			}
		}
		if ( ! empty( $invalid_user_select ) ) {
			throw new RuntimeException(
				sprintf(
					'These user_logins are invalid: %s',
					implode( ',', $invalid_user_select )
				)
			);
		}

		unset( $author_out );

		// Drive the import
		$importer->fetch_attachments = true;

		$_GET  = [
			'import' => 'wordpress',
			'step'   => 2,
		];
		$_POST = [
			'imported_authors'  => $author_in,
			'user_map'          => $user_select,
			'fetch_attachments' => $importer->fetch_attachments,
		];

		$GLOBALS['wpcli_import_current_file'] = basename( $this->wxr_file );
		$this->add_wxr_filters();
		ob_start();
		$importer->import( $this->wxr_file );
		ob_clean();
		$this->remove_wxr_filters();

		return count( $importer->processed_posts );
	}

	/**
	 * Creates users if they don't exist, and build an author mapping file.
	 *
	 * @param $author_data
	 * @return array|int|\WP_Error
	 */
	private function create_authors_for_mapping( $author_data ) {
		$author_mapping = [];
		foreach ( $author_data as $author ) {

			if ( isset( $author->user_email ) ) {
				$user = get_user_by( 'email', $author->user_email );
				if ( $user instanceof WP_User ) {
					$author_mapping[] = [
						'old_user_login' => $author->user_login,
						'new_user_login' => $user->user_login,
					];
					continue;
				}
			}

			$user = get_user_by( 'login', $author->user_login );
			if ( $user instanceof WP_User ) {
				$author_mapping[] = [
					'old_user_login' => $author->user_login,
					'new_user_login' => $user->user_login,
				];
				continue;
			}

			$user = [
				'user_login' => '',
				'user_email' => '',
				'user_pass'  => wp_generate_password(),
			];
			$user = array_merge( $user, (array) $author );

			$user_id = wp_insert_user( $user );
			if ( is_wp_error( $user_id ) ) {
				throw new RuntimeException( $user_id );
			}

			$user             = get_user_by( 'id', $user_id );
			$author_mapping[] = [
				'old_user_login' => $author->user_login,
				'new_user_login' => $user->user_login,
			];
		}
		return $author_mapping;
	}

	/**
	 * Add filters to shape WXR importer output.
	 */
	private function add_wxr_filters() {
		add_filter( 'wp_import_post_data_raw', [ $this, 'log_post_processing' ] );
		add_action( 'wp_import_insert_post', [ $this, 'log_imported_post' ], 10, 4 );
		add_action( 'wp_import_insert_term', [ $this, 'log_imported_term' ], 10, 4 );
		add_action( 'wp_import_set_post_terms', [ $this, 'log_associated_term' ], 10, 5 );
		add_action( 'wp_import_insert_comment', [ $this, 'log_imported_comment' ], 10, 4 );
		add_action( 'import_post_meta', [ $this, 'log_imported_post_meta' ], 10, 3 );
		add_filter( 'wp_import_post_data_processed', [ $this, 'remove_guid' ], 10, 2 );
	}

	/**
	 * Remove WXR importer output filters again.
	 */
	private function remove_wxr_filters() {
		remove_filter( 'wp_import_post_data_raw', [ $this, 'log_post_processing' ] );
		remove_action( 'wp_import_insert_post', [ $this, 'log_imported_post' ], 10 );
		remove_action( 'wp_import_insert_term', [ $this, 'log_imported_term' ], 10 );
		remove_action( 'wp_import_set_post_terms', [ $this, 'log_associated_term' ], 10 );
		remove_action( 'wp_import_insert_comment', [ $this, 'log_imported_comment' ], 10 );
		remove_action( 'import_post_meta', [ $this, 'log_imported_post_meta' ], 10 );
		remove_filter( 'wp_import_post_data_processed', [ $this, 'remove_guid' ], 10 );
	}

	public function log_post_processing( $post ) {
		WP_CLI::log(
			WP_CLI::colorize(
				"Processing post %Y#{$post['post_id']}%n (%G'{$post['post_title']}'%n) (%B{$post['post_type']}%n)..."
			)
		);

		return $post;
	}

	public function log_imported_post( $post_id ) {
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( '-- Error importing post: ' . $post_id->get_error_code() );
		} else {
			WP_CLI::log( "-- Imported post as post_id #{$post_id}" );
		}
	}

	public function log_imported_term( $t, $import_term ) {
		WP_CLI::log( "-- Created term \"{$import_term['name']}\"" );
	}

	public function log_associated_term( $tt_ids, $term_ids, $taxonomy ) {
		WP_CLI::log(
			'-- Added terms (' . implode(
				',',
				$term_ids
			) . ") for taxonomy \"{$taxonomy}\""
		);
	}

	public function log_imported_comment( $comment_id ) {
		WP_CLI::log(
			WP_CLI::colorize(
				"-- Added comment %Y#{$comment_id}%n"
			)
		);
	}

	public function log_imported_post_meta( $post_id, $key ) {
		WP_CLI::log(
			WP_CLI::colorize(
				"-- Added post_meta %G'{$key}'%n"
			)
		);
	}

	/**
	 * Remove GUID from post data.
	 *
	 * @param  array $postdata Post data.
	 * @return array Adapted post data.
	 */
	public function remove_guid( $postdata ) {
		$postdata['guid'] = '';

		return $postdata;
	}
}
