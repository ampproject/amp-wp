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
use WP_Error;
use WP_User;

final class ImportWxrFile implements ImportStep {

	/**
	 * Regular expression pattern for detecting upload URLs in post content.
	 *
	 * @var string
	 */
	const UPLOAD_URL_PATTERN = '#/wp-content/uploads/(\d+/\d+/.*?\.\w+)#i';

	/**
	 * Regular expression pattern for detecting a remote URL.
	 *
	 * @var string
	 */
	const IS_REMOTE_URL = '#^http(s)?://#i';

	/**
	 * Regular expression replacement string to use for adapting upload URLs in post content.
	 *
	 * This should contain one sprintf placeholder for the upload folder URL.
	 *
	 * @var string
	 */
	const UPLOAD_URL_REPLACEMENT = '%s/\1';

	/**
	 * File path to the WXR file to import.
	 *
	 * @var string
	 */
	private $wxr_file;

	/**
	 * Whether the WXR file is a temporary file and needs cleanup.
	 *
	 * @var bool
	 */
	private $wxr_is_temporary_file = false;

	/**
	 * ImportWxrFile constructor.
	 *
	 * @param string $wxr_file File path to the WXR file to import.
	 */
	public function __construct( $wxr_file ) {
		$this->wxr_file = $wxr_file;

		if ( preg_match( self::IS_REMOTE_URL, $this->wxr_file ) ) {
			$this->wxr_is_temporary_file = true;

			// Extract the file name from the URL.
			$file_name = basename( wp_parse_url( $this->wxr_file, PHP_URL_PATH ) );

			if ( ! $file_name ) {
				$file_name = md5( $this->wxr_file );
			}

			$tmp_file_name = wp_tempnam( $file_name );
			if ( ! $tmp_file_name ) {
				$message = __( 'Could not create temporary file for downloading remote WXR file.', 'amp' );
				WP_CLI::warning( $message );
				$this->wxr_file = '';
				return;
			}

			$context_options = [
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			];

			$data = file_get_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Needed for stream wrapper support.
				$this->wxr_file,
				false,
				stream_context_create( $context_options )
			);

			if ( false === $data || empty( $data ) ) {
				$message = __( 'Could not download remote WXR file: ', 'amp' ) . $this->wxr_file;
				WP_CLI::warning( $message );
				$this->wxr_file = '';
				return;
			}

			file_put_contents( $tmp_file_name, $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- Needed for stream wrapper support.
			$this->wxr_file = $tmp_file_name;
		}
	}

	public function __destruct() {
		if ( $this->wxr_is_temporary_file ) {
			unlink( $this->wxr_file );
		}
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		if ( empty( $this->wxr_file ) ) {
			throw new RuntimeException( 'Failed to retrieve WXR file, aborting.' );
		}

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
	 * @return array|int|WP_Error
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

		add_filter( 'wp_import_post_data_processed', [ $this, 'remove_guid' ], 10, 1 );
		add_filter( 'wp_import_post_data_processed', [ $this, 'adapt_image_links' ], 10, 1 );
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
		remove_filter( 'wp_import_post_data_processed', [ $this, 'adapt_image_links' ], 10 );
	}

	/**
	 * Log the processing of a post.
	 *
	 * @param array $postdata Post data that is being processed.
	 * @return array Post data.
	 */
	public function log_post_processing( $postdata ) {
		WP_CLI::log(
			WP_CLI::colorize(
				"Processing post %Y#{$postdata['post_id']}%n (%G'{$postdata['post_title']}'%n) (%B{$postdata['post_type']}%n)..."
			)
		);

		return $postdata;
	}

	/**
	 * Log the importing of a post.
	 *
	 * @param int|WP_Error $post_id ID of the post that was imported, or a WP_Error object.
	 */
	public function log_imported_post( $post_id ) {
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( '-- Error importing post: ' . $post_id->get_error_code() );
		} else {
			WP_CLI::log( "-- Imported post as post_id #{$post_id}" );
		}
	}

	/**
	 * Log the importing of a term.
	 *
	 * @param int   $term_id   ID of the term that was imported.
	 * @param array $term_data Term data that was imported.
	 */
	public function log_imported_term( $term_id, $term_data ) {
		WP_CLI::log( "-- Created term \"{$term_data['name']}\"" );
	}

	/**
	 * Log the addition of a taxonomy term.
	 *
	 * @param int[]  $taxonomy_term_ids Taxonomy term IDs.
	 * @param int[]  $term_ids          Term IDs.
	 * @param string $taxonomy          Taxonomy name
	 */
	public function log_associated_term( $taxonomy_term_ids, $term_ids, $taxonomy ) {
		WP_CLI::log(
			'-- Added terms (' . implode(
				',',
				$term_ids
			) . ") for taxonomy \"{$taxonomy}\""
		);
	}

	/**
	 * Log the importing of a comment.
	 *
	 * @param int $comment_id ID of the comment that was imported.
	 */
	public function log_imported_comment( $comment_id ) {
		WP_CLI::log(
			WP_CLI::colorize(
				"-- Added comment %Y#{$comment_id}%n"
			)
		);
	}

	/**
	 * Log the importing of post meta.
	 *
	 * @param int    $post_id ID of the post that meta was imported for.
	 * @param string $key     Key of the post meta that was imported.
	 */
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

	/**
	 * Adapt image URLs linked in post content.
	 *
	 * @param  array $postdata Post data.
	 * @return array Adapted post data.
	 */
	public function adapt_image_links( $postdata ) {
		if ( ! array_key_exists( 'post_content', $postdata ) || empty( $postdata['post_content'] ) ) {
			return $postdata;
		}

		$postdata['post_content'] = preg_replace(
			self::UPLOAD_URL_PATTERN,
			sprintf(
				self::UPLOAD_URL_REPLACEMENT,
				str_replace( 'http://', 'https://', content_url( 'uploads' ) )
			),
			$postdata['post_content']
		);

		return $postdata;
	}
}
